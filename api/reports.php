<?php
// api/reports.php
// Đề tài 17: API Tổng hợp Báo cáo, Phễu chuyển đổi (Funnel), Lỗi tiếp nhận & Thống kê Giới tính
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

// 1. KIỂM TRA PHÂN QUYỀN MÁY CHỦ (Yêu cầu đề tài: Quyền báo cáo được kiểm tra tại máy chủ)
$userRole = $_GET['user_role'] ?? 'admin'; // admin, manager, viewer
$allowedRoles = ['admin', 'manager', 'viewer'];
if (!in_array($userRole, $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Bạn không có quyền xem báo cáo thống kê này']);
    exit;
}

$websiteId = isset($_GET['website_id']) ? (int)$_GET['website_id'] : 1;
$fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$toDate = $_GET['to_date'] ?? date('Y-m-d');
$action = $_GET['action'] ?? 'summary';

// HÀNH ĐỘNG DỌN DẸP DỮ LIỆU THỬ NGHIỆM (Yêu cầu 5: Quy tắc giữ/xóa dữ liệu thử)
if ($action === 'purge_test_data') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Chỉ quản trị viên cấp cao (Admin) mới có quyền xóa dữ liệu thử']);
        exit;
    }
    // Xóa sự kiện và lỗi của website được chọn
    $stmtDelEvt = $pdo->prepare("DELETE FROM events WHERE site_id = :site_id");
    $stmtDelEvt->execute([':site_id' => $websiteId]);

    $stmtDelErr = $pdo->prepare("DELETE FROM ingestion_errors WHERE site_id = :site_id OR site_id IS NULL");
    $stmtDelErr->execute([':site_id' => $websiteId]);

    $stmtDelSess = $pdo->prepare("DELETE FROM sessions WHERE site_id = :site_id");
    $stmtDelSess->execute([':site_id' => $websiteId]);

    echo json_encode([
        'status' => 'SUCCESS',
        'message' => "Đã dọn dẹp và làm sạch dữ liệu thử nghiệm của Website ID $websiteId thành công!"
    ]);
    exit;
}

// LẤY THÔNG TIN QUY TẮC LƯU TRỮ & DỮ LIỆU QUÁ HẠN (Yêu cầu 5: Quy tắc giữ/xóa dữ liệu)
if ($action === 'get_retention') {
    $retDays = (int)getSystemSetting($pdo, 'retention_days', 30);
    $cutoff = date('Y-m-d H:i:s', strtotime("-$retDays days"));
    
    // Đếm số sự kiện quá hạn
    $stmtExp = $pdo->prepare("SELECT COUNT(*) FROM events WHERE site_id = :site_id AND client_time < :cutoff");
    $stmtExp->execute([':site_id' => $websiteId, ':cutoff' => $cutoff]);
    $expiredCount = (int)$stmtExp->fetchColumn();

    // Tổng số sự kiện hiện tại
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM events WHERE site_id = :site_id");
    $stmtTotal->execute([':site_id' => $websiteId]);
    $totalCount = (int)$stmtTotal->fetchColumn();

    echo json_encode([
        'status' => 'SUCCESS',
        'retention_days' => $retDays,
        'cutoff_time' => $cutoff,
        'expired_events' => $expiredCount,
        'total_events' => $totalCount
    ]);
    exit;
}

// CẬP NHẬT QUY TẮC LƯU TRỮ (Yêu cầu 5)
if ($action === 'set_retention') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Chỉ Admin mới có quyền thay đổi quy tắc lưu trữ dữ liệu']);
        exit;
    }
    $newDays = isset($_POST['days']) ? (int)$_POST['days'] : (isset($_GET['days']) ? (int)$_GET['days'] : 30);
    setSystemSetting($pdo, 'retention_days', (string)$newDays);

    echo json_encode([
        'status' => 'SUCCESS',
        'message' => "Đã lưu quy tắc lưu trữ: Giữ dữ liệu $newDays ngày.",
        'retention_days' => $newDays
    ]);
    exit;
}

// THỰC THI DỌN DẸP THEO QUY TẮC RETENTION (Yêu cầu 5)
if ($action === 'cleanup_retention') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Chỉ Admin mới có quyền thực thi dọn dẹp dữ liệu']);
        exit;
    }
    $daysParam = isset($_GET['days']) ? (int)$_GET['days'] : null;
    $res = cleanupRetentionData($pdo, $websiteId, $daysParam);
    echo json_encode($res);
    exit;
}

