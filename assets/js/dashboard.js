/**
 * assets/js/dashboard.js
 * Đề tài 17: Bộ điều khiển Bảng Thống Kê & Phân Tích Thao Tác Website (Cafe & Trà Sữa)
 */

let currentSiteId = 1;
let currentRole = 'admin';
let chartTimelineInstance = null;
let chartEventDistInstance = null;

// BIẾN PHÂN TRANG CHO NHẬT KÝ SỰ KIỆN (ĐÁNH SỐ TRANG 1 2 3)
let eventsCurrentPage = 1;
const eventsPerPage = 5; // Mặc định 5 sự kiện / trang
let allRecentEventsData = [];
let currentEventFilter = 'all';
let currentSearchQuery = '';
let lastKnownActionId = null;

// Hàm tạo chuỗi thời gian địa phương (GMT+7 Asia/Ho_Chi_Minh) chuẩn Y-m-d H:i:s
function getVietnamTimeString(offsetSeconds = 0) {
    const d = new Date(Date.now() + offsetSeconds * 1000);
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

document.addEventListener('DOMContentLoaded', function() {
    initTheme();
    initFilters();
    loadRegisteredWebsites();
    loadDashboardData();

    // Tự động làm mới dữ liệu sau mỗi 2.5 giây để thao tác người dùng bên tab khác xuất hiện tức thì
    setInterval(function() {
        loadDashboardData(true);
    }, 2500);
});

// ==========================================
// BỘ CHUYỂN ĐỔI CHẾ ĐỘ SÁNG / TỐI (THEME)
// ==========================================
function initTheme() {
    const savedTheme = localStorage.getItem('analytics_theme') || 'light';
    applyTheme(savedTheme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = (currentTheme === 'light') ? 'dark' : 'light';
    applyTheme(newTheme);
    localStorage.setItem('analytics_theme', newTheme);
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const icon = document.getElementById('themeToggleIcon');
    const text = document.getElementById('themeToggleText');
    if (icon && text) {
        if (theme === 'dark') {
            icon.innerText = '🌙';
            text.innerText = 'Chế Độ Tối';
        } else {
            icon.innerText = '☀️';
            text.innerText = 'Chế Độ Sáng';
        }
    }
    // Cập nhật lại màu sắc biểu đồ Chart.js nếu đã có dữ liệu
    if (window.lastTimelineData && window.lastEventDistData) {
        renderCharts(window.lastTimelineData, window.lastEventDistData);
    }
}

function initFilters() {
    const siteSelect = document.getElementById('selectWebsite');
    if (siteSelect) {
        siteSelect.addEventListener('change', function() {
            currentSiteId = parseInt(this.value);
            loadDashboardData();
        });
    }

    const roleSelect = document.getElementById('selectRole');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            currentRole = this.value;
            loadDashboardData();
        });
    }

    const fromDate = document.getElementById('filterFromDate');
    const toDate = document.getElementById('filterToDate');
    if (fromDate && toDate) {
        const d = new Date();
        toDate.value = d.toISOString().slice(0, 10);
        d.setDate(d.getDate() - 30);
        fromDate.value = d.toISOString().slice(0, 10);

        fromDate.addEventListener('change', loadDashboardData);
        toDate.addEventListener('change', loadDashboardData);
    }
}

function loadDashboardData(isSilent = false) {
    const from = document.getElementById('filterFromDate') ? document.getElementById('filterFromDate').value : '';
    const to = document.getElementById('filterToDate') ? document.getElementById('filterToDate').value : '';

    const url = `api/reports.php?website_id=${currentSiteId}&from_date=${from}&to_date=${to}&user_role=${currentRole}`;

    fetch(url)
        .then(res => {
            if (res.status === 403) {
                throw new Error('Bạn không có quyền truy cập báo cáo này (403 Forbidden)!');
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'SUCCESS') {
                renderKPIs(data.kpis);
                renderFunnel(data.funnel_steps, data.kpis.funnel_rate);
                renderReconciliation(data.reconciliation);
                renderLiveActionHUD(data.latest_action);
                renderGenderInsights(data.gender_analytics);
                renderCharts(data.timeline, data.event_distribution);
                renderTopPages(data.top_pages);
                renderErrorsTable(data.ingestion_errors, data.error_stats);
                allRecentEventsData = data.recent_events || [];
                renderRecentEvents();
            }
        })
        .catch(err => {
            console.error(err);
            if (!isSilent) {
                alert('Lỗi tải dữ liệu: ' + err.message);
            }
        });
}

function renderKPIs(kpis) {
    document.getElementById('lblTotalViews').innerText = Number(kpis.total_pageviews).toLocaleString('vi-VN');
    document.getElementById('lblActiveSessions').innerText = Number(kpis.active_sessions).toLocaleString('vi-VN');
    document.getElementById('lblTotalActions').innerText = Number(kpis.total_actions).toLocaleString('vi-VN');
    document.getElementById('lblDupsAvoided').innerText = Number(kpis.duplicates_avoided).toLocaleString('vi-VN');
    document.getElementById('lblFunnelRate').innerText = kpis.funnel_rate + '%';
}

function renderFunnel(steps, rate) {
    document.getElementById('lblStep1Count').innerText = steps.step1_page_view;
    document.getElementById('lblStep2Count').innerText = steps.step2_action_start;
    document.getElementById('lblStep3Count').innerText = steps.step3_completed;
    
    const bar = document.getElementById('barFunnelFill');
    if (bar) {
        bar.style.width = Math.min(rate, 100) + '%';
    }
}

