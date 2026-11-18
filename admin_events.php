<?php
// admin_events.php
// Đề tài 17: Cổng Quản Lý & Cấu Hình Sự Kiện Thu Thập, Phân Quyền Xem & Đăng Ký Website (Yêu Cầu 1)
session_start();
// Bắt buộc đăng nhập quản trị viên
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['admin_user'];
$currentRole = $_SESSION['admin_role'] ?? 'admin';

require_once __DIR__ . '/config/db.php';
$pdo = getDBConnection();

// Lấy danh sách website để render ban đầu
$websites = $pdo->query("SELECT * FROM websites ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý &amp; Cấu Hình Sự Kiện (Yêu Cầu 1) - Web Analytics Pro</title>
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --toggle-active: #10b981;
            --toggle-inactive: #94a3b8;
        }

        .events-page-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px 20px 80px;
        }

        /* Top Bar Enhancements */
        .page-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: rgba(14, 165, 233, 0.12);
            color: #0284c7;
            border: 1px solid rgba(14, 165, 233, 0.25);
        }

        /* Metric Bar */
        .metrics-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .metrics-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .metrics-grid-4 {
                grid-template-columns: 1fr;
            }
        }

        .metric-card-evt {
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .metric-card-evt:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .metric-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .metric-card-info h4 {
            margin: 0 0 4px;
            font-size: 13px;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-card-info .metric-num {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main, #0f172a);
            font-family: 'JetBrains Mono', monospace;
            line-height: 1;
        }

        /* Callout Box for Teacher Requirement Explanation */
        .requirement-callout {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.08), rgba(99, 102, 241, 0.08));
            border: 1px solid rgba(14, 165, 233, 0.3);
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 28px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .callout-icon {
            font-size: 32px;
            line-height: 1;
            flex-shrink: 0;
        }

        .callout-content h3 {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main, #0f172a);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .callout-content p {
            margin: 0 0 10px;
            font-size: 13.5px;
            color: var(--text-muted, #475569);
            line-height: 1.55;
        }

        .callout-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .callout-badge-item {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #cbd5e1);
            color: var(--text-main, #334155);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Action Toolbar */
        .event-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            padding: 16px 20px;
            border-radius: 14px;
        }

        .event-toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 9px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #cbd5e1);
            background: var(--card-bg, #ffffff);
            color: var(--text-main, #0f172a);
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }

        .filter-select:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .search-input-box {
            position: relative;
        }

        .search-input-box input {
            padding: 9px 14px 9px 36px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #cbd5e1);
            background: var(--card-bg, #ffffff);
            color: var(--text-main, #0f172a);
            font-size: 13.5px;
            width: 220px;
            outline: none;
            transition: width 0.2s;
        }

        .search-input-box input:focus {
            width: 280px;
            border-color: #0284c7;
        }

        .search-input-box .search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .event-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Buttons */
        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            font-weight: 600;
            font-size: 13.5px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
            transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
        }

        .btn-action-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            background: linear-gradient(135deg, #0369a1, #075985);
        }

        .btn-action-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 15px;
            background: transparent;
            color: var(--text-main, #0f172a);
            font-weight: 600;
            font-size: 13.5px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #cbd5e1);
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-action-outline:hover {
            background: rgba(0, 0, 0, 0.04);
            border-color: #94a3b8;
        }

        /* Events Table Card */
        .events-table-card {
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        .data-table th {
            background: var(--table-th-bg, #f8fafc);
            color: var(--text-muted, #475569);
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            text-transform: uppercase;
            font-size: 11.5px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color, #f1f5f9);
            color: var(--text-main, #1e293b);
            vertical-align: middle;
        }

        .data-table tr:hover td {
            background: rgba(2, 132, 199, 0.02);
        }

        .evt-code-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(2, 132, 199, 0.1);
            color: #0284c7;
            display: inline-block;
        }

        .badge-type {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-type-page { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
        .badge-type-button { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .badge-type-form { background: rgba(245, 158, 11, 0.12); color: #d97706; }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-pill-viewer { background: rgba(100, 116, 139, 0.12); color: #475569; }
        .role-pill-manager { background: rgba(14, 165, 233, 0.15); color: #0284c7; }
        .role-pill-admin { background: rgba(168, 85, 247, 0.15); color: #7e22ce; }

        /* Modern Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--toggle-inactive);
            transition: .3s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        input:checked + .slider {
            background-color: var(--toggle-active);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
        }

        .toggle-status-label {
            font-size: 12px;
            font-weight: 700;
            margin-left: 8px;
            vertical-align: middle;
        }

        .status-active { color: #10b981; }
        .status-inactive { color: #94a3b8; }

        /* Delete action button */
        .btn-delete-row {
            background: transparent;
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.15s;
        }

        .btn-delete-row:hover {
            background: #ef4444;
            color: #ffffff;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-box {
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #cbd5e1);
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main, #0f172a);
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #94a3b8;
            line-height: 1;
        }

        .modal-close-btn:hover {
            color: var(--text-main, #0f172a);
        }

        .modal-body {
            padding: 24px;
            max-height: 75vh;
            overflow-y: auto;
        }

        .form-group-evt {
            margin-bottom: 18px;
        }

        .form-group-evt label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main, #334155);
        }

        .form-group-evt label .req {
            color: #ef4444;
        }

        .form-group-evt .hint {
            font-size: 11.5px;
            color: var(--text-muted, #64748b);
            margin-top: 4px;
        }

        .form-control-evt {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #cbd5e1);
            background: var(--card-bg, #ffffff);
            color: var(--text-main, #0f172a);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
        }

        .form-control-evt:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color, #e2e8f0);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: var(--table-th-bg, #f8fafc);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-msg {
            padding: 12px 20px;
            border-radius: 10px;
            background: #1e293b;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 500;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInToast 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-left: 4px solid #0284c7;
        }

        .toast-success { border-left-color: #10b981; }
        .toast-error { border-left-color: #ef4444; }

        @keyframes slideInToast {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body>

    <!-- 1. TOPBAR QUẢN TRỊ VIÊN -->
    <header class="topbar">
        <div class="topbar-left">
            <a href="index.php" class="brand-badge">
                <div class="brand-icon">⚙️</div>
                <div class="brand-info">
                    <span class="brand-name">
                        Quản Lý &amp; Cấu Hình Sự Kiện
                        <span class="tag-dt17">Yêu Cầu 1</span>
                    </span>
                    <span class="brand-sub">Đăng Ký Website • Tên Sự Kiện • Cấu Hình Nút/Form • Quyền Xem</span>
                </div>
            </a>
        </div>

        <div class="topbar-right">
            <!-- Nút Theme -->
            <button type="button" id="btnThemeToggle" class="btn-theme-toggle" onclick="toggleTheme()" title="Chuyển đổi giao diện Sáng / Tối">
                <span id="themeToggleIcon">☀️</span> <span id="themeToggleText">Sáng</span>
            </button>

            <!-- Về Dashboard chính -->
            <a href="index.php" class="btn btn-action-primary" style="text-decoration: none;">
                📊 Về Dashboard Thống Kê
            </a>

            <!-- Mở Web Cafe Demo -->
            <a href="demo/cafe_shop/index.php" target="_blank" class="btn btn-shop-pill">
                ☕ Mở Tiệm Cafe
            </a>

            <!-- 6 Phép Thử -->
            <a href="test_runner.php" target="_blank" class="btn btn-secondary">
                🧪 6 Phép Thử
            </a>

            <!-- Profile Admin -->
            <div class="admin-topbar-profile">
                <span class="admin-topbar-avatar"><?= htmlspecialchars($currentUser['avatar'] ?? '👨‍💼') ?></span>
                <div class="admin-topbar-info">
                    <span class="admin-topbar-name"><?= htmlspecialchars($currentUser['fullname']) ?></span>
                    <span class="admin-topbar-role role-badge-<?= htmlspecialchars($currentRole) ?>">
                        <?= strtoupper(htmlspecialchars($currentRole)) ?>
                    </span>
                </div>
            </div>

            <!-- Đăng xuất -->
            <a href="logout.php" class="btn btn-logout-topbar" title="Đăng xuất khỏi phiên quản trị">
                🚪 Đăng Xuất
            </a>
        </div>
    </header>

    <!-- 2. NỘI DUNG CHÍNH -->
    <div class="events-page-container">

        <!-- GIẢI THÍCH CHUẨN YÊU CẦU 1 CỦA THẦY GIÁO -->
        <div class="requirement-callout">
            <div class="callout-icon">💡</div>
            <div class="callout-content">
                <h3>Yêu Cầu 1 Của Thầy Giáo Là Gì? Vì Sao Admin Phải Tạo Được Trang Sự Kiện?</h3>
                <p>
                    Trong hệ thống Phân Tích &amp; Theo Dõi Web chuyên nghiệp (như Google Tag Manager / PostHog), 
                    <strong>Admin không để website tự do gửi dữ liệu rác về server</strong>. Admin phải kiểm soát và cấu hình rõ:
                    <strong>Website nào</strong> được phép theo dõi, <strong>Tên &amp; Loại sự kiện</strong> là gì, 
                    <strong>Trang (URL), Nút bấm hoặc Biểu mẫu nào</strong> được phép thu thập số liệu, và 
                    <strong>Ai (Admin / Manager / Viewer)</strong> được quyền xem báo cáo.
                </p>
                <div class="callout-badges">
                    <span class="callout-badge-item">🔒 Bảo mật riêng tư (Không thu thập mật khẩu/thẻ tín dụng)</span>
                    <span class="callout-badge-item">🔘 Cấu hình nút &amp; form linh hoạt bằng CSS Selector</span>
                    <span class="callout-badge-item">⚡ Công tắc Bật/Tắt tức thì (Server chặn ngay khi Tắt)</span>
                    <span class="callout-badge-item">👥 Phân quyền xem báo cáo theo vai trò máy chủ (RBAC)</span>
                </div>
            </div>
        </div>

        <!-- 4 METRIC CARDS -->
        <div class="metrics-grid-4">
            <div class="metric-card-evt">
                <div class="metric-card-icon" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">🌐</div>
                <div class="metric-card-info">
                    <h4>Websites Đăng Ký</h4>
                    <div class="metric-num" id="statWebCount"><?= count($websites) ?></div>
                </div>
            </div>
            <div class="metric-card-evt">
                <div class="metric-card-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">🎯</div>
                <div class="metric-card-info">
                    <h4>Tổng Sự Kiện Cấu Hình</h4>
                    <div class="metric-num" id="statTotalEvts">0</div>
                </div>
            </div>
            <div class="metric-card-evt">
                <div class="metric-card-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">🟢</div>
                <div class="metric-card-info">
                    <h4>Đang Bật Thu Thập</h4>
                    <div class="metric-num" id="statActiveEvts">0</div>
                </div>
            </div>
            <div class="metric-card-evt">
                <div class="metric-card-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">🔴</div>
                <div class="metric-card-info">
                    <h4>Tạm Khóa / Chặn</h4>
                    <div class="metric-num" id="statInactiveEvts">0</div>
                </div>
            </div>
        </div>

        <!-- TOOLBAR ĐIỀU KHIỂN & BỘ LỌC -->
        <div class="event-toolbar">
            <div class="event-toolbar-left">
                <label for="filterSiteSelect" style="font-size: 13px; font-weight: 700; color: var(--text-muted);">🌐 Website:</label>
                <select id="filterSiteSelect" class="filter-select" onchange="loadEventsData()">
                    <option value="0">Tất cả website (Toàn bộ hệ thống)</option>
                    <?php foreach ($websites as $ws): ?>
                        <option value="<?= $ws['id'] ?>"><?= htmlspecialchars($ws['name']) ?> (<?= htmlspecialchars($ws['site_key']) ?>)</option>
                    <?php endforeach; ?>
                </select>

                <div class="search-input-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Tìm tên, mã, nút..." oninput="filterTableClientSide()">
                </div>
            </div>

            <div class="event-toolbar-right">
                <!-- Nút Đăng ký website mới -->
                <button type="button" class="btn-action-outline" onclick="openRegisterSiteModal()">
                    🌐 Đăng Ký Website Thử
                </button>

                <!-- Nút Tạo sự kiện mới -->
                <button type="button" class="btn-action-primary" onclick="openCreateEventModal()">
                    ➕ Tạo &amp; Cấu Hình Sự Kiện Mới
                </button>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH CẤU HÌNH SỰ KIỆN -->
        <div class="events-table-card">
            <div class="table-responsive">
                <table class="data-table" id="eventsTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Website Áp Dụng</th>
                            <th>Tên Sự Kiện &amp; Mã</th>
                            <th>Loại Phần Tử</th>
                            <th>Trang Áp Dụng (URL)</th>
                            <th>Nút / Biểu Mẫu Theo Dõi</th>
                            <th>Quyền Xem</th>
                            <th style="text-align: center;">Đã Thu Thập</th>
                            <th style="text-align: center; width: 140px;">Bật/Tắt Thu Thập</th>
                            <th style="text-align: center; width: 80px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                ⏳ Đang tải danh sách cấu hình sự kiện...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- THANH PHÂN TRANG 1 2 3 CHO CẤU HÌNH SỰ KIỆN -->
            <div class="events-pagination-bar" id="cfgPaginationBar" style="border-radius: 0 0 14px 14px; background: var(--table-th-bg, #f8fafc); border-top: 1px solid var(--border-color, #e2e8f0); padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div class="pagination-info" style="font-size: 12.5px; color: var(--text-muted);">
                    ⚙️ Hiển thị dòng <strong id="cfgPageRangeText" style="color: #0284c7;">1 - 5</strong> / tổng số <strong id="cfgTotalCount" style="color: var(--text-main);">0</strong> sự kiện cấu hình
                </div>
                <div class="pagination-nav-group" style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 11.5px; color: var(--text-muted); margin-right: 4px;">Chuyển Trang:</span>
                    <div class="pagination-nav" id="cfgPaginationControls" style="display: flex; gap: 5px;"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL 1: TẠO & CẤU HÌNH SỰ KIỆN MỚI -->
    <div class="modal-overlay" id="createEventModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>➕ Cấu Hình Sự Kiện Theo Dõi Mới</h3>
                <button type="button" class="modal-close-btn" onclick="closeCreateEventModal()">&times;</button>
            </div>
            <form id="createEventForm" onsubmit="handleCreateEvent(event)">
                <div class="modal-body">
                    <!-- Website -->
                    <div class="form-group-evt">
                        <label for="evtSiteId">Website Áp Dụng <span class="req">*</span></label>
                        <select id="evtSiteId" class="form-control-evt" required>
                            <?php foreach ($websites as $ws): ?>
                                <option value="<?= $ws['id'] ?>"><?= htmlspecialchars($ws['name']) ?> (<?= htmlspecialchars($ws['site_key']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="hint">Chỉ áp dụng thu thập cho website được chọn.</div>
                    </div>

                    <!-- Tên sự kiện -->
                    <div class="form-group-evt">
                        <label for="evtName">Tên Mô Tả Sự Kiện <span class="req">*</span></label>
                        <input type="text" id="evtName" class="form-control-evt" placeholder="Ví dụ: Bấm nút Xem Giỏ Hàng, Lịch Đặt Chỗ, Tải Menu..." required>
                        <div class="hint">Tên thân thiện hiển thị trên Bảng điều khiển &amp; Báo cáo.</div>
                    </div>

                    <!-- Mã sự kiện -->
                    <div class="form-group-evt">
                        <label for="evtCode">Mã Định Danh Sự Kiện (Event Code) <span class="req">*</span></label>
                        <input type="text" id="evtCode" class="form-control-evt" placeholder="btn_click, page_view, form_start, form_complete, v.v." required>
                        <div class="hint">Chuẩn mã sự kiện gửi từ SDK Javascript (chữ thường, gạch dưới).</div>
                    </div>

                    <!-- Loại phần tử -->
                    <div class="form-group-evt">
                        <label for="evtType">Loại Phần Tử Được Phép Thống Kê <span class="req">*</span></label>
                        <select id="evtType" class="form-control-evt" onchange="updateSelectorPlaceholder()">
                            <option value="button">🔘 Nút bấm (Button Click / Thao tác nhấn)</option>
                            <option value="page">📄 Trang web (Page View / Lượt xem)</option>
                            <option value="form">📝 Biểu mẫu (Form Input / Submit / Đặt chỗ)</option>
                        </select>
                    </div>

                    <!-- Trang được phép thu thập -->
                    <div class="form-group-evt">
                        <label for="evtTargetUrl">Trang Được Phép Thống Kê (URL Pattern)</label>
                        <input type="text" id="evtTargetUrl" class="form-control-evt" value="*" placeholder="* hoặc /demo/cafe_shop/index.php">
                        <div class="hint">Dùng <code>*</code> để cho phép trên toàn bộ các trang của website.</div>
                    </div>

                    <!-- Nút / Biểu mẫu được phép thống kê -->
                    <div class="form-group-evt">
                        <label for="evtSelector">Nút Bấm hoặc Biểu Mẫu Cho Phép (CSS Selector / Text)</label>
                        <input type="text" id="evtSelector" class="form-control-evt" value="*" placeholder=".btn-order-add, #reserveTableForm, .cart-trigger">
                        <div class="hint">CSS Selector hoặc Class/ID của phần tử. Dùng <code>*</code> nếu cho phép toàn bộ.</div>
                    </div>

                    <!-- Quyền xem tối thiểu -->
                    <div class="form-group-evt">
                        <label for="evtMinRole">Quyền Tối Thiểu Được Xem Báo Cáo <span class="req">*</span></label>
                        <select id="evtMinRole" class="form-control-evt">
                            <option value="viewer">👁️ Viewer (Tất cả nhân viên / Khách)</option>
                            <option value="manager" selected>👔 Manager (Quản lý cửa hàng)</option>
                            <option value="admin">👑 Admin (Chỉ Quản trị viên cấp cao)</option>
                        </select>
                        <div class="hint">Máy chủ sẽ kiểm tra quyền người dùng trước khi trả về dữ liệu sự kiện này.</div>
                    </div>

                    <!-- Trạng thái thu thập ban đầu -->
                    <div class="form-group-evt">
                        <label>
                            <input type="checkbox" id="evtAllowed" checked style="transform: scale(1.2); margin-right: 8px;">
                            <strong>Kích hoạt thu thập ngay (Allowed = 1)</strong>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action-outline" onclick="closeCreateEventModal()">Hủy Bỏ</button>
                    <button type="submit" class="btn-action-primary" id="btnSubmitCreateEvt">💾 Lưu Cấu Hình</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: ĐĂNG KÝ WEBSITE THỬ MỚI -->
    <div class="modal-overlay" id="registerSiteModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>🌐 Đăng Ký Website Thử Nghiệm Mới</h3>
                <button type="button" class="modal-close-btn" onclick="closeRegisterSiteModal()">&times;</button>
            </div>
            <form id="registerSiteForm" onsubmit="handleRegisterSite(event)">
                <div class="modal-body">
                    <div class="form-group-evt">
                        <label for="siteName">Tên Website Thử Nghiệm <span class="req">*</span></label>
                        <input type="text" id="siteName" class="form-control-evt" placeholder="Ví dụ: Noir Café Chi Nhánh 2, Shop Thời Trang..." required>
                    </div>
                    <div class="form-group-evt">
                        <label for="siteKey">Mã Định Danh Website (Site Key) <span class="req">*</span></label>
                        <input type="text" id="siteKey" class="form-control-evt" placeholder="site_cafe_branch2, site_shop_03..." required>
                        <div class="hint">Khóa định danh dùng trong mã nhúng SDK thu thập.</div>
                    </div>
                    <div class="form-group-evt">
                        <label for="siteDomains">Tên Miền Được Phép Thu Thập (Allowed Domains)</label>
                        <input type="text" id="siteDomains" class="form-control-evt" value="localhost, 127.0.0.1" required>
                        <div class="hint">Phân tách bằng dấu phẩy (Chỉ chấp nhận sự kiện gửi từ domain này).</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action-outline" onclick="closeRegisterSiteModal()">Đóng</button>
                    <button type="submit" class="btn-action-primary" id="btnSubmitRegSite">🚀 Đăng Ký Ngay</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TOAST CONTAINER -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // State
        let allEvents = [];
        let allWebsites = [];
        let cfgCurrentPage = 1;
        const cfgEventsPerPage = 5; // Hiển thị 5 cấu hình / trang để tạo phân trang rõ ràng 1 2 3
        let currentFilteredEvents = [];

        // Khởi động trang
        document.addEventListener('DOMContentLoaded', () => {
            loadEventsData();
        });

        // 1. Tải danh sách sự kiện từ API
        function loadEventsData() {
            const siteId = document.getElementById('filterSiteSelect').value;
            const tbody = document.getElementById('eventsTableBody');
            tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">⏳ Đang tải danh sách cấu hình sự kiện...</td></tr>`;

            fetch(`api/event_manager.php?action=list&site_id=${siteId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'SUCCESS') {
                        allEvents = res.events || [];
                        allWebsites = res.websites || [];
                        renderEventsTable(allEvents);
                        updateMetrics(allEvents);
                    } else {
                        tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; color: #ef4444; padding: 20px;">Lỗi: ${res.error || 'Không thể tải dữ liệu'}</td></tr>`;
                    }
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; color: #ef4444; padding: 20px;">Lỗi kết nối máy chủ: ${err.message}</td></tr>`;
                });
        }

        // 2. Render bảng danh sách sự kiện (Có hỗ trợ phân trang 1 2 3)
        function renderEventsTable(events) {
            currentFilteredEvents = events || [];
            const tbody = document.getElementById('eventsTableBody');
            const totalCount = currentFilteredEvents.length;
            const totalPages = Math.max(1, Math.ceil(totalCount / cfgEventsPerPage));

            if (cfgCurrentPage > totalPages) cfgCurrentPage = totalPages;
            if (cfgCurrentPage < 1) cfgCurrentPage = 1;

            // Cập nhật thông tin phân trang
            const elRange = document.getElementById('cfgPageRangeText');
            const elTotal = document.getElementById('cfgTotalCount');
            if (elTotal) elTotal.innerText = totalCount;

            if (!events || events.length === 0) {
                if (elRange) elRange.innerText = '0 - 0';
                tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">Không tìm thấy sự kiện nào phù hợp.</td></tr>`;
                renderCfgPaginationControls(1);
                return;
            }

            const startIdx = (cfgCurrentPage - 1) * cfgEventsPerPage;
            const endIdx = Math.min(startIdx + cfgEventsPerPage, totalCount);
            if (elRange) elRange.innerText = `${startIdx + 1} - ${endIdx}`;

            const pageItems = currentFilteredEvents.slice(startIdx, endIdx);

            tbody.innerHTML = pageItems.map(evt => {
                const isChecked = evt.allowed == 1 ? 'checked' : '';
                const statusClass = evt.allowed == 1 ? 'status-active' : 'status-inactive';
                const statusText = evt.allowed == 1 ? 'ĐANG BẬT' : 'ĐÃ TẮT';
                
                let typeBadge = '';
                if (evt.event_type === 'page') typeBadge = '<span class="badge-type badge-type-page">📄 Trang (URL)</span>';
                else if (evt.event_type === 'form') typeBadge = '<span class="badge-type badge-type-form">📝 Biểu Mẫu</span>';
                else typeBadge = '<span class="badge-type badge-type-button">🔘 Nút Bấm</span>';

                let roleBadge = '';
                if (evt.min_role === 'admin') roleBadge = '<span class="role-pill role-pill-admin">👑 Admin</span>';
                else if (evt.min_role === 'manager') roleBadge = '<span class="role-pill role-pill-manager">👔 Manager</span>';
                else roleBadge = '<span class="role-pill role-pill-viewer">👁️ Viewer</span>';

                return `
                    <tr id="row-evt-${evt.id}">
                        <td style="font-weight: 700; color: var(--text-muted);">#${evt.id}</td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 13.5px;">${escapeHtml(evt.site_name)}</div>
                            <div style="font-size: 11.5px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted);">${escapeHtml(evt.site_key)}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); margin-bottom: 4px;">${escapeHtml(evt.name)}</div>
                            <span class="evt-code-tag">${escapeHtml(evt.event_code)}</span>
                        </td>
                        <td>${typeBadge}</td>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #475569;" title="${escapeHtml(evt.target_url)}">
                            ${escapeHtml(evt.target_url)}
                        </td>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #0284c7;" title="${escapeHtml(evt.element_selector)}">
                            <code>${escapeHtml(evt.element_selector)}</code>
                        </td>
                        <td>${roleBadge}</td>
                        <td style="text-align: center; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: var(--text-main);">
                            ${evt.tracked_count || 0}
                        </td>
                        <td style="text-align: center;">
                            <label class="switch">
                                <input type="checkbox" ${isChecked} onchange="toggleEventAllowed(${evt.id}, this.checked)">
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-status-label ${statusClass}" id="label-status-${evt.id}">${statusText}</span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-delete-row" onclick="deleteEvent(${evt.id}, '${escapeHtml(evt.name)}')">
                                🗑️ Xóa
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            renderCfgPaginationControls(totalPages);
        }

        // Render các nút bấm chuyển trang (1 2 3...)
        function renderCfgPaginationControls(totalPages) {
            const ctrl = document.getElementById('cfgPaginationControls');
            if (!ctrl) return;
            ctrl.innerHTML = '';

            if (totalPages <= 1) {
                ctrl.innerHTML = `<button type="button" class="page-btn active" style="min-width:32px; height:32px; font-size:12px; border-radius:6px; background:#0284c7; color:#fff; border:none;" disabled>1</button>`;
                return;
            }

            // Nút Trước
            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'page-btn';
            prevBtn.style.cssText = 'min-width:32px; height:32px; font-size:12px; border-radius:6px; cursor:pointer; background:var(--card-bg, #fff); border:1px solid var(--border-color, #cbd5e1); color:var(--text-main);';
            prevBtn.innerHTML = '&laquo;';
            prevBtn.disabled = (cfgCurrentPage === 1);
            if (cfgCurrentPage === 1) prevBtn.style.opacity = '0.5';
            prevBtn.onclick = () => goToCfgPage(cfgCurrentPage - 1);
            ctrl.appendChild(prevBtn);

            for (let p = 1; p <= totalPages; p++) {
                const pageBtn = document.createElement('button');
                pageBtn.type = 'button';
                pageBtn.className = 'page-btn' + (p === cfgCurrentPage ? ' active' : '');
                pageBtn.innerText = p;
                pageBtn.style.cssText = 'min-width:32px; height:32px; font-size:12px; font-weight:700; border-radius:6px; cursor:pointer; ' + 
                    (p === cfgCurrentPage 
                        ? 'background:#0284c7; color:#ffffff; border:1px solid #0284c7;' 
                        : 'background:var(--card-bg, #fff); border:1px solid var(--border-color, #cbd5e1); color:var(--text-main);');
                pageBtn.onclick = () => goToCfgPage(p);
                ctrl.appendChild(pageBtn);
            }

            // Nút Sau
            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'page-btn';
            nextBtn.style.cssText = 'min-width:32px; height:32px; font-size:12px; border-radius:6px; cursor:pointer; background:var(--card-bg, #fff); border:1px solid var(--border-color, #cbd5e1); color:var(--text-main);';
            nextBtn.innerHTML = '&raquo;';
            nextBtn.disabled = (cfgCurrentPage === totalPages);
            if (cfgCurrentPage === totalPages) nextBtn.style.opacity = '0.5';
            nextBtn.onclick = () => goToCfgPage(cfgCurrentPage + 1);
            ctrl.appendChild(nextBtn);
        }

        function goToCfgPage(p) {
            cfgCurrentPage = p;
            renderEventsTable(currentFilteredEvents);
        }

        // 3. Cập nhật các thẻ số liệu trên đầu
        function updateMetrics(events) {
            document.getElementById('statTotalEvts').textContent = events.length;
            const activeCount = events.filter(e => e.allowed == 1).length;
            const inactiveCount = events.length - activeCount;
            document.getElementById('statActiveEvts').textContent = activeCount;
            document.getElementById('statInactiveEvts').textContent = inactiveCount;
        }

        // 4. Tìm kiếm lọc nhanh ở bảng
        function filterTableClientSide() {
            cfgCurrentPage = 1;
            const q = document.getElementById('searchInput').value.trim().toLowerCase();
            if (!q) {
                renderEventsTable(allEvents);
                return;
            }
            const filtered = allEvents.filter(e => {
                return (e.name && e.name.toLowerCase().includes(q)) ||
                       (e.event_code && e.event_code.toLowerCase().includes(q)) ||
                       (e.site_name && e.site_name.toLowerCase().includes(q)) ||
                       (e.element_selector && e.element_selector.toLowerCase().includes(q)) ||
                       (e.target_url && e.target_url.toLowerCase().includes(q));
            });
            renderEventsTable(filtered);
        }

        // 5. Toggle switch Bật / Tắt sự kiện (Allowed Switch)
        function toggleEventAllowed(id, isAllowed) {
            const label = document.getElementById(`label-status-${id}`);
            if (label) {
                label.textContent = isAllowed ? 'ĐANG BẬT' : 'ĐÃ TẮT';
                label.className = `toggle-status-label ${isAllowed ? 'status-active' : 'status-inactive'}`;
            }

            fetch('api/event_manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'toggle_allowed',
                    id: id,
                    allowed: isAllowed ? 1 : 0
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'SUCCESS') {
                    showToast(res.message, isAllowed ? 'success' : 'info');
                    // Cập nhật lại state trong allEvents
                    const found = allEvents.find(e => e.id == id);
                    if (found) found.allowed = isAllowed ? 1 : 0;
                    updateMetrics(allEvents);
                } else {
                    showToast('Lỗi: ' + (res.error || 'Không thể đổi trạng thái'), 'error');
                    loadEventsData(); // rollback
                }
            })
            .catch(err => {
                showToast('Lỗi kết nối: ' + err.message, 'error');
                loadEventsData(); // rollback
            });
        }

        // 6. Xóa sự kiện
        function deleteEvent(id, name) {
            if (!confirm(`Bạn có chắc chắn muốn xóa cấu hình sự kiện [${name}] này không? Thao tác không thể hoàn tác.`)) {
                return;
            }

            fetch('api/event_manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_event',
                    id: id
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'SUCCESS') {
                    showToast(res.message, 'success');
                    allEvents = allEvents.filter(e => e.id != id);
                    renderEventsTable(allEvents);
                    updateMetrics(allEvents);
                } else {
                    showToast('Lỗi: ' + (res.error || 'Không thể xóa'), 'error');
                }
            })
            .catch(err => {
                showToast('Lỗi kết nối: ' + err.message, 'error');
            });
        }

        // 7. Tạo sự kiện mới
        function handleCreateEvent(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitCreateEvt');
            btn.disabled = true;
            btn.textContent = '⏳ Đang lưu...';

            const payload = {
                action: 'create_event',
                site_id: document.getElementById('evtSiteId').value,
                name: document.getElementById('evtName').value.trim(),
                event_code: document.getElementById('evtCode').value.trim(),
                event_type: document.getElementById('evtType').value,
                target_url: document.getElementById('evtTargetUrl').value.trim() || '*',
                element_selector: document.getElementById('evtSelector').value.trim() || '*',
                min_role: document.getElementById('evtMinRole').value,
                allowed: document.getElementById('evtAllowed').checked ? 1 : 0
            };

            fetch('api/event_manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.textContent = '💾 Lưu Cấu Hình';
                if (res.status === 'SUCCESS') {
                    showToast(res.message, 'success');
                    closeCreateEventModal();
                    loadEventsData();
                } else {
                    showToast('Lỗi: ' + (res.error || 'Không thể tạo sự kiện'), 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = '💾 Lưu Cấu Hình';
                showToast('Lỗi kết nối: ' + err.message, 'error');
            });
        }

        // 8. Đăng ký website mới
        function handleRegisterSite(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitRegSite');
            btn.disabled = true;
            btn.textContent = '⏳ Đang đăng ký...';

            const payload = {
                action: 'register_site',
                name: document.getElementById('siteName').value.trim(),
                site_key: document.getElementById('siteKey').value.trim(),
                allowed_domains: document.getElementById('siteDomains').value.trim()
            };

            fetch('api/event_manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.textContent = '🚀 Đăng Ký Ngay';
                if (res.status === 'SUCCESS') {
                    showToast(res.message, 'success');
                    closeRegisterSiteModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Lỗi: ' + (res.error || 'Không thể đăng ký website'), 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = '🚀 Đăng Ký Ngay';
                showToast('Lỗi kết nối: ' + err.message, 'error');
            });
        }

        // Modal Helpers
        function openCreateEventModal() {
            document.getElementById('createEventForm').reset();
            document.getElementById('createEventModal').style.display = 'flex';
        }

        function closeCreateEventModal() {
            document.getElementById('createEventModal').style.display = 'none';
        }

        function openRegisterSiteModal() {
            document.getElementById('registerSiteForm').reset();
            document.getElementById('registerSiteModal').style.display = 'flex';
        }

        function closeRegisterSiteModal() {
            document.getElementById('registerSiteModal').style.display = 'none';
        }

        function updateSelectorPlaceholder() {
            const type = document.getElementById('evtType').value;
            const selInput = document.getElementById('evtSelector');
            if (type === 'page') {
                selInput.value = 'Window / Navigation';
            } else if (type === 'form') {
                selInput.value = '#reserveTableForm, #checkoutForm';
            } else {
                selInput.value = '.btn-order-add, .cart-trigger, #btnReserveTable';
            }
        }

        // Toast Helper
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-msg toast-${type}`;
            toast.innerHTML = `<span>${type === 'success' ? '✅' : (type === 'error' ? '❌' : 'ℹ️')}</span> <span>${escapeHtml(message)}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                toast.style.transition = 'all 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('analytics_theme', newTheme);
            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (icon && text) {
                icon.textContent = newTheme === 'dark' ? '🌙' : '☀️';
                text.textContent = newTheme === 'dark' ? 'Tối' : 'Sáng';
            }
        }
        (function() {
            const savedTheme = localStorage.getItem('analytics_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</body>
</html>
