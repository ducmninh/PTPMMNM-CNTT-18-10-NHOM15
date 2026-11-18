<?php
// import_mysql_utf8.php - Nạp schema và seed data với chuẩn UTF-8 nguyên vẹn vào MySQL XAMPP
header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `analytics_cafe_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `analytics_cafe_db`");
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");

    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Không tìm thấy file database.sql\n");
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // Tách câu lệnh theo dấu chấm phẩy
    // Thực thi tuần tự từng câu lệnh
    $queries = explode(";\n", $sqlContent);
    $executed = 0;
    foreach ($queries as $q) {
        $q = trim($q);
        if (!empty($q)) {
            $pdo->exec($q);
            $executed++;
        }
    }

    echo "Đã thực thi thành công {$executed} câu lệnh từ database.sql với chuẩn UTF-8!\n";

    // Kiểm tra ngay lại nội dung bảng lab_events, websites và orders
    echo "\n--- KIỂM TRA BẢNG `lab_events` ---\n";
    $stmt = $pdo->query("SELECT id, title, category, capacity FROM lab_events");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$r['id']} | Tiêu đề: {$r['title']} | Danh mục: {$r['category']} | Số chỗ: {$r['capacity']}\n";
    }

    echo "\n--- KIỂM TRA BẢNG `websites` ---\n";
    $stmt = $pdo->query("SELECT id, name FROM websites");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$r['id']} | Tên: {$r['name']}\n";
    }

    echo "\n--- KIỂM TRA BẢNG `event_definitions` ---\n";
    $stmt = $pdo->query("SELECT id, event_code, name FROM event_definitions LIMIT 4");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Mã: {$r['event_code']} | Tên hiển thị: {$r['name']}\n";
    }

    echo "\n--- KIỂM TRA BẢNG `orders` (Seed mẫu) ---\n";
    $stmt = $pdo->query("SELECT id, customer_name, items_summary FROM orders LIMIT 3");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Khách: {$r['customer_name']} | Món: {$r['items_summary']}\n";
    }

} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
}
