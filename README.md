# PTPMMNM-CNTT-18-10-NHOM15

Bài tập lớn môn Phát triển phần mềm nguồn mở.
- **Nhóm:** 15
- **Lớp:** CNTT 18-10

---

## Danh sách các bài thực hành (Labs)

- [x] **[Lab 01](./lab1)**: Vận hành dự án trên Linux và viết Shell Script
- [x] **[Lab 02](./lab2)**: Quy tắc đăng ký và Kiểm thử đơn vị (Capacity & Registration Policy)
- [x] **[Lab 03](./lab3)**: Xử lý Form, Bộ lọc sự kiện kết hợp và Kiểm tra dữ liệu
- [ ] **Lab 04**
- [ ] **Lab 05**

---

### Hướng dẫn chạy các bài Lab

#### Lab 01
```bash
cd lab1/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
bash lab.sh run      # Chạy ứng dụng web (cổng 8101)
bash lab.sh test     # Kiểm tra bài mẫu
bash lab.sh extend   # Kiểm tra bài mở rộng (scan-source.sh: SHELL_CASES=4 FAILED=0)
```

#### Lab 02
```bash
cd lab2/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
php demo.php         # Chạy demo (AVAILABLE)
bash lab.sh test     # Kiểm tra bài mẫu (TESTS=5 FAILED=0)
bash lab.sh extend   # Kiểm tra bài mở rộng (RegistrationPolicy: TESTS=8 FAILED=0)
```

#### Lab 03
```bash
cd lab3/Ma_nguon
bash lab.sh check    # Kiểm tra môi trường
bash lab.sh run      # Chạy máy chủ xem danh sách & đăng ký (cổng 8101)
bash lab.sh test     # Kiểm tra bài mẫu (TESTS=7 FAILED=0)
bash lab.sh extend   # Kiểm tra bài mở rộng (EventFilter: TESTS=8 FAILED=0)
```
