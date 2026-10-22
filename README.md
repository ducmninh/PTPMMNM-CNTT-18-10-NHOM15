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
| **Lab 04** | Quản lý bản nháp bằng Session và Cookie | 🔄 **Đang thực hiện** | — | [Xem Lab 04](./lab4) |
| **Lab 05** | Hệ thống quản lý thông tin sinh viên và điểm (PostgreSQL / MySQL) | 🔄 **Đang thực hiện** | — | [Xem Lab 05](./lab5) |

---

## 📝 Chi tiết kết quả thực hiện Lab 1, Lab 2, Lab 3

### 📌 Lab 01 — Vận hành dự án trên Linux và viết Shell Script
- **Mục tiêu:** Sử dụng dòng lệnh Linux/WSL, quản lý biến, điều kiện, vòng lặp, xử lý chuỗi và mã thoát trong Shell Script.
- **Tệp hoàn thiện:** [`lab1/Ma_nguon/scripts/scan-source.sh`](./lab1/Ma_nguon/scripts/scan-source.sh)
- **Nội dung thực hiện:**
  - **TODO 1 (Kiểm tra đầu vào):** Kiểm tra thư mục có tồn tại và đọc được hay không (`[[ ! -d "$root" || ! -r "$root" ]]`), kiểm tra công cụ `php` trên hệ thống. Nếu không hợp lệ, xuất thông báo lỗi ra stderr và thoát với mã `2`.
  - **TODO 2 (Duyệt cú pháp PHP):** Sử dụng vòng lặp duyệt qua danh sách các file PHP (xử lý an toàn đường dẫn có khoảng trắng), tăng biến đếm `total`, kiểm tra cú pháp với `php -l "$file"`, tăng `passed` hoặc `failed`. Không ngắt vòng lặp khi gặp tệp bị lỗi cú pháp.
  - **TODO 3 (Mã thoát tổng hợp):** Trả mã thoát `2` nếu thư mục rỗng/không chứa tệp PHP; trả mã `1` nếu có tệp sai cú pháp; trả mã `0` khi tất cả tệp đều đạt.
- **Kết quả kiểm thử:**
  - `bash lab.sh check` $\rightarrow$ `MOI_TRUONG=SAN_SANG | Lab 1`
  - `bash lab.sh test` $\rightarrow$ `CHECK_ENV=PASS` (mã thoát `0`)
  - `bash lab.sh extend` $\rightarrow$ **`SHELL_CASES=4 FAILED=0`** (vượt qua cả 4 trường hợp: `valid`, `mixed`, `missing`, `empty`).

---

### 📌 Lab 02 — Quy tắc kiểm tra sức chứa và Ba trạng thái đăng ký
- **Mục tiêu:** Xây dựng module nghiệp vụ hướng đối tượng độc lập với giao diện, áp dụng kiểm thử hồi quy và kiểm thử đơn vị tự động.
- **Tệp hoàn thiện:** [`lab2/Ma_nguon/src/RegistrationPolicy.php`](./lab2/Ma_nguon/src/RegistrationPolicy.php)
- **Nội dung thực hiện:**
  - Viết phương thức `decide(int $capacity, int $registered, bool $open, bool $waitingEnabled): string`:
    - Dữ liệu không hợp lệ (sức chứa $\le 0$, số đã đăng ký $< 0$ hoặc vượt quá sức chứa) $\rightarrow$ Trả về `'rejected'`.
    - Dữ liệu hợp lệ nhưng sự kiện đã đóng (`!$open`) $\rightarrow$ Trả về `'rejected'`.
    - Sự kiện mở và còn chỗ trống ($registered < capacity$) $\rightarrow$ Trả về `'confirmed'`.
    - Sự kiện mở, hết chỗ và có bật danh sách chờ (`$waitingEnabled`) $\rightarrow$ Trả về `'waiting'`.
    - Sự kiện mở, hết chỗ và không có danh sách chờ $\rightarrow$ Trả về `'rejected'`.
- **Kết quả kiểm thử:**
  - `php demo.php` $\rightarrow$ Trả về `AVAILABLE` với cặp giá trị `(10, 9)`
  - `bash lab.sh test` $\rightarrow$ **`TESTS=5 FAILED=0`** (vượt qua cả 5 ca kiểm thử sức chứa cơ bản)
  - `bash lab.sh extend` $\rightarrow$ **`TESTS=8 FAILED=0`** (vượt qua đủ 8 ca kiểm thử mở rộng gồm các tình huống đúng, sai, biên và dữ liệu bất thường).

---

### 📌 Lab 03 — Xử lý Form, Bộ lọc kết hợp và Kiểm tra dữ liệu
- **Mục tiêu:** Xử lý dữ liệu Form GET/POST an toàn, chống lỗ hổng XSS bằng mã hóa ký tự HTML, xây dựng bộ lọc sự kiện đa điều kiện kết hợp.
- **Tệp hoàn thiện:** 
  - [`lab3/Ma_nguon/src/EventFilter.php`](./lab3/Ma_nguon/src/EventFilter.php): Hoàn thiện hàm `filterEvents($events, $query, $category, $availableOnly)` kết hợp cả 3 điều kiện lọc theo logic `AND`. Trả về mảng dữ liệu mới, giữ nguyên mảng gốc không bị biến đổi.
  - [`lab3/Ma_nguon/public/index.php`](./lab3/Ma_nguon/public/index.php): Đọc các tham số qua GET (`q`, `category`, `available`), gọi hàm lọc với đầy đủ 4 tham số, bổ sung thẻ `<select>` chọn loại và `<input type="checkbox">` lọc còn chỗ vào biểu mẫu tìm kiếm, đồng thời giữ nguyên trạng thái đã chọn sau khi gửi form.
- **Kết quả kiểm thử:**
  - `bash lab.sh test` $\rightarrow$ **`TESTS=7 FAILED=0`** (kiểm thử tìm kiếm, chống mã độc XSS `escape-html`, xác thực form hợp lệ và không hợp lệ)
  - `bash lab.sh extend` $\rightarrow$ **`TESTS=8 FAILED=0`** (đạt toàn bộ các ca: `all`, `keyword`, `category`, `available`, `combined`, `full-category`, `unknown-category`, `input-unchanged`)
  - `php tests/ui-demo.php` $\rightarrow$ **`DEMO_DATA_ASSERTIONS=PASS`** (kiểm thử 6 sự kiện và 96 chỗ ngồi mẫu).

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
bash lab.sh run      # Khởi chạy ứng dụng web sự kiện (http://127.0.0.1:8101)
bash lab.sh test     # Kiểm tra form đăng ký và tìm kiếm cơ bản (TESTS=7 FAILED=0)
bash lab.sh extend   # Kiểm tra bộ lọc kết hợp mở rộng (TESTS=8 FAILED=0)
```
