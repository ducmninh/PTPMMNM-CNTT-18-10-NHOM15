<?php
// api/test_scenarios.php
// Đề tài 17: Bộ sinh 200 sự kiện kịch bản chuẩn có tổng tính trước để đối chiếu
// Cung cấp đầy đủ: Tên biểu mẫu, Tên người dùng và Địa chỉ người click chọn
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

// Xóa dữ liệu cũ để nạp mới kịch bản chuẩn
$pdo->exec("DELETE FROM events");
$pdo->exec("DELETE FROM sessions");
$pdo->exec("DELETE FROM ingestion_errors");
$pdo->exec("DELETE FROM orders");

$now = time();
$baseToday = date('Y-m-d', $now);
$baseYesterday = date('Y-m-d', strtotime('-1 day', $now));

// Danh sách hồ sơ khách hàng mẫu chuẩn tiếng Việt kèm địa chỉ thực tế
$userProfiles = [
    ['name' => 'Hoàng Minh Đức (Nam)', 'gender' => 'male', 'address' => 'Số 12 Chùa Bộc, Đống Đa, Hà Nội', 'dish' => 'Cà Phê Muối Tuyệt Hảo'],
    ['name' => 'Nguyễn Thùy Linh (Nữ)', 'gender' => 'female', 'address' => 'Số 88 Nguyễn Trãi, Thanh Xuân, Hà Nội', 'dish' => 'Trà Sữa Trân Châu Hoàng Gia'],
    ['name' => 'Trần Thu Trang (Nữ)', 'gender' => 'female', 'address' => 'Tòa S2 Vinhome Smart City, Nam Từ Liêm, Hà Nội', 'dish' => 'Trà Sữa Ô Long Nướng Kem Trứng'],
    ['name' => 'Vũ Thành Long (Nam)', 'gender' => 'male', 'address' => 'Số 250 Cầu Giấy, Hà Nội', 'dish' => 'Cold Brew Cam Sả'],
    ['name' => 'Phạm Quốc Cường (Nam)', 'gender' => 'male', 'address' => 'Số 45 Lê Duẩn, Hoàn Kiếm, Hà Nội', 'dish' => 'Double Espresso Đậm Đà'],
    ['name' => 'Đặng Ngọc Ánh (Nữ)', 'gender' => 'female', 'address' => 'Số 15 Quang Trung, Hà Đông, Hà Nội', 'dish' => 'Trà Đào Cam Sả Tươi Mát'],
    ['name' => 'Bùi Diệu Linh (Nữ)', 'gender' => 'female', 'address' => 'P.502 KTX ĐH Đại Nam, Hà Đông, Hà Nội', 'dish' => 'Trà Sữa Matcha Kyoto'],
    ['name' => 'Lê Hữu Đạt (Nam)', 'gender' => 'male', 'address' => 'Số 68 Giải Phóng, Hai Bà Trưng, Hà Nội', 'dish' => 'Cà Phê Sữa Đá Sài Gòn'],
    ['name' => 'Bùi Văn Hùng (Nam)', 'gender' => 'male', 'address' => 'Số 9 Duy Tân, Cầu Giấy, Hà Nội', 'dish' => 'Americano Đá Tươi Mát'],
    ['name' => 'Vũ Bích Ngọc (Nữ)', 'gender' => 'female', 'address' => 'Số 102 Hoàng Hoa Thám, Ba Đình, Hà Nội', 'dish' => 'Trà Sữa Lài Sữa Tươi']
];

// Chuẩn bị câu lệnh Insert
$stmtEvt = $pdo->prepare("INSERT INTO events (
    event_id, site_id, session_id, event_code, page_url, element_text, 
    category, item_name, user_gender, customer_name, customer_address, form_name, payload_hash, client_time, server_time, is_duplicate
) VALUES (
    :event_id, :site_id, :session_id, :event_code, :page_url, :element_text,
    :category, :item_name, :user_gender, :customer_name, :customer_address, :form_name, :payload_hash, :client_time, :server_time, :is_duplicate
)");

