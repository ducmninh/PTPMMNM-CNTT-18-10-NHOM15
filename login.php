<?php
// login.php - Cổng Đăng Nhập Quản Trị Hệ Thống (Admin Portal)
// Đề tài 17: Hệ thống Thu Thập & Thống Kê Truy Cập & Phân Tích Hành Vi
session_start();

// Nếu đã đăng nhập rồi thì vào thẳng Dashboard
if (!empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = getDBConnection();

$errorMessage = '';
$successMessage = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $successMessage = 'Bạn đã đăng xuất khỏi phiên làm việc an toàn.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $errorMessage = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } else {
        // Chỉ chấp nhận tài khoản Admin của Hoàng Minh Đức
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :u OR email = :u) AND role = 'admin' LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        $loginOk = false;

        if ($user) {
            // Kiểm tra mật khẩu (hỗ trợ hash và mật khẩu mặc định)
            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                $loginOk = true;
            } elseif ($user['password'] === $password) {
                $loginOk = true;
            }
        }

        // Dự phòng tài khoản duy nhất: admin / admin123
        if (!$loginOk && $username === 'admin' && $password === 'admin123') {
            $loginOk = true;
            $user = [
                'id' => 1,
                'fullname' => 'Hoàng Minh Đức (Admin)',
                'username' => 'admin',
                'email' => 'admin@dnu.edu.vn',
                'role' => 'admin',
                'avatar' => '👨‍💼'
            ];
        }

        if ($loginOk && $user) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user;
            $_SESSION['admin_role'] = 'admin';
            header('Location: index.php');
            exit;
        } else {
            $errorMessage = 'Tài khoản hoặc mật khẩu không chính xác! Hệ thống chỉ cấp quyền cho Quản trị viên (Admin).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Đăng Nhập Quản Trị Hệ Thống - Web Analytics Pro (Đề Tài 17)</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #07090e;
            --bg-card: rgba(15, 23, 42, 0.72);
            --border-card: rgba(255, 255, 255, 0.1);
            --border-focus: #38bdf8;
            --primary: #38bdf8;
            --primary-glow: rgba(56, 189, 248, 0.35);
            --amber: #f59e0b;
            --amber-glow: rgba(245, 158, 11, 0.3);
            --emerald: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --font-main: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-deep);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 30px 20px;
        }

        /* 1. HIỆU ỨNG VỆT SÁNG NỀN AURORA ĐỘNG (BACKGROUND GLOW) */
        .aurora-circle {
            position: fixed;
            border-radius: 50%;
            filter: blur(140px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.55;
            transition: all 1s ease;
        }

        .aurora-1 {
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.45), transparent 70%);
            top: -100px;
            left: -100px;
            animation: floatGlow 12s infinite alternate ease-in-out;
        }

        .aurora-2 {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.35), transparent 70%);
            bottom: -120px;
            right: -120px;
            animation: floatGlow 15s infinite alternate-reverse ease-in-out;
        }

        .aurora-3 {
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.3), transparent 70%);
            top: 45%;
            right: 25%;
            animation: floatGlow 10s infinite alternate ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, 30px) scale(1.1); }
            100% { transform: translate(-30px, -20px) scale(0.95); }
        }

        /* 2. KHỐI CONTAINER TRUNG TÂM */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 490px;
        }

        /* THẺ ĐĂNG NHẬP CHÍNH (GLASSMORHPISM CARD) */
        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 42px 38px;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.65), 0 0 30px rgba(56, 189, 248, 0.08);
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s;
        }

        .login-card:hover {
            border-color: rgba(255, 255, 255, 0.16);
        }

        /* VẠCH VIỀN PHÁT SÁNG TRÊN ĐẦU CARD */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #38bdf8, #818cf8, #f59e0b, #10b981);
        }

        /* LOGO & TIÊU ĐỀ */
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.28);
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .brand-icon {
            font-size: 16px;
        }

        .brand-title {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--primary);
            font-family: var(--font-mono);
        }

        .tag-pill {
            background: #f59e0b;
            color: #000;
            font-size: 10px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 4px;
        }

        .login-title {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .login-sub {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* THÔNG BÁO LỖI / THÀNH CÔNG */
        .alert-box {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.14);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.14);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* FORM INPUTS */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            font-size: 16px;
            color: var(--text-dim);
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 13px 44px 13px 42px;
            background: rgba(2, 6, 23, 0.65);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            font-family: var(--font-main);
            transition: all 0.25s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.18);
        }

        .form-input:focus + .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--primary);
        }

        .btn-toggle-eye {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color 0.2s;
        }

        .btn-toggle-eye:hover {
            color: #ffffff;
        }

        /* NÚT ĐĂNG NHẬP CHÍNH */
        .btn-submit-login {
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            background: linear-gradient(135deg, #0284c7, #0284c7 40%, #059669);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 700;
            font-family: var(--font-main);
            letter-spacing: 0.02em;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(2, 132, 199, 0.35);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit-login:hover {
            background: linear-gradient(135deg, #0369a1, #0284c7 50%, #10b981);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(2, 132, 199, 0.45);
        }

        .btn-submit-login:active {
            transform: translateY(0);
        }

        /* KHỐI TÀI KHOẢN MẪU (1-CLICK TEST LOGIN) */
        .demo-accounts-box {
            margin-top: 26px;
            padding-top: 22px;
            border-top: 1px dashed rgba(255, 255, 255, 0.12);
        }

        .demo-title {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            color: var(--amber);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .btn-demo-chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-main);
        }

        .btn-demo-chip:hover {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.35);
            transform: translateY(-2px);
        }

        .btn-demo-chip.active-chip {
            background: rgba(56, 189, 248, 0.18);
            border-color: var(--primary);
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.25);
        }

        .chip-icon {
            font-size: 18px;
            display: block;
            margin-bottom: 3px;
        }

        .chip-role {
            font-size: 11px;
            font-weight: 700;
            display: block;
            color: #ffffff;
        }

        .chip-account {
            font-size: 9.5px;
            color: var(--text-dim);
            font-family: var(--font-mono);
            display: block;
            margin-top: 2px;
        }

        /* LIÊN KẾT NHANH SANG CÁC TRANG DEMO */
        .quick-links {
            margin-top: 24px;
            display: flex;
            justify-content: center;
            gap: 16px;
            font-size: 12.5px;
        }

        .quick-link-item {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .quick-link-item:hover {
            color: var(--primary);
        }

        /* FOOTER NHỎ */
        .login-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 11.5px;
            color: var(--text-dim);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 22px;
            }
            .demo-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- 3 VỆT SÁNG NỀN AURORA -->
    <div class="aurora-circle aurora-1"></div>
    <div class="aurora-circle aurora-2"></div>
    <div class="aurora-circle aurora-3"></div>

    <div class="login-wrapper">
        <main class="login-card">
            
            <!-- HEADER THƯƠNG HIỆU -->
            <div class="brand-header">
                <div class="brand-badge">
                    <span class="brand-icon">📊</span>
                    <span class="brand-title">Web Analytics Pro</span>
                    <span class="tag-pill">Đề Tài 17</span>
                </div>
                <h1 class="login-title">Cổng Quản Trị Hệ Thống</h1>
                <p class="login-sub">
                    Hệ thống Thống Kê &amp; Phân Tích Thao Tác Thời Gian Thực
                </p>
            </div>

            <!-- THÔNG BÁO LỖI HOẶC ĐĂNG XUẤT -->
            <?php if (!empty($errorMessage)): ?>
                <div class="alert-box alert-error">
                    <span style="font-size: 16px;">⚠️</span>
                    <span><?= htmlspecialchars($errorMessage) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success">
                    <span style="font-size: 16px;">✓</span>
                    <span><?= htmlspecialchars($successMessage) ?></span>
                </div>
            <?php endif; ?>

            <!-- FORM ĐĂNG NHẬP -->
            <form method="POST" action="login.php" id="loginForm">
                
                <div class="form-group">
                    <label class="form-label" for="txtUsername">Tên Đăng Nhập hoặc Email:</label>
                    <div class="input-wrap">
                        <span class="input-icon">👤</span>
                        <input type="text" id="txtUsername" name="username" class="form-input" 
                               placeholder="Nhập admin hoặc email..." 
                               value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>" 
                               required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="txtPassword">Mật Khẩu Quản Trị:</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="txtPassword" name="password" class="form-input" 
                               placeholder="Nhập mật khẩu..." 
                               value="<?= htmlspecialchars($_POST['password'] ?? 'admin123') ?>" 
                               required autocomplete="current-password">
                        <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility()" title="Ẩn/Hiện mật khẩu">
                            <span id="eyeIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit-login" id="btnSubmit">
                    <span>🚀</span> Đăng Nhập Quản Trị Hệ Thống
                </button>
            </form>

            <!-- THÔNG TIN ĐĂNG NHẬP ADMIN DUY NHẤT -->
            <div class="demo-accounts-box" style="margin-top: 20px;">
                <div class="demo-title" style="display: flex; align-items: center; justify-content: space-between;">
                    <span>👑 Tài Khoản Quản Trị Viên (Duy Nhất)</span>
                    <span style="font-size: 10px; color: #38bdf8; font-weight: 700;">Admin Quyền Cao Nhất</span>
                </div>
                
                <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 26px;">👨‍💼</span>
                        <div>
                            <div style="color: #fff; font-weight: 700; font-size: 13.5px;">Hoàng Minh Đức</div>
                            <div style="font-size: 11.5px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace;">admin / admin123</div>
                        </div>
                    </div>
                    <button type="button" class="btn-demo-chip active-chip" style="width: auto; padding: 6px 14px; font-size: 12px;" onclick="fillAccount('admin', 'admin123')">
                        Điền Nhanh ⚡
                    </button>
                </div>
            </div>

            <!-- LIÊN KẾT NHANH SANG 2 TRANG WEB DEMO -->
            <div class="quick-links">
                <a href="demo/cafe_shop/index.php" target="_blank" class="quick-link-item" title="Mở trang chủ tiệm cà phê NOIR CAFÉ">
                    ☕ Xem Tiệm Cafe
                </a>
                <span style="color: var(--text-dim);">•</span>
                <a href="demo/site2/index.php" target="_blank" class="quick-link-item" title="Mở cổng học liệu EduPortal">
                    🎓 Cổng EduPortal
                </a>
            </div>

            <div class="login-footer">
                Phát Triển Phần Mềm Mã Nguồn Mở • FIT DNU • 2026
            </div>
        </main>
    </div>

    <script>
        // Hàm chuyển đổi ẩn/hiện mật khẩu
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('txtPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.innerText = '🙈';
            } else {
                pwdInput.type = 'password';
                eyeIcon.innerText = '👁️';
            }
        }

        // Hàm tự điền tài khoản Admin
        function fillAccount(username, password) {
            document.getElementById('txtUsername').value = username;
            document.getElementById('txtPassword').value = password;
            
            // Hiệu ứng focus nhẹ vào nút đăng nhập
            const btn = document.getElementById('btnSubmit');
            btn.style.transform = 'scale(1.02)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);
        }

        // Hiệu ứng khi bấm Đăng Nhập
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.innerHTML = '<span>⏳</span> Đang Xác Thực Tài Khoản...';
            btn.style.opacity = '0.85';
            btn.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>
