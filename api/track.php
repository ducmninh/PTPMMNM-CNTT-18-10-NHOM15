<?php
// api/track.php
// Đề tài 17: API Tiếp nhận sự kiện theo dõi website (Hỗ trợ phân loại Cafe & Trà Sữa)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($requestMethod !== 'POST' && $requestMethod !== 'CLI') {
    http_response_code(405);
    echo json_encode(['status' => 'METHOD_NOT_ALLOWED', 'error' => 'Chỉ chấp nhận phương thức POST']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'INVALID_JSON', 'error' => 'Dữ liệu JSON không hợp lệ']);
    exit;
}

// 1. KIỂM TRA BẢO MẬT & TRƯỜNG CẤM (Yêu cầu 1 & 2: Không thu thập mật khẩu/dữ liệu form gõ vào)
$forbiddenKeys = ['password', 'mat_khau', 'pass', 'pwd', 'credit_card', 'cc_number', 'cvv', 'secret'];
foreach ($forbiddenKeys as $fKey) {
    if (isset($data[$fKey])) {
        // Ghi nhận lỗi vi phạm an toàn với GIỚI HẠN CỨNG 100 lỗi
        logIngestionError($pdo, null, $data['event_id'] ?? 'unknown', 'FORBIDDEN_INPUT_DATA', 
            "Phát hiện dữ liệu chứa trường cấm bảo mật [$fKey]. Từ chối tiếp nhận.");

        http_response_code(400);
        echo json_encode([
            'status' => 'FORBIDDEN_INPUT_DATA',
            'error' => "Vi phạm chính sách: Dữ liệu chứa trường cấm bảo mật [$fKey]. Không thu thập mật khẩu hoặc nội dung nhạy cảm."
        ]);
        exit;
    }
}

// 2. KIỂM TRA CÁC TRƯỜNG BẮT BUỘC
$siteKey = trim($data['site_key'] ?? '');
$sessionId = trim($data['session_id'] ?? '');
$eventId = trim($data['event_id'] ?? '');
$eventCode = trim($data['event_code'] ?? '');
$pageUrl = trim($data['page_url'] ?? '');
$elementText = trim($data['element_text'] ?? '');
$category = trim($data['category'] ?? 'general');
$itemName = trim($data['item_name'] ?? '');
$userGender = trim($data['user_gender'] ?? 'unspecified');
$clientTimeRaw = trim($data['client_time'] ?? '');

if (!$siteKey || !$sessionId || !$eventId || !$eventCode || !$pageUrl) {
    http_response_code(400);
    echo json_encode(['status' => 'MISSING_FIELDS', 'error' => 'Thiếu các trường bắt buộc (site_key, session_id, event_id, event_code, page_url)']);
    exit;
}

// 3. XÁC THỰC WEBSITE
$stmtSite = $pdo->prepare("SELECT id, name FROM websites WHERE site_key = :site_key");
$stmtSite->execute([':site_key' => $siteKey]);
$site = $stmtSite->fetch();

if (!$site) {
    // Lưu lỗi INVALID_SITE với GIỚI HẠN CỨNG 100 lỗi
    logIngestionError($pdo, null, $eventId, 'INVALID_SITE', "Site key [$siteKey] không tồn tại trong hệ thống đăng ký.");

    http_response_code(403);
    echo json_encode(['status' => 'INVALID_SITE', 'error' => "Website với mã [$siteKey] chưa được đăng ký hoặc không được phép theo dõi"]);
    exit;
}
$siteId = (int)$site['id'];

