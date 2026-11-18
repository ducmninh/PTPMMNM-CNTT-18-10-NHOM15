<?php
// api/recommendations.php
// Đề tài 17: Bộ máy gợi ý và phân tích hành vi theo Giới Tính (Cafe & Trà Sữa)
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

$action = $_GET['action'] ?? 'get_recommendations';

// Danh mục sản phẩm chuẩn của quán
$menuCatalog = [
    // --- NHÓM CÀ PHÊ (Đề xuất cho Con Trai / Nam) ---
    [
        'id' => 'cf_muoi',
        'name' => 'Cà Phê Muối Tuyệt Hảo',
        'category' => 'coffee',
        'price' => 35000,
        'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Lớp kem béo mặn hòa quyện cà phê Robusta đậm đặc Đắk Lắk, hương vị đậm đà khó cưỡng.',
        'badge' => '🔥 Nam giới chuộng nhất (94%)',
        'calories' => '160 kcal',
        'tags' => ['Đậm đà', 'Béo mặn', 'Tỉnh táo']
    ],
    [
        'id' => 'cf_suada',
        'name' => 'Cà Phê Sữa Đá Sài Gòn',
        'category' => 'coffee',
        'price' => 32000,
        'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Hương vị truyền thống pha phin nguyên chất, ngọt thanh quyện vị đắng sánh mịn.',
        'badge' => '⚡ Best-seller phái mạnh',
        'calories' => '190 kcal',
        'tags' => ['Truyền thống', 'Đậm vị']
    ],
    [
        'id' => 'cf_bacxiu',
        'name' => 'Bạc Xỉu 3 Tầng Kem Sữa',
        'category' => 'coffee',
        'price' => 38000,
        'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Nhiều sữa ít cà phê, béo ngậy ngọt ngào, phù hợp cho buổi sáng nhẹ nhàng.',
        'badge' => '⭐ Cân bằng ngọt đắng',
        'calories' => '210 kcal',
        'tags' => ['Dễ uống', 'Ngọt ngào']
    ],
    [
        'id' => 'cf_espresso',
        'name' => 'Double Espresso Đậm Đặc',
        'category' => 'coffee',
        'price' => 35000,
        'image' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Chiết xuất áp suất cao từ hạt Arabica Cầu Đất, đượm vị caramel và hương hoa quả.',
        'badge' => '💪 Năng lượng bứt phá',
        'calories' => '15 kcal',
        'tags' => ['Không đường', 'Tập trung cao độ']
    ],
    [
        'id' => 'cf_coldbrew',
        'name' => 'Cold Brew Cam Sả Tươi',
        'category' => 'coffee',
        'price' => 45000,
        'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Ủ lạnh 16 tiếng với tép cam vàng mọng nước và sả tươi thanh mát chống say cà phê.',
        'badge' => '🍊 Sảng khoái ngày hè',
        'calories' => '80 kcal',
        'tags' => ['Ủ lạnh', 'Thanh mát']
    ],

    // --- NHÓM TRÀ SỮA (Đề xuất cho Con Gái / Nữ) ---
    [
        'id' => 'ts_hoanggia',
        'name' => 'Trà Sữa Trân Châu Hoàng Gia',
        'category' => 'milktea',
        'price' => 45000,
        'image' => 'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Trà đen Ceylon hảo hạng hòa sữa tươi thanh trùng, trân châu đen dai giòn hoàng gia thơm nức.',
        'badge' => '💖 Bạn nữ yêu thích số 1 (96%)',
        'calories' => '320 kcal',
        'tags' => ['Trân châu dai giòn', 'Béo thơm']
    ],
    [
        'id' => 'ts_olongnuong',
        'name' => 'Trà Sữa Ô Long Nướng Kem Trứng',
        'category' => 'milktea',
        'price' => 48000,
        'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Lớp kem trứng cháy khò lửa vàng ươm, thơm khói đặc trưng của lá trà ô long sấy truyền thống.',
        'badge' => '✨ Hot Trend Giới Trẻ',
        'calories' => '360 kcal',
        'tags' => ['Kem trứng khò', 'Ô Long nướng']
    ],
    [
        'id' => 'ts_matcha',
        'name' => 'Trà Sữa Matcha Uji Đậu Đỏ',
        'category' => 'milktea',
        'price' => 46000,
        'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Bột trà xanh nguyên chất nhập khẩu Kyoto, ăn kèm sốt đậu đỏ Nhật Bản ngọt bùi êm dịu.',
        'badge' => '🍵 Thanh tao chuẩn Nhật',
        'calories' => '280 kcal',
        'tags' => ['Matcha Kyoto', 'Đậu đỏ bùi']
    ],
    [
        'id' => 'ts_thaixanh',
        'name' => 'Trà Sữa Thái Xanh Kem Cheese',
        'category' => 'milktea',
        'price' => 42000,
        'image' => 'https://images.unsplash.com/photo-1541658016709-82535e94bc69?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Hương thảo mộc Thái Lan thanh mát, phủ lớp kem mặn cheese Macchiato bồng bềnh.',
        'badge' => '👑 Cực phẩm sống ảo',
        'calories' => '310 kcal',
        'tags' => ['Thái Xanh', 'Kem Cheese']
    ],
    [
        'id' => 'ts_daocamsa',
        'name' => 'Trà Đào Cam Sả Tươi Mát',
        'category' => 'milktea',
        'price' => 42000,
        'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?w=500&auto=format&fit=crop&q=80',
        'desc' => 'Vị ngọt thanh của đào giòn, vị chua dịu của cam tươi cùng hương thơm nồng ấm của sả cây.',
        'badge' => '🍑 Đào miếng giòn rụm',
        'calories' => '170 kcal',
        'tags' => ['Trái cây tươi', 'Đẹp da']
    ]
];