// Bảng đối chiếu số thao tác thực tế (Mở trang sản phẩm -> Bấm tải tài liệu -> Gửi biểu mẫu + Nhấp sản phẩm & Giỏ hàng)
function renderReconciliation(recon) {
    if (!recon) return;
    const pv = recon.product_page_views || 0;
    const dl = recon.download_document_clicks || 0;
    const fs = recon.form_starts || 0;
    const fc = recon.form_completes || 0;
    const prod = recon.product_clicks || 0;
    const cart = recon.cart_clicks || 0;

    const elRealPv = document.getElementById('reconRealPv');
    const elReportPv = document.getElementById('reconReportPv');
    if (elRealPv && elReportPv) {
        elRealPv.innerText = Number(pv).toLocaleString('vi-VN');
        elReportPv.innerText = Number(pv).toLocaleString('vi-VN');
    }

    const elRealDl = document.getElementById('reconRealDownload');
    const elReportDl = document.getElementById('reconReportDownload');
    if (elRealDl && elReportDl) {
        elRealDl.innerText = Number(dl).toLocaleString('vi-VN');
        elReportDl.innerText = Number(dl).toLocaleString('vi-VN');
    }

    const elRealFs = document.getElementById('reconRealStart');
    const elReportFs = document.getElementById('reconReportStart');
    if (elRealFs && elReportFs) {
        elRealFs.innerText = Number(fs).toLocaleString('vi-VN');
        elReportFs.innerText = Number(fs).toLocaleString('vi-VN');
    }

    const elRealFc = document.getElementById('reconRealComplete');
    const elReportFc = document.getElementById('reconReportComplete');
    if (elRealFc && elReportFc) {
        elRealFc.innerText = Number(fc).toLocaleString('vi-VN');
        elReportFc.innerText = Number(fc).toLocaleString('vi-VN');
    }

    // Đối chiếu 5: Nhấp vào sản phẩm
    const elRealProd = document.getElementById('reconRealProduct');
    const elReportProd = document.getElementById('reconReportProduct');
    if (elRealProd && elReportProd) {
        elRealProd.innerText = Number(prod).toLocaleString('vi-VN');
        elReportProd.innerText = Number(prod).toLocaleString('vi-VN');
    }

    // Đối chiếu 6: Nhấp vào giỏ hàng
    const elRealCart = document.getElementById('reconRealCart');
    const elReportCart = document.getElementById('reconReportCart');
    if (elRealCart && elReportCart) {
        elRealCart.innerText = Number(cart).toLocaleString('vi-VN');
        elReportCart.innerText = Number(cart).toLocaleString('vi-VN');
    }
}

// =========================================================================
// Ô HIỂN THỊ THAO TÁC NGƯỜI DÙNG TRỰC TIẾP (LIVE USER ACTION HUD)
// =========================================================================
function renderLiveActionHUD(act) {
    if (!act) return;

    const elBox = document.getElementById('liveActionHudBox');
    const elIcon = document.getElementById('hudActionIcon');
    const elName = document.getElementById('hudActionName');
    const elUser = document.getElementById('hudUserLabel');
    const elLoc = document.getElementById('hudLocationLabel');
    const elTime = document.getElementById('hudTimeLabel');

    if (!elName) return;

    let icon = '⚡';
    let actionLabel = act.element_text || act.item_name || 'Thao tác trên trang';
    let color = '#f59e0b';

    const itemStr = (act.item_name || '').toLowerCase();
    const elemStr = (act.element_text || '').toLowerCase();
    const catStr = (act.category || '').toLowerCase();

    // Nhận diện Giỏ Hàng
    if (itemStr.includes('gio_hang') || itemStr.includes('open_cart') || catStr === 'cart' || elemStr.includes('giỏ')) {
        icon = '🛒';
        actionLabel = 'Người dùng vừa NHẤP VÀO GIỎ HÀNG (Mở xem giỏ đồ uống)';
        color = '#34d399';
    } 
    // Nhận diện Sản Phẩm
    else if (catStr === 'coffee' || catStr === 'milktea' || catStr === 'espresso' || catStr === 'signature' || catStr === 'coldbrew' || catStr === 'tea' || itemStr.startsWith('cf_') || elemStr.includes('chọn món') || elemStr.includes('thêm món') || elemStr.includes('sản phẩm')) {
        icon = '☕';
        actionLabel = `Người dùng vừa NHẤP VÀO SẢN PHẨM: ${act.element_text || act.item_name}`;
        color = '#fbbf24';
    }
    // Nhận diện Tải tài liệu
    else if (catStr === 'document' || elemStr.includes('tải brochure') || elemStr.includes('tải tài liệu')) {
        icon = '📥';
        actionLabel = 'Người dùng vừa BẤM NÚT TẢI TÀI LIỆU (Brochure Menu PDF)';
        color = '#38bdf8';
    }
    // Nhận diện Đặt chỗ
    else if (act.event_code === 'form_complete' || catStr === 'reservation') {
        icon = '📅';
        actionLabel = `Người dùng vừa XÁC NHẬN: ${act.form_name || 'Lịch Đặt Chỗ & Giữ Bàn'}`;
        color = '#10b981';
    } else if (act.event_code === 'form_start') {
        icon = '📝';
        actionLabel = `Người dùng vừa MỞ BIỂU MẪU: ${act.form_name || 'Biểu Mẫu Tương Tác'}`;
        color = '#a78bfa';
    } else if (act.event_code === 'page_view') {
        icon = '👁️';
        actionLabel = `Người dùng vừa MỞ XEM TRANG: ${act.page_url}`;
        color = '#38bdf8';
    }

    if (elIcon) elIcon.innerText = icon;
    elName.innerText = actionLabel;
    elName.style.color = color;

    const uName = act.customer_name || 'Khách Hàng Trực Tuyến';
    const isFemale = (act.user_gender === 'female');
    if (elUser) {
        elUser.innerHTML = `👤 Khách: <strong style="color:var(--primary);">${isFemale ? '👧 ' : '👦 '}${uName}</strong>`;
    }

    const uAddr = act.customer_address || 'Số 12 Chùa Bộc, Đống Đa, Hà Nội';
    if (elLoc) {
        elLoc.innerHTML = `📍 Vị trí: <span style="color:var(--text-sub);">${uAddr}</span>`;
    }

    const t = act.client_time ? act.client_time.slice(11) : 'Vừa xong';
    if (elTime) {
        elTime.innerHTML = `⏱️ Thời gian: <span style="font-family:'JetBrains Mono', monospace; color:var(--text-muted);">${t}</span>`;
    }

    // Hiệu ứng chớp sáng khi có event mới
    if (elBox && act.event_id && act.event_id !== lastKnownActionId) {
        elBox.classList.remove('action-updated');
        void elBox.offsetWidth; // trigger reflow
        elBox.classList.add('action-updated');
        lastKnownActionId = act.event_id;
    }
}

// Thử nghiệm trực tiếp: Nhấp vào sản phẩm
function simulateProductClickLive() {
    const siteKey = (currentSiteId === 1) ? 'site_cafe_tea_01' : 'site_eduportal_02';
    const sess = 'sess_live_' + Date.now();
    const nowStr = getVietnamTimeString(0);

    fetch('api/track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            site_key: siteKey,
            session_id: sess,
            event_id: 'evt_clk_prod_' + Date.now(),
            event_code: 'btn_click',
            page_url: '/demo/cafe_shop/index.php',
            element_text: 'Thêm vào giỏ: Noir Espresso (Giá: 45.000 đ)',
            category: 'coffee',
            item_name: 'cf_espresso',
            user_gender: 'male',
            customer_name: 'Hoàng Minh Đức (Nam)',
            customer_address: 'Số 12 Chùa Bộc, Đống Đa, Hà Nội',
            form_name: 'Thực Đơn Cà Phê Chuẩn Gu',
            client_time: nowStr
        })
    }).then(() => {
        eventsCurrentPage = 1;
        loadDashboardData(true);
    });
}