$stmtSess = $pdo->prepare("REPLACE INTO sessions (
    session_id, site_id, user_gender, customer_name, customer_address, ip_prefix, user_agent, first_seen, last_seen
) VALUES (:session_id, :site_id, :gender, :customer_name, :customer_address, '192.168.1.xxx', 'Mozilla/5.0 ScenarioBot', :first_seen, :last_seen)");

$eventCounter = 0;

// =========================================================================
// PHẦN 1: PHÉP THỬ BẮT BUỘC SỐ 1 (10 LƯỢT XEM & 4 LẦN BẤM NÚT TRÊN SITE 1)
// =========================================================================
$sessTest1 = 'sess_truth_01';
$timeAnchor1 = $baseToday . ' 09:00:00';
$u0 = $userProfiles[0];
$stmtSess->execute([
    ':session_id' => $sessTest1, ':site_id' => 1, ':gender' => $u0['gender'], 
    ':customer_name' => $u0['name'], ':customer_address' => $u0['address'], 
    ':first_seen' => $timeAnchor1, ':last_seen' => $timeAnchor1
]);

// 10 Lượt xem trang chuẩn
for ($i = 1; $i <= 10; $i++) {
    $evtId = "evt_pv_truth_$i";
    $pUrl = ($i % 2 === 0) ? '/demo/cafe_shop/index.php' : '/demo/cafe_shop/menu.php';
    $hash = md5("1_{$sessTest1}_page_view_{$pUrl}_title_general_");
    $time = date('Y-m-d H:i:s', strtotime("$timeAnchor1 + " . ($i * 2) . " minutes"));
    $stmtEvt->execute([
        ':event_id' => $evtId, ':site_id' => 1, ':session_id' => $sessTest1, ':event_code' => 'page_view',
        ':page_url' => $pUrl, ':element_text' => 'Xem Danh Mục Thực Đơn Trang ' . $i, ':category' => 'coffee', ':item_name' => '',
        ':user_gender' => $u0['gender'], ':customer_name' => $u0['name'], ':customer_address' => $u0['address'],
        ':form_name' => 'Khu Vực Danh Mục Đồ Uống',
        ':payload_hash' => $hash, ':client_time' => $time, ':server_time' => $time, ':is_duplicate' => 0
    ]);
    $eventCounter++;
}

// 4 Lần bấm nút chuẩn
for ($i = 1; $i <= 4; $i++) {
    $evtId = "evt_btn_truth_$i";
    $pUrl = '/demo/cafe_shop/index.php';
    $btnText = "Bấm Chọn Món: " . $u0['dish'];
    $hash = md5("1_{$sessTest1}_btn_click_{$pUrl}_{$btnText}_coffee_Cà Phê Muối");
    $time = date('Y-m-d H:i:s', strtotime("$timeAnchor1 + " . (25 + $i * 2) . " minutes"));
    $stmtEvt->execute([
        ':event_id' => $evtId, ':site_id' => 1, ':session_id' => $sessTest1, ':event_code' => 'btn_click',
        ':page_url' => $pUrl, ':element_text' => $btnText, ':category' => 'coffee', ':item_name' => $u0['dish'],
        ':user_gender' => $u0['gender'], ':customer_name' => $u0['name'], ':customer_address' => $u0['address'],
        ':form_name' => 'Thực Đơn Cà Phê Chuẩn Gu',
        ':payload_hash' => $hash, ':client_time' => $time, ':server_time' => $time, ':is_duplicate' => 0
    ]);
    $eventCounter++;
}

