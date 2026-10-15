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
| **Lab 04** | *(Cập nhật theo tiến độ học phần)* | ⏳ *Chưa bắt đầu* | — | — |
| **Lab 05** | *(Cập nhật theo tiến độ học phần)* | ⏳ *Chưa bắt đầu* | — | — |

---

## 🚀 Hướng dẫn chạy và kiểm thử các bài Lab

### 🔹 Lab 01 — Linux & Shell Script
```bash
cd lab1/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường PHP & Git
bash lab.sh run      # Khởi chạy trang web mẫu (http://127.0.0.1:8101)
bash lab.sh test     # Kiểm tra bài mẫu
bash lab.sh extend   # Kiểm tra bài tập mở rộng (scan-source.sh)
```

### 🔹 Lab 02 — Kiểm thử đơn vị & Quy tắc đăng ký
```bash
cd lab2/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
php demo.php         # Chạy demo kiểm tra sức chứa (AVAILABLE)
bash lab.sh test     # Kiểm tra quy tắc sức chứa mẫu
bash lab.sh extend   # Kiểm tra quy tắc đăng ký mở rộng (RegistrationPolicy)
```

### 🔹 Lab 03 — Xử lý Form & Bộ lọc sự kiện
```bash
cd lab3/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
bash lab.sh run      # Khởi chạy ứng dụng web sự kiện (http://127.0.0.1:8101)
bash lab.sh test     # Kiểm tra form đăng ký và tìm kiếm cơ bản
bash lab.sh extend   # Kiểm tra bộ lọc kết hợp mở rộng (EventFilter)
```