if ($action === 'get_catalog') {
    echo json_encode(['status' => 'SUCCESS', 'catalog' => $menuCatalog]);
    exit;
}

if ($action === 'record_purchase') {
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['status' => 'INVALID_JSON', 'error' => 'Dữ liệu đơn hàng không hợp lệ']);
        exit;
    }

    $sessionId = trim($data['session_id'] ?? 'sess_' . time());
    $customerName = trim($data['customer_name'] ?? 'Khách vãng lai');
    $gender = trim($data['gender'] ?? 'male');
    $primaryCategory = trim($data['primary_category'] ?? 'coffee');
    $itemsSummary = trim($data['items_summary'] ?? 'Đơn hàng trà sữa / cà phê');
    $totalPrice = (int)($data['total_price'] ?? 0);
    $now = date('Y-m-d H:i:s');

    // Lưu đơn hàng
    $stmt = $pdo->prepare("INSERT INTO orders (site_id, session_id, customer_name, gender, primary_category, items_summary, total_price, created_at)
        VALUES (1, :session_id, :name, :gender, :cat, :items, :price, :now)");
    $stmt->execute([
        ':session_id' => $sessionId,
        ':name' => $customerName,
        ':gender' => $gender,
        ':cat' => $primaryCategory,
        ':items' => $itemsSummary,
        ':price' => $totalPrice,
        ':now' => $now
    ]);

    $stmtSessInfo = $pdo->prepare("SELECT customer_address FROM sessions WHERE session_id = :session_id");
    $stmtSessInfo->execute([':session_id' => $sessionId]);
    $sessInfo = $stmtSessInfo->fetch();
    $customerAddress = trim($data['customer_address'] ?? ($sessInfo['customer_address'] ?? 'Hà Nội'));
    $formName = 'Biểu Mẫu Xác Nhận Đặt Hàng';

    // Đồng thời kích hoạt sự kiện form_complete hợp lệ vào bảng events
    $eventId = 'evt_buy_' . uniqid();
    $payloadHash = md5(json_encode(['site_id' => 1, 'session_id' => $sessionId, 'event_code' => 'form_complete', 'cat' => $primaryCategory, 'cust' => $customerName]));
    $stmtEvt = $pdo->prepare("INSERT INTO events (
        event_id, site_id, session_id, event_code, page_url, element_text, category, item_name, user_gender, customer_name, customer_address, form_name, payload_hash, client_time, server_time, is_duplicate
    ) VALUES (
        :evt_id, 1, :session_id, 'form_complete', '/demo/cafe_shop/checkout.php', 'Xác Nhận Đặt Món', :cat, :item_name, :gender, :cust_name, :cust_addr, :form_name, :hash, :now, :now, 0
    )");
    $stmtEvt->execute([
        ':evt_id' => $eventId,
        ':session_id' => $sessionId,
        ':cat' => $primaryCategory,
        ':item_name' => $itemsSummary,
        ':gender' => $gender,
        ':cust_name' => $customerName,
        ':cust_addr' => $customerAddress,
        ':form_name' => $formName,
        ':hash' => $payloadHash,
        ':now' => $now
    ]);

    echo json_encode([
        'status' => 'SUCCESS',
        'message' => 'Lưu đơn mua hàng và ghi nhận thao tác thành công!',
        'order_id' => $pdo->lastInsertId(),
        'gender' => $gender,
        'category' => $primaryCategory
    ]);
    exit;
}