// Gửi lặp lại 2 mã cũ (is_duplicate = 1) -> Không làm tăng số đếm thực tế
$stmtEvt->execute([
    ':event_id' => "evt_btn_truth_1_dup", ':site_id' => 1, ':session_id' => $sessTest1, ':event_code' => 'btn_click',
    ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => "Bấm Chọn Món: " . $u0['dish'], ':category' => 'coffee', ':item_name' => $u0['dish'],
    ':user_gender' => $u0['gender'], ':customer_name' => $u0['name'], ':customer_address' => $u0['address'],
    ':form_name' => 'Thực Đơn Cà Phê Chuẩn Gu',
    ':payload_hash' => md5("1_{$sessTest1}_btn_click_/demo/cafe_shop/index.php_Chọn Cà Phê Mẫu 1_coffee_Cà Phê Muối"),
    ':client_time' => $timeAnchor1, ':server_time' => $timeAnchor1, ':is_duplicate' => 1
]);
$stmtEvt->execute([
    ':event_id' => "evt_btn_truth_2_dup", ':site_id' => 1, ':session_id' => $sessTest1, ':event_code' => 'btn_click',
    ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => "Bấm Chọn Món: " . $u0['dish'], ':category' => 'coffee', ':item_name' => $u0['dish'],
    ':user_gender' => $u0['gender'], ':customer_name' => $u0['name'], ':customer_address' => $u0['address'],
    ':form_name' => 'Thực Đơn Cà Phê Chuẩn Gu',
    ':payload_hash' => md5("1_{$sessTest1}_btn_click_/demo/cafe_shop/index.php_Chọn Cà Phê Mẫu 2_coffee_Cà Phê Muối"),
    ':client_time' => $timeAnchor1, ':server_time' => $timeAnchor1, ':is_duplicate' => 1
]);
$eventCounter += 2;

// Thao tác đối chiếu chuẩn theo Đề bài: "người dùng mở trang sản phẩm, bấm tải tài liệu và gửi biểu mẫu"
for ($k = 1; $k <= 5; $k++) {
    $u = $userProfiles[$k - 1];
    $sId = "sess_doc_$k";
    $stmtSess->execute([
        ':session_id' => $sId, ':site_id' => 1, ':gender' => $u['gender'],
        ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':first_seen' => $timeAnchor1, ':last_seen' => $timeAnchor1
    ]);

    $stmtEvt->execute([
        ':event_id' => "evt_doc_download_$k", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'btn_click',
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => '📥 Bấm nút Tải Brochure / Menu Quán', ':category' => 'document', ':item_name' => 'tai_tai_lieu_menu',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Lịch Đặt Chỗ & Giữ Bàn NOIR CAFÉ',
        ':payload_hash' => md5("doc_dl_$k"), ':client_time' => $timeAnchor1, ':server_time' => $timeAnchor1, ':is_duplicate' => 0
    ]);
    $stmtEvt->execute([
        ':event_id' => "evt_doc_form_start_$k", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'form_start',
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => '📝 Mở Điền Lịch Đặt Chỗ & Giữ Bàn Quán', ':category' => 'reservation', ':item_name' => 'mo_lich_dat_cho',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Lịch Đặt Chỗ & Giữ Bàn NOIR CAFÉ',
        ':payload_hash' => md5("doc_fs_$k"), ':client_time' => $timeAnchor1, ':server_time' => $timeAnchor1, ':is_duplicate' => 0
    ]);
    $stmtEvt->execute([
        ':event_id' => "evt_doc_form_complete_$k", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'form_complete',
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => '📅 Xác Nhận Lịch Đặt Chỗ & Giữ Bàn thành công', ':category' => 'reservation', ':item_name' => 'xac_nhan_lich_dat_cho',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Lịch Đặt Chỗ & Giữ Bàn NOIR CAFÉ',
        ':payload_hash' => md5("doc_fc_$k"), ':client_time' => $timeAnchor1, ':server_time' => $timeAnchor1, ':is_duplicate' => 0
    ]);
    $eventCounter += 3;
}

