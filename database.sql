-- =========================================================================
-- CƠ SỞ DỮ LIỆU MYSQL / MARIADB CHO XAMPP (ĐỀ TÀI 17 & LAB 05 - FIT DNU)
-- Môn: Phát triển Phần mềm Mã nguồn mở (Open Source Software Development)
-- Hệ Quản Trị CSDL: MySQL 8.0+ / MariaDB 10.4+ trên XAMPP
-- Engine: InnoDB | Character Set: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =========================================================================

CREATE DATABASE IF NOT EXISTS `analytics_cafe_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `analytics_cafe_db`;

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- 1. BẢNG `websites` (Đăng ký website thử nghiệm)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `websites`;
CREATE TABLE `websites` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_key` VARCHAR(64) NOT NULL UNIQUE,
    `name` VARCHAR(128) NOT NULL,
    `allowed_domains` TEXT,
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 2. BẢNG `event_definitions` (Cấu hình các loại sự kiện được phép theo dõi)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `event_definitions`;
CREATE TABLE `event_definitions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_id` INT NOT NULL,
    `event_code` VARCHAR(64) NOT NULL,
    `name` VARCHAR(128) NOT NULL,
    `allowed` INT DEFAULT 1,
    UNIQUE KEY `uk_site_event` (`site_id`, `event_code`),
    CONSTRAINT `fk_def_website` FOREIGN KEY (`site_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 3. BẢNG `sessions` (Quản lý phiên truy cập, không lưu thông tin nhạy cảm)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
    `session_id` VARCHAR(64) PRIMARY KEY,
    `site_id` INT NOT NULL,
    `user_gender` VARCHAR(20) DEFAULT 'unspecified',
    `ip_prefix` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `first_seen` DATETIME NOT NULL,
    `last_seen` DATETIME NOT NULL,
    KEY `idx_site_id` (`site_id`),
    CONSTRAINT `fk_sessions_website` FOREIGN KEY (`site_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 4. BẢNG `events` (Nhật ký sự kiện thời gian thực, chống trùng lặp Idempotent)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
    `event_id` VARCHAR(64) PRIMARY KEY,
    `site_id` INT NOT NULL,
    `session_id` VARCHAR(64) NOT NULL,
    `event_code` VARCHAR(64) NOT NULL,
    `page_url` VARCHAR(255) NOT NULL,
    `element_text` VARCHAR(128) DEFAULT NULL,
    `category` VARCHAR(64) DEFAULT 'general',
    `item_name` VARCHAR(128) DEFAULT NULL,
    `user_gender` VARCHAR(20) DEFAULT 'unspecified',
    `payload_hash` VARCHAR(64) NOT NULL,
    `client_time` DATETIME NOT NULL,
    `server_time` DATETIME NOT NULL,
    `is_duplicate` INT DEFAULT 0,
    KEY `idx_site_code` (`site_id`, `event_code`),
    KEY `idx_session` (`session_id`),
    KEY `idx_client_time` (`client_time`),
    KEY `idx_payload_hash` (`payload_hash`),
    CONSTRAINT `fk_events_website` FOREIGN KEY (`site_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_events_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 5. BẢNG `orders` (Lưu lịch sử mua hàng phục vụ thuật toán đề xuất thông minh)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_id` INT NOT NULL,
    `session_id` VARCHAR(64) NOT NULL,
    `customer_name` VARCHAR(128) DEFAULT NULL,
    `gender` VARCHAR(20) NOT NULL,
    `primary_category` VARCHAR(64) NOT NULL,
    `items_summary` TEXT NOT NULL,
    `total_price` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    CONSTRAINT `fk_orders_website` FOREIGN KEY (`site_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_orders_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 6. BẢNG `ingestion_errors` (Lưu vết lỗi tiếp nhận: 409 Conflict, 400 Forbidden, 403 Invalid Site)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `ingestion_errors`;
CREATE TABLE `ingestion_errors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_id` INT DEFAULT NULL,
    `event_id` VARCHAR(64) DEFAULT NULL,
    `error_type` VARCHAR(64) NOT NULL,
    `error_message` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    CONSTRAINT `fk_errors_website` FOREIGN KEY (`site_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 7. BẢNG `users` (Đăng ký thành viên & tài khoản khách hàng)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(128) NOT NULL,
    `email` VARCHAR(128) NOT NULL,
    `gender` VARCHAR(20) NOT NULL,
    `role` VARCHAR(32) DEFAULT 'customer',
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 8. CÁC BẢNG THEO ĐÚNG MẪU LAB 05 (FIT-DNU/Open-Source-Software-Development)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `lab_events`;

CREATE TABLE `lab_events` (
    `id` INT PRIMARY KEY,
    `title` VARCHAR(160) NOT NULL,
    `category` VARCHAR(30) NOT NULL,
    `capacity` INT NOT NULL CHECK (`capacity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registrations` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `name` VARCHAR(120) NOT NULL CHECK (length(trim(`name`)) > 0),
    `email` VARCHAR(200) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uk_event_email` UNIQUE (`event_id`, `email`),
    CONSTRAINT `fk_event` FOREIGN KEY (`event_id`) REFERENCES `lab_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- DỮ LIỆU KHỞI TẠO BAN ĐẦU (SEED DATA CHUẨN)
-- =========================================================================

-- Website mẫu 1 (Tiệm Cafe & Trà Sữa) và Website 2 (EduPortal)
INSERT INTO `websites` (`id`, `site_key`, `name`, `allowed_domains`, `created_at`) VALUES
(1, 'site_cafe_tea_01', 'Milky & Coffee Corner - Tiệm Trà Sữa & Cà Phê', 'localhost, 127.0.0.1', NOW()),
(2, 'site_eduportal_02', 'EduPortal - Cổng Học Liệu Trực Tuyến', 'localhost, 127.0.0.1', NOW());

-- 4 loại sự kiện cho Site 1 & Site 2
INSERT INTO `event_definitions` (`site_id`, `event_code`, `name`, `allowed`) VALUES
(1, 'page_view', 'Lượt xem trang (Menu, Sản phẩm, Giỏ hàng)', 1),
(1, 'btn_click', 'Bấm nút chọn món / Thêm vào giỏ / Tải tài liệu', 1),
(1, 'form_start', 'Mở biểu mẫu tương tác', 1),
(1, 'form_complete', 'Hoàn tất đơn hàng / Gửi form', 1),
(2, 'page_view', 'Lượt xem trang học liệu', 1),
(2, 'btn_click', 'Bấm nút tải tài liệu học tập', 1),
(2, 'form_start', 'Bắt đầu làm bài thi', 1),
(2, 'form_complete', 'Nộp bài thi thành công', 1);

-- Dữ liệu mẫu ban đầu cho lịch sử đơn hàng (AI Recommender)
-- Nam: 88% Cà phê | Nữ: 88% Trà sữa
INSERT INTO `orders` (`site_id`, `session_id`, `customer_name`, `gender`, `primary_category`, `items_summary`, `total_price`, `created_at`) VALUES
(1, 'sess_seed_m1', 'Hoàng Minh Đức', 'male', 'coffee', 'Cà Phê Muối Tuyệt Hảo x1', 35000, NOW()),
(1, 'sess_seed_m2', 'Nguyễn Tuấn Anh', 'male', 'coffee', 'Double Espresso Đậm Đà x1', 38000, NOW()),
(1, 'sess_seed_m3', 'Trần Hải Đăng', 'male', 'coffee', 'Cà Phê Sữa Đá Sài Gòn x1', 32000, NOW()),
(1, 'sess_seed_m4', 'Vũ Thành Long', 'male', 'coffee', 'Cold Brew Cam Sả x1', 45000, NOW()),
(1, 'sess_seed_m5', 'Phạm Quốc Cường', 'male', 'coffee', 'Bạc Xỉu 3 Tầng x1', 36000, NOW()),
(1, 'sess_seed_m6', 'Đỗ Minh Quân', 'male', 'coffee', 'Cà Phê Muối Tuyệt Hảo x2', 70000, NOW()),
(1, 'sess_seed_m7', 'Lê Hữu Đạt', 'male', 'coffee', 'Cà Phê Trứng Béo Ngậy x1', 45000, NOW()),
(1, 'sess_seed_m8', 'Bùi Văn Hùng', 'male', 'coffee', 'Americano Đá x1', 30000, NOW()),
(1, 'sess_seed_m9', 'Đinh Trọng Nam', 'male', 'milktea', 'Trà Sữa Ô Long Nướng x1', 48000, NOW()),
(1, 'sess_seed_f1', 'Nguyễn Thùy Linh', 'female', 'milktea', 'Trà Sữa Trân Châu Hoàng Gia x1', 45000, NOW()),
(1, 'sess_seed_f2', 'Trần Thu Trang', 'female', 'milktea', 'Trà Sữa Ô Long Nướng Kem Trứng x1', 48000, NOW()),
(1, 'sess_seed_f3', 'Phạm Khánh Huyền', 'female', 'milktea', 'Trà Sữa Matcha Kyoto x1', 50000, NOW()),
(1, 'sess_seed_f4', 'Đặng Ngọc Ánh', 'female', 'milktea', 'Trà Đào Cam Sả x1', 42000, NOW()),
(1, 'sess_seed_f5', 'Lê Phương Thảo', 'female', 'milktea', 'Trà Sữa Khoai Môn Dẻo x1', 46000, NOW()),
(1, 'sess_seed_f6', 'Vũ Bích Ngọc', 'female', 'milktea', 'Trà Sữa Lài Sữa Tươi x1', 45000, NOW()),
(1, 'sess_seed_f7', 'Bùi Diệu Linh', 'female', 'milktea', 'Trà Sữa Trân Châu Đường Đen x1', 52000, NOW()),
(1, 'sess_seed_f8', 'Hoàng Bảo Yến', 'female', 'coffee', 'Cà Phê Muối Tuyệt Hảo x1', 35000, NOW());

-- Thành viên mẫu trong bảng users
INSERT INTO `users` (`fullname`, `email`, `gender`, `role`, `created_at`) VALUES
('Hoàng Minh Đức', 'duc.minh@dnu.edu.vn', 'male', 'admin', NOW()),
('Nguyễn Thùy Linh', 'thuylinh.fit@dnu.edu.vn', 'female', 'customer', NOW());

-- Sự kiện mẫu cho bảng theo chuẩn Lab 05
INSERT INTO `lab_events` (`id`, `title`, `category`, `capacity`) VALUES
(1, 'Hội thảo Phần mềm Nguồn mở & Chuyển đổi số 2026', 'technology', 150),
(2, 'Workshop Lập trình Web Fullstack với PHP & MySQL', 'education', 80),
(3, 'Ngày hội Khởi nghiệp Công nghệ Sinh viên DNU', 'community', 200);

INSERT INTO `registrations` (`event_id`, `name`, `email`, `created_at`) VALUES
(1, 'Hoàng Minh Đức', 'duc.minh@dnu.edu.vn', NOW()),
(2, 'Nguyễn Thùy Linh', 'thuylinh.fit@dnu.edu.vn', NOW());
