<p align="center">
  <h1 align="center">☕ Web Analytics & User Behavior Tracker</h1>
  <h3 align="center">Đề tài 17 – Xây dựng công cụ thống kê lượt truy cập và thao tác trên website</h3>
  <p align="center">
    <strong>Học phần: Phát triển phần mềm mã nguồn mở</strong><br/>
    Trường Đại học Đại Nam – Khoa Công nghệ Thông tin<br/>
    Giảng viên hướng dẫn: <strong>ThS. Lê Tuấn Anh</strong>
  </p>
</p>



---

## 👥 Thành viên nhóm 15

| STT | Họ và Tên | Vai trò | Nhiệm vụ chính |
|-----|-----------|---------|----------------|
| 1 | **Dương Đức Minh** | Trưởng nhóm | Kiến trúc hệ thống, Database MySQL, Backend Ingestion API, Dashboard Chart.js |
| 2 | **Ngô Minh Khôi** | Thành viên | Thư viện tracker.js, Module CRUD sự kiện, Export CSV/JSON |
| 3 | **Nguyễn Nhật Minh** | Thành viên | Website thực nghiệm Noir Café, Kiểm thử tự động, Giao diện |

---

## 📋 Giới thiệu đề tài

Hệ thống **Web Analytics & User Behavior Tracker** là một công cụ thống kê lượt truy cập và phân tích hành vi người dùng **hoàn toàn tự chủ, mã nguồn mở**, được xây dựng trên nền tảng **Apache + PHP + MySQL** mà không phụ thuộc vào bất kỳ dịch vụ bên thứ ba nào (như Google Analytics).

Để kiểm nghiệm thực tế, nhóm đã xây dựng **website thực nghiệm "Tiệm Cà phê & Trà sữa Noir Café"** và nhúng bộ tracker vào để thu thập toàn bộ hành vi khách hàng: duyệt menu, thêm giỏ hàng, cuộn trang, nhấn nút đặt hàng…

### 🎯 Các chức năng chính

| Tính năng | Mô tả |
|-----------|-------|
| **📡 Tracker SDK (tracker.js)** | Thư viện JavaScript siêu nhẹ (<15KB), ghi nhận click, scroll depth, dwell time, lỗi JS |
| **🔒 Ingestion API (api/track.php)** | Tiếp nhận sự kiện qua Fetch/Beacon API, ẩn danh hóa IP, chống SQL Injection |
| **📊 Analytics Dashboard** | Bảng điều khiển Chart.js: biểu đồ lưu lượng theo giờ, top sản phẩm, thiết bị |
| **🛠️ Quản trị sự kiện CRUD** | Xem, lọc, tìm kiếm, sửa ghi chú, xóa log, xuất CSV/JSON |
| **☕ Website Noir Café** | Testbed thực nghiệm: menu đồ uống, giỏ hàng, form đặt hàng có validation |
| **🧪 Kiểm thử tự động** | test_runner.php – 6/6 kịch bản kiểm thử đạt Ground Truth 100% |

---

## 🏗️ Kiến trúc hệ thống

```
┌─────────────────────────────────────────────────────────────┐
│                     CLIENT (Trình duyệt)                     │
│  ┌──────────────┐    ┌──────────────┐    ┌───────────────┐  │
│  │ Noir Café    │    │  tracker.js  │    │  dashboard.js  │  │
│  │ Website      │───▶│  SDK Nhẹ    │    │  Chart.js UI  │  │
│  └──────────────┘    └──────┬───────┘    └───────────────┘  │
└─────────────────────────────│───────────────────────────────┘
                              │ Fetch API / Beacon API (JSON)
┌─────────────────────────────▼───────────────────────────────┐
│                     SERVER (XAMPP / Apache)                   │
│  ┌──────────────────┐    ┌────────────────────────────────┐  │
│  │  api/track.php   │    │  index.php (Dashboard Admin)   │  │
│  │  Ingestion API   │    │  admin_events.php (CRUD)       │  │
│  │  - Xác thực      │    │  export.php (CSV/JSON)         │  │
│  │  - Ẩn danh IP    │    └────────────────────────────────┘  │
│  │  - PDO + MySQL   │                  │                     │
│  └──────────┬────────┘                 │                     │
└─────────────│───────────────────────────────────────────────┘
              │
┌─────────────▼───────────────┐
│   MySQL: analytics_cafe_db  │
│  websites | sessions        │
│  events   | orders          │
│  event_definitions | users  │
└─────────────────────────────┘
```

---

## 📁 Cấu trúc mã nguồn

