<?php
// export.php
// Đề tài 17: Xuất Báo Cáo Thống Kê Truy Cập & Thao Tác Đa Định Dạng: CSV, Excel (.xls), PDF
require_once __DIR__ . '/config/db.php';
$pdo = getDBConnection();

$websiteId = isset($_GET['website_id']) ? (int)$_GET['website_id'] : 1;
$format = strtolower(trim($_GET['format'] ?? 'csv')); // csv, excel/xls, pdf

// Lấy thông tin Website
$stmtSite = $pdo->prepare("SELECT id, name, site_key, allowed_domains FROM websites WHERE id = :id");
$stmtSite->execute([':id' => $websiteId]);
$website = $stmtSite->fetch();
$websiteName = $website['name'] ?? ("Website ID " . $websiteId);
$siteKey = $website['site_key'] ?? "site_" . $websiteId;

// Lấy danh sách sự kiện
$stmt = $pdo->prepare("SELECT e.event_id, e.session_id, e.event_code, e.page_url, e.element_text, 
    e.category, e.item_name, e.user_gender, e.client_time, e.is_duplicate, w.name as website_name
    FROM events e
    JOIN websites w ON e.site_id = w.id
    WHERE e.site_id = :site_id
    ORDER BY e.client_time DESC");
$stmt->execute([':site_id' => $websiteId]);
$rows = $stmt->fetchAll();

// Thống kê nhanh
$totalEvents = count($rows);
$totalViews = 0;
$totalActions = 0;
$totalDups = 0;
$uniqueSessions = [];
$eventCounts = [];

foreach ($rows as $r) {
    if ($r['event_code'] === 'page_view') {
        if ($r['is_duplicate'] == 0) $totalViews++;
    } else {
        $totalActions++;
    }
    $totalDups += (int)$r['is_duplicate'];
    $uniqueSessions[$r['session_id']] = true;
    $eventCounts[$r['event_code']] = ($eventCounts[$r['event_code']] ?? 0) + 1;
}
$sessionCount = count($uniqueSessions);

// -------------------------------------------------------------
// 1. XUẤT ĐỊNH DẠNG CSV
// -------------------------------------------------------------
if ($format === 'csv') {
    $filename = "bao_cao_thong_ke_{$siteKey}_" . date('Ymd_His') . ".csv";
    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }
    
    // Thêm BOM UTF-8 cho Microsoft Excel hiển thị tiếng Việt chuẩn
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'Mã Sự Kiện (Event ID)',
        'Mã Phiên (Session ID)',
        'Loại Sự Kiện',
        'Đường Dẫn URL',
        'Thao Tác / Tên Nút',
        'Danh Mục Món',
        'Tên Món',
        'Giới Tính Người Thao Tác',
        'Thời Gian Ghi Nhận (Asia/HCM)',
        'Số Lần Trùng Lặp Bỏ Qua',
        'Tên Website'
    ]);
    
    foreach ($rows as $r) {
        fputcsv($output, [
            $r['event_id'],
            $r['session_id'],
            $r['event_code'],
            $r['page_url'],
            $r['element_text'],
            $r['category'],
            $r['item_name'],
            $r['user_gender'],
            $r['client_time'],
            $r['is_duplicate'],
            $r['website_name']
        ]);
    }
    fclose($output);
    exit;
}

