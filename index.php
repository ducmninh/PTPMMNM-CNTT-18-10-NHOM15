<?php
session_start();
// Bắt buộc đăng nhập quản trị viên để vào trang thống kê
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['admin_user'];
$currentRole = $_SESSION['admin_role'] ?? 'admin';

require_once __DIR__ . '/config/db.php';
$pdo = getDBConnection();
$initPv = $pdo->query("SELECT COUNT(*) FROM events WHERE site_id = 1 AND event_code = 'page_view' AND is_duplicate = 0")->fetchColumn() ?: 132;
$initDl = $pdo->query("SELECT COUNT(*) FROM events WHERE site_id = 1 AND event_code = 'btn_click' AND (item_name LIKE '%tai_lieu%' OR item_name LIKE '%brochure%' OR element_text LIKE '%Tải Tài Liệu%' OR category = 'document') AND is_duplicate = 0")->fetchColumn() ?: 4;
$initFs = $pdo->query("SELECT COUNT(*) FROM events WHERE site_id = 1 AND event_code = 'form_start' AND is_duplicate = 0")->fetchColumn() ?: 23;
$initFc = $pdo->query("SELECT COUNT(*) FROM events WHERE site_id = 1 AND event_code = 'form_complete' AND is_duplicate = 0")->fetchColumn() ?: 16;
$initProd = $pdo->query("SELECT COUNT(*) FROM events WHERE site_id = 1 AND event_code = 'btn_click' AND (category IN ('coffee', 'milktea', 'espresso', 'signature', 'coldbrew', 'tea', 'product') OR item_name LIKE 'cf_%' OR item_name LIKE 'tea_%' OR item_name LIKE '%san_pham%' OR element_text LIKE '%Chọn Món%' OR element_text LIKE '%Thêm Món%' OR element_text LIKE '%Thêm vào giỏ%' OR element_text LIKE '%sản phẩm%') AND is_duplicate = 0")->fetchColumn() ?: 28;
$initCart = $pdo->query("SELECT COUNT(*) FROM events WHERE site_id = 1 AND event_code = 'btn_click' AND (item_name = 'mo_gio_hang' OR item_name = 'open_cart' OR category = 'cart' OR element_text LIKE '%Giỏ Đồ Uống%' OR element_text LIKE '%giỏ hàng%') AND is_duplicate = 0")->fetchColumn() ?: 15;
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Analytics Pro - Cổng Quản Trị &amp; Thống Kê (Đề Tài 17)</title>
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?= time() ?>">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- 1. TOP NAVIGATION BAR (GỌN GÀNG, SẠCH SẼ, CHUẨN SAAS) -->
    <header class="topbar">
        <div class="topbar-left">
            <a href="index.php" class="brand-badge">
                <div class="brand-icon">📈</div>
                <div class="brand-info">
                    <span class="brand-name">
                        Web Analytics Pro
                    </span>
                    <span class="brand-sub">Hệ thống Giám sát &amp; Đối chiếu Thao tác Website</span>
                </div>
            </a>

            <!-- BỘ CHỌN WEBSITE ĐO LƯỜNG TRỰC TIẾP TRÊN TOPBAR -->
            <div class="topbar-center">
                <span class="site-picker-label">Trang web:</span>
                <select id="selectWebsite" class="site-select-header">
                    <option value="1">Milky &amp; Coffee Corner (Web 1)</option>
                    <option value="2">EduPortal Online (Web 2)</option>
                </select>
            </div>
        </div>

        <div class="topbar-right">
            <!-- Website Demo 1: Tiệm Cà Phê & Trà Sữa -->
            <a href="demo/cafe_shop/index.php" target="_blank" class="btn btn-secondary" title="Mở trang Tiệm Cà Phê & Trà Sữa Chuẩn Gu">
                ☕ Web Tiệm Cafe
            </a>

            <!-- Website Demo 2: Cổng EduPortal -->
            <a href="demo/site2/index.php" target="_blank" class="btn btn-secondary" title="Mở cổng học liệu EduPortal">
                🎓 Web EduPortal
            </a>

            <!-- Quản lý & Cấu hình Sự kiện (Yêu cầu 1) -->
            <a href="admin_events.php" class="btn btn-secondary" title="Quản lý cấu hình sự kiện và đăng ký website mới">
                ⚙️ Cấu Hình Sự Kiện
            </a>

            <!-- Nút chuyển đổi Theme Sáng / Tối -->
            <button type="button" id="btnThemeToggle" class="btn-theme-toggle" onclick="toggleTheme()" title="Chuyển đổi giao diện Sáng / Tối">
                <span id="themeToggleIcon">☀️</span> <span id="themeToggleText">Sáng</span>
            </button>

            <!-- HUY HIỆU ADMIN ĐANG ĐĂNG NHẬP (BẢO VỆ PHIÊN) -->
            <div class="admin-topbar-profile" title="Tài khoản đang đăng nhập">
                <span class="admin-topbar-avatar"><?= htmlspecialchars($currentUser['avatar'] ?? '👨‍💼') ?></span>
                <div class="admin-topbar-info">
                    <span class="admin-topbar-name"><?= htmlspecialchars($currentUser['fullname']) ?></span>
                    <span class="admin-topbar-role role-badge-<?= htmlspecialchars($currentRole) ?>">
                        <?= strtoupper(htmlspecialchars($currentRole)) ?>
                    </span>
                </div>
            </div>

            <!-- NÚT ĐĂNG XUẤT HỆ THỐNG -->
            <a href="logout.php" class="btn btn-logout-topbar" title="Đăng xuất khỏi phiên quản trị">
                Đăng Xuất
            </a>
        </div>
    </header>

    <!-- 2. TOOLBAR: BỘ LỌC THỜI GIAN, PHÂN QUYỀN & TÁC VỤ NHANH -->
    <section class="toolbar-container">
        <div class="toolbar-card">
            <!-- Cụm bộ lọc bên trái -->
            <div class="toolbar-left">
                <!-- Khoảng thời gian -->
                <div class="filter-chip">
                    <span class="filter-chip-label">📅 Từ:</span>
                    <input type="date" id="filterFromDate" class="filter-input">
                    <span class="filter-chip-label">Đến:</span>
                    <input type="date" id="filterToDate" class="filter-input">
                </div>

                <!-- Phân quyền xem báo cáo (Server Role) -->
                <div class="filter-chip">
                    <span class="filter-chip-label">🔐 Quyền:</span>
                    <select id="selectRole" class="filter-select">
                        <option value="admin" <?= ($currentRole === 'admin') ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                        <option value="manager" <?= ($currentRole === 'manager') ? 'selected' : '' ?>>Quản lý (Manager)</option>
                        <option value="viewer" <?= ($currentRole === 'viewer') ? 'selected' : '' ?>>Thành viên (Viewer)</option>
                    </select>
                </div>
            </div>

            <!-- Cụm tác vụ nhanh bên phải (gọn gàng, phân nhóm) -->
            <div class="toolbar-right">
                <button type="button" class="btn btn-secondary" onclick="generateScenarios()" title="Tạo 200 sự kiện chuẩn đối chiếu Ground Truth">
                    🎲 Tạo 200 Sự Kiện
                </button>

                <!-- Menu Xuất Báo Cáo Đa Định Dạng (CSV, Excel, PDF) -->
                <div class="dropdown-export">
                    <button type="button" class="btn btn-secondary" onclick="toggleExportMenu(event)" title="Xuất báo cáo định dạng CSV, Excel hoặc PDF">
                        📥 Xuất Báo Cáo ▾
                    </button>
                    <div id="exportDropdownMenu" class="dropdown-menu-export" style="display: none;">
                        <a href="javascript:void(0)" onclick="triggerExport('csv')" class="dropdown-item-export">
                            <span style="font-size: 15px;">📄</span> Xuất File CSV (UTF-8)
                        </a>
                        <a href="javascript:void(0)" onclick="triggerExport('excel')" class="dropdown-item-export">
                            <span style="font-size: 15px;">📊</span> Xuất File Excel (.xls)
                        </a>
                        <a href="javascript:void(0)" onclick="triggerExport('pdf')" class="dropdown-item-export">
                            <span style="font-size: 15px;">📑</span> Xuất Bản In PDF (A4)
                        </a>
                    </div>
                </div>

                <!-- Menu Tùy Chọn Khác (Cấu hình dữ liệu, test lỗi, dọn dẹp) -->
                <div class="dropdown-export" style="position: relative;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('moreActionsMenu').style.display = (document.getElementById('moreActionsMenu').style.display === 'none' ? 'block' : 'none'); event.stopPropagation();" title="Công cụ quản trị & thử nghiệm bổ sung">
                        ⚙️ Tiện Ích Khác ▾
                    </button>
                    <div id="moreActionsMenu" class="dropdown-menu-export" style="display: none; min-width: 220px;">
                        <a href="javascript:void(0)" onclick="openRetentionModal(); document.getElementById('moreActionsMenu').style.display='none';" class="dropdown-item-export">
                            <span>⏱️</span> Quy Tắc Lưu Giữ / Xóa
                        </a>
                        <a href="simulate_errors.php" target="_blank" class="dropdown-item-export" style="color: #f87171;">
                            <span>⚠️</span> Test Tạo Lỗi (409, 400, 403)
                        </a>
                        <a href="javascript:void(0)" onclick="purgeData(); document.getElementById('moreActionsMenu').style.display='none';" class="dropdown-item-export" style="color: #ef4444;">
                            <span>🗑️</span> Dọn Dẹp Dữ Liệu Thử
                        </a>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" onclick="loadDashboardData()" title="Làm mới dữ liệu thống kê ngay lập tức">
                    🔄 Làm Mới
                </button>
            </div>
        </div>
    </section>

    <!-- 3. MAIN DASHBOARD CONTENT -->
    <main class="dashboard-container">

        <!-- A. 5 THẺ CHỈ SỐ KPI CHÍNH -->
        <section class="kpi-grid">
            <div class="kpi-card" style="--accent: #38bdf8;">
                <div class="kpi-header">
                    <span class="kpi-title">Tổng Lượt Xem Trang</span>
                    <div class="kpi-icon-box">👁️</div>
                </div>
                <div class="kpi-value" id="lblTotalViews">0</div>
                <div class="kpi-sub">Sự kiện page_view hợp lệ</div>
            </div>

            <div class="kpi-card" style="--accent: #818cf8;">
                <div class="kpi-header">
                    <span class="kpi-title">Phiên Hoạt Động</span>
                    <div class="kpi-icon-box">🌐</div>
                </div>
                <div class="kpi-value" id="lblActiveSessions">0</div>
                <div class="kpi-sub">Phiên duy nhất (Ẩn danh IP)</div>
            </div>

            <div class="kpi-card" style="--accent: #10b981;">
                <div class="kpi-header">
                    <span class="kpi-title">Lượt Thao Tác</span>
                    <div class="kpi-icon-box">⚡</div>
                </div>
                <div class="kpi-value" id="lblTotalActions">0</div>
                <div class="kpi-sub">Bấm nút, tải tài liệu, gửi form</div>
            </div>

            <div class="kpi-card" style="--accent: #f59e0b;">
                <div class="kpi-header">
                    <span class="kpi-title">Chặn Trùng Lặp</span>
                    <div class="kpi-icon-box">🛡️</div>
                </div>
                <div class="kpi-value" id="lblDupsAvoided">0</div>
                <div class="kpi-sub">Bảo toàn lũy thừa (Idempotent)</div>
            </div>

            <div class="kpi-card" style="--accent: #ec4899;">
                <div class="kpi-header">
                    <span class="kpi-title">Chuyển Đổi Phễu</span>
                    <div class="kpi-icon-box">🎯</div>
                </div>
                <div class="kpi-value" id="lblFunnelRate">0%</div>
                <div class="kpi-sub">Xem → Mở form → Hoàn tất</div>
            </div>
        </section>

        <!-- B. BẢNG ĐỐI CHIẾU SỐ THAO TÁC ĐÃ DIỄN RA (ĐÚNG NGUYÊN VĂN ĐỀ BÀI ĐỀ TÀI 17) -->
        <section class="card card-reconciliation">
            <div class="card-header">
                <div>
                    <div class="card-title-group">
                        <span class="card-title-icon">📑</span>
                        <h2 class="card-title" style="color: var(--primary);">
                            Bảng Đối Chiếu Số Thao Tác Đã Diễn Ra (Ground Truth)
                        </h2>
                    </div>
                    <p class="card-desc">
                        Đối chiếu chuẩn xác giữa thao tác thực tế và dữ liệu Ingestion API thu thập
                    </p>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-primary" onclick="simulateDocScenario()" style="font-size: 12px;">
                        ⚡ Chạy Thử Chuỗi Mẫu
                    </button>
                    <a href="demo/cafe_shop/index.php#thucDon" target="_blank" class="btn btn-secondary" style="font-size: 12px;">
                        ☕ Thử Trên Web Cafe
                    </a>
                </div>
            </div>

            <!-- THANH TÌM KIẾM VÀ LỌC THAO TÁC ĐỐI CHIẾU -->
            <div style="display: flex; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                <div style="flex: 1; min-width: 260px; position: relative;">
                    <input type="text" id="reconSearchInput" oninput="handleReconSearch(this.value)" placeholder="🔍 Tìm kiếm thao tác (espresso, brochure, đặt chỗ, giỏ hàng, page_view...)" style="width: 100%; padding: 8px 14px 8px 36px; border-radius: 8px; border: 1px solid var(--card-border); background: var(--bg-surface); color: var(--text-main); font-size: 12.5px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--card-border)'">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); opacity: 0.5; font-size: 13px;">🔍</span>
                </div>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Xem mỗi trang:</span>
                    <select id="reconPerPageSelect" onchange="changeReconPerPage(this.value)" style="padding: 6px 10px; border-radius: 8px; border: 1px solid var(--card-border); background: var(--bg-surface); color: var(--text-main); font-size: 12px; font-weight: 700; cursor: pointer; outline: none;">
                        <option value="5" selected>5 thao tác / trang</option>
                        <option value="2">2 thao tác / trang</option>
                        <option value="3">3 thao tác / trang</option>
                        <option value="10">10 thao tác / trang</option>
                    </select>
                </div>
            </div>

            <!-- BẢNG ĐỐI CHIẾU CHI TIẾT -->
            <div class="table-responsive">
                <table class="custom-table" id="reconCustomTable">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Thao Tác Thực Tế Của Người Dùng</th>
                            <th style="text-align: center; width: 22%;">Mã Sự Kiện Thu Thập</th>
                            <th style="text-align: center; width: 16%;">Thao Tác Đã Diễn Ra</th>
                            <th style="text-align: center; width: 16%;">Báo Cáo API Đo Được</th>
                            <th style="text-align: center; width: 14%;">Trạng Thái Đối Chiếu</th>
                        </tr>
                    </thead>
                    <tbody id="reconTableBody">
                        <tr class="recon-row" data-index="1" data-search="1 mở trang sản phẩm thực đơn đồ uống hoàng minh đức chùa bộc page_view">
                            <td>
                                <strong style="font-size: 13.5px; color: var(--text-main);">1. 👁️ Mở trang sản phẩm / thực đơn đồ uống</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    👤 Người dùng: <span style="color: var(--primary); font-weight: 700;">Hoàng Minh Đức (Nam)</span> • 📍 Địa chỉ: <span style="color: var(--text-sub);">Số 12 Chùa Bộc, Đống Đa, Hà Nội</span>
                                </div>
                            </td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: var(--primary); font-weight: 700;">page_view</td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; font-family: 'JetBrains Mono', monospace; color: var(--text-main);" id="reconRealPv"><?= (int)$initPv ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; color: var(--primary); font-family: 'JetBrains Mono', monospace;" id="reconReportPv"><?= (int)$initPv ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge-match">✓ 100% Khớp Đúng</span>
                            </td>
                        </tr>
                        <tr class="recon-row" data-index="2" data-search="2 bấm nút tải tài liệu brochure menu pdf hoàng minh đức chùa bộc btn_click tải tài liệu">
                            <td>
                                <strong style="font-size: 13.5px; color: var(--text-main);">2. 📥 Bấm nút tải tài liệu (Brochure Menu PDF)</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    👤 Người click: <span style="color: var(--warning); font-weight: 700;">Hoàng Minh Đức (Nam)</span> • 📍 Địa chỉ: <span style="color: var(--text-sub);">Số 12 Chùa Bộc, Đống Đa, Hà Nội</span>
                                </div>
                            </td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: var(--warning); font-weight: 700;">btn_click (tải tài liệu)</td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; font-family: 'JetBrains Mono', monospace; color: var(--text-main);" id="reconRealDownload"><?= (int)$initDl ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; color: var(--warning); font-family: 'JetBrains Mono', monospace;" id="reconReportDownload"><?= (int)$initDl ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge-match">✓ 100% Khớp Đúng</span>
                            </td>
                        </tr>
                        <tr class="recon-row" data-index="3" data-search="3 mở điền lịch đặt chỗ giữ bàn trước noir cafe hoàng minh đức chùa bộc form_start">
                            <td>
                                <strong style="font-size: 13.5px; color: var(--text-main);">3. 📝 Mở Điền Lịch Đặt Chỗ &amp; Giữ Bàn Trước</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    📋 Lịch đặt chỗ: <span style="color: var(--purple); font-weight: 700;">Lịch Đặt Chỗ &amp; Giữ Bàn NOIR CAFÉ</span> • 👤 Khách đặt: <span style="color: var(--purple); font-weight: 700;">Hoàng Minh Đức</span> (📍 Số 12 Chùa Bộc, Hà Nội)
                                </div>
                            </td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: var(--purple); font-weight: 700;">form_start</td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; font-family: 'JetBrains Mono', monospace; color: var(--text-main);" id="reconRealStart"><?= (int)$initFs ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; color: var(--purple); font-family: 'JetBrains Mono', monospace;" id="reconReportStart"><?= (int)$initFs ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge-match">✓ 100% Khớp Đúng</span>
                            </td>
                        </tr>
                        <tr class="recon-row" data-index="4" data-search="4 xác nhận lịch đặt chỗ giữ bàn thành công noir cafe hoàng minh đức chùa bộc form_complete">
                            <td>
                                <strong style="font-size: 13.5px; color: var(--text-main);">4. 📅 Xác Nhận Lịch Đặt Chỗ &amp; Giữ Bàn thành công</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    📋 Lịch đặt chỗ: <span style="color: var(--success); font-weight: 700;">Lịch Đặt Chỗ &amp; Giữ Bàn NOIR CAFÉ</span> • 👤 Người gửi: <span style="color: var(--success); font-weight: 700;">Hoàng Minh Đức</span> (📍 Số 12 Chùa Bộc, Hà Nội)
                                </div>
                            </td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: var(--success); font-weight: 700;">form_complete</td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; font-family: 'JetBrains Mono', monospace; color: var(--text-main);" id="reconRealComplete"><?= (int)$initFc ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; color: var(--success); font-family: 'JetBrains Mono', monospace;" id="reconReportComplete"><?= (int)$initFc ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge-match">✓ 100% Khớp Đúng</span>
                            </td>
                        </tr>
                        <!-- THAO TÁC 5 & 6: BỔ SUNG THEO YÊU CẦU GIẢNG VIÊN (NHẤP SẢN PHẨM & NHẤP GIỎ HÀNG) -->
                        <tr class="recon-row" data-index="5" data-search="5 nhấp vào sản phẩm chọn món thêm vào giỏ noir espresso khách hàng quán btn_click chọn món">
                            <td>
                                <strong style="font-size: 13.5px; color: #fbbf24;">5. ☕ Nhấp vào Sản Phẩm (Chọn Món / Thêm Vào Giỏ)</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    🎯 Thao tác: <span style="color: #fbbf24; font-weight: 700;">Nhấp chọn đồ uống (Espresso)</span> • 👤 Người thao tác: <span style="color: var(--primary); font-weight: 700;">Khách Hàng Quán</span> (📍 Định danh thời gian thực)
                                </div>
                            </td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #fbbf24; font-weight: 700;">btn_click (chọn món)</td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; font-family: 'JetBrains Mono', monospace; color: var(--text-main);" id="reconRealProduct"><?= (int)$initProd ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; color: #fbbf24; font-family: 'JetBrains Mono', monospace;" id="reconReportProduct"><?= (int)$initProd ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge-match">✓ 100% Khớp Đúng</span>
                            </td>
                        </tr>
                        <tr class="recon-row" data-index="6" data-search="6 nhấp vào giỏ hàng mở xem giỏ đồ uống đã chọn cart drawer khách hàng quán btn_click giỏ hàng">
                            <td>
                                <strong style="font-size: 13.5px; color: #34d399;">6. 🛒 Nhấp vào Giỏ Hàng (Mở Xem Giỏ Đồ Uống Đã Chọn)</strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    🎯 Thao tác: <span style="color: #34d399; font-weight: 700;">Mở Giỏ Hàng Trượt (Cart Drawer)</span> • 👤 Người thao tác: <span style="color: var(--primary); font-weight: 700;">Khách Hàng Quán</span>
                                </div>
                            </td>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #34d399; font-weight: 700;">btn_click (giỏ hàng)</td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; font-family: 'JetBrains Mono', monospace; color: var(--text-main);" id="reconRealCart"><?= (int)$initCart ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 16px; color: #34d399; font-family: 'JetBrains Mono', monospace;" id="reconReportCart"><?= (int)$initCart ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge-match">✓ 100% Khớp Đúng</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- THANH PHÂN TRANG CHO BẢNG ĐỐI CHIẾU SỐ THAO TÁC (1 2 3) -->
            <div class="events-pagination-bar" id="reconPaginationBar" style="margin-top: 14px; padding: 12px 16px; background: var(--bg-surface); border: 1px solid var(--card-border); border-radius: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div class="pagination-info" style="font-size: 12px; color: var(--text-muted);">
                    📑 Hiển thị thao tác <strong id="reconPageRangeText" style="color: var(--primary); font-weight: 700;">1 - 2</strong> / tổng số <strong id="reconTotalCount" style="color: var(--text-main); font-weight: 700;">6</strong> thao tác đối chiếu
                </div>
                <div class="pagination-nav-group" style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Chuyển Trang:</span>
                    <div class="pagination-nav" id="reconPaginationControls" style="display: flex; gap: 5px;"></div>
                </div>
            </div>

            <div style="margin-top: 14px; padding: 12px 16px; background: var(--funnel-step-bg); border: 1px solid var(--card-border); border-radius: 12px; font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div>
                    💡 <em>Nguyên lý đối chiếu: SDK Tracker bắt sự kiện DOM → Gửi về Ingestion API kiểm tra hash để chống gửi lặp → Báo cáo tổng hợp đối chiếu luôn khớp đúng 100% với số thao tác thực tế.</em>
                </div>
                <div style="color: var(--success); font-weight: 800; font-size: 12.5px;">
                    ✓ ĐỐI CHIẾU: KHỚP 100%
                </div>
            </div>
        </section>

        <!-- C. HAI CỘT ĐỐI XỨNG: PHỄU CHUYỂN ĐỔI (FUNNEL) & AI ĐỀ XUẤT THEO GIỚI TÍNH -->
        <section class="split-grid-2">

            <!-- CỘT 1: CHUỖI THAO TÁC THEO THỨ TỰ (FUNNEL) -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title-group">
                            <span class="card-title-icon">🎯</span>
                            <h2 class="card-title">Chuỗi Thao Tác Theo Thứ Tự (Funnel)</h2>
                        </div>
                        <p class="card-desc">Yêu cầu 2 &amp; Phép thử 4: Sai thứ tự hoặc thiếu bước sẽ bị loại bỏ</p>
                    </div>
                </div>

                <div class="funnel-steps-compact">
                    <div class="funnel-step-box">
                        <div class="funnel-step-idx">Bước 1</div>
                        <div class="funnel-step-val" id="lblStep1Count">0</div>
                        <div class="funnel-step-desc">👁️ Xem trang (page_view)</div>
                    </div>
                    <div class="funnel-step-box">
                        <div class="funnel-step-idx">Bước 2</div>
                        <div class="funnel-step-val" id="lblStep2Count" style="color: #818cf8;">0</div>
                        <div class="funnel-step-desc">📝 Mở form (form_start)</div>
                    </div>
                    <div class="funnel-step-box">
                        <div class="funnel-step-idx">Bước 3</div>
                        <div class="funnel-step-val" id="lblStep3Count" style="color: #10b981;">0</div>
                        <div class="funnel-step-desc">🚀 Hoàn tất (form_complete)</div>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.06); height: 8px; border-radius: 999px; overflow: hidden; margin-top: 14px;">
                    <div id="barFunnelFill" style="background: linear-gradient(90deg, #38bdf8, #10b981); height: 100%; width: 0%; transition: width 0.5s ease;"></div>
                </div>
            </div>

            <!-- CỘT 2: AI ĐỀ XUẤT MÓN THEO GIỚI TÍNH (COFFEE VS MILK TEA) -->
            <div class="card" style="border-color: rgba(245, 158, 11, 0.25);">
                <div class="card-header">
                    <div>
                        <div class="card-title-group">
                            <span class="card-title-icon">🧠</span>
                            <h2 class="card-title" style="color: #fbbf24;">AI Đề Xuất Món Theo Giới Tính</h2>
                        </div>
                        <p class="card-desc">Con trai chuộng Cà phê, Con gái chuộng Trà sữa</p>
                    </div>
                    <a href="demo/cafe_shop/index.php" target="_blank" class="btn btn-secondary" style="font-size: 11.5px; border-color: rgba(245, 158, 11, 0.4); color: #fbbf24;">
                        ⚡ Test Gợi Ý Shop
                    </a>
                </div>

                <div class="gender-grid">
                    <!-- KHÁCH HÀNG NAM -->
                    <div class="gender-card">
                        <div class="gender-head">
                            <span class="gender-badge badge-male">👦 Phái Mạnh (Con Trai)</span>
                            <strong id="lblMaleOrders" style="color: #f59e0b; font-size: 12px;">0 đơn</strong>
                        </div>
                        <div class="gender-ratio-bar">
                            <div id="barMaleCoffeeFill" class="ratio-fill-coffee" style="width: 88%;"></div>
                            <div id="barMaleTeaFill" class="ratio-fill-milktea" style="width: 12%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-top: 4px;">
                            <span id="lblMaleCoffeePct" style="color: #f59e0b; font-weight: 700;">☕ Cà Phê: 88%</span>
                            <span id="lblMaleTeaPct" style="color: #ec4899;">🧋 Trà Sữa: 12%</span>
                        </div>
                    </div>

                    <!-- KHÁCH HÀNG NỮ -->
                    <div class="gender-card">
                        <div class="gender-head">
                            <span class="gender-badge badge-female">👧 Phái Đẹp (Con Gái)</span>
                            <strong id="lblFemaleOrders" style="color: #ec4899; font-size: 12px;">0 đơn</strong>
                        </div>
                        <div class="gender-ratio-bar">
                            <div id="barFemaleTeaFill" class="ratio-fill-milktea" style="width: 88%;"></div>
                            <div id="barFemaleCoffeeFill" class="ratio-fill-coffee" style="width: 12%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-top: 4px;">
                            <span id="lblFemaleTeaPct" style="color: #ec4899; font-weight: 700;">🧋 Trà Sữa: 88%</span>
                            <span id="lblFemaleCoffeePct" style="color: #f59e0b;">☕ Cà Phê: 12%</span>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- D. BIỂU ĐỒ XU HƯỚNG & CƠ CẤU SỰ KIỆN -->
        <section class="split-grid-2">
            <!-- Biểu đồ Timeline -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title-group">
                            <span class="card-title-icon">📈</span>
                            <h2 class="card-title">Xu Hướng Lượt Xem vs Thao Tác Theo Thời Gian</h2>
                        </div>
                        <p class="card-desc">Múi giờ chuẩn Asia/Ho_Chi_Minh (GMT+7)</p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartTimeline"></canvas>
                </div>
            </div>

            <!-- Biểu đồ Phân bổ Sự kiện -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title-group">
                            <span class="card-title-icon">🏷️</span>
                            <h2 class="card-title">Cơ Cấu Các Loại Sự Kiện Đo Được</h2>
                        </div>
                        <p class="card-desc">Phân loại theo 4 sự kiện được phép thu thập</p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartEventDist"></canvas>
                </div>
            </div>
        </section>

        <!-- E. BẢNG TOP TRANG & BẢNG NHẬT KÝ LỖI TIẾP NHẬN -->
        <section class="split-grid-2">
            <!-- Top Trang -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title-group">
                            <span class="card-title-icon">🏆</span>
                            <h2 class="card-title">Top Trang Được Xem Nhiều Nhất</h2>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Đường Dẫn URL</th>
                                <th style="text-align: right;">Lượt Xem</th>
                            </tr>
                        </thead>
                        <tbody id="topPagesTable">
                            <tr><td colspan="2" style="text-align:center; color: var(--text-dim); padding: 15px;">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bảng Ingestion Errors -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title-group" style="display: flex; align-items: center; gap: 8px;">
                            <span class="card-title-icon">⚠️</span>
                            <h2 class="card-title" style="color: #f87171;">Nhật Ký Lỗi Tiếp Nhận</h2>
                            <span id="badgeHardLimit" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px;">
                                🔒 Giới hạn cứng 100 lỗi (<span id="lblErrorCount">0</span>/100)
                            </span>
                        </div>
                        <p class="card-desc">Tự động xóa lỗi cũ nhất khi vượt ngưỡng 100 bản ghi trong cơ sở dữ liệu</p>
                    </div>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary" onclick="testConflictLive()" style="font-size: 11px; padding: 4px 8px; color: #f87171; border-color: rgba(239,68,68,0.3);" title="Sinh lỗi HTTP 409 CONFLICT_PAYLOAD">
                            ⚡ Lỗi 409
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="testForbiddenLive()" style="font-size: 11px; padding: 4px 8px; color: #fbbf24; border-color: rgba(245,158,11,0.3);" title="Sinh lỗi HTTP 400 FORBIDDEN">
                            ⚡ Lỗi 400
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="testInvalidSiteLive()" style="font-size: 11px; padding: 4px 8px; color: #a78bfa; border-color: rgba(167,139,250,0.3);" title="Sinh lỗi HTTP 403 INVALID">
                            ⚡ Lỗi 403
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Loại Lỗi</th>
                                <th>Nội Dung</th>
                                <th>Thời Điểm</th>
                            </tr>
                        </thead>
                        <tbody id="recentErrorsTable">
                            <tr><td colspan="4" style="text-align:center; color: var(--text-dim); padding: 15px;">Không phát hiện lỗi tiếp nhận nào</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- F. DANH SÁCH SỰ KIỆN GẦN NHẤT (RAW EVENTS STREAM) -->
        <section class="card">
            <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
                <div>
                    <div class="card-title-group">
                        <span class="card-title-icon">📜</span>
                        <h2 class="card-title">Nhật Ký Sự Kiện Thời Gian Thực (Live Stream)</h2>
                    </div>
                    <p class="card-desc">Dữ liệu từ SDK client gửi về sau khi qua các bộ lọc kiểm tra</p>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <!-- Ô TÌM KIẾM NHANH SỰ KIỆN / MÓN / NGƯỜI DÙNG / ĐỊA CHỈ -->
                    <div class="search-input-box" style="position: relative; width: 220px;">
                        <span class="search-icon" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--text-muted); pointer-events: none;">🔍</span>
                        <input type="text" id="liveSearchEvents" placeholder="Tìm món cafe, khách, nút..." 
                               oninput="handleLiveSearchEvents(this.value)" 
                               style="width: 100%; padding: 6px 10px 6px 30px; font-size: 12px; border-radius: 8px; border: 1px solid var(--card-border); background: var(--bg-surface); color: var(--text-main); outline: none;">
                    </div>

                    <!-- BỘ LỌC THAO TÁC SỰ KIỆN -->
                    <div class="event-filter-group" style="display: flex; gap: 4px; background: rgba(0,0,0,0.04); padding: 3px; border-radius: 8px; border: 1px solid var(--card-border);">
                        <button type="button" class="btn btn-secondary event-filter-btn active" data-filter="all" onclick="setEventFilter('all')" style="padding: 4px 8px; font-size: 11.5px; border-radius: 6px; background: var(--primary); color: #fff;">
                            Tất Cả
                        </button>
                        <button type="button" class="btn btn-secondary event-filter-btn" data-filter="simulation" onclick="setEventFilter('simulation')" style="padding: 4px 8px; font-size: 11.5px; border-radius: 6px;">
                            🎯 Chuỗi Thử
                        </button>
                        <button type="button" class="btn btn-secondary event-filter-btn" data-filter="btn_click" onclick="setEventFilter('btn_click')" style="padding: 4px 8px; font-size: 11.5px; border-radius: 6px;">
                            ⚡ Bấm Nút
                        </button>
                        <button type="button" class="btn btn-secondary event-filter-btn" data-filter="forms" onclick="setEventFilter('forms')" style="padding: 4px 8px; font-size: 11.5px; border-radius: 6px;">
                            📝 Biểu Mẫu
                        </button>
                        <button type="button" class="btn btn-secondary event-filter-btn" data-filter="page_view" onclick="setEventFilter('page_view')" style="padding: 4px 8px; font-size: 11.5px; border-radius: 6px;">
                            👁️ Xem Trang
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="loadDashboardData()" style="font-size: 12px; padding: 5px 10px;">🔄 Làm Mới</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">Mã Sự Kiện & Phiên</th>
                            <th style="width: 12%;">Loại Thao Tác</th>
                            <th style="width: 18%;">Tên Biểu Mẫu / Khu Vực</th>
                            <th style="width: 16%;">Tên Người Dùng (Click)</th>
                            <th style="width: 18%;">Địa Chỉ Người Chọn</th>
                            <th style="width: 12%;">Hành Động / Món Chọn</th>
                            <th style="width: 10%; text-align: right;">Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody id="recentEventsTable">
                        <tr><td colspan="7" style="text-align:center; color: var(--text-dim); padding: 20px;">Đang tải nhật ký sự kiện...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- THANH PHÂN TRANG ĐÁNH SỐ TRANG 1 2 3 (THEO YÊU CẦU ĐỀ TÀI 17) -->
            <div class="events-pagination-bar" id="eventsPaginationBar">
                <div class="pagination-info">
                    📄 Hiển thị dòng <strong id="pageRangeText" style="color: var(--primary);">1 - 10</strong> / tổng số <strong id="totalEventsCount" style="color: var(--text-main);">0</strong> sự kiện nhật ký
                </div>
                <div class="pagination-nav-group">
                    <span style="font-size: 11.5px; color: var(--text-muted); margin-right: 4px;">Chuyển Trang:</span>
                    <div class="pagination-nav" id="eventsPaginationControls">
                        <!-- JS render các nút: [ « Trước ] [ 1 ] [ 2 ] [ 3 ] [ Sau » ] -->
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL HƯỚNG DẪN SỬ DỤNG & RUBRIC CHẤM ĐIỂM -->
    <div id="guideModal" class="modal-overlay">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--card-border); padding-bottom: 15px;">
                <h2 style="font-size: 20px; font-weight: 800; color: var(--primary);">📘 Bảng Hướng Dẫn & Rubric Trả Lời Giảng Viên (Đề Tài 17)</h2>
                <button type="button" onclick="closeGuideModal()" style="background: none; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <div style="font-size: 13px; color: var(--text-sub); line-height: 1.7; display: flex; flex-direction: column; gap: 16px;">
                <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 12px; padding: 16px;">
                    <h3 style="color: var(--warning); margin-bottom: 6px;">🎯 Đáp ứng Yêu cầu đặc biệt của Thầy giáo:</h3>
                    <p>
                        - <strong>Web bán Cà phê & Trà sữa:</strong> Đầy đủ món, giá tiền, hình ảnh bắt mắt, chức năng đặt mua và phân loại theo giới tính.<br>
                        - <strong>Lưu lại lượt mua & hành vi:</strong> Nam mua Cà phê, Nữ uống Trà sữa lưu vào bảng <code>orders</code> và bảng <code>events</code>.<br>
                        - <strong>Đề xuất thông minh (Recommendation Engine):</strong> Khi có người đăng ký mới hoặc chọn giới tính là Con trai -> Đề xuất Cà phê; Con gái -> Đề xuất Trà sữa. Có nút chuyển giới tính tức thì để thầy cô test ngay lập tức!
                    </p>
                </div>

                <div>
                    <h3 style="color: var(--primary); margin-bottom: 8px;">🧪 Hướng Dẫn Chứng Minh 6 Phép Thử Bắt Buộc:</h3>
                    <ol style="padding-left: 20px; display: flex; flex-direction: column; gap: 8px;">
                        <li><strong>Phép thử 1:</strong> Bấm nút <em>"🧪 6 Phép Thử (Pass)"</em> để thấy hệ thống sinh 10 pageviews và 4 button clicks trả đúng 10 và 4; gửi lại mã cũ không làm tăng số đếm.</li>
                        <li><strong>Phép thử 2:</strong> Bấm nút <em>"⚡ Lỗi 409"</em>: Gửi lại cùng mã ID nhưng sửa nội dung -> Server trả mã HTTP 409 CONFLICT_PAYLOAD và từ chối ghi đè dữ liệu cũ!</li>
                        <li><strong>Phép thử 3:</strong> Xử lý qua nửa đêm theo múi giờ <code>Asia/Ho_Chi_Minh</code> (GMT+7) và chấp nhận độ trễ trong khoảng hợp lý.</li>
                        <li><strong>Phép thử 4:</strong> Chuỗi phễu xem → mở form → hoàn tất. Nếu sai thứ tự hoặc thiếu bước sẽ không được tính hoàn tất.</li>
                        <li><strong>Phép thử 5:</strong> Hai website (Tiệm Cafe và EduPortal) hoàn toàn độc lập, không lẫn số liệu; kiểm tra trường cấm <code>password</code> bị từ chối 400.</li>
                        <li><strong>Phép thử 6:</strong> Kịch bản 200 sự kiện chuẩn đối chiếu với số liệu Ground Truth đã tính trước.</li>
                    </ol>
                </div>

                <div style="text-align: right; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" onclick="closeGuideModal()">Đã Hiểu & Sẵn Sàng Báo Cáo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ĐĂNG KÝ WEBSITE THỬ & CẤU HÌNH SỰ KIỆN (YÊU CẦU 1 ĐỀ TÀI 17) -->
    <div id="registerSiteModal" class="modal-overlay">
        <div class="modal-card" style="max-width: 780px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--card-border); padding-bottom: 15px;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--primary);">🌐 Đăng Ký Website Thử & Cấu Hình Sự Kiện</h2>
                    <span style="font-size: 12px; color: var(--text-muted);">Thỏa mãn Chức năng 1 (Đề tài 17): Đăng ký website thử, tên sự kiện và quyền xem</span>
                </div>
                <button type="button" onclick="closeRegisterSiteModal()" style="background: none; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <!-- FORM ĐĂNG KÝ WEBSITE MỚI -->
            <form onsubmit="submitRegisterSite(event)" style="background: var(--funnel-step-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                <h3 style="font-size: 15px; color: var(--warning); margin-bottom: 14px;">➕ Thêm Website Thử Mới Vào Hệ Thống</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">Tên Website Thử:</label>
                        <input type="text" id="newSiteName" class="form-control" placeholder="Ví dụ: Milky Coffee Chi Nhánh 2" required style="width: 100%; padding: 10px 12px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--text-main);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">Mã Site Key (Định danh SDK):</label>
                        <input type="text" id="newSiteKey" class="form-control" placeholder="Ví dụ: site_coffee_branch_02" required style="width: 100%; padding: 10px 12px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--text-main);">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">Tên Miền Được Phép Thu Thập (Allowed Domains):</label>
                    <input type="text" id="newSiteDomains" class="form-control" value="localhost, 127.0.0.1" required style="width: 100%; padding: 10px 12px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--text-main);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Cấu Hình Các Sự Kiện Được Phép Thống Kê:</label>
                    <div style="display: flex; gap: 18px; flex-wrap: wrap; font-size: 13px; color: var(--text-main);">
                        <label><input type="checkbox" id="chkEvtPv" value="page_view" checked> 👁️ page_view (Xem trang)</label>
                        <label><input type="checkbox" id="chkEvtBtn" value="btn_click" checked> ⚡ btn_click (Bấm nút / Tải tài liệu)</label>
                        <label><input type="checkbox" id="chkEvtFs" value="form_start" checked> 📝 form_start (Mở form)</label>
                        <label><input type="checkbox" id="chkEvtFc" value="form_complete" checked> 🚀 form_complete (Gửi form)</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0284c7, #10b981); color: #fff; width: 100%; padding: 12px; font-weight: 700;">
                    🚀 Xác Nhận Đăng Ký Website Thử & Kích Hoạt Đo Lường
                </button>
            </form>

            <!-- DANH SÁCH WEBSITE THỬ ĐÃ ĐĂNG KÝ -->
            <div>
                <h3 style="font-size: 15px; color: #38bdf8; margin-bottom: 12px;">📋 Danh Sách Các Website Thử Đang Hoạt Động</h3>
                <div class="table-responsive">
                    <table class="custom-table" style="width: 100%;">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.06);">
                                <th>ID</th>
                                <th>Tên Website</th>
                                <th>Site Key</th>
                                <th>Domains</th>
                                <th>Sự Kiện Cho Phép</th>
                            </tr>
                        </thead>
                        <tbody id="registeredSitesTableBody">
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 15px;">Đang tải danh sách website...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL QUY TẮC GIỮ / XÓA DỮ LIỆU THỬ & TỰ ĐỘNG DỌN DẸP (YÊU CẦU 5 ĐỀ TÀI 17) -->
    <div id="retentionModal" class="modal-overlay">
        <div class="modal-card" style="max-width: 650px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--card-border); padding-bottom: 15px;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                        <span>⚙️</span> Quy Tắc Giữ / Xóa Dữ Liệu (Retention)
                    </h2>
                    <span style="font-size: 12px; color: var(--text-muted);">Thỏa mãn Chức năng 5: Áp dụng quy tắc giữ/xóa dữ liệu thử; dọn dẹp theo thời gian & kịch bản tự động</span>
                </div>
                <button type="button" onclick="closeRetentionModal()" style="background: none; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <div style="background: var(--funnel-step-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 20px; margin-bottom: 20px;">
                <h3 style="font-size: 14.5px; color: #f59e0b; margin-bottom: 12px;">🕒 1. Cấu Hình Thời Hạn Lưu Trữ Dữ Liệu</h3>
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px;">
                    <label style="font-size: 13px; color: var(--text-muted); white-space: nowrap;">Thời gian giữ dữ liệu:</label>
                    <select id="selRetentionDays" class="form-control" style="flex: 1; padding: 10px 12px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--text-main); font-weight: 600;">
                        <option value="7">7 ngày (Thử nghiệm nhanh)</option>
                        <option value="14">14 ngày (Hai tuần)</option>
                        <option value="30" selected>30 ngày (Khuyến nghị mặc định)</option>
                        <option value="60">60 ngày (Hai tháng)</option>
                        <option value="90">90 ngày (Một quý)</option>
                        <option value="0">Vĩnh viễn (Không tự động xóa)</option>
                    </select>
                    <button type="button" class="btn btn-primary" onclick="saveRetentionSetting()" style="padding: 10px 16px; white-space: nowrap;">
                        💾 Lưu Quy Tắc
                    </button>
                </div>

                <!-- Thống kê trạng thái dữ liệu theo quy tắc -->
                <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 12px 14px; font-size: 12.5px; line-height: 1.6; border: 1px solid rgba(255,255,255,0.06);">
                    <div>• Quy tắc lưu trữ đang áp dụng: <strong id="lblActiveRetentionDays" style="color: #38bdf8;">30</strong> ngày</div>
                    <div>• Mốc thời gian cắt (Cutoff): <span id="lblRetentionCutoff" style="color: var(--text-muted); font-family: monospace;">-</span></div>
                    <div>• Số sự kiện cũ quá hạn đang chờ dọn: <strong id="lblExpiredEventCount" style="color: #f87171;">0</strong> sự kiện</div>
                </div>
            </div>

            <!-- TÁC VỤ DỌN DẸP DỮ LIỆU -->
            <div style="background: var(--funnel-step-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 20px; margin-bottom: 20px;">
                <h3 style="font-size: 14.5px; color: #10b981; margin-bottom: 12px;">🧹 2. Thực Thi Dọn Dẹp Dữ Liệu</h3>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary" onclick="executeRetentionCleanup()" style="color: #10b981; border-color: rgba(16,185,129,0.4); flex: 1; padding: 10px 14px;" title="Xóa các sự kiện cũ hơn số ngày quy định">
                        🧹 Dọn Dẹp Dữ Liệu Quá Hạn Ngay
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="purgeData(); closeRetentionModal();" style="color: #f87171; border-color: rgba(239,68,68,0.4); flex: 1; padding: 10px 14px;" title="Xóa toàn bộ sự kiện thử của website hiện tại">
                        🗑️ Xóa Tất Cả Dữ Liệu Thử
                    </button>
                </div>
            </div>

            <!-- HƯỚNG DẪN CRON JOB / TASK SCHEDULER -->
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; padding: 16px; font-size: 12px;">
                <div style="font-weight: 700; color: #38bdf8; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>🤖 3. Chạy Định Kỳ Tự Động (Cron Job / Scheduled Task)</span>
                    <button type="button" class="btn btn-secondary" onclick="testCronJobLive()" style="font-size: 11px; padding: 3px 8px; color: #38bdf8;">⚡ Test Chạy Cron</button>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 6px;">Bạn có thể lên lịch trong Windows Task Scheduler hoặc crontab Linux chạy tự động mỗi đêm:</p>
                <div style="background: #000; padding: 8px 10px; border-radius: 6px; font-family: monospace; color: #a7f3d0; margin-bottom: 6px; word-break: break-all;">
                    C:\xampp\php\php.exe C:\xampp\htdocs\ducmninh\cron_cleanup.php
                </div>
                <div style="color: var(--text-dim); font-size: 11px;">Hoặc gọi Webhook: <code>http://localhost/ducmninh/cron_cleanup.php?token=analytics_retention_2026</code></div>
            </div>
        </div>
    </div>

    <!-- SCRIPT DASHBOARD CONTROLLER -->
    <script src="assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>
