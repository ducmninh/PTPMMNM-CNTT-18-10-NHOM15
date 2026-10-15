# PTPMMNM-CNTT-18-10-NHOM15

Bài tập lớn môn Phát triển phần mềm nguồn mở.
- Nhóm: 15
- Lớp: CNTT 18-10

---

## Lab01 — Bài thực hành dành cho sinh viên

Nhánh này chỉ chứa một bài lab. Dùng Ubuntu/WSL, PHP từ 8.1 và Git.

Không cần PostgreSQL hoặc Composer.

## Chuẩn bị trên Ubuntu/WSL

```bash
sudo apt update
sudo apt install php-cli git unzip
php -v
git --version
```

PHP cần từ 8.1 trở lên. Các lệnh bên dưới chạy trong Ubuntu/WSL.

## Chạy bài

```bash
cd Ma_nguon
bash lab.sh check
bash lab.sh run
```

Mở http://127.0.0.1:8101. Giữ terminal mở; Ctrl+C để dừng.

## Kiểm tra

Mở terminal thứ hai tại Ma_nguon, hoặc dừng máy chủ trước:

```bash
bash lab.sh test
bash lab.sh extend
```

- `check`: kiểm tra môi trường
- `test`: kiểm tra bài mẫu (`CHECK_ENV=PASS`)
- `extend`: kiểm tra bài tập mở rộng (`SHELL_CASES=4 FAILED=0`)

[Hướng dẫn đầy đủ](HUONG_DAN.md) · [Hướng dẫn mã nguồn](Ma_nguon/README.md)