```
ducmninh/
├── api/                        # Backend Ingestion & Report APIs
│   ├── track.php               # ⭐ Cổng tiếp nhận sự kiện tracking
│   ├── reports.php             # API truy vấn thống kê cho Dashboard
│   ├── event_manager.php       # API CRUD quản lý sự kiện
│   ├── recommendations.php     # API gợi ý sản phẩm
│   ├── register_site.php       # Đăng ký website theo dõi
│   └── register_user.php       # Quản lý tài khoản quản trị
├── assets/
│   ├── css/
│   │   ├── dashboard.css       # Giao diện Admin Dashboard
│   │   ├── noir.css            # Giao diện website Noir Café
│   │   └── shop.css            # CSS trang mua sắm
│   └── js/
│       ├── tracker.js          # ⭐ Thư viện theo dõi hành vi Client
│       ├── dashboard.js        # Logic biểu đồ Chart.js Dashboard
│       └── recommender.js      # Logic gợi ý sản phẩm Frontend
├── config/
│   └── db.php                  # Kết nối PDO MySQL (cấu hình tại đây)
├── demo/
│   ├── cafe_shop/              # ⭐ Website thực nghiệm Noir Café
│   │   ├── index.php           # Trang chủ & menu đồ uống
│   │   ├── menu.php            # Thực đơn chi tiết
│   │   ├── checkout.php        # Giỏ hàng & đặt hàng
│   │   ├── register.php        # Đăng ký thành viên
│   │   └── img/                # Ảnh sản phẩm
│   └── site2/                  # Website thực nghiệm thứ 2
├── index.php                   # ⭐ Trang Admin Analytics Dashboard
├── admin_events.php            # ⭐ Quản trị sự kiện CRUD
├── login.php                   # Đăng nhập hệ thống
├── logout.php                  # Đăng xuất
├── export.php                  # Xuất báo cáo CSV/JSON
├── database.sql                # ⭐ Script khởi tạo CSDL MySQL
├── cron_cleanup.php            # Dọn dẹp log cũ tự động
├── simulate_errors.php         # Mô phỏng lỗi để kiểm thử
├── test_runner.php             # Kiểm thử tự động 6 kịch bản
└── import_mysql_utf8.php       # Tiện ích import database
```

---

## ⚙️ Hướng dẫn cài đặt và chạy

### Yêu cầu hệ thống

- **XAMPP** (Apache 2.4+ & MySQL/MariaDB 10.4+)
- **PHP 8.0** trở lên
- Trình duyệt web hiện đại (Chrome, Firefox, Edge)
- **Không cần** Node.js, Composer hay bất kỳ framework nào

---

### Bước 1: Clone repository

```bash
git clone https://github.com/ducmninh/PTPMMNM-CNTT-18-10-NHOM15.git
```

Hoặc tải về dạng ZIP và giải nén vào thư mục:

```
C:\xampp\htdocs\ducmninh\
```

---

### Bước 2: Khởi động XAMPP

1. Mở **XAMPP Control Panel**
2. Nhấn **Start** cho **Apache** và **MySQL**
3. Đảm bảo cả hai đều chuyển sang trạng thái xanh ✅

---

### Bước 3: Khởi tạo cơ sở dữ liệu

**Cách 1 – Dùng phpMyAdmin (Khuyên dùng):**

1. Mở trình duyệt → truy cập `http://localhost/phpmyadmin`
2. Tạo database mới tên: `analytics_cafe_db`
3. Chọn tab **SQL** → Dán toàn bộ nội dung file `database.sql` vào → Nhấn **Go**

**Cách 2 – Dùng tiện ích import có sẵn:**

```
http://localhost/ducmninh/import_mysql_utf8.php
```

---

### Bước 4: Cấu hình kết nối database

Mở file `config/db.php` và kiểm tra các thông số:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'analytics_cafe_db');
define('DB_USER', 'root');       // Tên đăng nhập MySQL (mặc định XAMPP: root)
define('DB_PASS', '');           // Mật khẩu MySQL (mặc định XAMPP: để trống)
define('DB_CHAR', 'utf8mb4');
```

> ⚠️ **Lưu ý:** Nếu bạn đã cài mật khẩu MySQL khác thì cập nhật `DB_PASS` tương ứng.

---

### Bước 5: Chạy hệ thống

Mở trình duyệt và truy cập theo thứ tự:

| Địa chỉ | Mô tả |
|---------|-------|
| `http://localhost/ducmninh/login.php` | 🔑 Trang đăng nhập quản trị |
| `http://localhost/ducmninh/` | 📊 Dashboard phân tích (sau khi đăng nhập) |
| `http://localhost/ducmninh/admin_events.php` | 🛠️ Quản trị sự kiện CRUD |
| `http://localhost/ducmninh/demo/cafe_shop/` | ☕ Website thực nghiệm Noir Café |
| `http://localhost/ducmninh/test_runner.php` | 🧪 Chạy kiểm thử tự động |