// -------------------------------------------------------------
// 2. XUẤT ĐỊNH DẠNG EXCEL (.XLS)
// -------------------------------------------------------------
if ($format === 'excel' || $format === 'xls') {
    $filename = "bao_cao_thong_ke_{$siteKey}_" . date('Ymd_His') . ".xls";
    if (!headers_sent()) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    // Xuất bảng tính HTML định dạng Excel có màu sắc, định dạng số và KPI
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if gte mso 9]>
        <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Báo Cáo Thống Kê</x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, sans-serif; }
            .title-header { background-color: #0f172a; color: #38bdf8; font-size: 16pt; font-weight: bold; text-align: center; padding: 12px; }
            .sub-header { background-color: #1e293b; color: #cbd5e1; font-size: 10pt; text-align: center; padding: 6px; }
            .kpi-title { background-color: #334155; color: #ffffff; font-weight: bold; text-align: center; }
            .kpi-val { background-color: #f1f5f9; color: #0284c7; font-weight: bold; font-size: 12pt; text-align: center; }
            .th-head { background-color: #0284c7; color: #ffffff; font-weight: bold; border: 1px solid #0369a1; text-align: center; }
            .td-cell { border: 1px solid #cbd5e1; font-size: 9.5pt; }
            .td-center { border: 1px solid #cbd5e1; text-align: center; font-size: 9.5pt; }
            .badge-pv { background-color: #e0f2fe; color: #0369a1; font-weight: bold; text-align: center; }
            .badge-act { background-color: #fef3c7; color: #92400e; font-weight: bold; text-align: center; }
        </style>
    </head>
    <body>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <td colspan="11" class="title-header">BÁO CÁO THỐNG KÊ TRUY CẬP VÀ PHÂN TÍCH THAO TÁC NGƯỜI DÙNG</td>
            </tr>
            <tr>
                <td colspan="11" class="sub-header">
                    Website: <b><?= htmlspecialchars($websiteName) ?></b> (<?= htmlspecialchars($siteKey) ?>) | 
                    Xuất lúc: <?= date('d/m/Y H:i:s') ?> | 
                    Hệ thống: Web Analytics Pro (Đề Tài 17)
                </td>
            </tr>
            <tr><td colspan="11"></td></tr>
            <!-- KPI Summary Rows -->
            <tr>
                <td colspan="3" class="kpi-title">👁️ TỔNG LƯỢT XEM TRANG</td>
                <td colspan="3" class="kpi-title">👥 SỐ PHIÊN HOẠT ĐỘNG</td>
                <td colspan="3" class="kpi-title">⚡ TỔNG THAO TÁC / HÀNH ĐỘNG</td>
                <td colspan="2" class="kpi-title">🔄 TRÙNG LẶP ĐÃ TRÁNH</td>
            </tr>
            <tr>
                <td colspan="3" class="kpi-val"><?= number_format($totalViews) ?></td>
                <td colspan="3" class="kpi-val"><?= number_format($sessionCount) ?></td>
                <td colspan="3" class="kpi-val"><?= number_format($totalActions) ?></td>
                <td colspan="2" class="kpi-val"><?= number_format($totalDups) ?></td>
            </tr>
            <tr><td colspan="11"></td></tr>
            <!-- Detail Table Header -->
            <tr>
                <th class="th-head">STT</th>
                <th class="th-head">Mã Sự Kiện (Event ID)</th>
                <th class="th-head">Mã Phiên (Session ID)</th>
                <th class="th-head">Loại Sự Kiện</th>
                <th class="th-head">Đường Dẫn URL</th>
                <th class="th-head">Thao Tác / Nút Bấm</th>
                <th class="th-head">Danh Mục</th>
                <th class="th-head">Tên Món / Sản Phẩm</th>
                <th class="th-head">Giới Tính</th>
                <th class="th-head">Thời Gian Ghi Nhận</th>
                <th class="th-head">Trùng Lặp</th>
            </tr>
            <?php $stt = 1; foreach ($rows as $r): ?>
            <tr>
                <td class="td-center"><?= $stt++ ?></td>
                <td class="td-center" style="font-family: monospace;"><?= htmlspecialchars($r['event_id']) ?></td>
                <td class="td-center" style="font-family: monospace;"><?= htmlspecialchars($r['session_id']) ?></td>
                <td class="<?= ($r['event_code'] === 'page_view') ? 'badge-pv' : 'badge-act' ?>">
                    <?= htmlspecialchars($r['event_code']) ?>
                </td>
                <td class="td-cell"><?= htmlspecialchars($r['page_url']) ?></td>
                <td class="td-cell"><?= htmlspecialchars($r['element_text'] ?? '-') ?></td>
                <td class="td-center"><?= htmlspecialchars($r['category'] ?? '-') ?></td>
                <td class="td-cell"><?= htmlspecialchars($r['item_name'] ?? '-') ?></td>
                <td class="td-center"><?= ($r['user_gender'] === 'male') ? 'Nam' : (($r['user_gender'] === 'female') ? 'Nữ' : 'Chưa rõ') ?></td>
                <td class="td-center"><?= htmlspecialchars($r['client_time']) ?></td>
                <td class="td-center"><?= ($r['is_duplicate'] > 0) ? 'Có (' . $r['is_duplicate'] . ')' : '0' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </body>
    </html>
    <?php
    exit;
}

// -------------------------------------------------------------
// 3. XUẤT ĐỊNH DẠNG PDF (BÁO CÁO EXECUTIVE A4 CHUẨN IN ẤN)
// -------------------------------------------------------------
$autoPrint = isset($_GET['print']) && $_GET['print'] == '1';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Thống Kê Truy Cập & Phân Tích Thao Tác - <?= htmlspecialchars($websiteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            background: #f1f5f9;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .page-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
        }
        /* Thanh thao tác nhanh (ẩn khi in) */
        .action-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 12px 18px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 10px;
        }
        .btn-group { display: flex; gap: 8px; }
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-print { background: #38bdf8; color: #0f172a; }
        .btn-print:hover { background: #7dd3fc; }
        .btn-excel { background: #10b981; color: #ffffff; }
        .btn-excel:hover { background: #059669; }
        .btn-csv { background: #64748b; color: #ffffff; }
        .btn-csv:hover { background: #475569; }
        .btn-back { background: rgba(255,255,255,0.1); color: #ffffff; }

        /* Tiêu đề & Thông tin cơ quan */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 18px;
            margin-bottom: 24px;
        }
        .org-info { font-size: 11.5px; color: var(--text-muted); line-height: 1.4; }
        .org-name { font-weight: 800; font-size: 13px; color: var(--text-dark); text-transform: uppercase; }
        .doc-meta { text-align: right; font-size: 11.5px; color: var(--text-muted); }
        .doc-badge { display: inline-block; background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 11px; margin-bottom: 4px; }

        .report-title-box { text-align: center; margin: 24px 0; }
        .report-title { font-size: 20px; font-weight: 800; color: var(--text-dark); text-transform: uppercase; letter-spacing: -0.3px; }
        .report-sub { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

        /* Lưới 4 KPI */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .kpi-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            background: var(--bg-light);
            text-align: center;
        }
        .kpi-label { font-size: 11.5px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
        .kpi-value { font-size: 22px; font-weight: 800; color: var(--primary); margin-top: 4px; }

        /* Bảng sự kiện */
        .section-title { font-size: 14px; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 12px; }
        th { background: #f8fafc; color: #475569; font-weight: 700; text-align: left; padding: 10px 12px; border: 1px solid var(--border); }
        td { padding: 9px 12px; border: 1px solid var(--border); }
        tr:nth-child(even) { background: #fafafa; }
        .code-font { font-family: 'JetBrains Mono', monospace; font-size: 11px; }

        /* Chữ ký xác nhận */
        .sign-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        .sign-box { text-align: center; width: 220px; }
        .sign-title { font-weight: 700; font-size: 13px; }
        .sign-role { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .sign-space { height: 70px; }
        .sign-name { font-weight: 700; font-size: 13px; }

        /* Thiết lập chế độ in A4 */
        @media print {
            body { background: #ffffff; padding: 0; }
            .page-container { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .action-toolbar { display: none !important; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body>

    <div class="page-container">

        <!-- THANH CÔNG CỤ TÁC VỤ (ẨN KHI IN) -->
        <div class="action-toolbar">
            <div style="font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <span>📑</span> Báo Cáo Thống Kê Sẵn Sàng (PDF / Excel / CSV)
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-print" onclick="window.print()">
                    🖨️ In Báo Cáo / Lưu PDF
                </button>
                <a href="export.php?website_id=<?= $websiteId ?>&format=excel" class="btn btn-excel">
                    📊 Tải File Excel (.xls)
                </a>
                <a href="export.php?website_id=<?= $websiteId ?>&format=csv" class="btn btn-csv">
                    📄 Tải File CSV
                </a>
                <a href="index.php" class="btn btn-back">
                    ← Về Dashboard
                </a>
            </div>
        </div>

        <!-- HEADER CƠ QUAN / ĐƠN VỊ -->
        <div class="report-header">
            <div class="org-info">
                <div class="org-name">TRƯỜNG ĐẠI HỌC ĐẠI NAM • KHOA CÔNG NGHỆ THÔNG TIN</div>
                <div>Bộ Môn: Phát Triển Phần Mềm Mã Nguồn Mở</div>
                <div>Đề Tài 17: Hệ Thống Thu Thập &amp; Thống Kê Truy Cập &amp; Phân Tích Thao Tác</div>
            </div>
            <div class="doc-meta">
                <div class="doc-badge">BÁO CÁO HỆ THỐNG</div>
                <div>Số: BC-<?= date('Ym') ?>/WAP-<?= $websiteId ?></div>
                <div>Hà Nội, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></div>
            </div>
        </div>

        <!-- TIÊU ĐỀ BÁO CÁO -->
        <div class="report-title-box">
            <h1 class="report-title">BÁO CÁO TỔNG HỢP SỐ LIỆU TRUY CẬP VÀ THAO TÁC</h1>
            <p class="report-sub">
                Website Khảo Sát: <strong><?= htmlspecialchars($websiteName) ?></strong> (Site Key: <code><?= htmlspecialchars($siteKey) ?></code>)
            </p>
        </div>

        <!-- 4 KPI TỔNG HỢP -->
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-label">Tổng Lượt Xem Trang</div>
                <div class="kpi-value"><?= number_format($totalViews) ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Số Phiên Hoạt Động</div>
                <div class="kpi-value"><?= number_format($sessionCount) ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Tổng Thao Tác Ghi Nhận</div>
                <div class="kpi-value" style="color: #10b981;"><?= number_format($totalActions) ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Trùng Lặp Đã Tránh</div>
                <div class="kpi-value" style="color: #f59e0b;"><?= number_format($totalDups) ?></div>
            </div>
        </div>

        <!-- PHÂN BỔ LOẠI SỰ KIỆN -->
        <div class="section-title">📊 1. Phân Bổ Theo Loại Sự Kiện Ghi Nhận</div>
        <table>
            <thead>
                <tr>
                    <th>Mã Loại Sự Kiện</th>
                    <th>Tên Loại Sự Kiện / Thao Tác</th>
                    <th style="text-align: right;">Số Lượt</th>
                    <th style="text-align: right;">Tỷ Trọng (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventCounts as $code => $cnt): 
                    $pct = ($totalEvents > 0) ? round(($cnt / $totalEvents) * 100, 1) : 0;
                ?>
                <tr>
                    <td class="code-font"><strong><?= htmlspecialchars($code) ?></strong></td>
                    <td>
                        <?php 
                        if ($code === 'page_view') echo 'Lượt xem trang (Xem sản phẩm / Menu)';
                        elseif ($code === 'btn_click') echo 'Thao tác bấm nút (Chọn món / Tải tài liệu)';
                        elseif ($code === 'form_start') echo 'Bắt đầu tương tác biểu mẫu (Đặt món / Đăng ký)';
                        elseif ($code === 'form_complete') echo 'Hoàn tất biểu mẫu (Thanh toán thành công)';
                        else echo 'Sự kiện mở rộng: ' . htmlspecialchars($code);
                        ?>
                    </td>
                    <td style="text-align: right; font-weight: 700;"><?= number_format($cnt) ?></td>
                    <td style="text-align: right; color: var(--text-muted);"><?= $pct ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- DANH SÁCH SỰ KIỆN CHI TIẾT (TOP 25 GẦN NHẤT) -->
        <div class="section-title">📋 2. Chi Tiết Lượt Tương Tác Gần Nhất (Top <?= min(30, count($rows)) ?>)</div>
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã Phiên (Session)</th>
                    <th>Loại</th>
                    <th>Đường Dẫn / Trang</th>
                    <th>Thao Tác / Nút Bấm</th>
                    <th>Thời Điểm</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $countLimit = min(30, count($rows));
                for ($i = 0; $i < $countLimit; $i++): 
                    $r = $rows[$i];
                ?>
                <tr>
                    <td style="text-align: center; color: var(--text-muted);"><?= $i + 1 ?></td>
                    <td class="code-font"><?= htmlspecialchars(substr($r['session_id'], 0, 16)) ?>...</td>
                    <td class="code-font" style="font-weight: 700; color: <?= ($r['event_code'] === 'page_view') ? '#0284c7' : '#059669' ?>;">
                        <?= htmlspecialchars($r['event_code']) ?>
                    </td>
                    <td><?= htmlspecialchars($r['page_url']) ?></td>
                    <td><?= htmlspecialchars($r['element_text'] ?? '-') ?></td>
                    <td style="white-space: nowrap;"><?= htmlspecialchars($r['client_time']) ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- CHỮ KÝ XÁC NHẬN BÁO CÁO -->
        <div class="sign-row">
            <div class="sign-box">
                <div class="sign-title">NGƯỜI LẬP BÁO CÁO</div>
                <div class="sign-role">(Ký, ghi rõ họ tên)</div>
                <div class="sign-space"></div>
                <div class="sign-name">Nhóm Sinh Viên Đề Tài 17</div>
            </div>
            <div class="sign-box">
                <div class="sign-title">CÁN BỘ GIẢNG VIÊN ĐÁNH GIÁ</div>
                <div class="sign-role">(Ký, ghi rõ họ tên &amp; ngày duyệt)</div>
                <div class="sign-space"></div>
                <div class="sign-name">Khoa Công Nghệ Thông Tin - DNU</div>
            </div>
        </div>

    </div>

    <?php if ($autoPrint): ?>
    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 400);
        };
    </script>
    <?php endif; ?>
</body>
</html>
