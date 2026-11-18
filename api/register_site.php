<?php
// api/register_site.php
// Đăng ký website thử & cấu hình sự kiện theo dõi (Đề tài 17 - Yêu cầu 1)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    $stmt = $pdo->query("SELECT w.*, 
        (SELECT COUNT(*) FROM event_definitions ed WHERE ed.site_id = w.id) as event_count,
        (SELECT COUNT(*) FROM events e WHERE e.site_id = w.id) as total_events
        FROM websites w ORDER BY w.id ASC");
    $sites = $stmt->fetchAll();

    foreach ($sites as &$site) {
        $stmtEvt = $pdo->prepare("SELECT event_code, name, allowed FROM event_definitions WHERE site_id = :sid");
        $stmtEvt->execute([':sid' => $site['id']]);
        $site['events'] = $stmtEvt->fetchAll();
    }

    echo json_encode(['status' => 'SUCCESS', 'data' => $sites]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $name = trim($data['name'] ?? '');
    $siteKey = trim($data['site_key'] ?? '');
    $domains = trim($data['allowed_domains'] ?? 'localhost, 127.0.0.1');
    $events = $data['events'] ?? ['page_view', 'btn_click', 'form_start', 'form_complete'];

    if (!$name || !$siteKey) {
        http_response_code(400);
        echo json_encode(['status' => 'ERROR', 'message' => 'Vui lòng nhập tên website và mã site_key']);
        exit;
    }

    // Check trùng site_key
    $stmtCheck = $pdo->prepare("SELECT id FROM websites WHERE site_key = :k");
    $stmtCheck->execute([':k' => $siteKey]);
    if ($stmtCheck->fetch()) {
        http_response_code(400);
        echo json_encode(['status' => 'ERROR', 'message' => "Mã site_key [$siteKey] đã tồn tại, vui lòng chọn mã khác"]);
        exit;
    }

    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO websites (site_key, name, allowed_domains, created_at) VALUES (:k, :n, :d, :now)");
        $stmt->execute([
            ':k' => $siteKey,
            ':n' => $name,
            ':d' => $domains,
            ':now' => $now
        ]);
        $newSiteId = (int)$pdo->lastInsertId();

        $defaultEventNames = [
            'page_view' => 'Lượt xem trang sản phẩm/bài viết',
            'btn_click' => 'Bấm nút tương tác / Tải tài liệu',
            'form_start' => 'Mở biểu mẫu tương tác',
            'form_complete' => 'Gửi biểu mẫu thành công'
        ];

        foreach ($events as $evtCode) {
            $evtName = $defaultEventNames[$evtCode] ?? "Sự kiện $evtCode";
            $stmtEvt = $pdo->prepare("INSERT INTO event_definitions (site_id, event_code, name, allowed) VALUES (:sid, :code, :name, 1)");
            $stmtEvt->execute([
                ':sid' => $newSiteId,
                ':code' => $evtCode,
                ':name' => $evtName
            ]);
        }

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => 'Đăng ký website thử nghiệm thành công!',
            'site' => [
                'id' => $newSiteId,
                'site_key' => $siteKey,
                'name' => $name,
                'allowed_domains' => $domains,
                'events' => $events
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'ERROR', 'message' => 'Lỗi lưu website: ' . $e->getMessage()]);
    }
    exit;
}