// Thử nghiệm trực tiếp: Nhấp vào giỏ hàng
function simulateCartClickLive() {
    const siteKey = (currentSiteId === 1) ? 'site_cafe_tea_01' : 'site_eduportal_02';
    const sess = 'sess_live_' + Date.now();
    const nowStr = getVietnamTimeString(0);

    fetch('api/track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            site_key: siteKey,
            session_id: sess,
            event_id: 'evt_clk_cart_' + Date.now(),
            event_code: 'btn_click',
            page_url: '/demo/cafe_shop/index.php',
            element_text: '🛒 Nhấp Mở Giỏ Đồ Uống & Kiểm Tra Món',
            category: 'cart',
            item_name: 'mo_gio_hang',
            user_gender: 'male',
            customer_name: 'Hoàng Minh Đức (Nam)',
            customer_address: 'Số 12 Chùa Bộc, Đống Đa, Hà Nội',
            form_name: 'Khu Vực Giỏ Hàng Trượt (Cart Drawer)',
            client_time: nowStr
        })
    }).then(() => {
        eventsCurrentPage = 1;
        loadDashboardData(true);
    });
}

// Mô phỏng chuỗi 6 thao tác trọn vẹn đúng theo ví dụ đề bài + thao tác chọn món, giỏ hàng
function simulateDocScenario() {
    const siteKey = (currentSiteId === 1) ? 'site_cafe_tea_01' : 'site_eduportal_02';
    const testSess = 'sess_live_recon_' + Date.now();

    const demoUser = 'Hoàng Minh Đức (Nam)';
    const demoAddr = 'Số 12 Chùa Bộc, Đống Đa, Hà Nội';
    const demoForm = 'Lịch Đặt Chỗ & Giữ Bàn NOIR CAFÉ';

    // 1. Thao tác 1: Mở trang sản phẩm (t = 0s)
    fetch('api/track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            site_key: siteKey,
            session_id: testSess,
            event_id: 'evt_sim_pv_' + Date.now() + '_1',
            event_code: 'page_view',
            page_url: '/demo/cafe_shop/index.php',
            element_text: 'Mở trang sản phẩm / thực đơn đồ uống',
            category: 'general',
            user_gender: 'male',
            customer_name: demoUser,
            customer_address: demoAddr,
            form_name: 'Khu Vực Trang Chủ & Danh Mục Món',
            client_time: getVietnamTimeString(0)
        })
    }).then(() => {
        // 2. Thao tác 2: Bấm nút tải tài liệu (t = 1s)
        return fetch('api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_key: siteKey,
                session_id: testSess,
                event_id: 'evt_sim_dl_' + Date.now() + '_2',
                event_code: 'btn_click',
                page_url: '/demo/cafe_shop/index.php',
                element_text: '📥 Bấm nút Tải Brochure / Menu Quán',
                category: 'document',
                item_name: 'tai_tai_lieu_menu',
                user_gender: 'male',
                customer_name: demoUser,
                customer_address: demoAddr,
                form_name: demoForm,
                client_time: getVietnamTimeString(1)
            })
        });
    }).then(() => {
        // 3. Thao tác 3: Nhấp vào sản phẩm (t = 2s)
        return fetch('api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_key: siteKey,
                session_id: testSess,
                event_id: 'evt_sim_item_' + Date.now() + '_3',
                event_code: 'btn_click',
                page_url: '/demo/cafe_shop/index.php',
                element_text: 'Thêm vào giỏ: Noir Espresso (Giá: 45.000 đ)',
                category: 'coffee',
                item_name: 'cf_espresso',
                user_gender: 'male',
                customer_name: demoUser,
                customer_address: demoAddr,
                form_name: 'Thực Đơn Cà Phê Chuẩn Gu',
                client_time: getVietnamTimeString(2)
            })
        });
    }).then(() => {
        // 4. Thao tác 4: Nhấp vào giỏ hàng (t = 3s)
        return fetch('api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_key: siteKey,
                session_id: testSess,
                event_id: 'evt_sim_cart_' + Date.now() + '_4',
                event_code: 'btn_click',
                page_url: '/demo/cafe_shop/index.php',
                element_text: '🛒 Nhấp Mở Giỏ Đồ Uống & Kiểm Tra Món',
                category: 'cart',
                item_name: 'mo_gio_hang',
                user_gender: 'male',
                customer_name: demoUser,
                customer_address: demoAddr,
                form_name: 'Khu Vực Giỏ Hàng Trượt (Cart Drawer)',
                client_time: getVietnamTimeString(3)
            })
        });
    }).then(() => {
        // 5. Thao tác 5: Mở điền lịch đặt chỗ (t = 4s)
        return fetch('api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_key: siteKey,
                session_id: testSess,
                event_id: 'evt_sim_fs_' + Date.now() + '_5',
                event_code: 'form_start',
                page_url: '/demo/cafe_shop/index.php',
                element_text: '📝 Mở Điền Lịch Đặt Chỗ & Giữ Bàn Quán',
                category: 'reservation',
                item_name: 'mo_lich_dat_cho',
                user_gender: 'male',
                customer_name: demoUser,
                customer_address: demoAddr,
                form_name: demoForm,
                client_time: getVietnamTimeString(4)
            })
        });
    }).then(() => {
        // 6. Thao tác 6: Xác nhận lịch đặt chỗ hoàn tất (t = 5s)
        return fetch('api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_key: siteKey,
                session_id: testSess,
                event_id: 'evt_sim_fc_' + Date.now() + '_6',
                event_code: 'form_complete',
                page_url: '/demo/cafe_shop/index.php',
                element_text: '📅 Xác Nhận Lịch Đặt Chỗ & Giữ Bàn thành công',
                category: 'reservation',
                item_name: 'xac_nhan_lich_dat_cho',
                user_gender: 'male',
                customer_name: demoUser,
                customer_address: demoAddr,
                form_name: demoForm,
                client_time: getVietnamTimeString(5)
            })
        });
    }).then(() => {
        alert('🎉 ĐÃ CHẠY XONG MÔ PHỎNG TOÀN CHUỖI:\n- Người thực hiện: ' + demoUser + '\n- Địa chỉ: ' + demoAddr + '\n\n1. 👁️ Mở trang thực đơn (page_view)\n2. 📥 Bấm tải tài liệu Brochure (btn_click)\n3. ☕ Nhấp vào sản phẩm: Noir Espresso (btn_click)\n4. 🛒 Nhấp vào giỏ hàng: Giỏ đồ uống (btn_click)\n5. 📝 Mở điền lịch hẹn giữ bàn (form_start)\n6. 📅 Xác nhận đặt chỗ thành công (form_complete)\n\n-> 6 thao tác đã được thêm vào hệ thống và tự động phân bổ vào các trang 1, 2, 3...!');
        loadDashboardData();
    });
}

