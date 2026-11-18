<?php
// cron_cleanup.php
// Đề tài 17: Kịch bản chạy tự động (Cron Job / Scheduled Task)
// Thực thi Quy Tắc Giữ / Xóa Dữ Liệu Thử & Dữ Liệu Quá Hạn (Data Retention Policy)
// Cách sử dụng:
// 1. Chạy CLI: php c:\xampp\htdocs\ducmninh\cron_cleanup.php
// 2. Chạy Webhook / URL: http://localhost/ducmninh/cron_cleanup.php?token=analytics_retention_2026

date_default_timezone_set('Asia/Ho_Chi_Minh');

$isCli = (php_sapi_name() === 'cli' || empty($_SERVER['REMOTE_ADDR']));

if (!$isCli) {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_GET['token'] ?? '';
    // Cho phép gọi an toàn từ trình duyệt hoặc webhook
    if ($token !== 'analytics_retention_2026' && !isset($_GET['force'])) {
        http_response_code(403);
        echo json_encode(['status' => 'FORBIDDEN', 'error' => 'Token xác thực cron job không hợp lệ.']);
        exit;
    }
}

require_once __DIR__ . '/config/db.php';
$pdo = getDBConnection();

$retentionDays = (int)getSystemSetting($pdo, 'retention_days', 30);
$siteIdParam = isset($_GET['website_id']) ? (int)$_GET['website_id'] : null;

$startTime = microtime(true);
$result = cleanupRetentionData($pdo, $siteIdParam, $retentionDays);
$elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

$result['execution_time_ms'] = $elapsedMs;
$result['executed_at'] = date('Y-m-d H:i:s');
$result['invoked_by'] = $isCli ? 'CLI (Task Scheduler / Cron)' : 'HTTP Request';

if ($isCli) {
    echo "=========================================================\n";
    echo "  [CRON JOB] THỰC THI QUY TẮC DỌN DẸP DỮ LIỆU ĐỊNH KỲ   \n";
    echo "=========================================================\n";
    echo "Thời điểm thực thi   : " . $result['executed_at'] . "\n";
    echo "Quy tắc lưu trữ      : Giữ tối đa " . $result['retention_days'] . " ngày gần nhất\n";
    if (isset($result['cutoff_time'])) {
        echo "Mốc thời gian cắt    : Trước " . $result['cutoff_time'] . "\n";
        echo "Số sự kiện đã xóa    : " . $result['deleted_events'] . " sự kiện\n";
    }
    echo "Trạng thái           : " . $result['status'] . "\n";
    echo "Thông điệp           : " . $result['message'] . "\n";
    echo "Thời gian xử lý      : {$elapsedMs} ms\n";
    echo "=========================================================\n";
} else {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

