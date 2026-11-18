<?php
// api/register_user.php
// Đăng ký thành viên & tài khoản (Đề tài 17)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    // Trả về danh sách thành viên mới đăng ký
    $stmt = $pdo->query("SELECT id, fullname, email, gender, role, created_at FROM users ORDER BY id DESC LIMIT 50");
    $users = $stmt->fetchAll();
    echo json_encode(['status' => 'SUCCESS', 'data' => $users]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $fullname = trim($data['fullname'] ?? '');
    $email = trim($data['email'] ?? '');
    $gender = trim($data['gender'] ?? 'male');
    $role = trim($data['role'] ?? 'customer');

    if (!$fullname || !$email) {
        http_response_code(400);
        echo json_encode(['status' => 'ERROR', 'message' => 'Vui lòng nhập đầy đủ họ tên và email/số điện thoại']);
        exit;
    }

    if (!in_array($gender, ['male', 'female', 'other'])) {
        $gender = 'male';
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, gender, role, created_at) VALUES (:fullname, :email, :gender, :role, :now)");
        $stmt->execute([
            ':fullname' => $fullname,
            ':email' => $email,
            ':gender' => $gender,
            ':role' => $role,
            ':now' => date('Y-m-d H:i:s')
        ]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'SUCCESS',
            'message' => 'Đăng ký thành viên thành công!',
            'user' => [
                'id' => $newId,
                'fullname' => $fullname,
                'email' => $email,
                'gender' => $gender,
                'role' => $role
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'ERROR', 'message' => 'Lỗi lưu thông tin: ' . $e->getMessage()]);
    }
    exit;
}
