<?php
// NOIR CAFÉ — TRẢI NGHIỆM CÀ PHÊ ĐIỆN ẢNH
// Tích hợp hệ thống phân tích hành vi & gợi ý cá nhân hóa đồ uống (Đề tài 17)
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOIR CAFÉ — Trải Nghiệm Cà Phê Điện Ảnh | Có những khoảnh khắc xứng đáng để ta chậm lại</title>
    <meta name="description" content="Thương hiệu cà phê lấy cảm hứng từ điện ảnh, những cuộn phim cũ và khoảnh khắc con người tạm dừng giữa nhịp sống bận rộn. Cà phê chuẩn vị, không gian tĩnh lặng và một chút thảnh thơi để thở.">
    
    <!-- Hệ Thống Thiết Kế CSS -->
    <link rel="stylesheet" href="../../assets/css/noir.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../assets/css/shop.css?v=<?= time() ?>">
    <?php if (isset($_GET['skip_modal'])): ?>
    <script>
    sessionStorage.setItem('gender_selected_session', '1');
    localStorage.setItem('user_gender_authenticated', '1');
    localStorage.setItem('user_gender', 'male');
    localStorage.setItem('user_name', 'Hoàng Minh Đức');
    </script>
    <?php endif; ?>
    <?php if (isset($_GET['scroll'])): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const el = document.getElementById('<?= htmlspecialchars($_GET['scroll']) ?>');
            if (el) el.scrollIntoView();
        }, 400);
    });
    </script>
    <?php endif; ?>
