<?php
// api/event_manager.php
// Đề tài 17: Quản Lý & Cấu Hình Sự Kiện Thu Thập, Phân Quyền Xem & Đăng Ký Website (Yêu Cầu 1)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

// Kiểm tra quyền: Ưu tiên session đăng nhập, hoặc fallback tham số role nếu test API
$userRole = $_SESSION['admin_role'] ?? ($_GET['user_role'] ?? 'admin');
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    if ($jsonData) {
        $postData = $jsonData;
        if (isset($jsonData['action'])) {
            $action = $jsonData['action'];
        }
    } else {
        $postData = $_POST;
    }
} else {
    $postData = $_GET;
}

try {
    // 1. ACTION: LIST SỰ KIỆN & WEBSITES
    if ($action === 'list') {
        $siteId = isset($_GET['site_id']) ? (int)$_GET['site_id'] : 0;
        
        // Lấy danh sách website
        $stmtSites = $pdo->query("SELECT id, site_key, name, allowed_domains, created_at FROM websites ORDER BY id ASC");
        $websites = $stmtSites->fetchAll();

        // Lấy danh sách sự kiện theo website hoặc toàn bộ
        if ($siteId > 0) {
            $stmtEvts = $pdo->prepare("SELECT ed.*, w.name as site_name, w.site_key,
                (SELECT COUNT(*) FROM events e WHERE e.site_id = ed.site_id AND e.event_code = ed.event_code) as tracked_count
                FROM event_definitions ed
                JOIN websites w ON ed.site_id = w.id
                WHERE ed.site_id = :sid
                ORDER BY ed.id ASC");
            $stmtEvts->execute([':sid' => $siteId]);
        } else {
            $stmtEvts = $pdo->query("SELECT ed.*, w.name as site_name, w.site_key,
                (SELECT COUNT(*) FROM events e WHERE e.site_id = ed.site_id AND e.event_code = ed.event_code) as tracked_count
                FROM event_definitions ed
                JOIN websites w ON ed.site_id = w.id
                ORDER BY ed.site_id ASC, ed.id ASC");
        }
        $events = $stmtEvts->fetchAll();

        echo json_encode([
            'status' => 'SUCCESS',
            'websites' => $websites,
            'events' => $events,
            'current_user_role' => $userRole
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. ACTION: TOGGLE BẬT / TẮT SỰ KIỆN (Allowed Switch)
    if ($action === 'toggle_allowed') {
        if (!in_array($userRole, ['admin', 'manager'])) {
            http_response_code(403);
            echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Chỉ Admin hoặc Manager mới có quyền thay đổi cấu hình sự kiện']);
            exit;
        }

        $id = (int)($postData['id'] ?? 0);
        $allowed = isset($postData['allowed']) ? (int)$postData['allowed'] : 1;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'INVALID_ID', 'error' => 'ID sự kiện không hợp lệ']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE event_definitions SET allowed = :allowed WHERE id = :id");
        $stmt->execute([':allowed' => $allowed ? 1 : 0, ':id' => $id]);

        // Lấy lại thông tin dòng vừa sửa
        $stmtInfo = $pdo->prepare("SELECT * FROM event_definitions WHERE id = :id");
        $stmtInfo->execute([':id' => $id]);
        $updatedEvent = $stmtInfo->fetch();

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => $allowed ? "Đã KÍCH HOẠT theo dõi sự kiện [{$updatedEvent['name']}]" : "Đã TẮT TẠM DỪNG theo dõi sự kiện [{$updatedEvent['name']}]",
            'event' => $updatedEvent
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. ACTION: CREATE SỰ KIỆN MỚI
    if ($action === 'create_event') {
        if (!in_array($userRole, ['admin', 'manager'])) {
            http_response_code(403);
            echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Bạn không có quyền thêm mới sự kiện']);
            exit;
        }

        $siteId = (int)($postData['site_id'] ?? 1);
        $name = trim($postData['name'] ?? '');
        $eventCode = trim($postData['event_code'] ?? '');
        $eventType = trim($postData['event_type'] ?? 'button');
        $targetUrl = trim($postData['target_url'] ?? '*');
        $elementSelector = trim($postData['element_selector'] ?? '*');
        $minRole = trim($postData['min_role'] ?? 'viewer');
        $allowed = isset($postData['allowed']) ? (int)$postData['allowed'] : 1;

        if (!$name || !$eventCode) {
            http_response_code(400);
            echo json_encode(['status' => 'MISSING_FIELDS', 'error' => 'Vui lòng nhập Tên sự kiện và Mã sự kiện (Event Code)']);
            exit;
        }

        // Chuẩn hóa event_code (chữ thường, gạch dưới)
        $eventCode = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($eventCode));

        $stmt = $pdo->prepare("INSERT INTO event_definitions 
            (site_id, event_code, name, event_type, target_url, element_selector, min_role, allowed) 
            VALUES (:site_id, :event_code, :name, :event_type, :target_url, :element_selector, :min_role, :allowed)");
        $stmt->execute([
            ':site_id' => $siteId,
            ':event_code' => $eventCode,
            ':name' => $name,
            ':event_type' => $eventType,
            ':target_url' => $targetUrl ?: '*',
            ':element_selector' => $elementSelector ?: '*',
            ':min_role' => $minRole,
            ':allowed' => $allowed ? 1 : 0
        ]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => "Đã tạo thành công sự kiện mới: [$name] (Mã: $eventCode)",
            'id' => $newId
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. ACTION: DELETE SỰ KIỆN
    if ($action === 'delete_event') {
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Chỉ quản trị viên cấp cao (Admin) mới có quyền xóa cấu hình sự kiện']);
            exit;
        }

        $id = (int)($postData['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'INVALID_ID', 'error' => 'ID sự kiện không hợp lệ']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM event_definitions WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => "Đã xóa cấu hình sự kiện thành công!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5. ACTION: REGISTER WEBSITE MỚI
    if ($action === 'register_site') {
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Chỉ quản trị viên cấp cao (Admin) mới có quyền đăng ký website mới']);
            exit;
        }

        $siteName = trim($postData['name'] ?? '');
        $siteKey = trim($postData['site_key'] ?? '');
        $allowedDomains = trim($postData['allowed_domains'] ?? 'localhost, 127.0.0.1');

        if (!$siteName || !$siteKey) {
            http_response_code(400);
            echo json_encode(['status' => 'MISSING_FIELDS', 'error' => 'Vui lòng nhập Tên Website và Mã Site Key']);
            exit;
        }

        // Kiểm tra trùng site_key
        $stmtCheck = $pdo->prepare("SELECT id FROM websites WHERE site_key = :k");
        $stmtCheck->execute([':k' => $siteKey]);
        if ($stmtCheck->fetch()) {
            http_response_code(400);
            echo json_encode(['status' => 'DUPLICATE_KEY', 'error' => "Mã Site Key [$siteKey] đã được sử dụng. Vui lòng chọn mã khác"]);
            exit;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO websites (site_key, name, allowed_domains, created_at) VALUES (:k, :n, :d, :now)");
        $stmt->execute([':k' => $siteKey, ':n' => $siteName, ':d' => $allowedDomains, ':now' => $now]);
        $newSiteId = (int)$pdo->lastInsertId();

        // Tạo sẵn các cấu hình sự kiện chuẩn cho website mới
        $defaultRules = [
            ['page_view', 'Lượt xem trang (Page View)', 'page', '*', 'Window / Navigation', 'viewer', 1],
            ['btn_click', 'Bấm nút tương tác (Button Click)', 'button', '*', '.btn, button, [role=button]', 'viewer', 1],
            ['form_start', 'Mở biểu mẫu tương tác (Form Start)', 'form', '*', 'form input, form textarea', 'manager', 1],
            ['form_complete', 'Hoàn tất gửi biểu mẫu (Form Complete)', 'form', '*', 'form', 'admin', 1]
        ];

        $stmtDef = $pdo->prepare("INSERT INTO event_definitions 
            (site_id, event_code, name, event_type, target_url, element_selector, min_role, allowed) 
            VALUES (:sid, :code, :name, :type, :url, :selector, :role, :allowed)");

        foreach ($defaultRules as $r) {
            $stmtDef->execute([
                ':sid' => $newSiteId,
                ':code' => $r[0],
                ':name' => $r[1],
                ':type' => $r[2],
                ':url' => $r[3],
                ':selector' => $r[4],
                ':role' => $r[5],
                ':allowed' => $r[6]
            ]);
        }

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => "Đăng ký thành công website mới: [$siteName] (ID: $newSiteId)",
            'site_id' => $newSiteId
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(400);
    echo json_encode(['status' => 'UNKNOWN_ACTION', 'error' => "Hành động [$action] không được hỗ trợ"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'SERVER_ERROR', 'error' => $e->getMessage()]);
}