function renderGenderInsights(gStats) {
    if (!gStats) return;

    // Nam (Con trai)
    const mTotal = gStats.male.orders;
    const mCoffee = gStats.male.coffee;
    const mTea = gStats.male.milktea;
    const mCoffeePct = mTotal > 0 ? Math.round((mCoffee / mTotal) * 100) : 88;
    const mTeaPct = 100 - mCoffeePct;

    document.getElementById('lblMaleOrders').innerText = `${mTotal} đơn (${Number(gStats.male.revenue).toLocaleString('vi-VN')} đ)`;
    document.getElementById('lblMaleCoffeePct').innerText = `☕ Cà Phê: ${mCoffeePct}% (${mCoffee} lượt)`;
    document.getElementById('lblMaleTeaPct').innerText = `🧋 Trà Sữa: ${mTeaPct}% (${mTea} lượt)`;
    document.getElementById('barMaleCoffeeFill').style.width = mCoffeePct + '%';
    document.getElementById('barMaleTeaFill').style.width = mTeaPct + '%';

    // Nữ (Con gái)
    const fTotal = gStats.female.orders;
    const fTea = gStats.female.milktea;
    const fCoffee = gStats.female.coffee;
    const fTeaPct = fTotal > 0 ? Math.round((fTea / fTotal) * 100) : 88;
    const fCoffeePct = 100 - fTeaPct;

    document.getElementById('lblFemaleOrders').innerText = `${fTotal} đơn (${Number(gStats.female.revenue).toLocaleString('vi-VN')} đ)`;
    document.getElementById('lblFemaleTeaPct').innerText = `🧋 Trà Sữa: ${fTeaPct}% (${fTea} lượt)`;
    document.getElementById('lblFemaleCoffeePct').innerText = `☕ Cà Phê: ${fCoffeePct}% (${fCoffee} lượt)`;
    document.getElementById('barFemaleTeaFill').style.width = fTeaPct + '%';
    document.getElementById('barFemaleCoffeeFill').style.width = fCoffeePct + '%';
}

function renderCharts(timeline, eventDist) {
    window.lastTimelineData = timeline;
    window.lastEventDistData = eventDist;

    const isLight = (document.documentElement.getAttribute('data-theme') !== 'dark');
    const labelColor = isLight ? '#475569' : '#94a3b8';
    const gridColor = isLight ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.05)';

    // 1. Biểu đồ đường Timeline
    const ctxTimeline = document.getElementById('chartTimeline');
    if (ctxTimeline) {
        const labels = timeline.map(t => t.date_val);
        const views = timeline.map(t => parseInt(t.views));
        const actions = timeline.map(t => parseInt(t.actions));

        if (chartTimelineInstance) chartTimelineInstance.destroy();
        chartTimelineInstance = new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: labels.length > 0 ? labels : ['Hôm nay'],
                datasets: [
                    {
                        label: 'Lượt Xem (Pageviews)',
                        data: views.length > 0 ? views : [0],
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.12)',
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Thao Tác (Actions / Purchases)',
                        data: actions.length > 0 ? actions : [0],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: labelColor, font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' } } } },
                scales: {
                    x: { ticks: { color: labelColor }, grid: { color: gridColor } },
                    y: { ticks: { color: labelColor }, grid: { color: gridColor }, beginAtZero: true }
                }
            }
        });
    }

    // 2. Biểu đồ tròn Event Distribution
    const ctxDist = document.getElementById('chartEventDist');
    if (ctxDist) {
        const distLabels = eventDist.map(e => e.event_code);
        const distCounts = eventDist.map(e => parseInt(e.count));

        if (chartEventDistInstance) chartEventDistInstance.destroy();
        chartEventDistInstance = new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: distLabels.length > 0 ? distLabels : ['Chưa có dữ liệu'],
                datasets: [{
                    data: distCounts.length > 0 ? distCounts : [1],
                    backgroundColor: ['#0284c7', '#f59e0b', '#8b5cf6', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: labelColor, boxWidth: 12, font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' } } } }
            }
        });
    }
}