// 4. KIỂM TRA LOẠI SỰ KIỆN CÓ ĐƯỢC PHÉP TRÊN SITE (Hỗ trợ button_click, btn_click, btn_consult...)
$isBtn = (strpos($eventCode, 'btn') !== false || strpos($eventCode, 'button') !== false) ? 1 : 0;
$stmtEvtDef = $pdo->prepare("SELECT allowed FROM event_definitions 
    WHERE site_id = :site_id 
      AND (event_code = :code OR (:is_btn = 1 AND event_code IN ('button_click', 'btn_click', 'btn_consult', 'btn_cart')))
    ORDER BY allowed DESC LIMIT 1");
$stmtEvtDef->execute([':site_id' => $siteId, ':code' => $eventCode, ':is_btn' => $isBtn]);
$evtDef = $stmtEvtDef->fetch();
if (!$evtDef || $evtDef['allowed'] != 1) {
    http_response_code(403);
    echo json_encode(['status' => 'EVENT_NOT_ALLOWED', 'error' => "Loại sự kiện [$eventCode] đang bị tắt hoặc chưa được cấu hình cho website này"]);
    exit;
}

// 5. MASK IP ĐỂ BẢO VỆ RIÊNG TƯ (Yêu cầu đề tài: Không lưu địa chỉ IP đầy đủ)
$rawIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$ipParts = explode('.', $rawIp);
if (count($ipParts) === 4) {
    $ipPrefix = "{$ipParts[0]}.{$ipParts[1]}.{$ipParts[2]}.xxx";
} else {
    $ipPrefix = "masked_ip";
}

// 6. XỬ LÝ MÚI GIỜ & THỜI GIAN (Yêu cầu 2: Quy định rõ múi giờ Asia/Ho_Chi_Minh GMT+7, độ trễ hợp lý)
$serverTime = date('Y-m-d H:i:s');
if ($clientTimeRaw) {
    $clientTimestamp = strtotime($clientTimeRaw);
    if ($clientTimestamp === false) {
        $clientTime = $serverTime;
    } else {
        $clientTime = date('Y-m-d H:i:s', $clientTimestamp);
    }
} else {
    $clientTime = $serverTime;
}

// 7. TÍNH BẢN HASH NỘI DUNG (PAYLOAD HASH)
// Nhằm phát hiện gửi lặp (cùng ID, cùng nội dung) HOẶC xung đột (cùng ID, khác nội dung)
$contentForHash = json_encode([
    'site_id' => $siteId,
    'session_id' => $sessionId,
    'event_code' => $eventCode,
    'page_url' => $pageUrl,
    'element_text' => $elementText,
    'category' => $category,
    'item_name' => $itemName
]);
$payloadHash = md5($contentForHash);

// 8. KIỂM TRA XUNG ĐỘT HOẶC TRÙNG LẶP SỰ KIỆN (Yêu cầu 1: Gửi lại cùng mã không tăng số đếm; cùng mã khác nội dung bị từ chối 409)
$stmtExisting = $pdo->prepare("SELECT event_id, payload_hash, is_duplicate FROM events WHERE event_id = :event_id");
$stmtExisting->execute([':event_id' => $eventId]);
$existingEvent = $stmtExisting->fetch();

if ($existingEvent) {
    if ($existingEvent['payload_hash'] === $payloadHash) {
        // CÙNG MÃ, CÙNG NỘI DUNG: Ghi nhận lặp lại, KHÔNG tăng số đếm thực tế
        $stmtDup = $pdo->prepare("UPDATE events SET is_duplicate = is_duplicate + 1 WHERE event_id = :event_id");
        $stmtDup->execute([':event_id' => $eventId]);

        http_response_code(200);
        echo json_encode([
            'status' => 'DUPLICATE_IGNORED',
            'message' => 'Sự kiện đã tồn tại với cùng nội dung. Hệ thống xác nhận không đếm đôi.',
            'event_id' => $eventId
        ]);
        exit;
    } else {
        // CÙNG MÃ, NHƯNG KHÁC NỘI DUNG -> XUNG ĐỘT (HTTP 409 CONFLICT)
        // Ghi nhận lỗi xung đột với GIỚI HẠN CỨNG 100 lỗi
        logIngestionError($pdo, $siteId, $eventId, 'CONFLICT_PAYLOAD', 
            "Xung đột: Mã sự kiện [$eventId] gửi lại với nội dung khác so với bản ghi ban đầu. Từ chối sửa dữ liệu cũ.");

        http_response_code(409);
        echo json_encode([
            'status' => 'CONFLICT_PAYLOAD',
            'error' => "Xung đột (HTTP 409): Sự kiện [$eventId] đã tồn tại nhưng có nội dung khác. Máy chủ từ chối sửa đổi dữ liệu đã chốt."
        ]);
        exit;
    }
}

$customerName = trim($data['customer_name'] ?? '');
$customerAddress = trim($data['customer_address'] ?? '');
$formName = trim($data['form_name'] ?? '');

if (empty($formName)) {
    if ($eventCode === 'form_start' || $eventCode === 'form_complete') {
        $lowText = mb_strtolower($elementText . ' ' . $itemName . ' ' . $pageUrl, 'UTF-8');
        if (strpos($lowText, 'đặt chỗ') !== false || strpos($lowText, 'đặt bàn') !== false || strpos($lowText, 'reserve') !== false || strpos($lowText, 'giữ bàn') !== false || strpos($lowText, 'tài liệu') !== false || strpos($lowText, 'brochure') !== false || strpos($lowText, 'menu') !== false || strpos($lowText, 'doc') !== false) {
            $formName = 'Lịch Đặt Chỗ & Giữ Bàn NOIR CAFÉ';
        } elseif (strpos($lowText, 'thanh toán') !== false || strpos($lowText, 'đặt hàng') !== false || strpos($lowText, 'checkout') !== false || strpos($lowText, 'đơn') !== false) {
            $formName = 'Biểu Mẫu Đặt Món & Thanh Toán Giao Hàng';
        } elseif (strpos($lowText, 'đăng ký') !== false || strpos($lowText, 'register') !== false) {
            $formName = 'Biểu Mẫu Đăng Ký Thành Viên';
        } elseif (strpos($lowText, 'đăng nhập') !== false || strpos($lowText, 'login') !== false) {
            $formName = 'Biểu Mẫu Đăng Nhập Hệ Thống';
        } else {
            $formName = 'Biểu Mẫu Tương Tác Trực Tuyến';
        }
    } elseif ($eventCode === 'btn_click') {
        $lowText = mb_strtolower($elementText . ' ' . $itemName, 'UTF-8');
        if (strpos($lowText, 'tài liệu') !== false || strpos($lowText, 'brochure') !== false) {
            $formName = 'Nút Bấm Tải Brochure / Menu';
        } elseif (strpos($lowText, 'cà phê') !== false || strpos($lowText, 'coffee') !== false) {
            $formName = 'Thực Đơn Cà Phê Chuẩn Gu';
        } elseif (strpos($lowText, 'trà sữa') !== false || strpos($lowText, 'tea') !== false) {
            $formName = 'Thực Đơn Trà Sữa Đậm Vị';
        } else {
            $formName = 'Khu Vực Tương Tác Món';
        }
    } else {
        $formName = 'Trang Chủ & Danh Mục Món';
    }
}

// 9. CẬP NHẬT HOẶC TẠO MỚI PHIÊN (SESSION)
$stmtSession = $pdo->prepare("SELECT session_id, user_gender, customer_name, customer_address FROM sessions WHERE session_id = :session_id");
$stmtSession->execute([':session_id' => $sessionId]);
$existingSession = $stmtSession->fetch();

if ($existingSession) {
    if (empty($customerName) && !empty($existingSession['customer_name'])) {
        $customerName = $existingSession['customer_name'];
    }
    if (empty($customerAddress) && !empty($existingSession['customer_address'])) {
        $customerAddress = $existingSession['customer_address'];
    }
}

if (empty($customerName)) {
    if ($userGender === 'male') {
        $customerName = 'Khách Nam (Trực tuyến)';
    } elseif ($userGender === 'female') {
        $customerName = 'Khách Nữ (Trực tuyến)';
    } else {
        $customerName = 'Khách Vãng Lai';
    }
}

if (empty($customerAddress)) {
    $customerAddress = "Chưa cập nhật (IP: {$ipPrefix})";
}

if ($existingSession) {
    $currentGender = ($userGender !== 'unspecified') ? $userGender : $existingSession['user_gender'];
    $stmtUpSession = $pdo->prepare("UPDATE sessions SET last_seen = :now, user_gender = :gender, customer_name = :cust_name, customer_address = :cust_addr WHERE session_id = :session_id");
    $stmtUpSession->execute([
        ':now' => $serverTime,
        ':gender' => $currentGender,
        ':cust_name' => $customerName,
        ':cust_addr' => $customerAddress,
        ':session_id' => $sessionId
    ]);
} else {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $stmtNewSession = $pdo->prepare("INSERT INTO sessions (session_id, site_id, user_gender, customer_name, customer_address, ip_prefix, user_agent, first_seen, last_seen) 
        VALUES (:session_id, :site_id, :gender, :cust_name, :cust_addr, :ip_prefix, :user_agent, :now, :now)");
    $stmtNewSession->execute([
        ':session_id' => $sessionId,
        ':site_id' => $siteId,
        ':gender' => $userGender,
        ':cust_name' => $customerName,
        ':cust_addr' => $customerAddress,
        ':ip_prefix' => $ipPrefix,
        ':user_agent' => $userAgent,
        ':now' => $serverTime
    ]);
}

// 10. LƯU SỰ KIỆN MỚI HỢP LỆ VÀO DATABASE
$stmtInsert = $pdo->prepare("INSERT INTO events (
    event_id, site_id, session_id, event_code, page_url, element_text, 
    category, item_name, user_gender, customer_name, customer_address, form_name, payload_hash, client_time, server_time, is_duplicate
) VALUES (
    :event_id, :site_id, :session_id, :event_code, :page_url, :element_text,
    :category, :item_name, :user_gender, :customer_name, :customer_address, :form_name, :payload_hash, :client_time, :server_time, 0
)");

$stmtInsert->execute([
    ':event_id' => $eventId,
    ':site_id' => $siteId,
    ':session_id' => $sessionId,
    ':event_code' => $eventCode,
    ':page_url' => $pageUrl,
    ':element_text' => $elementText,
    ':category' => $category,
    ':item_name' => $itemName,
    ':user_gender' => $userGender,
    ':customer_name' => $customerName,
    ':customer_address' => $customerAddress,
    ':form_name' => $formName,
    ':payload_hash' => $payloadHash,
    ':client_time' => $clientTime,
    ':server_time' => $serverTime
]);

http_response_code(201);
echo json_encode([
    'status' => 'SUCCESS',
    'message' => 'Ghi nhận sự kiện thành công',
    'event_id' => $eventId,
    'site_id' => $siteId
]);
