<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công Cụ Giả Lập & Tạo Lỗi Kiểm Thử - Đề Tài 17</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #0a0d14; color: #f8fafc; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h1 { font-size: 24px; font-weight: 800; color: #fff; }
        .card { background: rgba(18, 24, 38, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 24px; margin-bottom: 20px; }
        .card h2 { font-size: 17px; font-weight: 700; margin-bottom: 8px; color: #38bdf8; }
        .card p { font-size: 13px; color: #94a3b8; line-height: 1.5; margin-bottom: 16px; }
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; border: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-red { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .btn-orange { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .btn-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-dash { background: rgba(255,255,255,0.08); color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; }
        .result-box { margin-top: 14px; padding: 14px; background: rgba(10, 13, 20, 0.9); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; font-family: 'JetBrains Mono', monospace; font-size: 12px; display: none; line-height: 1.6; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div>
                <h1>⚠️ Công Cụ Bắn Lỗi Kiểm Thử Trực Tiếp (Error Simulator)</h1>
                <p style="color: #94a3b8; font-size: 13px; margin-top: 4px;">Tự tay bấm tạo các lỗi CONFLICT, FORBIDDEN, INVALID_SITE để chứng minh cho Giảng viên xem</p>
            </div>
            <a href="index.php" class="btn-dash">← Quay Lại Dashboard</a>
        </div>

        <!-- LỖI 1: XUNG ĐỘT 409 -->
        <div class="card">
            <h2>1. Tạo Lỗi Xung Đột Dữ Liệu (HTTP 409 Conflict - CONFLICT_PAYLOAD)</h2>
            <p>
                <strong>Quy tắc kỹ thuật:</strong> Gửi sự kiện ban đầu, sau đó cố tình gửi lại cùng một mã <code>event_id</code> nhưng sửa nội dung sang món khác. Máy chủ phải phát hiện xung đột, từ chối sửa dữ liệu cũ và ghi nhận lỗi vào cơ sở dữ liệu.
            </p>
            <button class="btn btn-red" onclick="triggerConflict()">
                ⚡ Bắn Lỗi Xung Đột 409
            </button>
            <div id="resConflict" class="result-box"></div>
        </div>

        <!-- LỖI 2: VI PHẠM TRƯỜNG CẤM 400 -->
        <div class="card">
            <h2>2. Tạo Lỗi Vi Phạm Trường Cấm (HTTP 400 Bad Request - FORBIDDEN_INPUT_DATA)</h2>
            <p>
                <strong>Quy tắc kỹ thuật:</strong> Tracker SDK hoặc người dùng gửi dữ liệu có chứa trường cấm bảo mật như <code>password</code>, <code>credit_card</code>. Máy chủ phải phát hiện vi phạm chính sách an toàn, từ chối tiếp nhận và ghi nhận lỗi.
            </p>
            <button class="btn btn-orange" onclick="triggerForbidden()">
                ⚡ Bắn Lỗi Trường Cấm 400
            </button>
            <div id="resForbidden" class="result-box"></div>
        </div>

        <!-- LỖI 3: WEBSITE LẠ 403 -->
        <div class="card">
            <h2>3. Tạo Lỗi Website Chưa Đăng Ký (HTTP 403 Forbidden - INVALID_SITE)</h2>
            <p>
                <strong>Quy tắc kỹ thuật:</strong> Gửi sự kiện với <code>site_key</code> không tồn tại trong hệ thống đăng ký (ví dụ: <code>site_hacker_gia_mao</code>). Máy chủ phải chặn ngay lập tức.
            </p>
            <button class="btn btn-purple" onclick="triggerInvalidSite()">
                ⚡ Bắn Lỗi Website Lạ 403
            </button>
            <div id="resInvalidSite" class="result-box"></div>
        </div>
    </div>

    <script>
        function triggerConflict() {
            const box = document.getElementById('resConflict');
            box.style.display = 'block';
            box.innerHTML = '⏳ Bước 1: Đang gửi sự kiện gốc (Xem Cà Phê Muối)...<br>';

            const eId = 'evt_sim_conflict_' + Date.now();
            fetch('api/track.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    site_key: 'site_cafe_tea_01',
                    session_id: 'sess_sim_test',
                    event_id: eId,
                    event_code: 'btn_click',
                    page_url: '/demo/cafe_shop/index.php',
                    element_text: 'Chọn Cà Phê Muối Tuyệt Hảo',
                    category: 'coffee',
                    item_name: 'Cà Phê Muối'
                })
            })
            .then(res => res.json())
            .then(data => {
                box.innerHTML += `✅ Sự kiện gốc đã lưu thành công (Event ID: ${eId}).<br>`;
                box.innerHTML += `⏳ Bước 2: Đang gửi lại CÙNG ID [${eId}] nhưng SỬA NỘI DUNG thành Trà Sữa Hoàng Gia...<br>`;

                return fetch('api/track.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        site_key: 'site_cafe_tea_01',
                        session_id: 'sess_sim_test',
                        event_id: eId,
                        event_code: 'btn_click',
                        page_url: '/demo/cafe_shop/index.php',
                        element_text: 'Chọn Trà Sữa Trân Châu Hoàng Gia (ĐỔI MÓN!)',
                        category: 'milktea',
                        item_name: 'Trà Sữa Trân Châu'
                    })
                });
            })
            .then(async res => {
                const resData = await res.json();
                box.innerHTML += `<br><span style="color:#f87171; font-weight:bold;">🚨 KẾT QUẢ TỪ MÁY CHỦ (HTTP ${res.status}):</span><br>`;
                box.innerHTML += `<pre style="color:#fca5a5;">${JSON.stringify(resData, null, 2)}</pre>`;
                box.innerHTML += `<br>👉 <strong>Kết luận:</strong> Máy chủ đã phát hiện và ghi 1 bản ghi lỗi [CONFLICT_PAYLOAD] vào bảng Ingestion Errors trên Dashboard!`;
            });
        }

        function triggerForbidden() {
            const box = document.getElementById('resForbidden');
            box.style.display = 'block';
            box.innerHTML = '⏳ Đang gửi payload chứa trường nhạy cảm "password: mat_khau_123456"...<br>';

            fetch('api/track.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    site_key: 'site_cafe_tea_01',
                    session_id: 'sess_sim_test',
                    event_id: 'evt_sim_fbd_' + Date.now(),
                    event_code: 'form_complete',
                    page_url: '/demo/cafe_shop/checkout.php',
                    password: 'mat_khau_nguoi_dung_123456' // Trường cấm!
                })
            })
            .then(async res => {
                const resData = await res.json();
                box.innerHTML += `<br><span style="color:#f59e0b; font-weight:bold;">🚨 KẾT QUẢ TỪ MÁY CHỦ (HTTP ${res.status}):</span><br>`;
                box.innerHTML += `<pre style="color:#fcd34d;">${JSON.stringify(resData, null, 2)}</pre>`;
                box.innerHTML += `<br>👉 <strong>Kết luận:</strong> Máy chủ đã chặn thành công trường cấm và ghi nhận lỗi [FORBIDDEN_INPUT_DATA]!`;
            });
        }

        function triggerInvalidSite() {
            const box = document.getElementById('resInvalidSite');
            box.style.display = 'block';
            box.innerHTML = '⏳ Đang gửi sự kiện với site_key giả mạo "site_hacker_la_mat"...<br>';

            fetch('api/track.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    site_key: 'site_hacker_la_mat',
                    session_id: 'sess_sim_test',
                    event_id: 'evt_sim_inv_' + Date.now(),
                    event_code: 'page_view',
                    page_url: '/trang-ngoai'
                })
            })
            .then(async res => {
                const resData = await res.json();
                box.innerHTML += `<br><span style="color:#c084fc; font-weight:bold;">🚨 KẾT QUẢ TỪ MÁY CHỦ (HTTP ${res.status}):</span><br>`;
                box.innerHTML += `<pre style="color:#d8b4fe;">${JSON.stringify(resData, null, 2)}</pre>`;
                box.innerHTML += `<br>👉 <strong>Kết luận:</strong> Máy chủ đã chặn thành công và ghi nhận lỗi [INVALID_SITE]!`;
            });
        }
    </script>
</body>
</html>