function renderTopPages(pages) {
    const tbody = document.getElementById('topPagesTable');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!pages || pages.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center; color:var(--text-dim); padding:15px;">Chưa có lượt xem nào</td></tr>';
        return;
    }
    pages.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><code style="color:var(--primary); font-family:'JetBrains Mono', monospace; font-size:12px;">${p.page_url}</code></td>
            <td style="text-align: right; font-weight: 700; color: var(--text-main); font-family:'JetBrains Mono', monospace;">${Number(p.views).toLocaleString('vi-VN')}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderErrorsTable(errors, errorStats) {
    const lblCount = document.getElementById('lblErrorCount');
    if (lblCount && errorStats) {
        lblCount.innerText = errorStats.total_in_db ?? (errors ? errors.length : 0);
    }
    const tbody = document.getElementById('recentErrorsTable');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!errors || errors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--text-dim); padding:15px;">Không phát hiện lỗi tiếp nhận nào</td></tr>';
        return;
    }
    errors.forEach(err => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-family:'JetBrains Mono', monospace; color:var(--danger); font-size:12px;">#${err.id}</td>
            <td><span class="badge-error">${err.error_type}</span></td>
            <td style="color:var(--text-sub); font-size:12px;">${err.error_message}</td>
            <td style="color:var(--text-muted); font-size:11px; font-family:'JetBrains Mono', monospace;">${err.created_at}</td>
        `;
        tbody.appendChild(tr);
    });
}

// =========================================================================
// PHÂN TRANG & HIỂN THỊ NHẬT KÝ SỰ KIỆN THỜI GIAN THỰC (ĐÁNH SỐ TRANG 1 2 3)
// =========================================================================
function handleLiveSearchEvents(val) {
    currentSearchQuery = (val || '').trim().toLowerCase();
    eventsCurrentPage = 1;
    renderRecentEvents();
}

function setEventFilter(filterType) {
    currentEventFilter = filterType;
    eventsCurrentPage = 1;

    // Cập nhật giao diện nút bấm bộ lọc
    const filterBtns = document.querySelectorAll('.event-filter-btn');
    filterBtns.forEach(btn => {
        if (btn.getAttribute('data-filter') === filterType) {
            btn.classList.add('active');
            btn.style.background = 'var(--primary)';
            btn.style.color = '#fff';
            btn.style.borderColor = 'var(--primary)';
        } else {
            btn.classList.remove('active');
            btn.style.background = 'transparent';
            btn.style.color = 'var(--text-muted)';
            btn.style.borderColor = 'var(--card-border)';
        }
    });

    renderRecentEvents();
}

function renderRecentEvents(eventsParam) {
    if (eventsParam) {
        allRecentEventsData = eventsParam;
    }
    let rawEvents = allRecentEventsData || [];

    // Áp dụng bộ lọc thao tác nếu người dùng chọn
    let events = rawEvents;
    if (currentEventFilter === 'page_view') {
        events = rawEvents.filter(e => e.event_code === 'page_view');
    } else if (currentEventFilter === 'btn_click') {
        events = rawEvents.filter(e => e.event_code === 'btn_click');
    } else if (currentEventFilter === 'forms') {
        events = rawEvents.filter(e => e.event_code === 'form_start' || e.event_code === 'form_complete');
    } else if (currentEventFilter === 'simulation') {
        events = rawEvents.filter(e => (e.session_id && e.session_id.includes('sess_live_recon')) || (e.event_id && e.event_id.startsWith('evt_sim_')));
    }

    // Áp dụng tìm kiếm tức thì theo từ khóa (Món cafe, trà sữa, người dùng, URL, địa chỉ, ngày giờ, ID)
    if (currentSearchQuery) {
        const q = currentSearchQuery;
        events = events.filter(e => {
            return (e.element_text && e.element_text.toLowerCase().includes(q)) ||
                   (e.item_name && e.item_name.toLowerCase().includes(q)) ||
                   (e.customer_name && e.customer_name.toLowerCase().includes(q)) ||
                   (e.customer_address && e.customer_address.toLowerCase().includes(q)) ||
                   (e.page_url && e.page_url.toLowerCase().includes(q)) ||
                   (e.form_name && e.form_name.toLowerCase().includes(q)) ||
                   (e.event_code && e.event_code.toLowerCase().includes(q)) ||
                   (e.event_id && e.event_id.toLowerCase().includes(q)) ||
                   (e.client_time && e.client_time.toLowerCase().includes(q));
        });
    }

    const tbody = document.getElementById('recentEventsTable');
    if (!tbody) return;
    tbody.innerHTML = '';

    const totalCount = events.length;
    const totalPages = Math.max(1, Math.ceil(totalCount / eventsPerPage));

    // Đảm bảo trang hiện tại hợp lệ
    if (eventsCurrentPage > totalPages) {
        eventsCurrentPage = totalPages;
    }
    if (eventsCurrentPage < 1) {
        eventsCurrentPage = 1;
    }

    // Cập nhật thông tin số lượng và dải bản ghi
    const elRange = document.getElementById('pageRangeText');
    const elTotal = document.getElementById('totalEventsCount');
    if (elTotal) elTotal.innerText = totalCount;

    if (totalCount === 0) {
        if (elRange) elRange.innerText = '0 - 0';
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center; color:var(--text-muted); padding:35px 20px;">
                    <div style="font-size: 26px; margin-bottom: 6px;">🟢</div>
                    <strong style="color: var(--primary); font-size: 14px;">Không có sự kiện nào phù hợp với bộ lọc hiện tại</strong>
                    <p style="font-size: 12px; color: var(--text-dim); margin-top: 5px;">
                        Bạn có thể chọn tab <strong>"Tất Cả"</strong> hoặc bấm nút <strong>"🚀 Chạy Thử Toàn Chuỗi"</strong> phía trên để tạo chuỗi thao tác mới.
                    </p>
                </td>
            </tr>
        `;
        renderPaginationControls(1);
        return;
    }

    const startIdx = (eventsCurrentPage - 1) * eventsPerPage;
    const endIdx = Math.min(startIdx + eventsPerPage, totalCount);
    if (elRange) {
        elRange.innerText = `${startIdx + 1} - ${endIdx}`;
    }

    // Cắt mảng sự kiện của trang hiện tại
    const pageEvents = events.slice(startIdx, endIdx);

    pageEvents.forEach(e => {
        let badgeColor = '#0284c7';
        let badgeLabel = 'Xem Trang';
        if (e.event_code === 'btn_click') { badgeColor = '#d97706'; badgeLabel = 'Bấm Nút'; }
        if (e.event_code === 'form_start') { badgeColor = '#7c3aed'; badgeLabel = 'Mở Form'; }
        if (e.event_code === 'form_complete') { badgeColor = '#059669'; badgeLabel = 'Nộp Form'; }

        // Nhận diện nhãn chi tiết cho thao tác giỏ hàng và sản phẩm
        const itemStr = (e.item_name || '').toLowerCase();
        const elemStr = (e.element_text || '').toLowerCase();
        const catStr = (e.category || '').toLowerCase();

        let extraIcon = '📝';
        if (itemStr.includes('gio_hang') || itemStr.includes('open_cart') || catStr === 'cart' || elemStr.includes('giỏ')) {
            badgeColor = '#059669';
            badgeLabel = '🛒 Giỏ Hàng';
            extraIcon = '🛒';
        } else if (catStr === 'coffee' || catStr === 'milktea' || catStr === 'espresso' || catStr === 'signature' || catStr === 'coldbrew' || catStr === 'tea' || itemStr.startsWith('cf_')) {
            badgeColor = '#b45309';
            badgeLabel = '☕ Sản Phẩm';
            extraIcon = '☕';
        } else if (catStr === 'document' || elemStr.includes('tải brochure')) {
            badgeColor = '#0284c7';
            badgeLabel = '📥 Tải Brochure';
            extraIcon = '📥';
        }

        const formDisplayName = e.form_name || (e.event_code.startsWith('form') ? 'Biểu Mẫu Tương Tác' : 'Khu Vực Trang');
        const custName = e.customer_name || 'Khách Hàng Trực Tuyến';
        const custAddr = e.customer_address || 'Số 12 Chùa Bộc, Hà Nội';
        const isFemale = (e.user_gender === 'female');

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <code style="font-family:'JetBrains Mono', monospace; color:var(--primary); font-size:11.5px; font-weight:700;">${e.event_id}</code>
                <div style="font-family:'JetBrains Mono', monospace; color:var(--text-dim); font-size:10px;">${e.session_id}</div>
            </td>
            <td>
                <span style="background:${badgeColor}15; color:${badgeColor}; border:1px solid ${badgeColor}35; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px; display:inline-block; white-space:nowrap;">
                    ${badgeLabel} (${e.event_code})
                </span>
            </td>
            <td>
                <strong style="color:#b45309; font-size:12px; display:block;">${extraIcon} ${formDisplayName}</strong>
                <span style="color:var(--text-muted); font-size:11px;">${e.page_url}</span>
            </td>
            <td>
                <strong style="color:var(--text-main); font-size:12.5px; display:block;">
                    ${isFemale ? '👧' : '👦'} ${custName}
                </strong>
                <span style="font-size:11px; color:${isFemale ? '#db2777' : '#d97706'}; font-weight:600;">
                    ${isFemale ? 'Phái đẹp (Ưa Trà Sữa)' : 'Phái mạnh (Ưa Cà Phê)'}
                </span>
            </td>
            <td>
                <div style="color:var(--text-sub); font-size:12px; font-weight:500;">📍 ${custAddr}</div>
            </td>
            <td>
                <div style="color:var(--text-main); font-size:12px; font-weight:600;">${e.element_text || e.item_name || '-'}</div>
            </td>
            <td style="text-align: right;">
                <span style="color:var(--text-muted); font-size:11.5px; font-family:'JetBrains Mono', monospace; font-weight:600;">${e.client_time.slice(11)}</span>
                <div style="font-size:10.5px; color:var(--text-dim);">${e.client_time.slice(0, 10)}</div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Render các nút số trang
    renderPaginationControls(totalPages);
}

// Hàm render các nút bấm phân trang (1 2 3 ...)
function renderPaginationControls(totalPages) {
    const ctrl = document.getElementById('eventsPaginationControls');
    if (!ctrl) return;
    ctrl.innerHTML = '';

    if (totalPages <= 1) {
        ctrl.innerHTML = `<button type="button" class="page-btn active" disabled>1</button>`;
        return;
    }

    // Nút « Trước
    const prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '&laquo; Trước';
    prevBtn.disabled = (eventsCurrentPage === 1);
    prevBtn.onclick = () => goToEventsPage(eventsCurrentPage - 1);
    ctrl.appendChild(prevBtn);

    // Tính toán dải số trang cần hiển thị
    let startPage = Math.max(1, eventsCurrentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
        const btn1 = document.createElement('button');
        btn1.type = 'button';
        btn1.className = 'page-btn';
        btn1.innerText = '1';
        btn1.onclick = () => goToEventsPage(1);
        ctrl.appendChild(btn1);

        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.style.cssText = 'color: var(--text-muted); padding: 0 4px;';
            dots.innerText = '...';
            ctrl.appendChild(dots);
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        const pageBtn = document.createElement('button');
        pageBtn.type = 'button';
        pageBtn.className = 'page-btn' + (p === eventsCurrentPage ? ' active' : '');
        pageBtn.innerText = p;
        pageBtn.onclick = () => goToEventsPage(p);
        ctrl.appendChild(pageBtn);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dots = document.createElement('span');
            dots.style.cssText = 'color: var(--text-muted); padding: 0 4px;';
            dots.innerText = '...';
            ctrl.appendChild(dots);
        }

        const btnLast = document.createElement('button');
        btnLast.type = 'button';
        btnLast.className = 'page-btn';
        btnLast.innerText = totalPages;
        btnLast.onclick = () => goToEventsPage(totalPages);
        ctrl.appendChild(btnLast);
    }

    // Nút Sau »
    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = 'Tiếp &raquo;';
    nextBtn.disabled = (eventsCurrentPage === totalPages);
    nextBtn.onclick = () => goToEventsPage(eventsCurrentPage + 1);
    ctrl.appendChild(nextBtn);
}