// MẶC ĐỊNH: LẤY ĐỀ XUẤT THEO GIỚI TÍNH DỰA TRÊN DỮ LIỆU ĐÃ THỐNG KÊ
$gender = $_GET['gender'] ?? 'male'; // 'male' (con trai) hoặc 'female' (con gái)

// 1. Phân tích thống kê lịch sử mua hàng từ DB
$stmtStats = $pdo->query("SELECT gender, primary_category, COUNT(*) as count FROM orders GROUP BY gender, primary_category");
$allRows = $stmtStats->fetchAll();

$genderStats = [
    'male' => ['coffee' => 0, 'milktea' => 0, 'total' => 0],
    'female' => ['coffee' => 0, 'milktea' => 0, 'total' => 0]
];

foreach ($allRows as $r) {
    $g = $r['gender'];
    $cat = $r['primary_category'];
    $c = (int)$r['count'];
    if (isset($genderStats[$g])) {
        $genderStats[$g][$cat] = $c;
        $genderStats[$g]['total'] += $c;
    }
}

// Tính tỷ lệ %
$maleCoffeeRate = ($genderStats['male']['total'] > 0) ? round(($genderStats['male']['coffee'] / $genderStats['male']['total']) * 100) : 85;
$femaleMilkteaRate = ($genderStats['female']['total'] > 0) ? round(($genderStats['female']['milktea'] / $genderStats['female']['total']) * 100) : 92;

// 2. Quyết định danh mục đề xuất
if ($gender === 'female') {
    $targetCat = 'milktea';
    $headline = "✨ ĐỀ XUẤT CHO NÀNG XINH: {$femaleMilkteaRate}% phái đẹp chọn Trà Sữa Thơm Béo hôm nay!";
    $subline = "Dựa trên phân tích {$genderStats['female']['total']} lượt mua của khách hàng nữ tại quán, đây là top các món trà sữa best-seller khiến các nàng mê mẩn nhất!";
    $recItems = array_values(array_filter($menuCatalog, fn($i) => $i['category'] === 'milktea'));
} else {
    // Nam hoặc mặc định
    $targetCat = 'coffee';
    $headline = "⚡ ĐỀ XUẤT CHO PHÁI MẠNH: {$maleCoffeeRate}% chàng trai nạp năng lượng bằng Cà Phê Đậm Đà!";
    $subline = "Dựa trên phân tích {$genderStats['male']['total']} lượt mua của phái mạnh, đây là các dòng cà phê giúp tỉnh táo, tập trung và bứt phá ngày mới!";
    $recItems = array_values(array_filter($menuCatalog, fn($i) => $i['category'] === 'coffee'));
}

echo json_encode([
    'status' => 'SUCCESS',
    'requested_gender' => $gender,
    'target_category' => $targetCat,
    'headline' => $headline,
    'subline' => $subline,
    'recommendations' => $recItems,
    'analytics' => [
        'male_coffee_rate' => $maleCoffeeRate,
        'male_milktea_rate' => 100 - $maleCoffeeRate,
        'female_milktea_rate' => $femaleMilkteaRate,
        'female_coffee_rate' => 100 - $femaleMilkteaRate,
        'total_orders' => $genderStats['male']['total'] + $genderStats['female']['total'],
        'raw_stats' => $genderStats
    ]
]);
