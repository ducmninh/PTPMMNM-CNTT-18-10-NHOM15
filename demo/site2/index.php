<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPortal - Cổng Học Liệu Trực Tuyến (Website Thử Nghiệm 2)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: #0f172a; color: #f8fafc; min-height: 100vh; padding: 30px 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .brand { font-size: 20px; font-weight: 800; color: #38bdf8; display: flex; align-items: center; gap: 8px; }
        .btn { padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; border: none; cursor: pointer; }
        .btn-dash { background: rgba(255,255,255,0.1); color: #fff; }
        .btn-primary { background: #0284c7; color: #fff; }
        .hero { background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 30px; margin-bottom: 25px; }
        .course-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 20px; }
        .course-card { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 18px; }
    </style>
</head>
<body>

    <div class="container">
        <div class="navbar">
            <div class="brand">🎓 EduPortal Online</div>
            <div>
                <a href="../cafe_shop/index.php" class="btn btn-dash" style="margin-right: 8px;">☕ Tiệm Cafe & Trà Sữa (Site 1)</a>
                <a href="../../index.php" target="_blank" class="btn btn-dash">📊 Xem Dashboard</a>
            </div>
        </div>

        <div class="hero">
            <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                WEBSITE THỬ NGHIỆM ĐỘC LẬP SỐ 2 (Site Key: site_eduportal_02)
            </span>
            <h1 style="font-size: 26px; font-weight: 800; margin: 12px 0 8px;">Cổng Khóa Học Kỹ Thuật Lập Trình</h1>
            <p style="color: #94a3b8; font-size: 14px; line-height: 1.5;">
                Trang web này phục vụ kiểm thử Phép Thử 5: <strong>"Hai website không lẫn số liệu"</strong>. Mọi sự kiện và lượt xem ở đây đều được gắn nhãn riêng với Website ID 2, không bị pha lẫn vào số liệu của Tiệm Trà Sữa & Cà Phê!
            </p>

            <div class="course-grid">
                <div class="course-card">
                    <h3 style="font-size: 16px; margin-bottom: 6px;">Khoa học Dữ liệu &amp; AI</h3>
                    <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Nhập môn phân tích dữ liệu hành vi người dùng</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button class="btn btn-primary btn-track" data-category="document" data-item="tai_lieu_pdf" onclick="handleEduDownload('Khoa học Dữ liệu & AI')">
                            📄 Tải Tài Liệu PDF
                        </button>
                        <button class="btn btn-dash btn-track" data-category="course" data-item="them_vao_gio" onclick="handleEduAddToCart('Khoa học Dữ liệu & AI')">
                            🛒 Thêm Vào Giỏ
                        </button>
                    </div>
                </div>
                <div class="course-card">
                    <h3 style="font-size: 16px; margin-bottom: 6px;">Lập Trình Web PHP Chuyên Sâu</h3>
                    <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Xây dựng RESTful API và hệ thống theo dõi</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button class="btn btn-primary btn-track" data-category="document" data-item="tai_lieu_pdf" onclick="handleEduDownload('Lập Trình Web PHP Chuyên Sâu')">
                            📄 Tải Tài Liệu PDF
                        </button>
                        <button class="btn btn-dash btn-track" data-category="course" data-item="them_vao_gio" onclick="handleEduAddToCart('Lập Trình Web PHP Chuyên Sâu')">
                            🛒 Thêm Vào Giỏ
                        </button>
                    </div>
                </div>
            </div>

            <!-- Nút Đăng Ký Tư Vấn Trực Tuyến -->
            <div style="margin-top: 16px; display: flex; gap: 12px; align-items: center;">
                <button type="button" class="btn btn-track" data-category="consult" data-item="dang_ky_tu_van" onclick="handleEduConsult()" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4);">
                    📞 Đăng Ký Tư Vấn Lộ Trình Học
                </button>
            </div>

            <!-- FORM GỬI BIỂU MẪU ĐĂNG KÝ HỌC LIỆU -->
            <div style="margin-top: 30px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 22px;">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 10px; color: #38bdf8;">
                    📝 Gửi Biểu Mẫu Đăng Ký Nhận Bộ Học Liệu
                </h3>
                <form id="eduForm" onsubmit="handleEduSubmit(event)" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end;">
                    <div>
                        <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Họ tên học viên:</label>
                        <input type="text" id="eduName" placeholder="Ví dụ: Nguyễn Văn A" required style="width: 100%; padding: 8px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; color: #fff;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Email nhận tài liệu:</label>
                        <input type="email" id="eduEmail" placeholder="hocvien@gmail.com" required style="width: 100%; padding: 8px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; color: #fff;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-track" data-category="edu" data-item="gui_bieu_mau_edu" style="height: 38px;">
                        🚀 Gửi Biểu Mẫu
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SDK Tracker riêng cho Website 2 -->
    <script src="../../assets/js/tracker.js" data-site-key="site_eduportal_02"></script>
        function handleEduDownload(name) {
            alert('📄 Đã tải tài liệu PDF môn học: ' + (name || 'Khóa học') + '!\nSự kiện button_click (tải tài liệu PDF) đã gửi về server.');
        }
        function handleEduAddToCart(name) {
            alert('🛒 Đã thêm khóa học [' + name + '] vào giỏ học phần!\nSự kiện button_click (thêm vào giỏ) đã gửi về server.');
        }
        function handleEduConsult() {
            alert('📞 Bạn vừa bấm Đăng Ký Tư Vấn Khóa Học!\nSự kiện button_click (đăng ký tư vấn) đã gửi về server.');
        }
        function handleEduSubmit(e) {
            e.preventDefault();
            const name = document.getElementById('eduName').value.trim();
            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('form_complete', {
                    element_text: 'Gửi biểu mẫu EduPortal: ' + name,
                    category: 'edu',
                    item_name: 'gui_bieu_mau_edu'
                });
            }
            alert('🎉 Gửi biểu mẫu thành công! Báo cáo đã cập nhật số thao tác.');
            document.getElementById('eduForm').reset();
        }
    </script>
</body>
</html>