// Chuyển trang trực tiếp khi người dùng bấm nút trang 1, 2, 3
function goToEventsPage(pageNumber) {
    eventsCurrentPage = pageNumber;
    renderRecentEvents();
}

// =========================================================================
// CÁC HÀM TẠO LỖI TRỰC TIẾP TRÊN GIAO DIỆN ĐỂ THẦY CÔ KIỂM TRA
// =========================================================================

// 1. Bắn lỗi Xung Đột CONFLICT_PAYLOAD (HTTP 409)
function testConflictLive() {
    const uniqueId = 'evt_live_' + Date.now();
    const siteKey = (currentSiteId === 1) ? 'site_cafe_tea_01' : 'site_eduportal_02';

    // Bước 1: Gửi sự kiện gốc
    fetch('api/track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            site_key: siteKey,
            session_id: 'sess_live_test',
            event_id: uniqueId,
            event_code: 'page_view',
            page_url: '/demo/cafe_shop/index.php'
        })
    })
    .then(() => {
        // Bước 2: Gửi lại cùng mã ID nhưng thay đổi nội dung (sửa thành btn_click)
        return fetch('api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_key: siteKey,
                session_id: 'sess_live_test',
                event_id: uniqueId,
                event_code: 'btn_click', // Đổi nội dung!
                page_url: '/demo/cafe_shop/checkout.php'
            })
        });
    })
    .then(async res => {
        const data = await res.json();
        alert(`🚨 KẾT QUẢ TỪ SERVER (HTTP ${res.status}):\n- Loại lỗi: ${data.status}\n- Chi tiết: ${data.error}\n\n-> Đã ghi 1 bản ghi CONFLICT_PAYLOAD mới vào bảng Ingestion Errors!`);
        loadDashboardData();
    });
}

// 2. Bắn lỗi Vi Phạm Trường Cấm FORBIDDEN_INPUT_DATA (HTTP 400)
function testForbiddenLive() {
    const siteKey = (currentSiteId === 1) ? 'site_cafe_tea_01' : 'site_eduportal_02';
    fetch('api/track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            site_key: siteKey,
            session_id: 'sess_live_test',
            event_id: 'evt_fbd_' + Date.now(),
            event_code: 'btn_click',
            page_url: '/demo/cafe_shop/checkout.php',
            password: 'mat_khau_nguoi_dung_123456' // Trường cấm!
        })
    })
    .then(async res => {
        const data = await res.json();
        alert(`🚨 KẾT QUẢ TỪ SERVER (HTTP ${res.status}):\n- Loại lỗi: ${data.status}\n- Chi tiết: ${data.error}\n\n-> Đã ghi 1 bản ghi FORBIDDEN_INPUT_DATA mới vào bảng Ingestion Errors!`);
        loadDashboardData();
    });
}

// 3. Bắn lỗi Website Lạ INVALID_SITE (HTTP 403)
function testInvalidSiteLive() {
    fetch('api/track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            site_key: 'site_hacker_la_mat',
            session_id: 'sess_live_test',
            event_id: 'evt_inv_' + Date.now(),
            event_code: 'page_view',
            page_url: '/trang-ngoai'
        })
    })
    .then(async res => {
        const data = await res.json();
        alert(`🚨 KẾT QUẢ TỪ SERVER (HTTP ${res.status}):\n- Loại lỗi: ${data.status}\n- Chi tiết: ${data.error}\n\n-> Đã ghi 1 bản ghi INVALID_SITE mới vào bảng Ingestion Errors!`);
        loadDashboardData();
    });
}

// Xóa dữ liệu thử nghiệm
function purgeData() {
    if (!confirm('Bạn có chắc chắn muốn dọn dẹp và làm sạch dữ liệu thử nghiệm của website này theo quy tắc dọn dẹp không?')) return;
    fetch(`api/reports.php?website_id=${currentSiteId}&user_role=${currentRole}&action=purge_test_data`)
        .then(res => res.json())
        .then(res => {
            alert(res.message || res.error);
            loadDashboardData();
        });
}

