<?php
// NOIR CAFÉ — CLUB NOIR MEMBERSHIP REGISTRATION
// Đồng bộ hệ thống phân tích hành vi & gợi ý cá nhân hóa (Đề tài 17)
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thẻ Thành Viên NOIR CAFÉ — The Slow Drip Privilege</title>
    <meta name="description" content="Đăng ký thành viên NOIR CAFÉ để nhận đặc quyền cá nhân hóa thực đơn, lưu giữ nốt hương yêu thích và trải nghiệm cà phê điện ảnh.">

    <!-- Design System CSS -->
    <link rel="stylesheet" href="../../assets/css/noir.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../assets/css/shop.css?v=<?= time() ?>">

    <style>
        body {
            background-color: var(--c-espresso, #241711) !important;
            color: var(--c-paper-cream, #F2E9DA) !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .reg-page-wrap {
            flex-grow: 1;
            padding: 120px 5% 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: radial-gradient(circle at 50% 20%, rgba(185, 109, 58, 0.12) 0%, transparent 70%);
        }

        /* Dossier Member Card */
        .noir-dossier-card {
            max-width: 680px;
            width: 100%;
            background: rgba(36, 23, 17, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.14));
            border-radius: 4px;
            padding: 48px 42px;
            box-shadow: 0 25px 60px rgba(14, 9, 7, 0.65), 0 0 0 1px rgba(185, 109, 58, 0.15);
            position: relative;
        }

        .noir-dossier-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--c-amber, #B96D3A), transparent);
        }

        .dossier-header {
            text-align: center;
            margin-bottom: 34px;
            border-bottom: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.1));
            padding-bottom: 24px;
        }

        .dossier-stamp {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--c-amber, #B96D3A);
            margin-bottom: 12px;
            font-weight: 600;
        }

        .dossier-title {
            font-family: var(--font-serif, 'Cormorant Garamond', serif);
            font-size: clamp(2rem, 3.2vw, 2.6rem);
            font-weight: 400;
            color: #FAF6F0;
            line-height: 1.15;
            margin-bottom: 10px;
        }

        .dossier-title em {
            font-style: italic;
            color: var(--c-warm-beige, #D6C3A6);
        }

        .dossier-sub {
            font-size: 0.92rem;
            color: rgba(242, 233, 218, 0.72);
            font-weight: 300;
            line-height: 1.6;
            max-width: 520px;
            margin: 0 auto;
        }

        /* Form Controls */
        .form-row {
            margin-bottom: 22px;
        }

        .noir-label {
            display: block;
            font-size: 0.74rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--c-warm-beige, #D6C3A6);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .noir-input {
            width: 100%;
            padding: 13px 16px;
            background: rgba(242, 233, 218, 0.05);
            border: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.15));
            border-radius: 2px;
            color: #fff;
            font-family: var(--font-sans, inherit);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.25s ease;
        }

        .noir-input:focus {
            border-color: var(--c-amber, #B96D3A);
            background: rgba(242, 233, 218, 0.08);
            box-shadow: 0 0 16px rgba(185, 109, 58, 0.2);
        }

        .noir-input::placeholder {
            color: rgba(242, 233, 218, 0.35);
        }

        /* Gender & Flavor Preference Cards */
        .preference-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 8px;
        }

        .pref-card {
            border: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.15));
            border-radius: 3px;
            padding: 18px 16px;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(242, 233, 218, 0.03);
            position: relative;
        }

        .pref-card:hover {
            border-color: rgba(185, 109, 58, 0.4);
            background: rgba(242, 233, 218, 0.06);
            transform: translateY(-2px);
        }

        .pref-card.active-male {
            border-color: var(--c-amber, #B96D3A);
            background: rgba(185, 109, 58, 0.14);
            box-shadow: 0 8px 25px rgba(185, 109, 58, 0.2);
        }

        .pref-card.active-female {
            border-color: #d977a6;
            background: rgba(217, 119, 166, 0.12);
            box-shadow: 0 8px 25px rgba(217, 119, 166, 0.2);
        }

        .pref-icon {
            font-size: 26px;
            margin-bottom: 8px;
            display: block;
        }

        .pref-title {
            font-family: var(--font-serif, 'Cormorant Garamond', serif);
            font-size: 1.25rem;
            font-weight: 600;
            color: #FAF6F0;
            margin-bottom: 4px;
        }

        .pref-desc {
            font-size: 0.78rem;
            color: rgba(242, 233, 218, 0.68);
            line-height: 1.45;
        }

        .pref-card.active-male .pref-desc {
            color: var(--c-warm-beige, #D6C3A6);
        }

        .pref-card.active-female .pref-desc {
            color: #f5c2da;
        }

        /* Flavor Notes Chips */
        .notes-choice-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
        }

        .note-toggle-chip {
            padding: 6px 14px;
            background: rgba(242, 233, 218, 0.05);
            border: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.12));
            color: rgba(242, 233, 218, 0.75);
            font-size: 0.78rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .note-toggle-chip:hover {
            border-color: var(--c-amber, #B96D3A);
            color: #FAF6F0;
        }

        .note-toggle-chip.selected {
            background: var(--c-amber, #B96D3A);
            border-color: var(--c-amber, #B96D3A);
            color: #fff;
            font-weight: 600;
        }

        /* Live Privilege Preview Banner */
        .noir-preview-box {
            margin: 24px 0;
            padding: 16px 20px;
            border-radius: 3px;
            font-size: 0.86rem;
            line-height: 1.6;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            border: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.15));
            background: rgba(22, 14, 10, 0.6);
        }

        .noir-preview-box.male-mode {
            border-color: rgba(185, 109, 58, 0.4);
            background: linear-gradient(135deg, rgba(185, 109, 58, 0.15) 0%, rgba(22, 14, 10, 0.7) 100%);
        }

        .noir-preview-box.female-mode {
            border-color: rgba(217, 119, 166, 0.4);
            background: linear-gradient(135deg, rgba(217, 119, 166, 0.15) 0%, rgba(22, 14, 10, 0.7) 100%);
        }

        .btn-noir-submit {
            width: 100%;
            padding: 16px 24px;
            background: var(--c-amber, #B96D3A);
            color: #fff;
            border: none;
            border-radius: 2px;
            font-size: 0.84rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 12px 28px rgba(185, 109, 58, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-noir-submit:hover {
            background: var(--c-amber-light, #d4824d);
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(185, 109, 58, 0.42);
        }

        .btn-noir-submit:active {
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            .noir-dossier-card {
                padding: 30px 20px;
            }
            .preference-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- NAVIGATION -->
    <header>
        <nav class="noir-nav" style="background: rgba(22, 14, 10, 0.95); border-bottom: 1px solid rgba(242, 233, 218, 0.12);">
            <a href="index.php" class="noir-brand">
                <span class="noir-brand-name">NOIR CAFÉ</span>
                <span class="noir-brand-tag">Club Noir Privilege</span>
            </a>

            <div class="noir-nav-links">
                <a href="index.php" class="noir-link">Trang Chủ</a>
                <a href="index.php#menuSection" class="noir-link">Signature Menu</a>
                <a href="checkout.php" class="noir-link">Giỏ Hàng</a>
                <a href="../../index.php" target="_blank" class="noir-link" style="color: var(--c-amber, #B96D3A);">📊 Dashboard</a>
            </div>

            <div class="noir-nav-actions">
                <a href="index.php" class="btn-noir-ghost" style="padding: 7px 16px; font-size: 0.74rem;">
                    ← Quay Lại Quán
                </a>
            </div>
        </nav>
    </header>

    <!-- MAIN FORM AREA -->
    <main class="reg-page-wrap">
        <div class="noir-dossier-card">
            <div class="dossier-header">
                <span class="dossier-stamp">
                    <span>✦</span> The Noir Member Dossier · Đề Tài 17
                </span>
                <h1 class="dossier-title">
                    Gia Nhập <em>Club Noir</em>
                </h1>
                <p class="dossier-sub">
                    Tạo thẻ khách quý để hệ thống AI nhận diện gu thưởng thức riêng bạn, cá nhân hóa thực đơn và mở lối những khoảnh khắc tĩnh lặng trọn vẹn nhất.
                </p>
            </div>

            <form id="noirRegisterForm" onsubmit="submitNoirRegistration(event)">
                <!-- Full Name -->
                <div class="form-row">
                    <label class="noir-label" for="regFullname">Họ và Tên Khách Quý <span style="color:var(--c-amber);">*</span></label>
                    <input type="text" id="regFullname" class="noir-input" placeholder="Ví dụ: Hoàng Minh Đức, Nguyễn Thùy Chi..." required onfocus="trackFormStart()">
                </div>

                <!-- Contact -->
                <div class="form-row">
                    <label class="noir-label" for="regEmail">Email hoặc Số Điện Thoại Nhận Đặc Quyền <span style="color:var(--c-amber);">*</span></label>
                    <input type="text" id="regEmail" class="noir-input" placeholder="Ví dụ: ducminh@noircafe.vn hoặc 0912 345 678" required onfocus="trackFormStart()">
                </div>

                <!-- Gender / Persona Selection (Đề tài 17) -->
                <div class="form-row">
                    <label class="noir-label">Chọn Định Danh &amp; Gu Thưởng Thức Ưu Tiên <span style="color:var(--c-amber);">*</span></label>
                    <input type="hidden" id="selectedGender" value="male">
                    
                    <div class="preference-grid">
                        <div id="cardMale" class="pref-card active-male" onclick="setGender('male')">
                            <span class="pref-icon">☕</span>
                            <h3 class="pref-title">Phái Mạnh · Nam</h3>
                            <p class="pref-desc">
                                Ưu tiên các dòng <strong>Espresso đậm mộc</strong>, <strong>Sea Salt Cold Brew</strong> và cà phê năng lượng tỉnh táo.
                            </p>
                        </div>

                        <div id="cardFemale" class="pref-card" onclick="setGender('female')">
                            <span class="pref-icon">🌸</span>
                            <h3 class="pref-title">Phái Đẹp · Nữ</h3>
                            <p class="pref-desc">
                                Ưu tiên các dòng <strong>Brown Butter Latte</strong>, <strong>Classic Matcha Uji</strong> và trà thanh ngọt mượt mà.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Favorite Flavor Notes -->
                <div class="form-row">
                    <label class="noir-label">Nốt Hương Bạn Yêu Thích (Tùy chọn)</label>
                    <div class="notes-choice-grid">
                        <span class="note-toggle-chip selected" onclick="toggleNoteChip(this)">Socola Đen &amp; Hạnh Nhân</span>
                        <span class="note-toggle-chip" onclick="toggleNoteChip(this)">Caramel &amp; Bơ Nâu Pháp</span>
                        <span class="note-toggle-chip" onclick="toggleNoteChip(this)">Vỏ Cam Vàng Tươi Mát</span>
                        <span class="note-toggle-chip" onclick="toggleNoteChip(this)">Kem Muối Biển Mịn</span>
                        <span class="note-toggle-chip" onclick="toggleNoteChip(this)">Uji Matcha Umami</span>
                        <span class="note-toggle-chip" onclick="toggleNoteChip(this)">Hạt Dẻ &amp; Gỗ Tần Bì</span>
                    </div>
                </div>

                <!-- Live Preview Banner -->
                <div id="recomPreview" class="noir-preview-box male-mode">
                    <span style="font-size: 1.5rem; color: var(--c-amber);">✨</span>
                    <div>
                        <strong style="color: #FAF6F0;">Đề Xuất Dự Đoán:</strong> 
                        Bạn đang chọn định danh <span id="previewGenderLabel" style="color: var(--c-amber); font-weight: 600;">Phái Mạnh (Nam)</span>. 
                        Sau khi kích hoạt, thực đơn sẽ ưu tiên làm nổi bật <span id="previewDrinkLabel" style="font-weight: 700; color: var(--c-amber);">Noir Espresso &amp; Sea Salt Cold Brew ủ chậm 18h</span>.
                    </div>
                </div>

                <!-- Custom Taste Note -->
                <div class="form-row">
                    <label class="noir-label" for="regNote">Ghi Chú Pha Chế Riêng (Độ đậm, loại sữa, mức ngọt...)</label>
                    <input type="text" id="regNote" class="noir-input" placeholder="Ví dụ: Ristretto đậm vị, ít đá, dùng sữa yến mạch Oatside..." onfocus="trackFormStart()">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btnSubmitReg" class="btn-noir-submit btn-track" data-category="auth" data-item="xac_nhan_dang_ky_noir">
                    <span>✦</span> Khởi Tạo Thẻ Thành Viên NOIR
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; font-size: 0.82rem; color: rgba(242, 233, 218, 0.55);">
                Đã từng đăng ký? <a href="index.php" style="color: var(--c-amber); text-decoration: underline; margin-left: 4px;">Khám phá thực đơn ngay &rarr;</a>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="noir-footer" style="padding: 40px 6%; text-align: center; border-top: 1px solid var(--c-border-dark);">
        <p style="font-family: var(--font-serif); font-size: 1.1rem; color: rgba(242, 233, 218, 0.7); font-style: italic;">
            "Some moments deserve to slow down."
        </p>
        <p style="font-size: 0.75rem; color: rgba(242, 233, 218, 0.4); margin-top: 8px;">
            &copy; <?= date('Y') ?> NOIR CAFÉ. Đề tài 17: Phân tích hành vi &amp; Gợi ý cá nhân hóa đồ uống.
        </p>
    </footer>

    <!-- TRACKING SDK -->
    <script src="../../assets/js/tracker.js?v=<?= time() ?>" data-site-key="site_cafe_tea_01"></script>
    <script>
        let formStartTracked = false;

        function trackFormStart() {
            if (!formStartTracked && window.AnalyticsTracker) {
                formStartTracked = true;
                AnalyticsTracker.track('form_start', {
                    element_text: 'Bắt đầu điền thẻ thành viên Club Noir',
                    category: 'auth',
                    item_name: 'form_dang_ky_noir'
                });
            }
        }

        function setGender(gender) {
            document.getElementById('selectedGender').value = gender;
            const cardMale = document.getElementById('cardMale');
            const cardFemale = document.getElementById('cardFemale');
            const previewBox = document.getElementById('recomPreview');
            const previewGenderLabel = document.getElementById('previewGenderLabel');
            const previewDrinkLabel = document.getElementById('previewDrinkLabel');

            if (gender === 'female') {
                cardFemale.className = 'pref-card active-female';
                cardMale.className = 'pref-card';
                previewBox.className = 'noir-preview-box female-mode';
                previewGenderLabel.textContent = 'Phái Đẹp (Nữ)';
                previewGenderLabel.style.color = '#d977a6';
                previewDrinkLabel.textContent = 'Brown Butter Latte béo ngậy & Classic Matcha Uji';
                previewDrinkLabel.style.color = '#d977a6';
            } else {
                cardMale.className = 'pref-card active-male';
                cardFemale.className = 'pref-card';
                previewBox.className = 'noir-preview-box male-mode';
                previewGenderLabel.textContent = 'Phái Mạnh (Nam)';
                previewGenderLabel.style.color = 'var(--c-amber)';
                previewDrinkLabel.textContent = 'Noir Espresso đậm đà & Sea Salt Cold Brew ủ chậm 18h';
                previewDrinkLabel.style.color = 'var(--c-amber)';
            }
        }

        function toggleNoteChip(el) {
            el.classList.toggle('selected');
        }

        async function submitNoirRegistration(e) {
            e.preventDefault();
            const fullname = document.getElementById('regFullname').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const gender = document.getElementById('selectedGender').value;
            const note = document.getElementById('regNote').value.trim();

            if (!fullname || !email) {
                alert('Vui lòng nhập họ tên và email/số điện thoại liên hệ');
                return;
            }

            // Thu thập các nốt hương được chọn
            const selectedNotes = Array.from(document.querySelectorAll('.note-toggle-chip.selected'))
                .map(chip => chip.innerText)
                .join(', ');

            // 1. Lưu CSDL
            try {
                await fetch('../../api/register_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fullname: fullname,
                        email: email,
                        gender: gender,
                        role: 'customer'
                    })
                });
            } catch (err) {
                console.warn('Lỗi gọi API đăng ký:', err);
            }

            // 2. Lưu trạng thái vào Storage
            localStorage.setItem('user_gender', gender);
            localStorage.setItem('user_name', fullname);
            localStorage.setItem('cafe_customer_name', fullname);
            localStorage.setItem('user_gender_authenticated', 'true');
            sessionStorage.setItem('gender_selected_session', 'true');

            // 3. Ghi nhận sự kiện thời gian thực
            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('form_complete', {
                    form_name: 'Thẻ Thành Viên Club Noir',
                    customer_name: fullname,
                    element_text: `Đăng ký thành viên NOIR: ${fullname} (${gender}) | Nốt hương: ${selectedNotes}`,
                    category: 'auth',
                    item_name: 'register_' + gender
                });
            }

            const isFemale = (gender === 'female');
            const welcomeText = isFemale
                ? `🌸 Chào mừng bạn ${fullname} đến với Club Noir!\n\n✨ Đăng ký thành công! Thực đơn đã kích hoạt các dòng thức uống thanh ngọt, Brown Butter Latte và Matcha Uji chuẩn gu dành riêng cho bạn.`
                : `☕ Chào mừng bạn ${fullname} đến với Club Noir!\n\n⚡ Đăng ký thành công! Thực đơn đã kích hoạt các dòng Espresso mộc nguyên bản và Cold Brew ủ chậm 18 giờ bứt phá năng lượng dành riêng cho bạn.`;

            alert(welcomeText);
            window.location.href = 'index.php#recommendationContainer';
        }
    </script>
</body>
</html>