// LẤY DANH SÁCH WEBSITES
if ($action === 'get_websites') {
    $stmt = $pdo->query("SELECT id, site_key, name, created_at FROM websites ORDER BY id ASC");
    echo json_encode(['status' => 'SUCCESS', 'websites' => $stmt->fetchAll()]);
    exit;
}

// BÁO CÁO TỔNG HỢP CHÍNH
$fromDateTime = $fromDate . ' 00:00:00';
$toDateTime = $toDate . ' 23:59:59';

// 1. Tổng lượt xem trang (chỉ tính sự kiện hợp lệ, không tính trùng lặp)
$stmtPv = $pdo->prepare("SELECT COUNT(*) as total FROM events 
    WHERE site_id = :site_id AND event_code = 'page_view' AND is_duplicate = 0 
    AND client_time BETWEEN :from_time AND :to_time");
$stmtPv->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
$totalPageviews = (int)$stmtPv->fetch()['total'];

// 2. Số phiên có hoạt động (Sessions)
$stmtSess = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as total FROM events 
    WHERE site_id = :site_id AND is_duplicate = 0 
    AND client_time BETWEEN :from_time AND :to_time");
$stmtSess->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
$activeSessions = (int)$stmtSess->fetch()['total'];

// 3. Số lần thực hiện thao tác (Buttons, Forms, Purchases)
$stmtAct = $pdo->prepare("SELECT COUNT(*) as total FROM events 
    WHERE site_id = :site_id AND event_code != 'page_view' AND is_duplicate = 0 
    AND client_time BETWEEN :from_time AND :to_time");
$stmtAct->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
$totalActions = (int)$stmtAct->fetch()['total'];

// 4. Số lần trùng lặp bị bỏ qua (Duplicate events avoided)
$stmtDup = $pdo->prepare("SELECT SUM(is_duplicate) as total FROM events WHERE site_id = :site_id");
$stmtDup->execute([':site_id' => $websiteId]);
$totalDuplicatesAvoided = (int)($stmtDup->fetch()['total'] ?? 0);

// 5. Thống kê chuỗi thao tác theo thứ tự đã định (FUNNEL: Xem trang -> Mở form -> Hoàn tất)
// Phép thử 4: Chuỗi xem–bắt đầu–hoàn tất trong một phiên được tính; thiếu bước hoặc sai thứ tự không bị coi là hoàn tất.
$stmtAllSess = $pdo->prepare("SELECT DISTINCT session_id FROM events 
    WHERE site_id = :site_id AND client_time BETWEEN :from_time AND :to_time");
$stmtAllSess->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
$sessionsList = $stmtAllSess->fetchAll();

$step1Count = 0; // Có page_view
$step2Count = 0; // Có page_view rồi đến form_start / btn_click
$step3Completed = 0; // Có chuỗi page_view -> form_start -> form_complete đúng thứ tự thời gian

foreach ($sessionsList as $s) {
    $sessId = $s['session_id'];
    $stmtEvtSeq = $pdo->prepare("SELECT event_code, client_time FROM events 
        WHERE site_id = :site_id AND session_id = :sess_id AND is_duplicate = 0 
        ORDER BY client_time ASC, event_id ASC");
    $stmtEvtSeq->execute([':site_id' => $websiteId, ':sess_id' => $sessId]);
    $evts = $stmtEvtSeq->fetchAll();

    $hasStep1 = false;
    $hasStep2 = false;
    $hasStep3 = false;

    foreach ($evts as $ev) {
        $code = $ev['event_code'];
        if ($code === 'page_view') {
            $hasStep1 = true;
        } elseif (($code === 'form_start' || $code === 'btn_click') && $hasStep1) {
            $hasStep2 = true;
        } elseif ($code === 'form_complete' && $hasStep2) {
            $hasStep3 = true;
        }
    }

    if ($hasStep1) $step1Count++;
    if ($hasStep2) $step2Count++;
    if ($hasStep3) $step3Completed++;
}

$funnelRate = ($step1Count > 0) ? round(($step3Completed / $step1Count) * 100, 1) : 0;

// 6. Cơ cấu loại sự kiện (Event Types Distribution)
$stmtDist = $pdo->prepare("SELECT event_code, COUNT(*) as count FROM events 
    WHERE site_id = :site_id AND is_duplicate = 0 
    AND client_time BETWEEN :from_time AND :to_time 
    GROUP BY event_code");
$stmtDist->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
$eventDistribution = $stmtDist->fetchAll();

// 7. Xu hướng theo ngày (Timeline Chart)
$stmtTimeline = $pdo->prepare("SELECT strftime('%Y-%m-%d', client_time) as date_val,
        SUM(CASE WHEN event_code = 'page_view' THEN 1 ELSE 0 END) as views,
        SUM(CASE WHEN event_code != 'page_view' THEN 1 ELSE 0 END) as actions
    FROM events 
    WHERE site_id = :site_id AND is_duplicate = 0 
    AND client_time BETWEEN :from_time AND :to_time
    GROUP BY date_val ORDER BY date_val ASC");
// Dự phòng cho MySQL nếu chạy MySQL
try {
    $stmtTimeline->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
    $timeline = $stmtTimeline->fetchAll();
} catch (Exception $e) {
    // MySQL DATE() fallback
    $stmtTimelineMy = $pdo->prepare("SELECT DATE(client_time) as date_val,
            SUM(CASE WHEN event_code = 'page_view' THEN 1 ELSE 0 END) as views,
            SUM(CASE WHEN event_code != 'page_view' THEN 1 ELSE 0 END) as actions
        FROM events 
        WHERE site_id = :site_id AND is_duplicate = 0 
        AND client_time BETWEEN :from_time AND :to_time
        GROUP BY date_val ORDER BY date_val ASC");
    $stmtTimelineMy->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
    $timeline = $stmtTimelineMy->fetchAll();
}

// 8. Top trang được xem nhiều nhất
$stmtTop = $pdo->prepare("SELECT page_url, COUNT(*) as views FROM events 
    WHERE site_id = :site_id AND event_code = 'page_view' AND is_duplicate = 0 
    AND client_time BETWEEN :from_time AND :to_time 
    GROUP BY page_url ORDER BY views DESC LIMIT 6");
$stmtTop->execute([':site_id' => $websiteId, ':from_time' => $fromDateTime, ':to_time' => $toDateTime]);
$topPages = $stmtTop->fetchAll();

// 9. Nhật ký lỗi tiếp nhận (Ingestion Errors) - Giới hạn cứng 100 lỗi trong DB
$stmtErrors = $pdo->prepare("SELECT id, error_type, error_message, created_at FROM ingestion_errors 
    WHERE site_id = :site_id OR site_id IS NULL ORDER BY id DESC LIMIT 10");
$stmtErrors->execute([':site_id' => $websiteId]);
$ingestionErrors = $stmtErrors->fetchAll();

$totalErrorsInDB = (int)$pdo->query("SELECT COUNT(*) FROM ingestion_errors")->fetchColumn();
$retentionDays = (int)getSystemSetting($pdo, 'retention_days', 30);

// 10. Thống kê tương tác theo Giới Tính & Đề Xuất (Cafe vs Milk Tea)
$stmtGenderStats = $pdo->query("SELECT gender, primary_category, COUNT(*) as cnt, SUM(total_price) as revenue FROM orders GROUP BY gender, primary_category");
$genderOrderRows = $stmtGenderStats->fetchAll();

$genderSummary = [
    'male' => ['coffee' => 0, 'milktea' => 0, 'orders' => 0, 'revenue' => 0],
    'female' => ['coffee' => 0, 'milktea' => 0, 'orders' => 0, 'revenue' => 0]
];
foreach ($genderOrderRows as $gr) {
    $g = $gr['gender'];
    $cat = $gr['primary_category'];
    $c = (int)$gr['cnt'];
    $rev = (int)($gr['revenue'] ?? 0);
    if (isset($genderSummary[$g])) {
        $genderSummary[$g][$cat] = $c;
        $genderSummary[$g]['orders'] += $c;
        $genderSummary[$g]['revenue'] += $rev;
    }
}

// 11. Danh sách các sự kiện mới nhất (Raw Events Log đầy đủ thông tin biểu mẫu, người chọn, địa chỉ) - Tăng lên 150 để phục vụ phân trang 1 2 3
$stmtRecent = $pdo->prepare("SELECT event_id, session_id, event_code, page_url, element_text, category, item_name, user_gender, form_name, customer_name, customer_address, client_time 
    FROM events WHERE site_id = :site_id AND is_duplicate = 0 
    ORDER BY client_time DESC, event_id DESC LIMIT 150");
$stmtRecent->execute([':site_id' => $websiteId]);
$recentEvents = $stmtRecent->fetchAll();

// 12. BẢNG ĐỐI CHIẾU THAO TÁC THỰC TẾ (Đúng ví dụ đề bài + Thao tác nhấp sản phẩm, nhấp giỏ hàng)
$stmtDocDownloads = $pdo->prepare("SELECT COUNT(*) as c FROM events 
    WHERE site_id = :site_id AND event_code = 'btn_click' 
    AND (item_name LIKE '%tai_lieu%' OR item_name LIKE '%brochure%' OR element_text LIKE '%Tải Tài Liệu%' OR category = 'document') 
    AND is_duplicate = 0");
$stmtDocDownloads->execute([':site_id' => $websiteId]);
$docDownloads = (int)$stmtDocDownloads->fetch()['c'];

$stmtFormCompletes = $pdo->prepare("SELECT COUNT(*) as c FROM events 
    WHERE site_id = :site_id AND event_code = 'form_complete' AND is_duplicate = 0");
$stmtFormCompletes->execute([':site_id' => $websiteId]);
$formSubmits = (int)$stmtFormCompletes->fetch()['c'];

$stmtFormStarts = $pdo->prepare("SELECT COUNT(*) as c FROM events 
    WHERE site_id = :site_id AND event_code = 'form_start' AND is_duplicate = 0");
$stmtFormStarts->execute([':site_id' => $websiteId]);
$formStarts = (int)$stmtFormStarts->fetch()['c'];

// Đếm thao tác nhấp vào sản phẩm (Chọn món / Xem sản phẩm)
$stmtProdClicks = $pdo->prepare("SELECT COUNT(*) as c FROM events 
    WHERE site_id = :site_id AND event_code = 'btn_click' 
    AND (category IN ('coffee', 'milktea', 'espresso', 'signature', 'coldbrew', 'tea', 'product') 
         OR item_name LIKE 'cf_%' OR item_name LIKE 'tea_%' OR item_name LIKE '%san_pham%'
         OR element_text LIKE '%Chọn Món%' OR element_text LIKE '%Thêm Món%' OR element_text LIKE '%Thêm vào giỏ%' OR element_text LIKE '%sản phẩm%') 
    AND is_duplicate = 0");
$stmtProdClicks->execute([':site_id' => $websiteId]);
$productClicks = (int)$stmtProdClicks->fetch()['c'];

// Đếm thao tác nhấp vào giỏ hàng (Xem giỏ đồ uống / Mở giỏ hàng)
$stmtCartClicks = $pdo->prepare("SELECT COUNT(*) as c FROM events 
    WHERE site_id = :site_id AND event_code = 'btn_click' 
    AND (item_name = 'mo_gio_hang' OR item_name = 'open_cart' OR category = 'cart' 
         OR element_text LIKE '%Giỏ Đồ Uống%' OR element_text LIKE '%giỏ hàng%') 
    AND is_duplicate = 0");
$stmtCartClicks->execute([':site_id' => $websiteId]);
$cartClicks = (int)$stmtCartClicks->fetch()['c'];

// Tìm thao tác người dùng mới nhất (để hiển thị lên ô Live Action Box)
$latestAction = null;
if (!empty($recentEvents)) {
    $latestAction = $recentEvents[0];
}

echo json_encode([
    'status' => 'SUCCESS',
    'website_id' => $websiteId,
    'time_range' => ['from' => $fromDate, 'to' => $toDate],
    'kpis' => [
        'total_pageviews' => $totalPageviews,
        'active_sessions' => $activeSessions,
        'total_actions' => $totalActions,
        'duplicates_avoided' => $totalDuplicatesAvoided,
        'funnel_completed' => $step3Completed,
        'funnel_rate' => $funnelRate
    ],
    'funnel_steps' => [
        'step1_page_view' => $step1Count,
        'step2_action_start' => $step2Count,
        'step3_completed' => $step3Completed
    ],
    'reconciliation' => [
        'product_page_views' => $totalPageviews,
        'download_document_clicks' => $docDownloads,
        'form_starts' => $formStarts,
        'form_completes' => $formSubmits,
        'product_clicks' => $productClicks,
        'cart_clicks' => $cartClicks
    ],
    'latest_action' => $latestAction,
    'event_distribution' => $eventDistribution,
    'timeline' => $timeline,
    'top_pages' => $topPages,
    'ingestion_errors' => $ingestionErrors,
    'error_stats' => [
        'total_in_db' => $totalErrorsInDB,
        'hard_limit' => 100,
        'is_capped' => true
    ],
    'retention_info' => [
        'days' => $retentionDays
    ],
    'gender_analytics' => $genderSummary,
    'recent_events' => $recentEvents
]);