// Sinh kịch bản 200 sự kiện chuẩn
function generateScenarios() {
    if (!confirm('Tạo kịch bản 200 sự kiện chuẩn để đối chiếu với đáp án Ground Truth?')) return;
    fetch('api/test_scenarios.php')
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            loadDashboardData();
        });
}

function openGuideModal() {
    const modal = document.getElementById('guideModal');
    if (modal) modal.style.display = 'flex';
}

function closeGuideModal() {
    const modal = document.getElementById('guideModal');
    if (modal) modal.style.display = 'none';
}

// Chức năng 1 (Đề tài 17): Đăng ký website thử & cấu hình sự kiện
function openRegisterSiteModal() {
    const modal = document.getElementById('registerSiteModal');
    if (modal) {
        modal.style.display = 'flex';
        loadRegisteredWebsites();
    }
}

function closeRegisterSiteModal() {
    const modal = document.getElementById('registerSiteModal');
    if (modal) modal.style.display = 'none';
}

function loadRegisteredWebsites() {
    fetch('api/register_site.php')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'SUCCESS' && Array.isArray(res.data)) {
                // 1. Cập nhật dropdown selectWebsite
                const select = document.getElementById('selectWebsite');
                if (select) {
                    const savedVal = select.value;
                    select.innerHTML = res.data.map(s => `<option value="${s.id}">${s.name} (${s.site_key})</option>`).join('');
                    if (savedVal) select.value = savedVal;
                }

                // 2. Cập nhật bảng trong modal
                const tbody = document.getElementById('registeredSitesTableBody');
                if (tbody) {
                    tbody.innerHTML = res.data.map(s => {
                        const evts = (s.events || []).map(e => `<span class="gender-badge" style="background: rgba(2,132,199,0.12); color: var(--primary); font-size: 11px; margin-right: 4px;">${e.event_code}</span>`).join('');
                        return `
                            <tr>
                                <td style="font-weight: 700; color: var(--text-main);">#${s.id}</td>
                                <td style="color: var(--text-main); font-weight: 600;">${s.name}</td>
                                <td style="font-family: 'JetBrains Mono', monospace; color: var(--primary); font-weight: 600;">${s.site_key}</td>
                                <td style="font-size: 12px; color: var(--text-muted);">${s.allowed_domains}</td>
                                <td>${evts || '<span style="color:var(--text-dim);">Mặc định</span>'}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }
        })
        .catch(err => console.warn('Lỗi tải danh sách website:', err));
}

function submitRegisterSite(e) {
    e.preventDefault();
    const name = document.getElementById('newSiteName').value.trim();
    const siteKey = document.getElementById('newSiteKey').value.trim();
    const domains = document.getElementById('newSiteDomains').value.trim();

    const events = [];
    if (document.getElementById('chkEvtPv')?.checked) events.push('page_view');
    if (document.getElementById('chkEvtBtn')?.checked) events.push('btn_click');
    if (document.getElementById('chkEvtFs')?.checked) events.push('form_start');
    if (document.getElementById('chkEvtFc')?.checked) events.push('form_complete');

    if (!name || !siteKey) {
        alert('Vui lòng nhập tên website và mã site_key');
        return;
    }

    fetch('api/register_site.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: name,
            site_key: siteKey,
            allowed_domains: domains,
            events: events
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'SUCCESS') {
            alert('🎉 ' + data.message + '\n\nWebsite [' + name + '] đã sẵn sàng nhận sự kiện!');
            document.getElementById('newSiteName').value = '';
            document.getElementById('newSiteKey').value = '';
            loadRegisteredWebsites();
        } else {
            alert('❌ ' + (data.message || 'Lỗi đăng ký website'));
        }
    })
    .catch(err => alert('Lỗi kết nối: ' + err));
}

// =========================================================================
// XUẤT BÁO CÁO ĐA ĐỊNH DẠNG: CSV, EXCEL (.XLS), PDF (CHỨC NĂNG 5)
// =========================================================================
function toggleExportMenu(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('exportDropdownMenu');
    if (!menu) return;
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportDropdownMenu');
    if (menu && menu.style.display === 'block' && !e.target.closest('.dropdown-export')) {
        menu.style.display = 'none';
    }
});

function triggerExport(format) {
    const menu = document.getElementById('exportDropdownMenu');
    if (menu) menu.style.display = 'none';
    const url = `export.php?website_id=${currentSiteId}&format=${format}`;
    window.open(url, '_blank');
}

// =========================================================================
// QUY TẮC GIỮ / XÓA DỮ LIỆU THỬ & RETENTION (CHỨC NĂNG 5)
// =========================================================================
function openRetentionModal() {
    const modal = document.getElementById('retentionModal');
    if (modal) {
        modal.classList.add('active');
        loadRetentionInfo();
    }
}

function closeRetentionModal() {
    const modal = document.getElementById('retentionModal');
    if (modal) modal.classList.remove('active');
}

function loadRetentionInfo() {
    fetch(`api/reports.php?website_id=${currentSiteId}&user_role=${currentRole}&action=get_retention`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'SUCCESS') {
                const days = data.retention_days;
                const sel = document.getElementById('selRetentionDays');
                if (sel) sel.value = days;
                const lblActive = document.getElementById('lblActiveRetentionDays');
                if (lblActive) lblActive.innerText = (days > 0) ? days : 'Vĩnh viễn';
                const lblCutoff = document.getElementById('lblRetentionCutoff');
                if (lblCutoff) lblCutoff.innerText = (days > 0) ? `Trước ${data.cutoff_time}` : 'Không áp dụng mốc cắt';
                const lblExpired = document.getElementById('lblExpiredEventCount');
                if (lblExpired) lblExpired.innerText = (data.expired_events || 0);
            }
        })
        .catch(err => console.error('Lỗi tải thông tin retention:', err));
}

function saveRetentionSetting() {
    const sel = document.getElementById('selRetentionDays');
    if (!sel) return;
    const days = sel.value;
    fetch(`api/reports.php?website_id=${currentSiteId}&user_role=${currentRole}&action=set_retention&days=${days}`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'SUCCESS') {
            alert('✅ Đã lưu quy tắc lưu trữ: ' + (days > 0 ? `Giữ ${days} ngày gần nhất` : 'Lưu trữ vĩnh viễn'));
            loadRetentionInfo();
        } else {
            alert('❌ ' + (data.error || 'Lỗi lưu quy tắc'));
        }
    })
    .catch(err => alert('Lỗi kết nối: ' + err));
}

function executeRetentionCleanup() {
    const sel = document.getElementById('selRetentionDays');
    const days = sel ? sel.value : 30;
    if (!confirm(`Bạn có chắc muốn thực thi dọn dẹp các sự kiện cũ hơn ${days} ngày theo quy tắc lưu trữ không?`)) return;

    fetch(`api/reports.php?website_id=${currentSiteId}&user_role=${currentRole}&action=cleanup_retention&days=${days}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'SUCCESS') {
                alert(`🧹 Dọn dẹp hoàn tất!\n• Đã xóa: ${data.deleted_events} sự kiện quá hạn\n• Mốc cắt: ${data.cutoff_time}`);
                loadRetentionInfo();
                loadDashboardData();
            } else if (data.status === 'SKIPPED') {
                alert(`ℹ️ ${data.message}`);
            } else {
                alert('❌ ' + (data.error || 'Lỗi thực thi dọn dẹp'));
            }
        })
        .catch(err => alert('Lỗi: ' + err));
}

