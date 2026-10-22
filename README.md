# PTPMMNM-CNTT-18-10-NHOM15

Dự án bài tập thực hành môn **Phát triển phần mềm mã nguồn mở**.
- **Khoa:** Công nghệ thông tin — Trường Đại học Đại Nam (DNU)
- **Lớp:** CNTT 18-10
- **Nhóm:** 15

---

## 👥 Danh sách thành viên nhóm

| STT | Họ và tên | Mã sinh viên | GitHub | Vai trò |
| :---: | :--- | :---: | :---: | :---: |
| 1 | **Dương Đức Minh** | `1871020396` | [@ducmninh](https://github.com/ducmninh) | Trưởng nhóm |
| 2 | **Nguyễn Nhật Minh** | `1871020395` | [@minhneee](https://github.com/minhneee) | Thành viên |
| 3 | **Ngô Minh Khôi** | `1871020335` | [@MinhKhoi111](https://github.com/MinhKhoi111) | Thành viên |

---

## 📊 Tiến trình làm bài thực hành (Lab Progress Tracker)

| Bài Lab | Nội dung thực hành | Trạng thái | Kết quả kiểm thử | Thư mục |
| :---: | :--- | :---: | :---: | :---: |
| **Lab 01** | Vận hành dự án trên Linux & Viết Shell Script quét mã nguồn | ✅ **Đã hoàn thành** | `CHECK_ENV=PASS`<br>`SHELL_CASES=4 FAILED=0` | [Xem Lab 01](./lab1) |
| **Lab 02** | Quy tắc kiểm tra sức chứa & Ba trạng thái đăng ký (`decide`) | ✅ **Đã hoàn thành** | `TESTS=5 FAILED=0`<br>`TESTS=8 FAILED=0` | [Xem Lab 02](./lab2) |
| **Lab 03** | Xử lý Form GET/POST, Lọc sự kiện kết hợp (`AND`) & Xem trước | ✅ **Đã hoàn thành** | `TESTS=7 FAILED=0`<br>`TESTS=8 FAILED=0` | [Xem Lab 03](./lab3) |
| **Lab 04** | Quản lý bản nháp bằng Session và Cookie | ✅ **Đã hoàn thành** | `TESTS=6 FAILED=0`<br>`TESTS=7 FAILED=0` | [Xem Lab 04](./Lap4) |
| **Lab 05** | Lưu, truy vấn và thống kê đăng ký với PostgreSQL | ✅ **Đã hoàn thành** | `TESTS=6 FAILED=0`<br>`TESTS=7 FAILED=0`<br>`TESTS=4 FAILED=0` | [Xem Lab 05](./lap5) |

---

## 📝 Chi tiết kết quả thực hiện Lab 1, Lab 2, Lab 3, Lab 4, Lab 5

### 📌 Lab 01 — Vận hành dự án trên Linux và viết Shell Script
- **Mục tiêu:** Sử dụng dòng lệnh Linux/WSL, quản lý biến, điều kiện, vòng lặp, xử lý chuỗi và mã thoát trong Shell Script.
- **Tệp hoàn thiện:** [`lab1/Ma_nguon/scripts/scan-source.sh`](./lab1/Ma_nguon/scripts/scan-source.sh)
- **Nội dung thực hiện:**
  - **TODO 1 (Kiểm tra đầu vào):** Kiểm tra thư mục có tồn tại và đọc được hay không (`[[ ! -d "$root" || ! -r "$root" ]]`), kiểm tra công cụ `php` trên hệ thống. Nếu không hợp lệ, xuất thông báo lỗi ra stderr và thoát với mã `2`.
  - **TODO 2 (Duyệt cú pháp PHP):** Sử dụng vòng lặp duyệt qua danh sách các file PHP (xử lý an toàn đường dẫn có khoảng trắng), tăng biến đếm `total`, kiểm tra cú pháp với `php -l "$file"`, tăng `passed` hoặc `failed`. Không ngắt vòng lặp khi gặp tệp bị lỗi cú pháp.
  - **TODO 3 (Mã thoát tổng hợp):** Trả mã thoát `2` nếu thư mục rỗng/không chứa tệp PHP; trả mã `1` nếu có tệp sai cú pháp; trả mã `0` khi tất cả tệp đều đạt.
- **Kết quả kiểm thử:**
  - `bash lab.sh check` → `MOI_TRUONG=SAN_SANG | Lab 1`
  - `bash lab.sh test` → `CHECK_ENV=PASS` (mã thoát `0`)
  - `bash lab.sh extend` → **`SHELL_CASES=4 FAILED=0`** (vượt qua cả 4 trường hợp: `valid`, `mixed`, `missing`, `empty`).

---

### 📌 Lab 02 — Quy tắc kiểm tra sức chứa và Ba trạng thái đăng ký
- **Mục tiêu:** Xây dựng module nghiệp vụ hướng đối tượng độc lập với giao diện, áp dụng kiểm thử hồi quy và kiểm thử đơn vị tự động.
- **Tệp hoàn thiện:** [`lab2/Ma_nguon/src/RegistrationPolicy.php`](./lab2/Ma_nguon/src/RegistrationPolicy.php)
- **Nội dung thực hiện:**
  - Viết phương thức `decide(int $capacity, int $registered, bool $open, bool $waitingEnabled): string`:
    - Dữ liệu không hợp lệ (sức chứa ≤ 0, số đã đăng ký < 0 hoặc vượt quá sức chứa) → Trả về `'rejected'`.
    - Dữ liệu hợp lệ nhưng sự kiện đã đóng (`!$open`) → Trả về `'rejected'`.
    - Sự kiện mở và còn chỗ trống (registered < capacity) → Trả về `'confirmed'`.
    - Sự kiện mở, hết chỗ và có bật danh sách chờ (`$waitingEnabled`) → Trả về `'waiting'`.
    - Sự kiện mở, hết chỗ và không có danh sách chờ → Trả về `'rejected'`.
- **Kết quả kiểm thử:**
  - `php demo.php` → Trả về `AVAILABLE` với cặp giá trị `(10, 9)`
  - `bash lab.sh test` → **`TESTS=5 FAILED=0`** (vượt qua cả 5 ca kiểm thử sức chứa cơ bản)
  - `bash lab.sh extend` → **`TESTS=8 FAILED=0`** (vượt qua đủ 8 ca kiểm thử mở rộng gồm các tình huống đúng, sai, biên và dữ liệu bất thường).

---

### 📌 Lab 03 — Xử lý Form, Bộ lọc kết hợp và Kiểm tra dữ liệu
- **Mục tiêu:** Xử lý dữ liệu Form GET/POST an toàn, chống lỗ hổng XSS bằng mã hóa ký tự HTML, xây dựng bộ lọc sự kiện đa điều kiện kết hợp.
- **Tệp hoàn thiện:** 
  - [`lab3/Ma_nguon/src/EventFilter.php`](./lab3/Ma_nguon/src/EventFilter.php): Hoàn thiện hàm `filterEvents($events, $query, $category, $availableOnly)` kết hợp cả 3 điều kiện lọc theo logic `AND`. Trả về mảng dữ liệu mới, giữ nguyên mảng gốc không bị biến đổi.
  - [`lab3/Ma_nguon/public/index.php`](./lab3/Ma_nguon/public/index.php): Đọc các tham số qua GET (`q`, `category`, `available`), gọi hàm lọc với đầy đủ 4 tham số, bổ sung thẻ `<select>` chọn loại và `<input type="checkbox">` lọc còn chỗ vào biểu mẫu tìm kiếm, đồng thời giữ nguyên trạng thái đã chọn sau khi gửi form.
- **Kết quả kiểm thử:**
  - `bash lab.sh test` → **`TESTS=7 FAILED=0`** (kiểm thử tìm kiếm, chống mã độc XSS `escape-html`, xác thực form hợp lệ và không hợp lệ)
  - `bash lab.sh extend` → **`TESTS=8 FAILED=0`** (đạt toàn bộ các ca: `all`, `keyword`, `category`, `available`, `combined`, `full-category`, `unknown-category`, `input-unchanged`)
  - `php tests/ui-demo.php` → **`DEMO_DATA_ASSERTIONS=PASS`** (kiểm thử 6 sự kiện và 96 chỗ ngồi mẫu).

---

### 📌 Lab 04 — Quản lý bản nháp bằng Session và Cookie
- **Mục tiêu:** Phân biệt dữ liệu Session ở phía máy chủ và tùy chọn Cookie phía trình duyệt. Xây luồng nhập, xác nhận, sửa, hủy và hoàn tất mô phỏng. Kiểm tra nháp vắng mặt, hết hạn và hai phiên độc lập.
- **Tệp hoàn thiện:**
  - [`Lap4/Ma_nguon/src/Draft.php`](./Lap4/Ma_nguon/src/Draft.php): Hàm `saveDraft`, `draftExpired`, `cancelDraft` quản lý bản nháp trong Session.
  - [`Lap4/Ma_nguon/src/DraftGuard.php`](./Lap4/Ma_nguon/src/DraftGuard.php): Hoàn thiện hàm `canComplete` kiểm tra bản nháp tồn tại, đúng event, TTL dương, thời điểm tạo hợp lệ và tuổi bản nháp chưa hết hạn.
  - [`Lap4/Ma_nguon/public/register.php`](./Lap4/Ma_nguon/public/register.php): Nhận form đăng ký, lưu bản nháp vào Session, chuyển trang xác nhận.
  - [`Lap4/Ma_nguon/public/confirm.php`](./Lap4/Ma_nguon/public/confirm.php): Đọc nháp từ Session để xem lại, cho phép sửa hoặc hủy.
  - [`Lap4/Ma_nguon/public/complete.php`](./Lap4/Ma_nguon/public/complete.php): Kết thúc mô phỏng, xóa nháp rồi hiển thị SIMULATED_OK.
  - [`Lap4/Ma_nguon/public/cancel.php`](./Lap4/Ma_nguon/public/cancel.php): Xử lý hủy bản nháp bằng POST.
  - [`Lap4/Ma_nguon/public/preferences.php`](./Lap4/Ma_nguon/public/preferences.php): Lưu tùy chọn hiển thị vào Cookie (`compact`/`full`).
- **Nội dung thực hiện:**
  - **Bài mẫu:** Đọc và thực hiện lại luồng nhập → lưu nháp → xác nhận → sửa/hủy. Session lưu `draft` gồm `data` và `created_at`. Cookie `view` chỉ chứa `compact` hoặc `full`.
  - **Bài mở rộng:** Viết hàm `canComplete(array $session, int $requestedEventId, int $now, int $ttl): bool` kiểm tra:
    - Phải có bản nháp (`draft` tồn tại trong session)
    - Đúng event (`event_id` khớp)
    - TTL phải dương (> 0)
    - `created_at` không ở tương lai
    - Tuổi bản nháp < TTL (chưa hết hạn)
- **Kết quả kiểm thử:**
  - `bash lab.sh test` → **`TESTS=6 FAILED=0`** (kiểm thử lưu, hết hạn, hủy bản nháp)
  - `bash lab.sh extend` → **`TESTS=7 FAILED=0`** (kiểm thử: `valid`, `missing`, `wrong-event`, `before-expiry`, `at-expiry`, `future-created-at`, `invalid-ttl`)

---

### 📌 Lab 05 — Lưu, truy vấn và thống kê đăng ký với PostgreSQL
- **Mục tiêu:** Kết nối PHP với PostgreSQL, lưu/đọc đăng ký bằng truy vấn tham số hóa, chống trùng theo cặp sự kiện–email, và thống kê số đăng ký kể cả sự kiện chưa có người tham dự.
- **Tệp hoàn thiện:**
  - [`lap5/Ma_nguon/src/RegistrationRepository.php`](./lap5/Ma_nguon/src/RegistrationRepository.php): Phương thức `save` lưu đăng ký trả `CREATED`/`DUPLICATE`; `listForEvent` đọc danh sách theo sự kiện.
  - [`lap5/Ma_nguon/src/RegistrationStats.php`](./lap5/Ma_nguon/src/RegistrationStats.php): Hàm `registrationStats` dùng `LEFT JOIN` để đếm số đăng ký theo sự kiện — sự kiện chưa có người trả `total=0`, ID không tồn tại trả mảng rỗng.
  - [`lap5/Ma_nguon/src/Database.php`](./lap5/Ma_nguon/src/Database.php): Mở kết nối và thực thi SQL có tham số qua pgsql.
  - [`lap5/Ma_nguon/demo-db.php`](./lap5/Ma_nguon/demo-db.php): Kiểm tra CSDL qua dòng lệnh trước khi nối giao diện.
  - [`lap5/Ma_nguon/public/complete.php`](./lap5/Ma_nguon/public/complete.php): Thay hoàn tất mô phỏng bằng lưu CSDL thực; chỉ xóa nháp sau khi lưu thành công.
- **Nội dung thực hiện:**
  - **Bài mẫu:** Tạo schema PostgreSQL (bảng `events`, `registrations` với ràng buộc `UNIQUE(event_id, email)`), lưu đăng ký bằng `INSERT ... ON CONFLICT DO NOTHING RETURNING id`, đọc danh sách và kiểm tra CREATED/DUPLICATE.
  - **Bài mở rộng:** Viết hàm `registrationStats(PgSql\Connection $connection, int $eventId): array` dùng `LEFT JOIN` giữa `events` và `registrations`, đếm `COUNT(r.id)` để phân biệt sự kiện chưa có đăng ký (total=0) và ID không tồn tại (trả `[]`).
- **Kết quả kiểm thử:**
  - `bash lab.sh test` → **`TESTS=6 FAILED=0`** (kiểm thử Draft cơ bản)
  - `bash lab.sh test` (tích hợp) → **`TESTS=7 FAILED=0`** (tạo, trùng, dấu nháy đơn, danh sách, 2 người)
  - `bash lab.sh extend` → **`TESTS=4 FAILED=0`** (zero-included, one, duplicate-no-change, missing)

---

## 🚀 Hướng dẫn chạy và kiểm thử các bài Lab

### 🔹 Lab 01 — Linux & Shell Script
```bash
cd lab1/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường PHP & Git
bash lab.sh run      # Khởi chạy trang web mẫu (http://127.0.0.1:8101)
bash lab.sh test     # Kiểm tra bài mẫu (CHECK_ENV=PASS)
bash lab.sh extend   # Kiểm tra bài mở rộng (scan-source.sh: SHELL_CASES=4 FAILED=0)
```

### 🔹 Lab 02 — Kiểm thử đơn vị & Quy tắc đăng ký
```bash
cd lab2/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
php demo.php         # Chạy demo kiểm tra sức chứa (AVAILABLE)
bash lab.sh test     # Kiểm tra quy tắc sức chứa mẫu (TESTS=5 FAILED=0)
bash lab.sh extend   # Kiểm tra quy tắc đăng ký mở rộng (TESTS=8 FAILED=0)
```

### 🔹 Lab 03 — Xử lý Form & Bộ lọc sự kiện
```bash
cd lab3/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
bash lab.sh run      # Khởi chạy ứng dụng web sự kiện (http://127.0.0.1:8103)
bash lab.sh test     # Kiểm tra form đăng ký và tìm kiếm cơ bản (TESTS=7 FAILED=0)
bash lab.sh extend   # Kiểm tra bộ lọc kết hợp mở rộng (TESTS=8 FAILED=0)
```

### 🔹 Lab 04 — Session & Cookie
```bash
cd Lap4/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường PHP & Git
bash lab.sh run      # Khởi chạy ứng dụng web (http://127.0.0.1:8104)
bash lab.sh test     # Kiểm tra bài mẫu (TESTS=6 FAILED=0)
bash lab.sh extend   # Kiểm tra bài mở rộng (TESTS=7 FAILED=0)
```

### 🔹 Lab 05 — PostgreSQL: Lưu, truy vấn và thống kê đăng ký
```bash
cd lap5/Ma_nguon
bash lab.sh db-config  # Cấu hình kết nối PostgreSQL (chỉ lần đầu)
bash lab.sh check      # Kiểm tra môi trường PHP, pgsql, psql
bash lab.sh sql        # Mở psql để chạy schema và kiểm tra
bash lab.sh run        # Khởi chạy ứng dụng web (http://127.0.0.1:8105)
bash lab.sh test       # Kiểm tra bài mẫu (TESTS=6 FAILED=0 và TESTS=7 FAILED=0)
bash lab.sh extend     # Kiểm tra thống kê mở rộng (TESTS=4 FAILED=0)
```