// =========================================================================
// PHẦN 2: PHÉP THỬ SỐ 3 (SỰ KIỆN QUA NỬA ĐÊM & ĐẾN MUỘN THEO MÚI GIỜ ASIA/HO_CHI_MINH)
// =========================================================================
$sessMidnight = 'sess_midnight_cross';
$uMid = $userProfiles[1]; // Nguyễn Thùy Linh
$stmtSess->execute([
    ':session_id' => $sessMidnight, ':site_id' => 1, ':gender' => 'female',
    ':customer_name' => $uMid['name'], ':customer_address' => $uMid['address'],
    ':first_seen' => $baseYesterday . ' 23:50:00', ':last_seen' => $baseToday . ' 00:10:00'
]);

for ($i = 1; $i <= 10; $i++) {
    $tYest = date('Y-m-d H:i:s', strtotime("$baseYesterday 23:50:00 + $i minutes"));
    $stmtEvt->execute([
        ':event_id' => "evt_yest_$i", ':site_id' => 1, ':session_id' => $sessMidnight, ':event_code' => 'page_view',
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => 'Xem Menu Đêm Muộn Hôm Qua', ':category' => 'milktea', ':item_name' => '',
        ':user_gender' => 'female', ':customer_name' => $uMid['name'], ':customer_address' => $uMid['address'],
        ':form_name' => 'Thực Đơn Trà Sữa Đậm Vị',
        ':payload_hash' => md5("yest_$i"), ':client_time' => $tYest, ':server_time' => $tYest, ':is_duplicate' => 0
    ]);
    $eventCounter++;
}

for ($i = 1; $i <= 10; $i++) {
    $tToday = date('Y-m-d H:i:s', strtotime("$baseToday 00:01:00 + $i minutes"));
    $stmtEvt->execute([
        ':event_id' => "evt_today_$i", ':site_id' => 1, ':session_id' => $sessMidnight, ':event_code' => 'page_view',
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => 'Xem Menu Rạng Sáng Hôm Nay', ':category' => 'milktea', ':item_name' => '',
        ':user_gender' => 'female', ':customer_name' => $uMid['name'], ':customer_address' => $uMid['address'],
        ':form_name' => 'Thực Đơn Trà Sữa Đậm Vị',
        ':payload_hash' => md5("today_$i"), ':client_time' => $tToday, ':server_time' => $tToday, ':is_duplicate' => 0
    ]);
    $eventCounter++;
}

// =========================================================================
// PHẦN 3: PHÉP THỬ SỐ 4 (CHUỖI PHỄU: XEM -> BẮT ĐẦU -> HOÀN TẤT)
// =========================================================================
// 8 Phiên ĐÚNG thứ tự -> Tính là Hoàn Tất (Funnel Success)
for ($f = 1; $f <= 8; $f++) {
    $sId = "sess_funnel_ok_$f";
    $u = $userProfiles[$f % count($userProfiles)];
    $t0 = date('Y-m-d H:i:s', strtotime("$baseToday 10:00:00 + " . ($f * 15) . " minutes"));
    $t1 = date('Y-m-d H:i:s', strtotime("$t0 + 2 minutes"));
    $t2 = date('Y-m-d H:i:s', strtotime("$t0 + 5 minutes"));
    $stmtSess->execute([
        ':session_id' => $sId, ':site_id' => 1, ':gender' => $u['gender'],
        ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':first_seen' => $t0, ':last_seen' => $t2
    ]);

    // B1: Xem
    $stmtEvt->execute([
        ':event_id' => "evt_fok_{$f}_1", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'page_view',
        ':page_url' => '/demo/cafe_shop/menu.php', ':element_text' => 'Khách mở xem danh mục thực đơn', ':category' => 'general', ':item_name' => '',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Thực Đơn Trực Tuyến',
        ':payload_hash' => md5("fok1_$f"), ':client_time' => $t0, ':server_time' => $t0, ':is_duplicate' => 0
    ]);
    // B2: Bắt đầu form
    $stmtEvt->execute([
        ':event_id' => "evt_fok_{$f}_2", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'form_start',
        ':page_url' => '/demo/cafe_shop/checkout.php', ':element_text' => 'Khách nhập thông tin giao hàng', ':category' => 'checkout', ':item_name' => '',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Biểu Mẫu Thanh Toán & Giao Hàng',
        ':payload_hash' => md5("fok2_$f"), ':client_time' => $t1, ':server_time' => $t1, ':is_duplicate' => 0
    ]);
    // B3: Hoàn tất form
    $stmtEvt->execute([
        ':event_id' => "evt_fok_{$f}_3", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'form_complete',
        ':page_url' => '/demo/cafe_shop/checkout.php', ':element_text' => 'Nộp Đơn Đặt Món: ' . $u['dish'], ':category' => 'checkout', ':item_name' => $u['dish'],
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Biểu Mẫu Thanh Toán & Giao Hàng',
        ':payload_hash' => md5("fok3_$f"), ':client_time' => $t2, ':server_time' => $t2, ':is_duplicate' => 0
    ]);
    $eventCounter += 3;
}