function testCronJobLive() {
    fetch('cron_cleanup.php?force=1')
        .then(res => res.json())
        .then(data => {
            alert(`🤖 KẾT QUẢ TEST CRON JOB:\n• Thực thi lúc: ${data.executed_at}\n• Quy tắc: Giữ ${data.retention_days} ngày\n• Sự kiện xóa: ${data.deleted_events}\n• Thời gian xử lý: ${data.execution_time_ms} ms\n• Trạng thái: ${data.status}`);
            loadRetentionInfo();
            loadDashboardData();
        })
        .catch(err => alert('Lỗi chạy cron: ' + err));
}

// ==========================================
// PHÂN TRANG VÀ TÌM KIẾM CHO BẢNG ĐỐI CHIẾU SỐ THAO TÁC (SECTION B - ĐỀ TÀI 17)
// ==========================================
let reconCurrentPage = 1;
let reconPerPage = 5; // Mặc định 5 thao tác / trang theo đúng yêu cầu
let reconSearchQuery = '';

function handleReconSearch(query) {
    reconSearchQuery = (query || '').trim().toLowerCase();
    reconCurrentPage = 1;
    renderReconciliationTable();
}

function changeReconPerPage(val) {
    reconPerPage = parseInt(val, 10) || 2;
    reconCurrentPage = 1;
    renderReconciliationTable();
}

function goToReconPage(page) {
    reconCurrentPage = page;
    renderReconciliationTable();
}

function renderReconciliationTable() {
    const rows = Array.from(document.querySelectorAll('#reconTableBody tr.recon-row'));
    if (!rows || rows.length === 0) return;

    // Lọc theo từ khóa tìm kiếm
    const matchedRows = rows.filter(row => {
        if (!reconSearchQuery) return true;
        const searchText = (row.getAttribute('data-search') || '') + ' ' + (row.innerText || '');
        return searchText.toLowerCase().includes(reconSearchQuery);
    });

    const totalMatched = matchedRows.length;
    const totalPages = Math.ceil(totalMatched / reconPerPage) || 1;

    if (reconCurrentPage > totalPages) {
        reconCurrentPage = totalPages;
    }
    if (reconCurrentPage < 1) {
        reconCurrentPage = 1;
    }

    const startIdx = (reconCurrentPage - 1) * reconPerPage;
    const endIdx = startIdx + reconPerPage;

    // Ẩn toàn bộ và chỉ hiện các dòng của trang hiện tại
    rows.forEach(r => r.style.display = 'none');

    matchedRows.forEach((r, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            r.style.display = '';
        }
    });

    // Cập nhật thông tin dòng hiển thị
    const rangeText = document.getElementById('reconPageRangeText');
    const totalText = document.getElementById('reconTotalCount');
    if (rangeText) {
        if (totalMatched === 0) {
            rangeText.innerText = '0 - 0';
        } else {
            rangeText.innerText = `${startIdx + 1} - ${Math.min(endIdx, totalMatched)}`;
        }
    }
    if (totalText) {
        totalText.innerText = totalMatched;
    }

    // Render các nút số trang 1 2 3
    renderReconPaginationControls(totalPages);
}

function renderReconPaginationControls(totalPages) {
    const ctrl = document.getElementById('reconPaginationControls');
    if (!ctrl) return;
    ctrl.innerHTML = '';

    if (totalPages <= 1) {
        ctrl.innerHTML = `<button type="button" class="page-btn active" style="min-width:32px; height:32px; padding:0 8px; border-radius:6px; font-weight:700; font-size:12px; cursor:default; background:var(--primary); color:#fff; border:1px solid var(--primary);" disabled>1</button>`;
        return;
    }

    // Nút « Trước
    const prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '&laquo; Trước';
    prevBtn.style.cssText = 'min-width:55px; height:32px; padding:0 10px; border-radius:6px; font-weight:700; font-size:12px; cursor:pointer; background:rgba(255,255,255,0.06); color:var(--text-main); border:1px solid var(--card-border); transition:0.2s;';
    prevBtn.disabled = (reconCurrentPage === 1);
    if (reconCurrentPage === 1) {
        prevBtn.style.opacity = '0.4';
        prevBtn.style.cursor = 'not-allowed';
    }
    prevBtn.onclick = () => goToReconPage(reconCurrentPage - 1);
    ctrl.appendChild(prevBtn);

    // Các nút số trang 1, 2, 3...
    for (let p = 1; p <= totalPages; p++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'page-btn' + (p === reconCurrentPage ? ' active' : '');
        btn.innerText = p;
        btn.style.cssText = `min-width:34px; height:32px; padding:0 8px; border-radius:6px; font-weight:800; font-size:12px; cursor:pointer; border:1px solid ${p === reconCurrentPage ? 'var(--primary)' : 'var(--card-border)'}; background:${p === reconCurrentPage ? 'var(--primary)' : 'rgba(255,255,255,0.06)'}; color:${p === reconCurrentPage ? '#fff' : 'var(--text-main)'}; transition:0.2s;`;
        
        const thisPage = p;
        btn.onclick = () => goToReconPage(thisPage);
        ctrl.appendChild(btn);
    }

    // Nút Tiếp »
    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = 'Tiếp &raquo;';
    nextBtn.style.cssText = 'min-width:55px; height:32px; padding:0 10px; border-radius:6px; font-weight:700; font-size:12px; cursor:pointer; background:rgba(255,255,255,0.06); color:var(--text-main); border:1px solid var(--card-border); transition:0.2s;';
    nextBtn.disabled = (reconCurrentPage === totalPages);
    if (reconCurrentPage === totalPages) {
        nextBtn.style.opacity = '0.4';
        nextBtn.style.cursor = 'not-allowed';
    }
    nextBtn.onclick = () => goToReconPage(reconCurrentPage + 1);
    ctrl.appendChild(nextBtn);
}

// Khởi chạy phân trang cho Bảng đối chiếu ngay khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        renderReconciliationTable();
    }, 100);
});


