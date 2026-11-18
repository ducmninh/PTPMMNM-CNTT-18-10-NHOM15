<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực Đơn Đầy Đủ - Milky & Coffee Corner</title>
    <link rel="stylesheet" href="../../assets/css/shop.css?v=<?= time() ?>">
    <?php if (isset($_GET['skip_modal'])): ?>
    <script>
    sessionStorage.setItem('gender_selected_session', '1');
    localStorage.setItem('user_gender_authenticated', '1');
    localStorage.setItem('user_gender', 'male');
    localStorage.setItem('user_name', 'Hoàng Minh Đức');
    </script>
    <?php endif; ?>
</head>
<body>

    <!-- TOP NAVIGATION BAR -->
    <nav class="navbar">
        <a href="index.php" class="brand-wrap">
            <div class="brand-icon-box">☕🧋</div>
            <div class="brand-text-col">
                <span class="brand-name">Milky & Coffee Corner</span>
                <span class="brand-sub">Thực Đơn Chọn Món</span>
            </div>
        </a>

        <!-- USER PROFILE BADGE -->
        <div class="nav-center">
            <div id="userProfileNavBadge" class="user-status-card" onclick="RecommenderEngine.openGenderModal(false)" title="Bấm vào để đổi giới tính hoặc cập nhật tên của bạn">
                <div class="user-avatar-circle">👦</div>
                <div class="user-status-text">
                    <span>Khách Quý</span>
                    <span style="color: #b45309; font-size: 11.5px; font-weight: 800;">(Con Trai)</span>
                </div>
                <span class="btn-change-gender">🔄 Đổi</span>
            </div>
        </div>

        <div class="nav-right">
            <a href="index.php" class="nav-link-item">🏠 Trang Chủ</a>
            <a href="register.php" class="nav-link-item" style="color: #b45309; font-weight: 700; background: rgba(180,83,9,0.08); padding: 7px 14px; border-radius: 999px;">📝 Đăng Ký</a>
            <a href="checkout.php" class="btn-cart-nav btn-track" data-category="general" data-item="xem_gio_hang">
                🛒 Giỏ Hàng <span id="cartCountBadge" class="cart-count-badge" style="display: none;">0</span>
            </a>
            <a href="../../index.php" target="_blank" class="btn-dash-pill">
                📊 Dashboard Thống Kê
            </a>
        </div>
    </nav>

    <main class="container" style="margin-top: 30px;">
        <div class="section-head">
            <div>
                <h1 class="section-title">
                    <span>📋</span> Thực Đơn Đầy Đủ Quán Trà Sữa & Cà Phê
                </h1>
                <p style="color: var(--text-muted); font-size: 14.5px; margin-top: 4px;">
                    Khám phá toàn bộ hương vị cà phê hảo hạng và trà sữa đậm đà
                </p>
            </div>

            <!-- TÌM KIẾM NHANH REALTIME -->
            <div class="search-box-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="menuSearchInput" placeholder="Tìm theo tên hoặc hương vị..." oninput="filterMenuByKeyword(this.value)">
            </div>
        </div>

        <!-- BỘ NÚT LỌC DANH MỤC -->
        <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div class="menu-filter-row">
                <button class="filter-pill-btn active" id="btnFilterAll" onclick="filterCatalog('all', this)">
                    🌟 Tất Cả Món (<span id="countAll">0</span>)
                </button>
                <button class="filter-pill-btn" id="btnFilterCoffee" onclick="filterCatalog('coffee', this)">
                    ☕ Cà Phê (Con Trai Chuộng) (<span id="countCoffee">0</span>)
                </button>
                <button class="filter-pill-btn" id="btnFilterTea" onclick="filterCatalog('milktea', this)">
                    🧋 Trà Sữa (Con Gái Chuộng) (<span id="countTea">0</span>)
                </button>
            </div>
            <div style="font-size: 13.5px; color: var(--text-muted); font-weight: 600;" id="filterStatusLabel">
                Hiển thị toàn bộ thực đơn
            </div>
        </div>

        <div id="fullMenuGrid" class="products-grid">
            <div style="grid-column: 1/-1; text-align: center; padding: 50px 20px; color: #78716c;">
                Đang tải danh sách món...
            </div>
        </div>
    </main>

    <footer class="footer-wrapper" style="margin-top: 60px;">
        <div class="footer-bottom">
            <span>© 2026 Milky & Coffee Corner. Đề tài 17: Phân tích thao tác & Đề xuất món theo giới tính.</span>
            <span>Hệ thống nhận diện đề xuất tức thì</span>
        </div>
    </footer>

    <script src="../../assets/js/tracker.js?v=<?= time() ?>" data-site-key="site_cafe_tea_01"></script>
    <script src="../../assets/js/recommender.js?v=<?= time() ?>"></script>
    <script>
        let allCatalogItems = [];
        let currentFilterCat = 'all';

        fetch('../../api/recommendations.php?action=get_catalog')
            .then(res => res.json())
            .then(data => {
                allCatalogItems = data.catalog || [];
                const cfCount = allCatalogItems.filter(i => i.category === 'coffee').length;
                const teaCount = allCatalogItems.filter(i => i.category === 'milktea').length;
                const elAll = document.getElementById('countAll');
                const elCf = document.getElementById('countCoffee');
                const elTea = document.getElementById('countTea');
                if (elAll) elAll.innerText = allCatalogItems.length;
                if (elCf) elCf.innerText = cfCount;
                if (elTea) elTea.innerText = teaCount;

                renderFullMenu(allCatalogItems);
            });

        function renderFullMenu(items) {
            const grid = document.getElementById('fullMenuGrid');
            if (!grid) return;

            if (items.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--text-muted); background: #ffffff; border-radius: var(--radius-lg); border: 2px dashed var(--border-subtle);">
                        <div style="font-size: 40px; margin-bottom: 12px;">🔍</div>
                        <h3 style="color: var(--text-heading); margin-bottom: 6px;">Không tìm thấy món nào phù hợp</h3>
                        <p style="font-size: 14px;">Vui lòng thử tìm kiếm bằng từ khóa khác.</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = items.map(item => {
                const isCf = (item.category === 'coffee');
                return `
                    <div class="product-card ${isCf ? 'card-male' : 'card-female'}">
                        <div class="product-badge">${isCf ? '☕ Cà Phê' : '🧋 Trà Sữa'}</div>
                        <div class="product-img-wrap">
                            <img src="${item.image}" alt="${item.name}" loading="lazy">
                        </div>
                        <div class="product-info">
                            <div class="product-meta-row">
                                <div class="product-tags">
                                    ${item.tags.map(t => `<span class="tag">${t}</span>`).join('')}
                                    <span class="tag tag-cal">🔥 ${item.calories || '180 kcal'}</span>
                                </div>
                                <span class="product-stars">⭐ 4.9</span>
                            </div>
                            <h3 class="product-title">${item.name}</h3>
                            <p class="product-desc">${item.desc}</p>
                            <div class="product-footer">
                                <div class="product-price-col">
                                    <span class="product-price-label">Giá niêm yết</span>
                                    <span class="product-price">${Number(item.price).toLocaleString('vi-VN')} đ</span>
                                </div>
                                <button class="btn-add-cart btn-track" 
                                    data-category="${item.category}" 
                                    data-item="${item.name}"
                                    onclick="RecommenderEngine.addToCart('${item.id}', '${item.name}', ${item.price}, '${item.category}', '${item.image}')">
                                    🛒 Thêm Giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterCatalog(cat, btn) {
            currentFilterCat = cat;
            document.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const kw = (document.getElementById('menuSearchInput')?.value || '').trim().toLowerCase();
            applyFilterAndSearch(cat, kw);
        }

        function filterMenuByKeyword(kw) {
            applyFilterAndSearch(currentFilterCat, kw.trim().toLowerCase());
        }

        function applyFilterAndSearch(cat, kw) {
            let filtered = allCatalogItems;
            if (cat !== 'all') {
                filtered = filtered.filter(i => i.category === cat);
            }
            if (kw) {
                filtered = filtered.filter(i => 
                    i.name.toLowerCase().includes(kw) || 
                    i.desc.toLowerCase().includes(kw) ||
                    i.tags.some(t => t.toLowerCase().includes(kw))
                );
            }
            const statusLabel = document.getElementById('filterStatusLabel');
            if (statusLabel) {
                statusLabel.innerText = `Tìm thấy ${filtered.length} món`;
            }
            renderFullMenu(filtered);
        }
    </script>
</body>
</html>