// 4 Phiên SAI THỨ TỰ (Ví dụ: nộp form trước khi xem trang hoặc thiếu form_start) -> KHÔNG ĐƯỢC COI LÀ HOÀN TẤT
for ($f = 1; $f <= 4; $f++) {
    $sId = "sess_funnel_bad_$f";
    $u = $userProfiles[($f + 3) % count($userProfiles)];
    $t0 = date('Y-m-d H:i:s', strtotime("$baseToday 14:00:00 + " . ($f * 10) . " minutes"));
    $t1 = date('Y-m-d H:i:s', strtotime("$t0 + 2 minutes"));
    $stmtSess->execute([
        ':session_id' => $sId, ':site_id' => 1, ':gender' => $u['gender'],
        ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':first_seen' => $t0, ':last_seen' => $t1
    ]);

    // Sai thứ tự: form_complete trước!
    $stmtEvt->execute([
        ':event_id' => "evt_fbad_{$f}_1", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'form_complete',
        ':page_url' => '/demo/cafe_shop/checkout.php', ':element_text' => 'Gửi form khi chưa mở phiên', ':category' => 'checkout', ':item_name' => '',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Biểu Mẫu Thanh Toán & Giao Hàng',
        ':payload_hash' => md5("fbad1_$f"), ':client_time' => $t0, ':server_time' => $t0, ':is_duplicate' => 0
    ]);
    // Xem trang sau
    $stmtEvt->execute([
        ':event_id' => "evt_fbad_{$f}_2", ':site_id' => 1, ':session_id' => $sId, ':event_code' => 'page_view',
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => 'Xem Trang Sau Khi Nộp', ':category' => 'coffee', ':item_name' => '',
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => 'Khu Vực Trang Chủ',
        ':payload_hash' => md5("fbad2_$f"), ':client_time' => $t1, ':server_time' => $t1, ':is_duplicate' => 0
    ]);
    $eventCounter += 2;
}

// =========================================================================
// PHẦN 4: PHÉP THỬ SỐ 5 (WEBSITE 2 - EDUPORTAL CÓ 50 SỰ KIỆN ĐỘC LẬP)
// =========================================================================
for ($i = 1; $i <= 50; $i++) {
    $sId2 = 'sess_site2_' . ceil($i / 5);
    $tSite2 = date('Y-m-d H:i:s', strtotime("$baseToday 08:00:00 + " . ($i * 10) . " minutes"));
    $code = ($i % 3 === 0) ? 'btn_click' : 'page_view';
    $stmtEvt->execute([
        ':event_id' => "evt_site2_$i", ':site_id' => 2, ':session_id' => $sId2, ':event_code' => $code,
        ':page_url' => '/demo/site2/index.php', ':element_text' => 'Học viên xem bài giảng ' . $i, ':category' => 'education', ':item_name' => 'Khóa Học AI',
        ':user_gender' => 'unspecified', ':customer_name' => 'Học Viên DNU #' . $i, ':customer_address' => 'Đại Học Đại Nam, Hà Đông, Hà Nội',
        ':form_name' => 'Cổng Học Liệu Trực Tuyến',
        ':payload_hash' => md5("site2_$i"), ':client_time' => $tSite2, ':server_time' => $tSite2, ':is_duplicate' => 0
    ]);
    $eventCounter++;
}

