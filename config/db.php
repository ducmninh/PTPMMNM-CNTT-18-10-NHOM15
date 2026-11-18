<?php
// config/db.php
// Đề tài 17: Hệ thống Thu Thập & Thống Kê Truy Cập & Thao Tác Website (Cafe & Trà Sữa)
date_default_timezone_set('Asia/Ho_Chi_Minh');

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbType = 'sqlite';
    $sqlitePath = __DIR__ . '/../data/database.sqlite';

    // Thử kết nối MySQL trước nếu dịch vụ MySQL của XAMPP đang chạy
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `analytics_cafe_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `analytics_cafe_db`");
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $dbType = 'mysql';
    } catch (Exception $e) {
        // Nếu MySQL chưa bật, tự động fallback sang SQLite siêu ổn định và sẵn sàng ngay
        $dbType = 'sqlite';
        if (!is_dir(dirname($sqlitePath))) {
            mkdir(dirname($sqlitePath), 0777, true);
        }
        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    initDatabaseSchema($pdo, $dbType);
    return $pdo;
}

function initDatabaseSchema($pdo, $dbType) {
    $autoId = ($dbType === 'mysql') ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    
    // 1. Bảng websites
    $pdo->exec("CREATE TABLE IF NOT EXISTS websites (
        id $autoId,
        site_key VARCHAR(64) UNIQUE NOT NULL,
        name VARCHAR(128) NOT NULL,
        allowed_domains TEXT,
        created_at DATETIME NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS event_definitions (
        id $autoId,
        site_id INT NOT NULL,
        event_code VARCHAR(64) NOT NULL,
        name VARCHAR(128) NOT NULL,
        event_type VARCHAR(32) DEFAULT 'button',
        target_url VARCHAR(255) DEFAULT '*',
        element_selector VARCHAR(255) DEFAULT '*',
        min_role VARCHAR(32) DEFAULT 'viewer',
        allowed INT DEFAULT 1
    )");

    // Tự động nâng cấp cột cho bảng event_definitions nếu bảng đã tạo trước đó
    $colsToAdd = [
        "event_type VARCHAR(32) DEFAULT 'button'",
        "target_url VARCHAR(255) DEFAULT '*'",
        "element_selector VARCHAR(255) DEFAULT '*'",
        "min_role VARCHAR(32) DEFAULT 'viewer'"
    ];
    foreach ($colsToAdd as $colSql) {
        try {
            $pdo->exec("ALTER TABLE event_definitions ADD COLUMN $colSql");
        } catch (Exception $e) {
            // Cột đã tồn tại, bỏ qua an toàn
        }
    }

    // 3. Bảng sessions
    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        session_id VARCHAR(64) PRIMARY KEY,
        site_id INT NOT NULL,
        user_gender VARCHAR(20) DEFAULT 'unspecified',
        customer_name VARCHAR(128),
        customer_address VARCHAR(255),
        ip_prefix VARCHAR(45) NOT NULL,
        user_agent TEXT,
        first_seen DATETIME NOT NULL,
        last_seen DATETIME NOT NULL
    )");

    // 4. Bảng events
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        event_id VARCHAR(64) PRIMARY KEY,
        site_id INT NOT NULL,
        session_id VARCHAR(64) NOT NULL,
        event_code VARCHAR(64) NOT NULL,
        page_url VARCHAR(255) NOT NULL,
        element_text VARCHAR(128),
        category VARCHAR(64) DEFAULT 'general',
        item_name VARCHAR(128),
        user_gender VARCHAR(20) DEFAULT 'unspecified',
        customer_name VARCHAR(128),
        customer_address VARCHAR(255),
        form_name VARCHAR(128),
        payload_hash VARCHAR(64) NOT NULL,
        client_time DATETIME NOT NULL,
        server_time DATETIME NOT NULL,
        is_duplicate INT DEFAULT 0
    )");

    // 5. Bảng orders (Lưu lượt mua hàng để phân tích đề xuất Nam -> Cafe, Nữ -> Trà sữa)
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id $autoId,
        site_id INT NOT NULL,
        session_id VARCHAR(64) NOT NULL,
        customer_name VARCHAR(128),
        gender VARCHAR(20) NOT NULL,
        primary_category VARCHAR(64) NOT NULL,
        items_summary TEXT NOT NULL,
        total_price INT NOT NULL,
        created_at DATETIME NOT NULL
    )");

    // 6. Bảng ingestion_errors (Lưu lỗi tiếp nhận có giới hạn)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ingestion_errors (
        id $autoId,
        site_id INT,
        event_id VARCHAR(64),
        error_type VARCHAR(64) NOT NULL,
        error_message TEXT NOT NULL,
        created_at DATETIME NOT NULL
    )");

    // 7. Bảng users (Đăng ký thành viên & tài khoản quản trị)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $autoId,
        fullname VARCHAR(128) NOT NULL,
        username VARCHAR(64),
        email VARCHAR(128) NOT NULL,
        password VARCHAR(255),
        gender VARCHAR(20) NOT NULL DEFAULT 'unspecified',
        role VARCHAR(32) DEFAULT 'customer',
        avatar VARCHAR(32) DEFAULT '',
        created_at DATETIME NOT NULL
    )");

    // 8. Bảng system_settings (Lưu cấu hình quy tắc lưu trữ dữ liệu retention & hệ thống)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(64) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at DATETIME NOT NULL
    )");

    // Khởi tạo cấu hình retention mặc định: 30 ngày nếu chưa có
    try {
        $stmtCheckRet = $pdo->query("SELECT COUNT(*) FROM system_settings WHERE setting_key = 'retention_days'");
        if ((int)$stmtCheckRet->fetchColumn() === 0) {
            $now = date('Y-m-d H:i:s');
            $pdo->exec("INSERT INTO system_settings (setting_key, setting_value, updated_at) VALUES ('retention_days', '30', '$now')");
        }
    } catch (Exception $e) {}

    // Tự động nâng cấp cột cho bảng users nếu bảng đã tạo trước đó (thiếu username/password)
    $userColsToAdd = [
        "username VARCHAR(64)",
        "password VARCHAR(255)",
        "avatar VARCHAR(32) DEFAULT ''"
    ];
    foreach ($userColsToAdd as $colSql) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $colSql");
        } catch (Exception $e) {
            // Cột đã tồn tại, bỏ qua an toàn
        }
    }

    // Gieo sẵn 3 tài khoản quản trị mẫu nếu chưa có
    $stmtCheckAdmin = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE role IN ('admin','manager','viewer')");
    $adminCnt = $stmtCheckAdmin->fetch()['cnt'];
    if ($adminCnt == 0) {
        $now = date('Y-m-d H:i:s');
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $managerPass = password_hash('manager123', PASSWORD_DEFAULT);
        $viewerPass = password_hash('viewer123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (fullname, username, email, password, gender, role, avatar, created_at) VALUES
            ('Hoàng Minh Đức (Admin)', 'admin', 'admin@dnu.edu.vn', '$adminPass', 'male', 'admin', '👨‍💼', '$now'),
            ('Nguyễn Tiến Lợi (Quản Lý)', 'manager', 'manager@dnu.edu.vn', '$managerPass', 'male', 'manager', '👔', '$now'),
            ('Giảng Viên / Khách Thử Nghiệm', 'viewer', 'viewer@dnu.edu.vn', '$viewerPass', 'unspecified', 'viewer', '🎓', '$now')
        ");
    }

    // Kiểm tra và gieo mầm dữ liệu cơ bản nếu chưa có website
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM websites");
    $cnt = $stmt->fetch()['cnt'];
    if ($cnt == 0) {
        $now = date('Y-m-d H:i:s');
        // Website 1: Web Trà Sữa & Cà Phê
        $pdo->exec("INSERT INTO websites (site_key, name, allowed_domains, created_at) 
            VALUES ('site_cafe_tea_01', 'Milky & Coffee Corner - Tiệm Trà Sữa & Cà Phê', 'localhost, 127.0.0.1', '$now')");
        // Website 2: Web Giáo Dục (Để thỏa mãn yêu cầu 2 website độc lập không lẫn số liệu)
        $pdo->exec("INSERT INTO websites (site_key, name, allowed_domains, created_at) 
            VALUES ('site_eduportal_02', 'EduPortal - Cổng Học Liệu Trực Tuyến', 'localhost, 127.0.0.1', '$now')");

        // Gieo cấu hình 4 loại sự kiện cho Site 1
        $site1Id = 1;
        $events1 = [
            ['page_view', 'Lượt xem trang (Menu, Sản phẩm, Giỏ hàng)'],
            ['btn_click', 'Bấm nút chọn món / Thêm vào giỏ hàng'],
            ['form_start', 'Mở biểu mẫu thanh toán đặt món'],
            ['form_complete', 'Hoàn tất đơn hàng thanh toán']
        ];
        foreach ($events1 as $e) {
            $pdo->exec("INSERT INTO event_definitions (site_id, event_code, name, allowed) VALUES ($site1Id, '{$e[0]}', '{$e[1]}', 1)");
        }

        // Gieo cấu hình 4 loại sự kiện cho Site 2
        $site2Id = 2;
        foreach ($events1 as $e) {
            $pdo->exec("INSERT INTO event_definitions (site_id, event_code, name, allowed) VALUES ($site2Id, '{$e[0]}', '{$e[1]}', 1)");
        }

        // Tạo sẵn dữ liệu mầm lịch sử mua hàng để thuật toán gợi ý ban đầu đã có số liệu thống kê sinh động:
        // Nam: 85% mua Cafe, 15% mua Trà sữa
        // Nữ: 90% mua Trà sữa, 10% mua Cafe
        seedInitialPurchaseHistory($pdo);
    }
}

function seedInitialPurchaseHistory($pdo) {
    $now = date('Y-m-d H:i:s');
    $sampleOrders = [
        // Khách Nam (ưu tiên Cafe)
        ['Quốc Bảo', 'male', 'coffee', 'Cà Phê Muối Tuyệt Hảo x2', 70000],
        ['Minh Đức', 'male', 'coffee', 'Cà Phê Sữa Đá Sài Gòn x1', 32000],
        ['Hoàng Nam', 'male', 'coffee', 'Espresso Đậm Đà x1', 35000],
        ['Tuấn Anh', 'male', 'coffee', 'Bạc Xỉu 3 Tầng x2', 76000],
        ['Văn Hùng', 'male', 'coffee', 'Cold Brew Cam Sả x1', 45000],
        ['Đức Anh', 'male', 'milktea', 'Trà Sữa Trân Châu Hoàng Gia x1', 45000], // 1 bạn nam uống trà sữa
        ['Thành Long', 'male', 'coffee', 'Americano Đá x1', 35000],
        ['Trọng Hiếu', 'male', 'coffee', 'Cà Phê Muối Tuyệt Hảo x1', 35000],

        // Khách Nữ (ưu tiên Trà Sữa)
        ['Thảo Vy', 'female', 'milktea', 'Trà Sữa Trân Châu Hoàng Gia x2', 90000],
        ['Hương Giang', 'female', 'milktea', 'Trà Sữa Ô Long Nướng Kem Trứng x1', 48000],
        ['Bảo Ngọc', 'female', 'milktea', 'Trà Đào Cam Sả Tươi Mát x1', 42000],
        ['Khánh Linh', 'female', 'milktea', 'Trà Sữa Matcha Đậu Đỏ x1', 45000],
        ['Thu Trang', 'female', 'milktea', 'Trà Sữa Thái Xanh Phô Mai x2', 88000],
        ['Mai Anh', 'female', 'coffee', 'Bạc Xỉu Ngọt Ngào x1', 38000], // 1 bạn nữ uống bạc xỉu cafe
        ['Quỳnh Như', 'female', 'milktea', 'Trà Sữa Trân Châu Hoàng Gia x1', 45000],
        ['Ánh Dương', 'female', 'milktea', 'Trà Sữa Ô Long Nướng Kem Trứng x2', 96000]
    ];

    $stmt = $pdo->prepare("INSERT INTO orders (site_id, session_id, customer_name, gender, primary_category, items_summary, total_price, created_at) 
        VALUES (1, :session_id, :name, :gender, :cat, :items, :price, :time)");

    $i = 1;
    foreach ($sampleOrders as $ord) {
        $pastTime = date('Y-m-d H:i:s', strtotime("-" . ($i * 2) . " hours"));
        $stmt->execute([
            ':session_id' => 'sess_seed_' . $i,
            ':name' => $ord[0],
            ':gender' => $ord[1],
            ':cat' => $ord[2],
            ':items' => $ord[3],
            ':price' => $ord[4],
            ':time' => $pastTime
        ]);
        $i++;
    }
}

/**
 * Ghi nhận lỗi tiếp nhận và áp dụng GIỚI HẠN CỨNG (Hard Limit) tối đa 100 lỗi trong DB
 * Tự động xóa các bản ghi lỗi cũ nhất khi vượt ngưỡng.
 */
function logIngestionError($pdo, $siteId, $eventId, $errorType, $errorMessage, $maxLimit = 100) {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO ingestion_errors (site_id, event_id, error_type, error_message, created_at) 
        VALUES (:site_id, :evt_id, :type, :msg, :now)");
    $stmt->execute([
        ':site_id' => $siteId,
        ':evt_id' => $eventId ?? 'unknown',
        ':type' => $errorType,
        ':msg' => $errorMessage,
        ':now' => $now
    ]);

    // Giới hạn cứng: Kiểm tra tổng số bản ghi lỗi, nếu vượt quá $maxLimit thì xóa các lỗi cũ nhất
    try {
        $totalErrors = (int)$pdo->query("SELECT COUNT(*) FROM ingestion_errors")->fetchColumn();
        if ($totalErrors > $maxLimit) {
            $excess = $totalErrors - $maxLimit;
            $pdo->exec("DELETE FROM ingestion_errors WHERE id IN (SELECT id FROM ingestion_errors ORDER BY id ASC LIMIT $excess)");
        }
    } catch (Exception $e) {
        // Dự phòng câu truy vấn nếu SQLite / MySQL có phiên bản hạn chế subquery
        try {
            $oldIds = $pdo->query("SELECT id FROM ingestion_errors ORDER BY id ASC LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($oldIds)) {
                $inClause = implode(',', array_map('intval', $oldIds));
                $pdo->exec("DELETE FROM ingestion_errors WHERE id IN ($inClause)");
            }
        } catch (Exception $e2) {}
    }
}

/**
 * Lấy cấu hình hệ thống
 */
function getSystemSetting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Cập nhật cấu hình hệ thống
 */
function setSystemSetting($pdo, $key, $value) {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_at) 
        VALUES (:k, :v, :now)
        ON CONFLICT(setting_key) DO UPDATE SET setting_value = :v2, updated_at = :now2");
    try {
        $stmt->execute([
            ':k' => $key,
            ':v' => $value,
            ':now' => $now,
            ':v2' => $value,
            ':now2' => $now
        ]);
        return true;
    } catch (Exception $e) {
        // Fallback cho MySQL
        try {
            $stmtMy = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_at) 
                VALUES (:k, :v, :now) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)");
            $stmtMy->execute([':k' => $key, ':v' => $value, ':now' => $now]);
            return true;
        } catch (Exception $e2) {
            return false;
        }
    }
}

/**
 * Thực thi quy tắc dọn dẹp dữ liệu cũ theo Retention Days
 * Xóa các events có client_time cũ hơn số ngày quy định
 * Đồng thời dọn dẹp sessions mồ côi và lỗi tiếp nhận quá hạn
 */
function cleanupRetentionData($pdo, $websiteId = null, $retentionDays = null) {
    if ($retentionDays === null) {
        $retentionDays = (int)getSystemSetting($pdo, 'retention_days', 30);
    }
    $retentionDays = (int)$retentionDays;

    if ($retentionDays <= 0) {
        return [
            'status' => 'SKIPPED',
            'retention_days' => 0,
            'message' => 'Quy tắc lưu trữ đang đặt là Vĩnh viễn (0 ngày), không xóa dữ liệu.'
        ];
    }

    $cutoffTime = date('Y-m-d H:i:s', strtotime("-$retentionDays days"));

    // Đếm số lượng sắp xóa
    $sqlCountEvt = "SELECT COUNT(*) FROM events WHERE client_time < :cutoff";
    $params = [':cutoff' => $cutoffTime];
    if ($websiteId !== null && $websiteId > 0) {
        $sqlCountEvt .= " AND site_id = :site_id";
        $params[':site_id'] = $websiteId;
    }
    $stmtCount = $pdo->prepare($sqlCountEvt);
    $stmtCount->execute($params);
    $eventsToDelete = (int)$stmtCount->fetchColumn();

    // Xóa events
    $sqlDelEvt = "DELETE FROM events WHERE client_time < :cutoff";
    if ($websiteId !== null && $websiteId > 0) {
        $sqlDelEvt .= " AND site_id = :site_id";
    }
    $stmtDel = $pdo->prepare($sqlDelEvt);
    $stmtDel->execute($params);

    // Xóa sessions không còn event nào và quá hạn
    $sqlDelSess = "DELETE FROM sessions WHERE last_seen < :cutoff AND session_id NOT IN (SELECT DISTINCT session_id FROM events)";
    $pdo->prepare($sqlDelSess)->execute([':cutoff' => $cutoffTime]);

    // Xóa lỗi tiếp nhận quá hạn
    $sqlDelErr = "DELETE FROM ingestion_errors WHERE created_at < :cutoff";
    $pdo->prepare($sqlDelErr)->execute([':cutoff' => $cutoffTime]);

    return [
        'status' => 'SUCCESS',
        'retention_days' => $retentionDays,
        'cutoff_time' => $cutoffTime,
        'deleted_events' => $eventsToDelete,
        'message' => "Đã dọn dẹp thành công các dữ liệu cũ hơn $retentionDays ngày (trước $cutoffTime)."
    ];
}