</head>
<body>

    <!-- A. THANH ĐIỀU HƯỚNG CHÍNH -->
    <header>
        <nav class="noir-nav" id="mainNav">
            <a href="index.php" class="noir-brand">
                <span class="noir-brand-name">NOIR CAFÉ</span>
                <span class="noir-brand-tag">Từ Năm 1998</span>
            </a>

            <div class="noir-nav-links">
                <a href="#cauChuyen" class="noir-link">Câu Chuyện</a>
                <a href="#thucDon" class="noir-link">Thực Đơn</a>
                <a href="#quyTrinh" class="noir-link">Nghệ Thuật Pha Chế</a>
                <a href="#khongGian" class="noir-link">Không Gian Quán</a>
                <a href="#nhatKy" class="noir-link">Góc Chiêm Nghiệm</a>
                <a href="#gheTham" class="noir-link">Ghé Thăm Quán</a>
                <a href="javascript:void(0)" onclick="RecommenderEngine.openGenderModal(false)" class="noir-link" style="color: var(--c-amber); font-weight: 700;">✦ Đăng Ký Thành Viên</a>
            </div>

            <div class="noir-nav-actions">
                <!-- Nút Đổi Gu Thưởng Thức Cá Nhân (Đề Tài 17) -->
                <div id="userProfileNavBadge" class="user-persona-pill" onclick="RecommenderEngine.openGenderModal(false)" title="Bấm vào để đổi gu hoặc đăng ký lại thẻ thành viên">
                    <span id="userPersonaIcon">☕</span>
                    <span id="userPersonaName">Khách Quý</span>
                    <span style="color: var(--c-amber); font-size: 0.72rem; font-weight: 700;">(Đổi Gu)</span>
                </div>

                <!-- Nút Giỏ Hàng Trượt Nhanh -->
                <button type="button" class="cart-trigger-btn btn-track" data-category="general" data-item="mo_gio_hang" onclick="toggleCartDrawer(true)">
                    <span>Giỏ Đồ Uống</span>
                    <span id="cartCountBadge" class="cart-badge" style="display: none;">0</span>
                </button>

                <!-- Nút Menu Di Động -->
                <button type="button" class="mobile-menu-toggle" onclick="toggleMobileNav()" aria-label="Mở danh mục menu">
                    ☰
                </button>
            </div>
        </nav>
    </header>

    <!-- MENU DI ĐỘNG (MOBILE NAVIGATION) -->
    <div id="mobileNavMenu" style="display:none; position:fixed; top:70px; left:0; width:100%; background:#1a100c; border-bottom:1px solid rgba(242,233,218,0.15); padding:24px 6%; z-index:999;">
        <div style="display:flex; flex-direction:column; gap:16px;">
            <a href="#cauChuyen" class="noir-link" onclick="toggleMobileNav()">Câu Chuyện</a>
            <a href="#thucDon" class="noir-link" onclick="toggleMobileNav()">Thực Đơn Đặc Trưng</a>
            <a href="#quyTrinh" class="noir-link" onclick="toggleMobileNav()">Nghệ Thuật Pha Chế</a>
            <a href="#khongGian" class="noir-link" onclick="toggleMobileNav()">Không Gian Quán</a>
            <a href="#nhatKy" class="noir-link" onclick="toggleMobileNav()">Góc Chiêm Nghiệm</a>
            <a href="#gheTham" class="noir-link" onclick="toggleMobileNav()">Ghé Thăm Quán</a>
            <a href="javascript:void(0)" onclick="toggleMobileNav(); RecommenderEngine.openGenderModal(false);" class="noir-link" style="color:var(--c-amber); font-weight:700;">✦ Đăng Ký Thành Viên</a>
            <a href="../../index.php" target="_blank" class="noir-link" style="color:var(--c-warm-beige);">📊 Bảng Báo Cáo Thống Kê</a>
        </div>
    </div>

    <!-- B. KHU VỰC HERO — KHOẢNG LẶNG ĐIỆN ẢNH -->
    <section class="noir-hero" id="hero">
        <div class="hero-overlay-gradient"></div>
        <div class="hero-content">
            <span class="eyebrow">CÀ PHÊ · KHÔNG GIAN · SỐNG CHẬM</span>
            <h1 class="hero-title">
                Tìm khoảng lặng<br>
                <em>của riêng bạn.</em>
            </h1>
            <p class="hero-desc">
                Cà phê chuẩn vị, không gian tĩnh lặng và một chút thảnh thơi để thở. Tạm dừng nhịp sống vội vã để thưởng thức trọn vẹn từng giọt hương vị nguyên bản.
            </p>
            <div class="hero-actions" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                <a href="#thucDon" class="btn-noir-primary btn-track" data-category="hero" data-item="kham_pha_menu">
                    Khám Phá Thực Đơn
                </a>
                <a href="javascript:void(0)" onclick="downloadMenuPdf(event)" class="btn-noir-ghost btn-track btn-download-pdf" data-category="document" data-item="tai_lieu_pdf" title="Tải tài liệu thực đơn điện tử (PDF)" style="border-color: rgba(242, 233, 218, 0.4);">
                    📄 Tải Tài Liệu PDF
                </a>
                <a href="#gheTham" class="btn-noir-ghost btn-track btn-consult-trigger" data-category="consult" data-item="dang_ky_tu_van" title="Đăng ký tư vấn bàn tiệc và góc thưởng thức">
                    📞 Đăng Ký Tư Vấn
                </a>
                <a href="#khongGian" class="btn-noir-ghost btn-track" data-category="hero" data-item="kham_pha_khong_gian">
                    Khám Phá Không Gian
                </a>
            </div>
        </div>

        <div class="hero-scroll-cue">
            <span>CUỘN XUỐNG ĐỂ CHẬM LẠI</span>
            <div class="hero-scroll-line"></div>
        </div>
    </section>

    <!-- C. CÂU CHUYỆN THƯƠNG HIỆU (NỀN GIẤY KEM ẤM ÁP) -->
    <section class="noir-statement" id="cauChuyen">
        <div class="statement-grid">
            <div class="statement-text-col">
                <span class="eyebrow">TRIẾT LÝ SỐNG</span>
                <h2 class="statement-quote" style="margin-top: 14px;">
                    "Không phải khoảnh khắc nào<br>
                    <em>cũng cần phải lấp đầy."</em>
                </h2>
                <p class="statement-body">
                    Tại NOIR CAFÉ, chúng tôi tin rằng một tách cà phê ngon không đơn thuần là nguồn caffeine giúp tỉnh táo tức thời. Đó là một chiếc vé đưa bạn trở về với hiện tại, chậm rãi cảm nhận ánh nắng chiều buông trên mặt bàn gỗ mộc, nghe tiếng máy xay rộn rã và tìm lại sự cân bằng trong tâm trí.
                </p>
                <div class="statement-meta-row">
                    <div class="statement-meta-col">
                        <span class="num">1998</span>
                        <span class="lbl">Năm Khởi Nguồn</span>
                    </div>
                    <div class="statement-meta-col">
                        <span class="num">100%</span>
                        <span class="lbl">Hạt Cà Phê Mộc Thượng Hạng</span>
                    </div>
                    <div class="statement-meta-col">
                        <span class="num">18 Giờ</span>
                        <span class="lbl">Chiết Xuất Lạnh Chậm Rãi</span>
                    </div>
                </div>
            </div>

            <div class="statement-image-frame">
                <img src="img/espresso.jpg" alt="Chiết xuất Espresso mộc thủ công tại quầy pha chế NOIR CAFÉ" loading="lazy">
                <div class="statement-caption">
                    <span>Hình 01 — Chiết Xuất Thủ Công Bằng Tay</span>
                    <span>Áp Suất Vi Mô Chuẩn 9 Bar</span>
                </div>
            </div>
        </div>
    </section>

    <!-- D. PHẦN GỢI Ý MÓN THÔNG MINH AI (ĐỒNG BỘ MÀU TỐI SANG TRỌNG · ĐỀ TÀI 17) -->
    <section style="background-color: var(--c-espresso-deep); padding: 70px 6% 30px; border-top: 1px solid var(--c-border-dark);">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <span class="eyebrow">AI GỢI Ý THÔNG MINH · ĐỀ TÀI 17</span>
                    <h3 style="font-family: var(--font-serif); font-size: 2.1rem; color: #FAF6F0; margin-top: 6px;">
                        Gợi Ý Món Chuẩn Gu Riêng Bạn
                    </h3>
                </div>
                <button type="button" class="btn-noir-ghost" onclick="RecommenderEngine.openGenderModal(false)" style="font-size: 0.76rem; padding: 8px 18px;">
                    ⚡ Đổi Gu Thưởng Thức
                </button>
            </div>
            
            <!-- Vùng Render Kết Quả Gợi Ý Từ Recommender Engine -->
            <div id="recommendationContainer">
                <!-- Sẽ được fill động bởi RecommenderEngine.fetchAndRenderRecommendations() -->
            </div>
        </div>
    </section>

    <!-- E. THỰC ĐƠN ĐẶC TRƯNG & CHỌN MÓN BÁN HÀNG -->
    <section class="noir-menu-section" id="thucDon">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="menu-header-bar">
                <div>
                    <span class="eyebrow">TUYỂN TẬP TINH HOA</span>
                    <h2 class="menu-title" style="margin-top: 10px;">Thực Đơn Đặc Trưng</h2>
                    <p style="color: rgba(242, 233, 218, 0.65); font-size: 0.95rem; margin-top: 8px; font-weight: 300;">
                        Tuyển chọn những dòng thức uống thủ công tinh hoa, được pha chế tỉ mỉ từng mẻ nhỏ.
                    </p>
                </div>

                <!-- Bộ Lọc Danh Mục Món (Hoạt Động Thật 100% Tiếng Việt) -->
                <div class="menu-filter-nav">
                    <button type="button" class="filter-btn active" onclick="filterMenu('all', this)">Tất Cả Món</button>
                    <button type="button" class="filter-btn" onclick="filterMenu('espresso', this)">Espresso &amp; Cà Phê Pha Máy</button>
                    <button type="button" class="filter-btn" onclick="filterMenu('signature', this)">Món Đặc Biệt</button>
                    <button type="button" class="filter-btn" onclick="filterMenu('coldbrew', this)">Cà Phê Ủ Lạnh</button>
                    <button type="button" class="filter-btn" onclick="filterMenu('tea', this)">Trà &amp; Sữa Tươi</button>
                </div>
            </div>

            <!-- Lưới Sản Phẩm Nghệ Thuật -->
            <div class="noir-menu-grid" id="menuGridContainer">
                
                <!-- Món 1: Noir Espresso -->
                <article class="menu-item-card" data-category="espresso">
                    <div>
                        <div class="item-visual-wrap">
                            <img src="img/espresso.jpg" alt="Noir Espresso" loading="lazy">
                            <span class="item-tag-badge">Hạt Rang Nhà Làm</span>
                        </div>
                        <div class="item-content">
                            <div class="item-header-line">
                                <h3 class="item-name">Noir Espresso</h3>
                                <span class="item-price">45.000 đ</span>
                            </div>
                            <p class="item-desc">
                                Đậm đà, lớp crema dày mịn màu hổ phách, mang nốt hương socola đen nguyên chất và vỏ cam sấy.
                            </p>
                            <div class="item-notes">
                                <span class="note-chip">Độ đậm: 5/5</span>
                                <span class="note-chip">Hạt Cầu Đất 1650m</span>
                                <span class="note-chip">Double Shot</span>
                            </div>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <button type="button" class="btn-detail" onclick="openItemQuickview('cf_espresso', 'Noir Espresso (Cà Phê Đậm Vị)', 45000, 'img/espresso.jpg', 'Đậm đà, lớp crema dày mịn màu hổ phách, mang nốt hương socola đen nguyên chất và vỏ cam sấy.', 'Độ đậm: 5/5 · Hạt Cầu Đất 1650m · Double Shot chiết xuất 27 giây')">
                            Chi Tiết Món
                        </button>
                        <button type="button" class="btn-order-add btn-track" data-category="espresso" data-item="cf_espresso" onclick="RecommenderEngine.addToCart('cf_espresso', 'Noir Espresso', 45000, 'coffee', 'img/espresso.jpg')">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </article>

                <!-- Món 2: Brown Butter Latte -->
                <article class="menu-item-card" data-category="signature">
                    <div>
                        <div class="item-visual-wrap">
                            <img src="img/latte.jpg" alt="Brown Butter Latte" loading="lazy">
                            <span class="item-tag-badge">Món Độc Quyền</span>
                        </div>
                        <div class="item-content">
                            <div class="item-header-line">
                                <h3 class="item-name">Latte Bơ Nâu Pháp</h3>
                                <span class="item-price">65.000 đ</span>
                            </div>
                            <p class="item-desc">
                                Sữa tươi nóng quyện cùng bơ nâu tự nấu kiểu Pháp, tạo hương thơm hạt phỉ rang bùi béo khó quên.
                            </p>
                            <div class="item-notes">
                                <span class="note-chip">Bơ Nâu Pháp</span>
                                <span class="note-chip">Béo Ngậy Tự Nhiên</span>
                                <span class="note-chip">Tạo Bọt Thủ Công</span>
                            </div>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <button type="button" class="btn-detail" onclick="openItemQuickview('cf_brownbutter', 'Latte Bơ Nâu Pháp', 65000, 'img/latte.jpg', 'Sữa tươi nóng quyện cùng bơ nâu tự nấu kiểu Pháp, tạo hương thơm hạt phỉ rang bùi béo khó quên.', 'Bơ Pháp gia nhiệt chậm · Hạt Arabica thượng hạng · Sữa tươi thanh trùng Dalat Milk')">
                            Chi Tiết Món
                        </button>
                        <button type="button" class="btn-order-add btn-track" data-category="signature" data-item="cf_brownbutter" onclick="RecommenderEngine.addToCart('cf_brownbutter', 'Latte Bơ Nâu Pháp', 65000, 'coffee', 'img/latte.jpg')">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </article>

                <!-- Món 3: Sea Salt Cold Brew -->
                <article class="menu-item-card" data-category="coldbrew">
                    <div>
                        <div class="item-visual-wrap">
                            <img src="https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80" alt="Sea Salt Cold Brew" loading="lazy">
                            <span class="item-tag-badge">Ủ Lạnh 18 Giờ</span>
                        </div>
                        <div class="item-content">
                            <div class="item-header-line">
                                <h3 class="item-name">Cold Brew Muối Biển</h3>
                                <span class="item-price">58.000 đ</span>
                            </div>
                            <p class="item-desc">
                                Cà phê ủ lạnh 18 giờ kết hợp với lớp bọt kem muối biển mặn nhẹ bồng bềnh, mượt mà và thanh thoát.
                            </p>
                            <div class="item-notes">
                                <span class="note-chip">Ủ Lạnh Chậm</span>
                                <span class="note-chip">Kem Muối Hồng</span>
                                <span class="note-chip">Vị Thanh Ít Đắng</span>
                            </div>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <button type="button" class="btn-detail" onclick="openItemQuickview('cf_seasalt_cb', 'Cold Brew Muối Biển', 58000, 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80', 'Cà phê ủ lạnh 18 giờ kết hợp với lớp bọt kem muối biển mặn nhẹ bồng bềnh, mượt mà và thanh thoát.', 'Cà phê Ethiopia & Khe Sanh ủ lạnh sâu · Muối hồng Himalaya tự nhiên')">
                            Chi Tiết Món
                        </button>
                        <button type="button" class="btn-order-add btn-track" data-category="coldbrew" data-item="cf_seasalt_cb" onclick="RecommenderEngine.addToCart('cf_seasalt_cb', 'Cold Brew Muối Biển', 58000, 'coffee', 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80')">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </article>

                <!-- Món 4: Vanilla Bean Flat White -->
                <article class="menu-item-card" data-category="espresso">
                    <div>
                        <div class="item-visual-wrap">
                            <img src="img/latte.jpg" alt="Vanilla Bean Flat White" loading="lazy">
                            <span class="item-tag-badge">Vani Tự Nhiên</span>
                        </div>
                        <div class="item-content">
                            <div class="item-header-line">
                                <h3 class="item-name">Flat White Vani Quả</h3>
                                <span class="item-price">62.000 đ</span>
                            </div>
                            <p class="item-desc">
                                Chiết xuất Ristretto đậm đặc kết hợp cùng bọt sữa micro-foam siêu mịn và chiết xuất quả vani Madagascar thơm dịu.
                            </p>
                            <div class="item-notes">
                                <span class="note-chip">Vani Madagascar</span>
                                <span class="note-chip">Bọt Sữa Mịn</span>
                                <span class="note-chip">Ristretto 20 Giây</span>
                            </div>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <button type="button" class="btn-detail" onclick="openItemQuickview('cf_vanilla_flat', 'Flat White Vani Quả', 62000, 'img/latte.jpg', 'Chiết xuất Ristretto đậm đặc kết hợp cùng bọt sữa micro-foam siêu mịn và chiết xuất quả vani Madagascar.', 'Qủa Vani tự nhiên · Sữa tươi nóng 65 độ C · Hương hoa quả ngọt ngào')">
                            Chi Tiết Món
                        </button>
                        <button type="button" class="btn-order-add btn-track" data-category="espresso" data-item="cf_vanilla_flat" onclick="RecommenderEngine.addToCart('cf_vanilla_flat', 'Flat White Vani Quả', 62000, 'coffee', 'img/latte.jpg')">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </article>

                <!-- Món 5: Orange Blossom Cold Brew -->
                <article class="menu-item-card" data-category="coldbrew">
                    <div>
                        <div class="item-visual-wrap">
                            <img src="https://images.unsplash.com/photo-1541167760496-1628856ab772?w=800&q=80" alt="Orange Blossom Cold Brew" loading="lazy">
                            <span class="item-tag-badge">Thức Uống Mùa Hè</span>
                        </div>
                        <div class="item-content">
                            <div class="item-header-line">
                                <h3 class="item-name">Cold Brew Hoa Cam</h3>
                                <span class="item-price">62.000 đ</span>
                            </div>
                            <p class="item-desc">
                                Vị đắng thanh của cà phê ủ chậm kết hợp cùng tinh dầu hoa cam thanh khiết và lát cam vàng sấy lạnh.
                            </p>
                            <div class="item-notes">
                                <span class="note-chip">Tinh Dầu Hoa Cam</span>
                                <span class="note-chip">Tươi Mát Giải Nhiệt</span>
                                <span class="note-chip">Ủ Lạnh 18 Giờ</span>
                            </div>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <button type="button" class="btn-detail" onclick="openItemQuickview('cf_orange_cb', 'Cold Brew Hoa Cam', 62000, 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=800&q=80', 'Vị đắng thanh của cà phê ủ chậm kết hợp cùng tinh dầu hoa cam thanh khiết và lát cam vàng sấy lạnh.', 'Cam vàng hữu cơ · Cold Brew ủ mộc mẻ nhỏ · Rất thơm và giải khát')">
                            Chi Tiết Món
                        </button>
                        <button type="button" class="btn-order-add btn-track" data-category="coldbrew" data-item="cf_orange_cb" onclick="RecommenderEngine.addToCart('cf_orange_cb', 'Cold Brew Hoa Cam', 62000, 'coffee', 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=800&q=80')">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </article>

                <!-- Món 6: Classic Matcha Latte -->
                <article class="menu-item-card" data-category="tea">
                    <div>
                        <div class="item-visual-wrap">
                            <img src="https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=800&q=80" alt="Classic Matcha Latte" loading="lazy">
                            <span class="item-tag-badge">Chuẩn Nghi Lễ Nhật</span>
                        </div>
                        <div class="item-content">
                            <div class="item-header-line">
                                <h3 class="item-name">Matcha Latte Uji Kyoto</h3>
                                <span class="item-price">55.000 đ</span>
                            </div>
                            <p class="item-desc">
                                Bột trà xanh Uji Kyoto vụ xuân xay cối đá, kết hợp cùng sữa tươi béo ngậy, giữ trọn hậu vị umami lắng đọng.
                            </p>
                            <div class="item-notes">
                                <span class="note-chip">Matcha Uji Kyoto</span>
                                <span class="note-chip">Hậu Vị Umami</span>
                                <span class="note-chip">Đánh Chổi Tre</span>
                            </div>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <button type="button" class="btn-detail" onclick="openItemQuickview('ts_matcha_kyoto', 'Matcha Latte Uji Kyoto', 55000, 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=800&q=80', 'Bột trà xanh Uji Kyoto vụ xuân xay cối đá, kết hợp cùng sữa tươi béo ngậy, giữ trọn hậu vị umami lắng đọng.', 'Matcha chuẩn nghi lễ Nhật Bản · Đánh bằng chổi tre Chasen truyền thống')">
                            Chi Tiết Món
                        </button>
                        <button type="button" class="btn-order-add btn-track" data-category="tea" data-item="ts_matcha_kyoto" onclick="RecommenderEngine.addToCart('ts_matcha_kyoto', 'Matcha Latte Uji Kyoto', 55000, 'milktea', 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=800&q=80')">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </article>

            </div>
            
            <p style="margin-top: 36px; text-align: center; font-size: 0.8rem; color: rgba(242, 233, 218, 0.45); letter-spacing: 0.1em;">
                * Dữ liệu menu mẫu thể hiện phong cách NOIR. Bạn có thể thay đổi tên món, ảnh và đơn giá trực tiếp trong mã nguồn.
            </p>
        </div>
    </section>

    <!-- F. QUY TRÌNH PHA CHẾ (NGHỆ THUẬT PHA CHẾ CÀ PHÊ) -->
    <section class="noir-ritual" id="quyTrinh">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="ritual-header">
                <span class="eyebrow" style="justify-content: center;">QUY TRÌNH THỦ CÔNG</span>
                <h2 style="font-family: var(--font-serif); font-size: clamp(2.4rem, 4vw, 3.4rem); color: #FAF6F0; margin-top: 12px;">
                    Nghệ Thuật Pha Chế
                </h2>
                <p style="color: rgba(242, 233, 218, 0.7); font-size: 1rem; margin-top: 10px; font-weight: 300;">
                    Hành trình đánh thức từng hạt cà phê qua 4 bước chuẩn mực từ nông trại đến tách gốm trên tay bạn.
                </p>
            </div>

            <div class="ritual-grid">
                <div class="ritual-step-card">
                    <span class="step-num">01</span>
                    <h3 class="step-title">Chọn Hạt</h3>
                    <p class="step-desc">
                        Chỉ thu hái những trái cà phê chín mọng 100% bằng tay trên đỉnh đồi Cầu Đất (1.650m) và Khe Sanh. Tuyển lựa hạt đạt chuẩn Specialty SCAA trên 84 điểm.
                    </p>
                </div>

                <div class="ritual-step-card">
                    <span class="step-num">02</span>
                    <h3 class="step-title">Xay Mộc</h3>
                    <p class="step-desc">
                        Rang mộc mẻ nhỏ nhằm tôn vinh acid hữu cơ tự nhiên. Xay mịn chính xác bằng cối nghiền phẳng Mazzer chống ma sát nhiệt để giữ trọn tầng hương.
                    </p>
                </div>

                <div class="ritual-step-card">
                    <span class="step-num">03</span>
                    <h3 class="step-title">Chiết Xuất</h3>
                    <p class="step-desc">
                        Áp suất chuẩn xác 9 bar kết hợp nguồn nước lọc khoáng tinh sạch 93°C, hoặc ủ lạnh sâu 18 giờ để chưng cất những giọt tinh chất trọn vẹn nhất.
                    </p>
                </div>

                <div class="ritual-step-card">
                    <span class="step-num">04</span>
                    <h3 class="step-title">Thưởng Thức</h3>
                    <p class="step-desc">
                        Rót vào tách gốm nung mộc mạc giữ nhiệt. Hít thở hương thơm dịu nhẹ, nhấp một ngụm nhỏ và để thời gian chậm lại bên góc quán thân quen.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- G. KHÔNG GIAN QUÁN (BỘ ẢNH NGHỆ THUẬT BẤT ĐỐI XỨNG) -->
    <section class="noir-space" id="khongGian">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="space-intro-grid">
                <div>
                    <span class="eyebrow">KHÔNG GIAN &amp; ÁNH SÁNG</span>
                    <h2 style="font-family: var(--font-serif); font-size: clamp(2.4rem, 4vw, 3.4rem); margin-top: 10px; color: var(--c-espresso);">
                        Không Gian Quán
                    </h2>
                </div>
                <div>
                    <p style="color: #4A3930; font-size: 1.05rem; line-height: 1.8;">
                        Một không gian được thiết kế với sự tĩnh lặng có chủ đích. Ánh nắng chiều len qua khung cửa sổ lớn, những cuốn sách nghệ thuật mở dở và mùi hương cà phê rang mộc bao bọc lấy từng khoảng lặng.
                    </p>
                </div>
            </div>

            <div class="space-gallery-mosaic">
                <!-- Ảnh 1: Quầy Bar -->
                <div class="mosaic-item item-1" onclick="openLightbox('img/hero_bg.jpg', 'Góc quầy pha chế ấm áp với ánh đèn amber và máy espresso mạ đồng cổ điển')">
                    <img src="img/hero_bg.jpg" alt="Quầy bar NOIR CAFÉ" loading="lazy">
                    <div class="mosaic-caption">01 — Quầy Pha Chế &amp; Cỗ Máy Đồng Cổ Điển</div>
                </div>

                <!-- Ảnh 2: Rót Espresso -->
                <div class="mosaic-item item-2" onclick="openLightbox('img/espresso.jpg', 'Cận cảnh dòng chảy espresso crema sóng sánh trong tách gốm nung')">
                    <img src="img/espresso.jpg" alt="Chiết xuất cà phê mộc" loading="lazy">
                    <div class="mosaic-caption">02 — Dòng Chảy Crema &amp; Tách Gốm Mộc</div>
                </div>

                <!-- Ảnh 3: Cà phê sữa trên bàn gỗ -->
                <div class="mosaic-item item-3" onclick="openLightbox('img/latte.jpg', 'Tách cà phê sữa Flat White trên mặt bàn gỗ tự nhiên đón nắng')">
                    <img src="img/latte.jpg" alt="Tách latte art" loading="lazy">
                    <div class="mosaic-caption">03 — Cà Phê Sữa &amp; Vân Gỗ Tự Nhiên</div>
                </div>

                <!-- Ảnh 4: Góc Đọc Sách -->
                <div class="mosaic-item item-4" onclick="openLightbox('https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=1200&q=80', 'Góc bàn đọc sách đón nắng chiều bên khung cửa kính lớn')">
                    <img src="https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=1200&q=80" alt="Góc ngồi đọc sách cạnh cửa sổ" loading="lazy">
                    <div class="mosaic-caption">04 — Ánh Nắng Chiều &amp; Góc Bàn Tĩnh Lặng</div>
                </div>
            </div>
        </div>
    </section>

    <!-- H. GÓC CHIÊM NGHIỆM (NHẬT KÝ BÀI VIẾT · ĐÁNH SỐ TRANG 1 2 3) -->
    <section class="noir-journal" id="nhatKy">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="journal-header">
                <div>
                    <span class="eyebrow">CHIÊM NGHIỆM &amp; ĐỌC SÁCH · NHẬT KÝ QUÁN</span>
                    <h2 style="font-family: var(--font-serif); font-size: clamp(2.2rem, 3.8vw, 3.2rem); color: #FAF6F0; margin-top: 10px;">
                        Nhật Ký Tĩnh Lặng
                    </h2>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.8rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--c-amber); display: block;">
                        Ấn Bản Số IV · Mùa Thu
                    </span>
                    <span id="journalPageIndicator" style="font-size: 0.75rem; color: rgba(242, 233, 218, 0.55); font-family: var(--font-sans);">
                        Trang 1 / 3 (Tuyển tập chiêm nghiệm)
                    </span>
                </div>
            </div>

            <!-- TRANG 1 (MẶC ĐỊNH HIỂN THỊ) -->
            <div class="journal-grid journal-page-content" id="journalPage1">
                <!-- Bài viết 1 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=700&q=80" alt="Cách sống chậm cùng cà phê" loading="lazy">
                    </div>
                    <span class="journal-meta">Lối Sống Chậm · 12 Tháng 10, 2026</span>
                    <h3 class="journal-title">Nghệ thuật sống chậm cùng một tách cà phê</h3>
                    <p class="journal-excerpt">
                        Dành riêng cho mình 15 phút không màn hình điện thoại mỗi sáng. Chỉ có hơi ấm của tách sứ và dòng suy nghĩ lắng dịu.
                    </p>
                </article>

                <!-- Bài viết 2 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=700&q=80" alt="Hiểu về tách espresso" loading="lazy">
                    </div>
                    <span class="journal-meta">Kiến Thức Cà Phê · 08 Tháng 10, 2026</span>
                    <h3 class="journal-title">Đọc vị và thấu hiểu tách Espresso của bạn</h3>
                    <p class="journal-excerpt">
                        Đọc vị lớp crema, cân bằng giữa độ chua sáng của quả mọng và vị đắng ngọt socola để tìm ra gu chiết xuất hoàn hảo nhất.
                    </p>
                </article>

                <!-- Bài viết 3 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1521017432531-fbd92d768814?w=700&q=80" alt="Góc nhỏ cho buổi sáng an yên" loading="lazy">
                    </div>
                    <span class="journal-meta">Kiến Trúc &amp; Không Gian · 28 Tháng 9, 2026</span>
                    <h3 class="journal-title">Một góc nhỏ cho những buổi sáng an yên</h3>
                    <p class="journal-excerpt">
                        Câu chuyện kiến trúc đằng sau việc lựa chọn gỗ tần bì mộc, ánh sáng vàng 2700K và cách âm cho những buổi sáng suy tư.
                    </p>
                </article>
            </div>

            <!-- TRANG 2 (ẨN BAN ĐẦU) -->
            <div class="journal-grid journal-page-content" id="journalPage2" style="display: none;">
                <!-- Bài viết 4 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=700&q=80" alt="Nghệ thuật ủ lạnh trà ô long" loading="lazy">
                    </div>
                    <span class="journal-meta">Trà Thảo Mộc · 20 Tháng 9, 2026</span>
                    <h3 class="journal-title">Trà Ô Long Rang &amp; Nghệ Thuật Ủ Lạnh Tự Nhiên</h3>
                    <p class="journal-excerpt">
                        Quá trình ủ trà lạnh trong 12 giờ giúp giải phóng nốt hương hoa quả chín, vị ngọt hậu thanh tao không chát gắt.
                    </p>
                </article>

                <!-- Bài viết 5 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=700&q=80" alt="Nốt hương hạt Cầu Đất" loading="lazy">
                    </div>
                    <span class="journal-meta">Hương Vị Bản Địa · 15 Tháng 9, 2026</span>
                    <h3 class="journal-title">Nốt Hương Vỏ Cam &amp; Bơ Nâu Trong Hạt Cầu Đất</h3>
                    <p class="journal-excerpt">
                        Ở độ cao 1.650m, khí hậu sương giá tạo nên những quả cà phê chín mọng mang axit malic tươi mát tựa táo xanh.
                    </p>
                </article>

                <!-- Bài viết 6 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1442512595331-e89e73853f31?w=700&q=80" alt="Khoảng lặng phố thị" loading="lazy">
                    </div>
                    <span class="journal-meta">Tản Mạn · 05 Tháng 9, 2026</span>
                    <h3 class="journal-title">Tìm Lại Khoảng Lặng Giữa Nhịp Phố Vội Vã</h3>
                    <p class="journal-excerpt">
                        Khi cả thành phố vội vã chạy theo guồng quay, một chiếc bàn gỗ nhỏ và tách cà phê ấm sẽ neo giữ lại sự an yên trong bạn.
                    </p>
                </article>
            </div>

            <!-- TRANG 3 (ẨN BAN ĐẦU) -->
            <div class="journal-grid journal-page-content" id="journalPage3" style="display: none;">
                <!-- Bài viết 7 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1518832553480-cd0e625ed3e6?w=700&q=80" alt="Chiết xuất Pour-over thủ công" loading="lazy">
                    </div>
                    <span class="journal-meta">Kỹ Thuật Pha Chế · 28 Tháng 8, 2026</span>
                    <h3 class="journal-title">Chiết Xuất Pour-Over: Kiên Nhẫn Từng Giọt Nước</h3>
                    <p class="journal-excerpt">
                        Điều chỉnh dòng nước 92°C uốn lượn theo vòng xoắn ốc đều đặn, một bài tập thiền định nhẹ nhàng cho tâm trí đầu ngày.
                    </p>
                </article>

                <!-- Bài viết 8 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1461360370896-922624d12aa1?w=700&q=80" alt="Âm thanh đĩa than Jazz" loading="lazy">
                    </div>
                    <span class="journal-meta">Âm Nhạc Quán · 18 Tháng 8, 2026</span>
                    <h3 class="journal-title">Giai Điệu Jazz Đĩa Than: Tiếng Nổ Lách Tách Cũ Kỹ</h3>
                    <p class="journal-excerpt">
                        Âm thanh mộc mạc từ máy hát đĩa than vintage hòa quyện cùng tiếng máy xay cà phê tạo nên không gian sống chậm độc bản.
                    </p>
                </article>

                <!-- Bài viết 9 -->
                <article class="journal-card">
                    <div class="journal-img-wrap">
                        <img src="https://images.unsplash.com/photo-1497636577773-f1231844b336?w=700&q=80" alt="Thói quen đọc sách" loading="lazy">
                    </div>
                    <span class="journal-meta">Thói Quen Sống · 02 Tháng 8, 2026</span>
                    <h3 class="journal-title">Đọc Một Trang Sách, Nhấp Một Ngụm Cà Phê</h3>
                    <p class="journal-excerpt">
                        Tại sao sự kết hợp giữa giấy in mực và hương thơm cà phê rang mộc lại kích thích sự tập trung và sáng tạo đến thế?
                    </p>
                </article>
            </div>

            <!-- THANH PHÂN TRANG ĐÁNH SỐ TRANG 1 2 3 (THEO YÊU CẦU ĐỀ TÀI 17) -->
            <div class="journal-pagination-bar">
                <button type="button" class="journal-page-btn" id="btnPrevJournal" onclick="switchJournalPage('prev')" disabled>
                    &laquo; Trang Trước
                </button>
                <button type="button" class="journal-page-btn active" id="btnJournalPage1" onclick="switchJournalPage(1)">
                    1
                </button>
                <button type="button" class="journal-page-btn" id="btnJournalPage2" onclick="switchJournalPage(2)">
                    2
                </button>
                <button type="button" class="journal-page-btn" id="btnJournalPage3" onclick="switchJournalPage(3)">
                    3
                </button>
                <button type="button" class="journal-page-btn" id="btnNextJournal" onclick="switchJournalPage('next')">
                    Trang Sau &raquo;
                </button>
            </div>
        </div>
    </section>

    <!-- I. GHÉ THĂM & ĐẶT LỊCH GIỮ BÀN -->
    <section class="noir-visit" id="gheTham">
        <div class="visit-grid">
            <div class="visit-info-col">
                <span class="eyebrow">ĐỊA CHỈ &amp; GIỜ MỞ CỬA</span>
                <h2 style="margin-top: 12px;">Mời bạn ghé qua dùng một tách cà phê.</h2>
                <p style="color: rgba(242, 233, 218, 0.75); font-size: 1rem; line-height: 1.7; margin-bottom: 30px;">
                    Cánh cửa gỗ của NOIR luôn mở đón bạn. Hãy ghé qua khi bạn cần một chốn trú ẩn yên bình giữa nhịp sống phố xá.
                </p>

                <div class="visit-detail-item">
                    <span class="visit-label">Địa Chỉ Quán (Chi nhánh mẫu)</span>
                    <div class="visit-val">48 Hàng Chiếu, Hoàn Kiếm, Hà Nội</div>
                    <div class="visit-val" style="margin-top: 4px; color: rgba(242, 233, 218, 0.7);">Chi nhánh 2: Số 12 Chùa Bộc, Đống Đa, Hà Nội</div>
                </div>

                <div class="visit-detail-item">
                    <span class="visit-label">Thời Gian Phục Vụ</span>
                    <div class="visit-val">Thứ Hai — Chủ Nhật: 07:30 — 22:30 hàng ngày</div>
                </div>

                <div class="visit-detail-item">
                    <span class="visit-label">Đường Dây Nóng Giữ Chỗ</span>
                    <div class="visit-val">(+84) 24 3828 xxxx (Mẫu)</div>
                </div>

                <div style="margin-top: 24px;">
                    <a href="https://maps.google.com" target="_blank" class="btn-noir-ghost" style="display: inline-flex;">
                        📍 Xem Chỉ Đường Trên Bản Đồ
                    </a>
                </div>
            </div>

            <div class="visit-form-col">
                <h3 class="visit-form-title">Lịch Đặt Chỗ &amp; Giữ Bàn</h3>
                <p style="font-size: 0.85rem; color: rgba(242, 233, 218, 0.65); margin-bottom: 22px;">
                    Đặt lịch giữ bàn trước để quán chuẩn bị cho bạn góc ngồi ngắm phố hoặc góc đọc sách yên tĩnh nhất.
                </p>

                <form onsubmit="handleReserveTable(event)">
                    <div class="form-group">
                        <label class="form-label">Họ và tên của bạn <span style="color:var(--c-amber);">*</span></label>
                        <input type="text" id="reserveName" class="form-input" placeholder="Ví dụ: Hoàng Minh Đức" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại liên hệ <span style="color:var(--c-amber);">*</span></label>
                        <input type="tel" id="reservePhone" class="form-input" placeholder="09xx xxx xxx" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label">Giờ đến</label>
                            <input type="time" id="reserveTime" class="form-input" value="09:00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số lượng người</label>
                            <input type="number" id="reserveGuests" class="form-input" value="2" min="1" max="12" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-noir-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                        📅 Xác Nhận Lịch Đặt Chỗ &amp; Giữ Bàn
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- K. CHÂN TRANG (FOOTER) -->
    <footer class="noir-footer">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="footer-top">
                <div class="footer-brand">
                    <h3>NOIR CAFÉ</h3>
                    <p class="footer-tagline">"Có những khoảnh khắc xứng đáng để ta chậm lại."</p>
                    <p style="color: rgba(242, 233, 218, 0.45); font-size: 0.8rem; margin-top: 14px; max-width: 320px;">
                        Thương hiệu trải nghiệm cà phê điện ảnh. Phục vụ hạt rang mộc nguyên bản và không gian chiêm nghiệm.
                    </p>
                </div>

                <div>
                    <h4 class="footer-col-title">Điều Hướng Quán</h4>
                    <ul class="footer-links">
                        <li><a href="#cauChuyen">Câu Chuyện</a></li>
                        <li><a href="#thucDon">Thực Đơn Đậm Gu</a></li>
                        <li><a href="#quyTrinh">Nghệ Thuật Pha Chế</a></li>
                        <li><a href="#khongGian">Không Gian Quán</a></li>
                        <li><a href="#nhatKy">Góc Chiêm Nghiệm</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-col-title">Đồ Uống &amp; Hội Viên</h4>
                    <ul class="footer-links">
                        <li><a href="checkout.php">Giỏ Đồ Uống &amp; Thanh Toán</a></li>
                        <li><a href="javascript:void(0)" onclick="RecommenderEngine.openGenderModal(false)">Đăng Ký Thẻ Khách Quý</a></li>
                        <li><a href="../../index.php" target="_blank">Báo Cáo Phân Tích</a></li>
                        <li><a href="javascript:void(0)" onclick="RecommenderEngine.openGenderModal(false)">Đổi Gu Thưởng Thức</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-col-title">Mạng Xã Hội &amp; Giờ Mở Cửa</h4>
                    <ul class="footer-links">
                        <li><a href="#">Instagram @noircafe.vn</a></li>
                        <li><a href="#">Facebook /noircafe.vietnam</a></li>
                        <li><span style="font-size:0.82rem; color:rgba(242,233,218,0.5);">Phục vụ hàng ngày 07:30 — 22:30</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; <?= date('Y') ?> NOIR CAFÉ. Bản quyền thuộc về thương hiệu cà phê NOIR.</div>
                <div>Đề tài 17: Phân tích hành vi &amp; Gợi ý cá nhân hóa đồ uống.</div>
            </div>
        </div>
    </footer>

    <!-- L. GIỎ ĐỒ UỐNG TRƯỢT (MINI CART DRAWER) -->
    <div id="cartDrawerOverlay" class="cart-drawer-overlay" onclick="toggleCartDrawer(false)"></div>
    <aside id="cartDrawer" class="cart-drawer">
        <div class="cart-drawer-header">
            <h3 class="cart-drawer-title">Giỏ Đồ Uống Của Bạn</h3>
            <button type="button" class="close-drawer-btn" onclick="toggleCartDrawer(false)" aria-label="Đóng giỏ hàng">&times;</button>
        </div>
        <div class="cart-drawer-body" id="cartDrawerBody">
            <!-- Đổ động từ localStorage -->
        </div>
        <div class="cart-drawer-footer">
            <div class="cart-total-row">
                <span style="color: rgba(242, 233, 218, 0.7); font-size: 0.95rem;">Tổng Tạm Tính:</span>
                <span id="cartDrawerTotalVal" style="color: var(--c-amber);">0 đ</span>
            </div>
            <a href="checkout.php" class="cart-checkout-btn">
                Tiến Hành Đặt Hàng &amp; Thanh Toán &rarr;
            </a>
        </div>
    </aside>

    <!-- M. HỘP THOẠI XEM NHANH CHI TIẾT MÓN (QUICK VIEW) -->
    <div id="quickviewModal" class="noir-modal-overlay" onclick="closeQuickviewOnOverlay(event)">
        <div class="noir-modal-card">
            <button type="button" class="modal-close-icon" onclick="closeQuickview()">&times;</button>
            <div style="height: 250px; overflow: hidden; position: relative;">
                <img id="qvImage" src="" alt="" style="width:100%; height:100%; object-fit:cover;">
                <span id="qvTag" style="position: absolute; bottom: 16px; left: 16px; background: rgba(22,14,10,0.85); color: var(--c-amber); padding: 4px 10px; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase;">
                    NOIR TUYỂN CHỌN
                </span>
            </div>
            <div style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                    <h3 id="qvName" style="font-family: var(--font-serif); font-size: 1.8rem; color: #FAF6F0;">Tên Món</h3>
                    <span id="qvPrice" style="font-size: 1.25rem; font-weight: 700; color: var(--c-amber);">0 đ</span>
                </div>
                <p id="qvDesc" style="font-size: 0.92rem; color: rgba(242, 233, 218, 0.8); line-height: 1.6; margin-bottom: 14px;">
                    Mô tả món
                </p>
                <div style="background: rgba(242, 233, 218, 0.05); border: 1px solid var(--c-border-dark); padding: 12px 14px; border-radius: 2px; margin-bottom: 16px;">
                    <span style="font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--c-amber); display: block; margin-bottom: 4px;">Nốt Hương &amp; Phương Pháp Pha:</span>
                    <span id="qvNotes" style="font-size: 0.85rem; color: var(--c-warm-beige);">...</span>
                </div>

                <!-- NÚT LIKE VÀ LIKE NGƯỢC (ĐÁNH GIÁ MÓN TRỰC TIẾP) -->
                <div class="qv-reaction-row" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding: 10px 14px; background: rgba(242, 233, 218, 0.04); border: 1px solid var(--c-border-dark); border-radius: 3px;">
                    <span style="font-size: 0.8rem; color: var(--c-warm-beige); letter-spacing: 0.05em; font-weight: 500;">
                        Bạn thích món này chứ?
                    </span>
                    <div style="display: flex; gap: 8px;">
                        <!-- Nút Like -->
                        <button type="button" id="qvLikeBtn" class="btn-reaction btn-track" onclick="toggleItemReaction('like')" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border: 1px solid rgba(242, 233, 218, 0.2); background: transparent; color: #FAF6F0; border-radius: 20px; font-size: 0.8rem; cursor: pointer; transition: all 0.25s ease;" title="Thích món này">
                            <span style="font-size: 15px;">👍</span> <span>Thích</span> <span id="qvLikeCount" style="font-size: 0.75rem; font-weight: 700; color: #10b981;">(0)</span>
                        </button>
                        <!-- Nút Like ngược (Dislike) -->
                        <button type="button" id="qvDislikeBtn" class="btn-reaction btn-track" onclick="toggleItemReaction('dislike')" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border: 1px solid rgba(242, 233, 218, 0.2); background: transparent; color: #FAF6F0; border-radius: 20px; font-size: 0.8rem; cursor: pointer; transition: all 0.25s ease;" title="Like ngược / Chưa hợp khẩu vị">
                            <span style="font-size: 15px;">👎</span> <span>Like ngược</span> <span id="qvDislikeCount" style="font-size: 0.75rem; font-weight: 700; color: #f87171;">(0)</span>
                        </button>
                    </div>
                </div>

                <button type="button" id="qvAddBtn" class="btn-noir-primary" style="width: 100%; justify-content: center;">
                    🛒 Thêm Vào Giỏ Hàng
                </button>
            </div>
        </div>
    </div>

    <!-- N. HỘP THOẠI PHÓNG TO ẢNH (LIGHTBOX) -->
    <div id="galleryLightbox" class="noir-modal-overlay" onclick="closeLightboxOnOverlay(event)">
        <div style="max-width: 900px; width: 100%; position: relative; text-align: center;">
            <button type="button" class="modal-close-icon" style="top: -40px; right: 0; color: #fff;" onclick="closeLightbox()">&times;</button>
            <img id="lbImage" src="" alt="" style="width: 100%; max-height: 75vh; object-fit: contain; border-radius: 2px; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
            <div id="lbCaption" style="margin-top: 14px; font-size: 0.85rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--c-warm-beige);"></div>
        </div>
    </div>

    <!-- O. MODAL ĐĂNG KÝ THÀNH VIÊN VÀ CHỌN GU TRỰC TIẾP (100% TIẾNG VIỆT · TỰ HIỆN KHI MỞ TRANG) -->
    <div id="genderModalOverlay" class="gender-modal-overlay noir-member-modal-overlay">
        <div class="gender-modal-card noir-member-modal-card">
            <button type="button" class="modal-close-noir-btn" onclick="RecommenderEngine.closeGenderModal()" aria-label="Đóng">&times;</button>
            
            <div style="text-align: center; margin-bottom: 24px; border-bottom: 1px solid rgba(242, 233, 218, 0.12); padding-bottom: 18px;">
                <span style="font-size: 0.72rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--c-amber); font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                    <span>✦</span> THẺ THÀNH VIÊN NOIR CAFÉ · ĐỀ TÀI 17
                </span>
                <h2 style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: clamp(1.8rem, 3.5vw, 2.3rem); color: #FAF6F0; margin-bottom: 6px; font-weight: 400;">
                    Đăng Ký Thẻ Khách Quý
                </h2>
                <p style="font-size: 0.88rem; color: rgba(242, 233, 218, 0.72); line-height: 1.55; max-width: 480px; margin: 0 auto; font-weight: 300;">
                    Dành một khoảng lặng để tận hưởng cà phê chuẩn vị. Vui lòng hoàn tất thông tin để hệ thống AI nhận diện gu và kích hoạt thực đơn riêng cho bạn.
                </p>
            </div>

            <form onsubmit="RecommenderEngine.confirmGenderLogin(event)">
                <!-- Họ Tên & Liên Hệ -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 6px; font-weight: 600;">
                            Họ và Tên Khách Quý <span style="color:var(--c-amber);">*</span>
                        </label>
                        <input type="text" id="modalUserNameInput" class="noir-modal-input" placeholder="Ví dụ: Hoàng Minh Đức" autocomplete="name" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 6px; font-weight: 600;">
                            Email hoặc Số Điện Thoại
                        </label>
                        <input type="text" id="modalUserContactInput" class="noir-modal-input" placeholder="Ví dụ: 0912 345 678" autocomplete="tel">
                    </div>
                </div>

                <!-- Chọn Định Danh & Gu Thưởng Thức -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 8px; font-weight: 600;">
                        Chọn Định Danh &amp; Gu Thưởng Thức <span style="color:var(--c-amber);">*</span>
                    </label>
                    <div class="gender-picker-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <!-- Thẻ Phái Mạnh -->
                        <div id="modalCardMale" class="gender-pick-card noir-pick-card active-male" onclick="RecommenderEngine.selectModalGender('male')">
                            <span style="font-size: 24px; display: block; margin-bottom: 4px;">☕</span>
                            <div style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: 1.15rem; font-weight: 600; color: #FAF6F0; margin-bottom: 2px;">Phái Mạnh · Nam</div>
                            <span style="font-size: 0.72rem; color: var(--c-amber); font-weight: 600; display: block; margin-bottom: 4px;">⚡ Bứt Phá Năng Lượng</span>
                            <p style="font-size: 0.76rem; color: rgba(242, 233, 218, 0.65); line-height: 1.4; margin: 0;">Ưu tiên Noir Espresso, Cold Brew Muối Biển và các dòng cà phê mộc đậm đà.</p>
                        </div>

                        <!-- Thẻ Phái Đẹp -->
                        <div id="modalCardFemale" class="gender-pick-card noir-pick-card" onclick="RecommenderEngine.selectModalGender('female')">
                            <span style="font-size: 24px; display: block; margin-bottom: 4px;">🌸</span>
                            <div style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: 1.15rem; font-weight: 600; color: #FAF6F0; margin-bottom: 2px;">Phái Đẹp · Nữ</div>
                            <span style="font-size: 0.72rem; color: #d977a6; font-weight: 600; display: block; margin-bottom: 4px;">💖 Ngọt Ngào &amp; Thanh Dịu</span>
                            <p style="font-size: 0.76rem; color: rgba(242, 233, 218, 0.65); line-height: 1.4; margin: 0;">Ưu tiên Latte Bơ Nâu Pháp, Matcha Uji Kyoto và Trà thanh béo mượt mà.</p>
                        </div>
                    </div>
                </div>

                <!-- Nốt Hương Yêu Thích -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 6px; font-weight: 600;">
                        Nốt Hương Bạn Yêu Thích (Tùy chọn)
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span class="modal-note-chip selected" onclick="this.classList.toggle('selected')">Socola Đen &amp; Hạnh Nhân</span>
                        <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Bơ Nâu &amp; Caramel Nướng</span>
                        <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Vỏ Cam Vàng Thanh Mát</span>
                        <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Kem Muối Biển Mịn Màng</span>
                        <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Trà Xanh Uji Chuẩn Nhật</span>
                    </div>
                </div>

                <!-- Hộp Xem Trước Gợi Ý Thời Gian Thực -->
                <div style="background: rgba(22, 14, 10, 0.65); border: 1px solid rgba(242, 233, 218, 0.12); padding: 12px 16px; border-radius: 3px; font-size: 0.82rem; color: rgba(242, 233, 218, 0.75); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="color: var(--c-amber); font-size: 1.2rem;">✨</span>
                    <div id="modalRecPreviewText">
                        Đề xuất ưu tiên: <strong style="color:var(--c-amber);">Noir Espresso đậm đà, Cold Brew Muối Biển ủ chậm 18 giờ</strong>.
                    </div>
                </div>

                <button type="submit" class="modal-submit-noir-btn">
                    <span>✦</span> Xác Nhận Đăng Ký &amp; Vào Quán
                </button>

                <div style="text-align: center; margin-top: 14px;">
                    <a href="javascript:void(0)" onclick="RecommenderEngine.closeGenderModal()" style="font-size: 0.76rem; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(242, 233, 218, 0.5); text-decoration: underline;">
                        Xem Thực Đơn Trước, Đăng Ký Sau &rarr;
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPTS CÓ CHỐNG CACHE TRÌNH DUYỆT -->
    <script src="../../assets/js/tracker.js?v=<?= time() ?>"></script>
    <script src="../../assets/js/recommender.js?v=<?= time() ?>"></script>

    <script>
        // Tự động mở modal khi vừa vào trang nếu chưa chọn trong phiên
        document.addEventListener('DOMContentLoaded', () => {
            const hasChosenInSession = sessionStorage.getItem('gender_selected_session');
            const hasAuth = localStorage.getItem('user_gender_authenticated');
            if (!hasChosenInSession || !hasAuth) {
                setTimeout(() => {
                    RecommenderEngine.openGenderModal(true);
                }, 300);
            }
        });

        // Hiệu ứng cuộn thanh điều hướng
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Đóng mở menu di động
        function toggleMobileNav() {
            const m = document.getElementById('mobileNavMenu');
            m.style.display = (m.style.display === 'none' || m.style.display === '') ? 'block' : 'none';
        }

        // Lọc danh mục thực đơn
        function filterMenu(category, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('#menuGridContainer .menu-item-card');
            cards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('btn_click', {
                    element_text: 'Lọc danh mục thực đơn: ' + category,
                    category: 'menu_filter',
                    item_name: category
                });
            }
        }

        // Đóng mở giỏ hàng trượt & Bắt thao tác người dùng
        function toggleCartDrawer(open) {
            const overlay = document.getElementById('cartDrawerOverlay');
            const drawer = document.getElementById('cartDrawer');
            if (open) {
                renderMiniCart();
                overlay.classList.add('open');
                drawer.classList.add('open');

                // Hiển thị ô thao tác tức thì lên màn hình Web Cafe
                showUserActionHUD('🛒', 'Bạn vừa nhấp vào Giỏ Hàng (Mở giỏ đồ uống)');

                // Ghi nhận sự kiện thời gian thực gửi về Dashboard
                if (window.AnalyticsTracker) {
                    AnalyticsTracker.track('btn_click', {
                        category: 'cart',
                        item_name: 'mo_gio_hang',
                        element_text: '🛒 Nhấp Mở Giỏ Đồ Uống & Kiểm Tra Món'
                    });
                }
            } else {
                overlay.classList.remove('open');
                drawer.classList.remove('open');
            }
        }

        function renderMiniCart() {
            const cart = JSON.parse(localStorage.getItem('cafe_cart') || '[]');
            const body = document.getElementById('cartDrawerBody');
            const totalEl = document.getElementById('cartDrawerTotalVal');

            if (cart.length === 0) {
                body.innerHTML = `
                    <div class="cart-empty-state">
                        <div style="font-size: 3rem; margin-bottom: 12px;">☕</div>
                        <p>Giỏ đồ uống của bạn đang trống.</p>
                        <p style="font-size: 0.8rem; margin-top: 6px;">Hãy khám phá thực đơn để chọn món ngon chuẩn gu nhé!</p>
                    </div>
                `;
                totalEl.innerText = '0 đ';
                return;
            }

            let html = '';
            let total = 0;
            cart.forEach((item, idx) => {
                const sub = item.price * item.qty;
                total += sub;
                const thumb = item.image || 'img/espresso.jpg';
                html += `
                    <div class="cart-item-row">
                        <img src="${thumb}" alt="${item.name}" class="cart-item-thumb">
                        <div class="cart-item-details">
                            <div class="cart-item-title">${item.name}</div>
                            <div class="cart-item-price">${Number(item.price).toLocaleString('vi-VN')} đ</div>
                            <div class="cart-qty-ctrl">
                                <button type="button" class="qty-btn" onclick="updateCartQty(${idx}, -1)">-</button>
                                <span style="font-size: 0.85rem; font-weight: 600;">${item.qty}</span>
                                <button type="button" class="qty-btn" onclick="updateCartQty(${idx}, 1)">+</button>
                            </div>
                        </div>
                        <button type="button" onclick="removeCartItem(${idx})" style="color: #f87171; font-size: 1.1rem; padding: 4px;">&times;</button>
                    </div>
                `;
            });

            body.innerHTML = html;
            totalEl.innerText = Number(total).toLocaleString('vi-VN') + ' đ';
        }

        function updateCartQty(idx, delta) {
            let cart = JSON.parse(localStorage.getItem('cafe_cart') || '[]');
            if (cart[idx]) {
                cart[idx].qty += delta;
                if (cart[idx].qty <= 0) {
                    cart.splice(idx, 1);
                }
                localStorage.setItem('cafe_cart', JSON.stringify(cart));
                RecommenderEngine.updateCartBadge();
                renderMiniCart();
            }
        }

        function removeCartItem(idx) {
            let cart = JSON.parse(localStorage.getItem('cafe_cart') || '[]');
            cart.splice(idx, 1);
            localStorage.setItem('cafe_cart', JSON.stringify(cart));
            RecommenderEngine.updateCartBadge();
            renderMiniCart();
        }

        // Xem nhanh chi tiết món & Bắt thao tác người dùng
        let activeQvItem = null;
        function openItemQuickview(id, name, price, img, desc, notes, category = 'coffee') {
            activeQvItem = { id, name, price, img, category };
            document.getElementById('qvImage').src = img;
            document.getElementById('qvName').innerText = name;
            document.getElementById('qvPrice').innerText = Number(price).toLocaleString('vi-VN') + ' đ';
            document.getElementById('qvDesc').innerText = desc;
            document.getElementById('qvNotes').innerText = notes || 'Nguyên liệu tuyển chọn tự nhiên • Công thức cân bằng độc quyền';
            
            const btn = document.getElementById('qvAddBtn');
            btn.onclick = () => {
                RecommenderEngine.addToCart(id, name, price, category, img);
                closeQuickview();
                toggleCartDrawer(true);
            };

            document.getElementById('quickviewModal').classList.add('open');

            // Cập nhật trạng thái Like / Like ngược của món
            updateReactionUI(id);

            // Hiển thị ô thao tác ngay trên Web Cafe
            const iconEmoji = (category === 'milktea' || (id && id.startsWith('ts_'))) ? '🧋' : '☕';
            showUserActionHUD(iconEmoji, 'Bạn vừa xem chi tiết món: ' + name);

            // Ghi nhận sự kiện thời gian thực gửi về Dashboard đối chiếu
            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('btn_click', {
                    element_text: 'Nhấp xem chi tiết: ' + name,
                    category: category,
                    item_name: id
                });
            }
        }

        // =========================================================================
        // HỆ THỐNG ĐÁNH GIÁ: LIKE & LIKE NGƯỢC (REACTIONS) CHO TỪNG MÓN
        // =========================================================================
        function getItemReactions(id) {
            const defaults = {
                'cf_espresso': { likes: 42, dislikes: 2 },
                'cf_brownbutter': { likes: 58, dislikes: 1 },
                'cf_seasalt_cb': { likes: 65, dislikes: 3 },
                'cf_vanilla_flat': { likes: 37, dislikes: 1 },
                'cf_orange_cb': { likes: 44, dislikes: 2 },
                'ts_hoanggia': { likes: 88, dislikes: 2 },
                'ts_olongnuong': { likes: 92, dislikes: 3 },
                'ts_matcha_kyoto': { likes: 51, dislikes: 2 },
                'ts_thaixanh': { likes: 43, dislikes: 1 },
                'ts_daocamsa': { likes: 60, dislikes: 2 }
            };
            const stored = JSON.parse(localStorage.getItem('cafe_reactions') || '{}');
            if (!stored[id]) {
                stored[id] = defaults[id] || { likes: 25, dislikes: 1 };
            }
            return stored[id];
        }

        function updateReactionUI(id) {
            if (!id) return;
            const data = getItemReactions(id);
            const userVotes = JSON.parse(localStorage.getItem('cafe_user_votes') || '{}');
            const currentVote = userVotes[id] || null;

            const likeCountEl = document.getElementById('qvLikeCount');
            const dislikeCountEl = document.getElementById('qvDislikeCount');
            const likeBtn = document.getElementById('qvLikeBtn');
            const dislikeBtn = document.getElementById('qvDislikeBtn');

            if (likeCountEl) likeCountEl.innerText = `(${data.likes})`;
            if (dislikeCountEl) dislikeCountEl.innerText = `(${data.dislikes})`;

            if (likeBtn) {
                if (currentVote === 'like') {
                    likeBtn.style.background = 'rgba(16, 185, 129, 0.25)';
                    likeBtn.style.borderColor = '#10b981';
                    likeBtn.style.color = '#34d399';
                    likeBtn.style.fontWeight = '700';
                } else {
                    likeBtn.style.background = 'transparent';
                    likeBtn.style.borderColor = 'rgba(242, 233, 218, 0.2)';
                    likeBtn.style.color = '#FAF6F0';
                    likeBtn.style.fontWeight = '500';
                }
            }

            if (dislikeBtn) {
                if (currentVote === 'dislike') {
                    dislikeBtn.style.background = 'rgba(239, 68, 68, 0.25)';
                    dislikeBtn.style.borderColor = '#ef4444';
                    dislikeBtn.style.color = '#f87171';
                    dislikeBtn.style.fontWeight = '700';
                } else {
                    dislikeBtn.style.background = 'transparent';
                    dislikeBtn.style.borderColor = 'rgba(242, 233, 218, 0.2)';
                    dislikeBtn.style.color = '#FAF6F0';
                    dislikeBtn.style.fontWeight = '500';
                }
            }
        }

        function toggleItemReaction(type) {
            if (!activeQvItem) return;
            const id = activeQvItem.id;
            const name = activeQvItem.name;
            const category = activeQvItem.category || 'coffee';

            const reactions = JSON.parse(localStorage.getItem('cafe_reactions') || '{}');
            if (!reactions[id]) reactions[id] = getItemReactions(id);

            const userVotes = JSON.parse(localStorage.getItem('cafe_user_votes') || '{}');
            const prevVote = userVotes[id] || null;

            let actionText = '';

            if (type === 'like') {
                if (prevVote === 'like') {
                    // Bỏ like
                    reactions[id].likes = Math.max(0, reactions[id].likes - 1);
                    delete userVotes[id];
                    actionText = 'Bỏ thích món: ' + name;
                    showUserActionHUD('↩️', actionText);
                } else {
                    if (prevVote === 'dislike') {
                        reactions[id].dislikes = Math.max(0, reactions[id].dislikes - 1);
                    }
                    reactions[id].likes++;
                    userVotes[id] = 'like';
                    actionText = 'Thích (Like 👍) món: ' + name;
                    showUserActionHUD('👍', actionText);
                }
            } else if (type === 'dislike') {
                if (prevVote === 'dislike') {
                    // Bỏ dislike
                    reactions[id].dislikes = Math.max(0, reactions[id].dislikes - 1);
                    delete userVotes[id];
                    actionText = 'Bỏ like ngược món: ' + name;
                    showUserActionHUD('↩️', actionText);
                } else {
                    if (prevVote === 'like') {
                        reactions[id].likes = Math.max(0, reactions[id].likes - 1);
                    }
                    reactions[id].dislikes++;
                    userVotes[id] = 'dislike';
                    actionText = 'Like ngược (Không thích 👎) món: ' + name;
                    showUserActionHUD('👎', actionText);
                }
            }

            localStorage.setItem('cafe_reactions', JSON.stringify(reactions));
            localStorage.setItem('cafe_user_votes', JSON.stringify(userVotes));
            updateReactionUI(id);

            // Ghi nhận sự kiện thời gian thực gửi về Dashboard Đề tài 17
            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('btn_click', {
                    element_text: actionText,
                    category: 'reaction',
                    item_name: id
                });
            }
        }

        function closeQuickview() {
            document.getElementById('quickviewModal').classList.remove('open');
        }

        function closeQuickviewOnOverlay(e) {
            if (e.target.id === 'quickviewModal') {
                closeQuickview();
            }
        }

        // Hộp thoại phóng to ảnh không gian
        function openLightbox(src, caption) {
            document.getElementById('lbImage').src = src;
            document.getElementById('lbCaption').innerText = caption;
            document.getElementById('galleryLightbox').classList.add('open');
        }

        function closeLightbox() {
            document.getElementById('galleryLightbox').classList.remove('open');
        }

        function closeLightboxOnOverlay(e) {
            if (e.target.id === 'galleryLightbox') {
                closeLightbox();
            }
        }

        // Bấm nút Tải tài liệu PDF (button_click - Đề tài 17)
        function downloadMenuPdf(e) {
            if (e) e.preventDefault();
            
            showUserActionHUD('📄', 'Bạn vừa bấm nút: Tải tài liệu PDF (Thực đơn điện tử)');

            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('btn_click', {
                    element_text: '📄 Tải tài liệu PDF (Thực đơn NOIR CAFÉ)',
                    category: 'document',
                    item_name: 'tai_lieu_pdf'
                });
            }

            // Tải file tài liệu PDF thực tế về máy
            const sampleContent = "NOIR CAFÉ — THỰC ĐƠN ĐIỆN TỬ & TÀI LIỆU QUÁN\n\n1. Noir Espresso (Cà Phê Đậm Vị) - 45.000 đ\n2. Latte Bơ Nâu Pháp - 65.000 đ\n3. Cold Brew Muối Biển - 58.000 đ\n4. Flat White Vani Quả - 62.000 đ\n5. Cold Brew Hoa Cam - 62.000 đ\n6. Matcha Latte Uji Kyoto - 55.000 đ\n\nĐịa chỉ: 48 Hàng Chiếu, Hoàn Kiếm, Hà Nội\nHotline: (+84) 24 3828 xxxx";
            const blob = new Blob([sampleContent], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Thuc_Don_Noir_Cafe.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            RecommenderEngine.showToast('📄 Đã tải thành công tài liệu Thực đơn PDF!');
        }

        // Xử lý Lịch Đặt Chỗ & Giữ Bàn
        function handleReserveTable(e) {
            e.preventDefault();
            const name = document.getElementById('reserveName').value.trim();
            const phone = document.getElementById('reservePhone').value.trim();
            const time = document.getElementById('reserveTime').value;
            const guests = document.getElementById('reserveGuests').value;

            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('form_complete', {
                    form_name: 'Lịch Đặt Chỗ & Giữ Bàn NOIR CAFÉ',
                    customer_name: name,
                    element_text: `📅 Xác Nhận Lịch Đặt Chỗ: ${guests} người vào lúc ${time}`,
                    category: 'reservation',
                    item_name: 'xac_nhan_lich_dat_cho'
                });
            }

            showUserActionHUD('📅', `Xác nhận lịch đặt chỗ cho ${name} (${guests} khách)`);

            alert(`✨ Kính chào bạn ${name}!\n\nNOIR CAFÉ đã ghi nhận yêu cầu giữ bàn cho ${guests} người vào lúc ${time}.\nNhân viên sẽ liên hệ số ${phone} trước 30 phút để chuẩn bị góc ngồi ấm áp và riêng tư nhất cho bạn!`);
            e.target.reset();
        }

        // Đồng bộ huy hiệu người dùng trên thanh điều hướng
        const originalUpdateProfile = RecommenderEngine.updateUserProfileUI;
        RecommenderEngine.updateUserProfileUI = function() {
            if (typeof originalUpdateProfile === 'function') {
                originalUpdateProfile.call(this);
            }
            const navBadge = document.getElementById('userPersonaName');
            const iconBadge = document.getElementById('userPersonaIcon');
            if (navBadge) {
                navBadge.innerText = this.userName || 'Khách Quý';
            }
            if (iconBadge) {
                iconBadge.innerText = (this.currentGender === 'female') ? '🌸' : '☕';
            }
        };

        // Ghi đè thông báo Toast mang phong cách NOIR
        RecommenderEngine.showToast = function(msg) {
            let toast = document.getElementById('noirToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'noirToast';
                toast.className = 'noir-toast';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<span style="color:var(--c-amber); font-size:1.1rem;">✨</span> <div>${msg}</div>`;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3200);
        };

        // Ghi đè thêm hiển thị HUD khi bấm addToCart
        const originalAddToCart = RecommenderEngine.addToCart;
        RecommenderEngine.addToCart = function(id, name, price, category, image) {
            originalAddToCart.call(this, id, name, price, category, image);
            showUserActionHUD('☕', `Bạn vừa nhấp chọn sản phẩm: ${name} (+ Thêm vào giỏ)`);
        };

        // =========================================================================
        // HÀM HIỂN THỊ Ô THAO TÁC NGƯỜI DÙNG TRỰC TIẾP TRÊN WEB CAFE (HUD)
        // =========================================================================
        function showUserActionHUD(icon, msg) {
            const hud = document.getElementById('cafeActionHud');
            const elIcon = document.getElementById('cafeHudIcon');
            const elMsg = document.getElementById('cafeHudMsg');
            if (!hud || !elIcon || !elMsg) return;

            elIcon.innerText = icon;
            elMsg.innerText = msg;

            hud.classList.remove('active');
            void hud.offsetWidth; // kích hoạt animation lại
            hud.classList.add('active');

            clearTimeout(window.cafeHudTimer);
            window.cafeHudTimer = setTimeout(() => {
                hud.classList.remove('active');
            }, 3600);
        }

        // =========================================================================
        // CHỨC NĂNG PHÂN TRANG BÀI VIẾT NHẬT KÝ CHIÊM NGHIỆM (ĐÁNH SỐ TRANG 1 2 3)
        // =========================================================================
        let currentJournalPage = 1;
        const totalJournalPages = 3;

        function switchJournalPage(targetPage) {
            if (targetPage === 'prev') {
                if (currentJournalPage > 1) {
                    currentJournalPage--;
                }
            } else if (targetPage === 'next') {
                if (currentJournalPage < totalJournalPages) {
                    currentJournalPage++;
                }
            } else {
                currentJournalPage = parseInt(targetPage);
            }

            // Ẩn hiện các trang bài viết
            for (let i = 1; i <= totalJournalPages; i++) {
                const pEl = document.getElementById('journalPage' + i);
                const btnEl = document.getElementById('btnJournalPage' + i);
                if (pEl) {
                    pEl.style.display = (i === currentJournalPage) ? 'grid' : 'none';
                }
                if (btnEl) {
                    if (i === currentJournalPage) {
                        btnEl.classList.add('active');
                    } else {
                        btnEl.classList.remove('active');
                    }
                }
            }

            // Cập nhật nút Trước / Sau
            const prevBtn = document.getElementById('btnPrevJournal');
            const nextBtn = document.getElementById('btnNextJournal');
            if (prevBtn) prevBtn.disabled = (currentJournalPage === 1);
            if (nextBtn) nextBtn.disabled = (currentJournalPage === totalJournalPages);

            // Cập nhật text số trang
            const indicator = document.getElementById('journalPageIndicator');
            if (indicator) {
                indicator.innerText = `Trang ${currentJournalPage} / ${totalJournalPages} (Tuyển tập chiêm nghiệm)`;
            }

            // Hiển thị HUD thao tác
            showUserActionHUD('📖', `Bạn vừa chuyển sang xem Nhật Ký — Trang ${currentJournalPage}`);

            // Ghi nhận sự kiện thời gian thực
            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('btn_click', {
                    element_text: `Chuyển trang nhật ký số ${currentJournalPage}`,
                    category: 'journal_pagination',
                    item_name: 'journal_page_' + currentJournalPage
                });
            }
        }
    </script>

    <!-- Ô THÔNG BÁO THAO TÁC NGƯỜI DÙNG TRỰC TIẾP (LIVE USER ACTION HUD) -->
    <div id="cafeActionHud" class="cafe-action-hud">
        <span class="hud-icon-badge" id="cafeHudIcon">⚡</span>
        <div class="hud-content-text">
            <span class="hud-tag-line">THAO TÁC VỪA THỰC HIỆN · ĐỀ TÀI 17</span>
            <span class="hud-msg-text" id="cafeHudMsg">Đã chọn món thành công</span>
        </div>
    </div>
</body>
</html>