// =========================================================================
// PHẦN 5: BỔ SUNG CÁC SỰ KIỆN TƯƠNG TÁC THƯƠNG MẠI TRÀ SỮA & CÀ PHÊ (ĐẠT ~200 SỰ KIỆN)
// =========================================================================
$remaining = 200 - $eventCounter;
for ($i = 1; $i <= $remaining; $i++) {
    $sId = 'sess_shop_browse_' . ceil($i / 4);
    $t = date('Y-m-d H:i:s', strtotime("$baseToday 16:00:00 + " . ($i * 5) . " minutes"));
    $u = $userProfiles[($i - 1) % count($userProfiles)];
    $code = ($i % 2 === 0) ? 'btn_click' : 'page_view';
    $actText = ($code === 'btn_click') ? ('Bấm chọn món: ' . $u['dish']) : ('Mở xem chi tiết: ' . $u['dish']);
    $formName = ($u['gender'] === 'male') ? 'Thực Đơn Cà Phê Chuẩn Gu' : 'Thực Đơn Trà Sữa Đậm Vị';

    $stmtEvt->execute([
        ':event_id' => "evt_extra_$i", ':site_id' => 1, ':session_id' => $sId, ':event_code' => $code,
        ':page_url' => '/demo/cafe_shop/index.php', ':element_text' => $actText, ':category' => ($u['gender'] === 'male' ? 'coffee' : 'milktea'), ':item_name' => $u['dish'],
        ':user_gender' => $u['gender'], ':customer_name' => $u['name'], ':customer_address' => $u['address'],
        ':form_name' => $formName,
        ':payload_hash' => md5("extra_$i"), ':client_time' => $t, ':server_time' => $t, ':is_duplicate' => 0
    ]);
    $eventCounter++;
}

// Khôi phục mầm đơn hàng và gieo sẵn 2 lỗi tiếp nhận để bảng Ingestion Errors có dữ liệu demo sinh động
seedInitialPurchaseHistory($pdo);

$stmtErr = $pdo->prepare("INSERT INTO ingestion_errors (site_id, event_id, error_type, error_message, created_at) VALUES (:site_id, :evt_id, :type, :msg, :time)");
$stmtErr->execute([
    ':site_id' => 1,
    ':evt_id' => 'evt_err_demo_01',
    ':type' => 'CONFLICT_PAYLOAD',
    ':msg' => 'Xung đột: Mã sự kiện [evt_err_demo_01] gửi lại với nội dung khác so với bản ghi ban đầu.',
    ':time' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
]);
$stmtErr->execute([
    ':site_id' => 1,
    ':evt_id' => 'evt_err_demo_02',
    ':type' => 'FORBIDDEN_INPUT_DATA',
    ':msg' => 'Phát hiện dữ liệu chứa trường cấm bảo mật [password]. Từ chối tiếp nhận.',
    ':time' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
]);

echo json_encode([
    'status' => 'SUCCESS',
    'total_events_generated' => $eventCounter,
    'message' => "Đã tạo thành công chuẩn xác $eventCounter sự kiện kịch bản mẫu bao gồm đầy đủ: Tên biểu mẫu, Tên và Địa chỉ người click chọn!",
    'ground_truth' => [
        'site1_test1_views' => 10,
        'site1_test1_clicks' => 4,
        'site1_funnel_completed_sessions' => 8,
        'site2_total_events' => 50
    ]
]);
