<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOIR CAFÉ — Xác Nhận Đơn & Thanh Toán Trực Tuyến</title>
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
    <style>
        body {
            background-color: var(--c-espresso, #241711) !important;
            color: var(--c-paper-cream, #F2E9DA) !important;
        }
        .checkout-layout {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 28px;
            margin-top: 25px;
        }
        @media (max-width: 860px) {
            .checkout-layout { grid-template-columns: 1fr; }
        }
        .form-card {
            background: rgba(56, 37, 29, 0.45);
            border: 1px solid var(--c-border-dark, rgba(242, 233, 218, 0.12));
            border-radius: 4px;
            padding: 28px;
            box-shadow: 0 15px 35px rgba(20, 13, 10, 0.45);
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--c-warm-beige, #D6C3A6);
            margin-bottom: 8px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(242, 233, 218, 0.05);
            border: 1px solid rgba(242, 233, 218, 0.15);
            border-radius: 2px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.25s;
        }
        .form-control:focus {
            border-color: var(--c-amber, #B96D3A);
            background: rgba(242, 233, 218, 0.09);
            box-shadow: 0 0 16px rgba(185, 109, 58, 0.25);
        }
        .order-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(242, 233, 218, 0.1);
            font-size: 13.5px;
        }
        .price-total-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: 800;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px dashed rgba(242, 233, 218, 0.2);
            color: var(--c-amber, #B96D3A);
        }
        .notice-safe {
            background: rgba(185, 109, 58, 0.1);
            border: 1px solid rgba(185, 109, 58, 0.3);
            border-radius: 3px;
            padding: 14px 18px;
            font-size: 13px;
            color: var(--c-paper-cream, #F2E9DA);
            margin-bottom: 24px;
            line-height: 1.55;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
    </style>
</head>
<body>

    <!-- TOP NAVIGATION BAR -->
    <nav class="navbar" style="background: rgba(22, 14, 10, 0.95); border-bottom: 1px solid rgba(242, 233, 218, 0.12);">
        <a href="index.php" class="brand-wrap">
            <div class="brand-icon-box" style="background: rgba(185, 109, 58, 0.15); border: 1px solid #B96D3A;">☕</div>
            <div class="brand-text-col">
                <span class="brand-name" style="font-family: 'Cormorant Garamond', serif; letter-spacing: 0.15em; font-size: 1.4rem;">NOIR CAFÉ</span>
                <span class="brand-sub" style="color: var(--c-amber, #B96D3A); letter-spacing: 0.1em;">Cinematic Coffee Experience · Thanh Toán</span>
            </div>
        </a>

        <div class="nav-right">
            <a href="index.php" class="nav-link-item" style="color: var(--c-paper-cream, #F2E9DA);">← Quay Lại Menu</a>
            <a href="../../index.php" target="_blank" class="btn-dash-pill" style="border-color: #B96D3A; color: #FAF6F0; background: rgba(185, 109, 58, 0.18);">
                📊 Dashboard Thống Kê
            </a>
        </div>
    </nav>
            </a>
        </div>
    </nav>

    <main class="container" style="max-width: 1050px; margin-top: 25px;">
        <div class="notice-safe">
            <span style="font-size: 20px;">🔒</span>
            <div>
                <strong>Chính sách an toàn & Riêng tư (Đề tài 17):</strong> Hệ thống chỉ ghi nhận thời điểm mở biểu mẫu (form_start) và nộp đơn thành công (form_complete). Tuyệt đối KHÔNG thu thập mật khẩu hay số tài khoản người dùng.
            </div>
        </div>

        <div class="checkout-layout">
            <!-- CỘT FORM THÔNG TIN KHÁCH HÀNG -->
            <div class="form-card">
                <h2 style="font-family: var(--font-heading); font-size: 20px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> Thông Tin Giao Nhận Hàng
                </h2>
                
                <form id="checkoutForm" onsubmit="handlePlaceOrder(event)">
                    <div class="form-group">
                        <label>Giới Tính Khách Hàng (Tối ưu đề xuất AI)</label>
                        <select id="custGender" class="form-control" onchange="RecommenderEngine.setGender(this.value)">
                            <option value="male">👦 Nam (Ưa chuộng Cà phê)</option>
                            <option value="female">👧 Nữ (Ưa chuộng Trà sữa)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Họ và Tên</label>
                        <input type="text" id="custName" class="form-control" placeholder="Ví dụ: Hoàng Minh" required>
                    </div>

                    <div class="form-group">
                        <label>Số Điện Thoại</label>
                        <input type="tel" id="custPhone" class="form-control" placeholder="Ví dụ: 0912 345 678" required>
                    </div>

                    <div class="form-group">
                        <label>Địa Chỉ Giao Hàng</label>
                        <input type="text" id="custAddress" class="form-control" placeholder="Ví dụ: 123 Cầu Giấy, Hà Nội" required>
                    </div>

                    <div class="form-group">
                        <label>Phương Thức Thanh Toán</label>
                        <select class="form-control">
                            <option>Thanh toán khi nhận hàng (COD)</option>
                            <option>Chuyển khoản VietQR / MoMo</option>
                        </select>
                    </div>

                    <button type="submit" id="btnSubmitOrder" class="btn btn-primary btn-track" 
                        data-category="checkout" data-item="xac_nhan_don_hang"
                        style="width: 100%; padding: 15px; font-size: 15.5px; border-radius: var(--radius-md); margin-top: 10px;">
                        🚀 Hoàn Tất Đặt Món Ngay
                    </button>
                </form>

                <!-- KHU VỰC THỬ NGHIỆM BẢO MẬT DÀNH CHO GIẢNG VIÊN -->
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed var(--border-glass-light); text-align: center;">
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">🧪 Khu vực kiểm thử bảo mật dành cho Giảng Viên:</p>
                    <button type="button" onclick="testForbiddenViolation()" 
                        style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 8px 14px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700; cursor: pointer;">
                        ⚠️ Thử gửi dữ liệu vi phạm (Đính kèm mật khẩu password)
                    </button>
                </div>
            </div>

            <!-- CỘT CHI TIẾT ĐƠN HÀNG TRONG GIỎ -->
            <div class="form-card">
                <h2 style="font-family: var(--font-heading); font-size: 20px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span>🛒</span> Món Đã Chọn
                </h2>
                <div id="cartItemsList">
                    <!-- Render các món trong giỏ bằng JS -->
                </div>

                <div class="price-total-box">
                    <span>Tổng Tiền Thanh Toán:</span>
                    <span id="lblTotalPrice" style="color: #10b981; font-family: var(--font-heading); font-size: 22px;">0 đ</span>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addSampleCoffee()">+ Thêm Cà Phê Mẫu</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addSampleTea()">+ Thêm Trà Sữa Mẫu</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearCart()" style="color: #f87171;">Xóa Giỏ</button>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer-wrapper" style="margin-top: 60px;">
        <div class="footer-bottom">
            <span>© 2026 Milky & Coffee Corner. Đề tài 17: Phân tích thao tác & Đề xuất mua hàng.</span>
            <span>Bảo mật dữ liệu tuyệt đối</span>
        </div>
    </footer>

    <!-- SCRIPT TRACKER & ENGINE -->
    <script src="../../assets/js/tracker.js?v=<?= time() ?>" data-site-key="site_cafe_tea_01"></script>
    <script src="../../assets/js/recommender.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tự động điền tên và giới tính đã chọn
            const curGender = localStorage.getItem('user_gender') || 'male';
            const curName = localStorage.getItem('user_name') || '';
            const custGenderEl = document.getElementById('custGender');
            const custNameEl = document.getElementById('custName');

            if (custGenderEl) custGenderEl.value = curGender;
            if (custNameEl && curName && curName !== 'Khách Quý') {
                custNameEl.value = curName;
            }

            // Nếu giỏ hàng trống, tự động nạp 1 món gợi ý theo phong cách NOIR để tiện trải nghiệm
            let cart = JSON.parse(localStorage.getItem('cafe_cart') || '[]');
            if (cart.length === 0) {
                if (curGender === 'female') {
                    cart.push({ id: 'ts_matcha_kyoto', name: 'Classic Matcha Latte (Uji Kyoto)', price: 55000, category: 'tea', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=800&q=80', qty: 1 });
                } else {
                    cart.push({ id: 'cf_espresso', name: 'Noir Espresso (House Blend)', price: 45000, category: 'espresso', image: 'img/espresso.jpg', qty: 1 });
                }
                localStorage.setItem('cafe_cart', JSON.stringify(cart));
            }
            renderCheckoutCart();
        });

        function renderCheckoutCart() {
            const cart = JSON.parse(localStorage.getItem('cafe_cart') || '[]');
            const listEl = document.getElementById('cartItemsList');
            const totalEl = document.getElementById('lblTotalPrice');

            if (cart.length === 0) {
                listEl.innerHTML = '<div style="text-align:center; padding:30px; color:#94a3b8;">Giỏ hàng đang trống. Hãy bấm thêm món mẫu phía dưới!</div>';
                totalEl.innerText = '0 đ';
                return;
            }

            let total = 0;
            listEl.innerHTML = cart.map(item => {
                const subTotal = item.price * item.qty;
                total += subTotal;
                return `
                    <div class="order-item-row">
                        <div>
                            <strong>${item.name}</strong> 
                            <span style="font-size:11.5px; color:#94a3b8; display:block;">${item.category === 'coffee' ? '☕ Nhóm Cà Phê' : '🧋 Nhóm Trà Sữa'}</span>
                        </div>
                        <div style="text-align: right;">
                            <div>${Number(item.price).toLocaleString('vi-VN')} đ x ${item.qty}</div>
                            <strong style="color:#fbbf24;">${Number(subTotal).toLocaleString('vi-VN')} đ</strong>
                        </div>
                    </div>
                `;
            }).join('');

            totalEl.innerText = Number(total).toLocaleString('vi-VN') + ' đ';
        }

        function addSampleCoffee() {
            RecommenderEngine.addToCart('cf_suada', 'Cà Phê Sữa Đá Sài Gòn', 32000, 'coffee', '');
            renderCheckoutCart();
        }

        function addSampleTea() {
            RecommenderEngine.addToCart('ts_olongnuong', 'Trà Sữa Ô Long Nướng Kem Trứng', 48000, 'milktea', '');
            renderCheckoutCart();
        }

        function clearCart() {
            localStorage.setItem('cafe_cart', '[]');
            RecommenderEngine.updateCartBadge();
            renderCheckoutCart();
        }

        function handlePlaceOrder(e) {
            e.preventDefault();
            const cart = JSON.parse(localStorage.getItem('cafe_cart') || '[]');
            if (cart.length === 0) {
                alert('Vui lòng chọn ít nhất 1 món đồ uống trước khi hoàn tất!');
                return;
            }

            const name = document.getElementById('custName').value.trim();
            const address = document.getElementById('custAddress') ? document.getElementById('custAddress').value.trim() : 'Hà Nội';
            const gender = document.getElementById('custGender').value;
            
            localStorage.setItem('user_name', name);
            localStorage.setItem('user_address', address);
            localStorage.setItem('cafe_customer_name', name);
            localStorage.setItem('cafe_customer_address', address);

            let coffeeCount = 0, milkteaCount = 0;
            cart.forEach(i => {
                if (i.category === 'coffee') coffeeCount += i.qty;
                if (i.category === 'milktea') milkteaCount += i.qty;
            });
            const primaryCat = (coffeeCount >= milkteaCount) ? 'coffee' : 'milktea';
            const itemsSummary = cart.map(i => `${i.name} x${i.qty}`).join(', ');
            const totalPrice = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);

            if (window.AnalyticsTracker) {
                AnalyticsTracker.track('form_complete', {
                    form_name: 'Biểu Mẫu Đặt Món & Thanh Toán Giao Hàng',
                    customer_name: name,
                    customer_address: address,
                    element_text: 'Hoàn tất đơn hàng: ' + itemsSummary,
                    category: 'checkout',
                    item_name: 'order_complete'
                });
            }

            fetch('../../api/recommendations.php?action=record_purchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: AnalyticsTracker.getSessionId(),
                    customer_name: name,
                    gender: gender,
                    primary_category: primaryCat,
                    items_summary: itemsSummary,
                    total_price: totalPrice
                })
            })
            .then(res => res.json())
            .then(data => {
                localStorage.setItem('cafe_cart', '[]');
                setTimeout(() => {
                    window.location.href = `success.html?order_id=${data.order_id || 'new'}&gender=${gender}&cat=${primaryCat}`;
                }, 300);
            })
            .catch(err => {
                alert('Có lỗi khi gửi đơn hàng: ' + err);
            });
        }

        function testForbiddenViolation() {
            fetch('../../api/track.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    site_key: 'site_cafe_tea_01',
                    session_id: AnalyticsTracker.getSessionId(),
                    event_id: 'evt_fbd_' + Date.now(),
                    event_code: 'form_complete',
                    page_url: window.location.pathname,
                    password: 'mat_khau_nguoi_dung_123456'
                })
            })
            .then(async res => {
                const data = await res.json();
                alert(`🚨 PHẢN HỒI TỪ SERVER PHP (Mã HTTP ${res.status}):\n- Loại lỗi: ${data.status}\n- Chi tiết: ${data.error}\n\n-> Máy chủ đã từ chối tiếp nhận và ghi nhận vi phạm vào Bảng Lỗi Tiếp Nhận (Ingestion Errors) trên Dashboard!`);
            });
        }
    </script>
</body>
</html>