---

### Tài khoản đăng nhập mặc định

> Tài khoản được tạo sẵn trong `database.sql`:

| Trường | Giá trị |
|--------|---------|
| Username | `admin` |
| Password | `admin123` |

> 💡 Hãy đổi mật khẩu sau khi đăng nhập lần đầu để bảo mật.

---

## 🚀 Hướng dẫn sử dụng

### 1. Theo dõi website của bạn

Nhúng thư viện tracker vào bất kỳ website nào bạn muốn theo dõi:

```html
<!-- Dán vào trước thẻ </body> của website cần theo dõi -->
<script>
  window._track_site_key = 'your_site_key_here';
</script>
<script src="http://localhost/ducmninh/assets/js/tracker.js" defer></script>
```

### 2. Xem Dashboard phân tích

Sau khi đăng nhập, Dashboard hiển thị:
- 📈 **Biểu đồ đường** – Lưu lượng truy cập theo giờ trong ngày
- 📊 **Biểu đồ cột** – Top sản phẩm/nút bấm được tương tác nhiều nhất
- 🍩 **Biểu đồ tròn** – Phân bố thiết bị (Desktop / Mobile / Tablet)
- 🔴 **Live Feed** – 10 sự kiện mới nhất theo thời gian thực

### 3. Quản trị sự kiện CRUD

Tại `admin_events.php`, bạn có thể:
- Lọc sự kiện theo loại: `pageview`, `click`, `scroll_depth`, `order_submit`, `js_error`
- Tìm kiếm theo URL hoặc Session ID
- Chỉnh sửa ghi chú, xóa log không cần thiết
- Xuất báo cáo **CSV** hoặc **JSON**

### 4. Dọn dẹp log tự động (Cron Job)

Thêm vào Task Scheduler của Windows hoặc cron Linux:

```bash
# Chạy mỗi ngày lúc 2:00 sáng
0 2 * * * php /path/to/ducmninh/cron_cleanup.php
```

---

## 🗄️ Mô hình cơ sở dữ liệu

```
websites ──────┬── event_definitions
               │
               └── sessions ──────┬── events
                                   ├── orders
                                   └── ingestion_errors

users (Quản trị viên hệ thống)
```

| Bảng | Mô tả |
|------|-------|
| `websites` | Danh sách website đăng ký theo dõi |
| `event_definitions` | Cấu hình các loại sự kiện cho phép |
| `sessions` | Phiên truy cập (ẩn danh, không lưu IP đầy đủ) |
| `events` | Nhật ký chi tiết từng thao tác tương tác |
| `orders` | Đơn hàng từ website thực nghiệm Noir Café |
| `ingestion_errors` | Log lỗi JavaScript phía client |
| `users` | Tài khoản quản trị viên hệ thống |

---

## 🔐 Bảo mật

- ✅ **PDO Prepared Statements** – Chống SQL Injection tuyệt đối
- ✅ **IP Masking** – Ẩn danh hóa địa chỉ IP người dùng (192.168.1.xxx)
- ✅ **password_hash/verify** – Mã hóa mật khẩu BCRYPT
- ✅ **Session Authentication** – Bảo vệ toàn bộ trang quản trị
- ✅ **Origin/Domain Validation** – Kiểm tra `allowed_domains` trước khi ghi dữ liệu

---

## 🛠️ Công nghệ sử dụng

| Thành phần | Công nghệ | Phiên bản |
|-----------|-----------|-----------|
| Web Server | Apache HTTP Server (XAMPP) | 2.4.x |
| Backend | PHP | 8.2.x |
| Database | MySQL / MariaDB | 8.0 / 10.4 |
| Frontend Tracking | Vanilla JavaScript | ES6+ |
| Interactive UI | jQuery | 3.7.x |
| Data Visualization | Chart.js | 4.4.x |
| Styling | HTML5 / CSS3 | Modern |

---

## 📄 Giấy phép

Dự án được phát hành theo giấy phép **MIT License** – hoàn toàn miễn phí sử dụng, nghiên cứu và phát triển.

---

<p align="center">
  Được phát triển bởi <strong>Nhóm 15 – Lớp CNTT 18-10</strong><br/>
  Trường Đại học Đại Nam | Học kỳ I, Năm học 2026-2027<br/>
  ⭐ Nếu dự án có ích, hãy cho chúng mình một Star nhé!
</p>
