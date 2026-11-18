/**
 * assets/js/tracker.js
 * Đề tài 17: SDK Theo dõi lượt truy cập và thao tác trên website
 * Tiệm Trà Sữa & Cà Phê Milky Corner
 */
(function() {
    'use strict';

    // 1. Lấy cấu hình từ thẻ script
    const currentScript = document.currentScript || (function() {
        const scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();

    const siteKey = currentScript.getAttribute('data-site-key') || 'site_cafe_tea_01';
    const apiEndpoint = currentScript.getAttribute('data-endpoint') || '../../api/track.php';

    // 2. Khởi tạo hoặc lấy định danh phiên (session_id)
    function getSessionId() {
        let sess = sessionStorage.getItem('analytics_sess_id');
        if (!sess) {
            sess = 'sess_' + Math.random().toString(36).substring(2, 10) + '_' + Date.now().toString(36);
            sessionStorage.setItem('analytics_sess_id', sess);
        }
        return sess;
    }

    // 3. Lấy giới tính người dùng hiện tại (nếu đã chọn/đăng ký)
    function getUserGender() {
        return localStorage.getItem('user_gender') || 'male'; // mặc định nam
    }

    // 4. Hàm sinh UUID ngẫu nhiên cho từng sự kiện
    function generateEventId() {
        return 'evt_' + Math.random().toString(36).substring(2, 9) + '_' + Date.now().toString(36);
    }

    // 5. Hàm gửi sự kiện tới API PHP
    function sendEvent(eventCode, extraData = {}) {
        const custName = extraData.customer_name || localStorage.getItem('user_name') || localStorage.getItem('cafe_customer_name') || '';
        const custAddr = extraData.customer_address || localStorage.getItem('user_address') || localStorage.getItem('cafe_customer_address') || '';
        const formName = extraData.form_name || (eventCode.startsWith('form') ? 'Biểu Mẫu Tương Tác Trực Tuyến' : '');

        const payload = {
            site_key: siteKey,
            session_id: getSessionId(),
            event_id: extraData.event_id || generateEventId(),
            event_code: eventCode,
            page_url: window.location.pathname,
            element_text: extraData.element_text || document.title,
            category: extraData.category || 'general',
            item_name: extraData.item_name || '',
            user_gender: getUserGender(),
            customer_name: custName,
            customer_address: custAddr,
            form_name: formName,
            client_time: new Date().toISOString().slice(0, 19).replace('T', ' ')
        };

        // BẢO VỆ CHÍNH SÁCH BẢO MẬT: Không bao giờ đính kèm mật khẩu hoặc giá trị form nhập
        fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(res => res.json())
          .then(data => {
              console.log('[Tracker SDK]', eventCode, data);
          }).catch(err => {
              console.warn('[Tracker SDK Error]', err);
          });
    }

    // 6. Tự động ghi nhận lượt xem trang (page_view) khi tải trang
    window.addEventListener('DOMContentLoaded', function() {
        sendEvent('page_view', {
            element_text: document.title
        });

        // Tự động gắn bộ lắng nghe thao tác bấm nút (button_click / btn_click)
        // Đáp ứng chính xác yêu cầu đề tài: Bấm nút "Tải tài liệu PDF", "Thêm vào giỏ", hoặc "Đăng ký tư vấn"
        document.body.addEventListener('click', function(e) {
            const trackBtn = e.target.closest('[data-track-click], .btn-track, button, a.btn, a[href$=".pdf"], .btn-download, .btn-noir-primary, .btn-noir-ghost, .btn-order-add, .cart-trigger-btn, .btn-consult-trigger, .btn-reserve-trigger');
            if (trackBtn) {
                const elemText = (trackBtn.innerText || trackBtn.textContent || trackBtn.getAttribute('title') || trackBtn.getAttribute('aria-label') || '').trim().substring(0, 80);
                const category = trackBtn.getAttribute('data-category') || 'button_interaction';
                let itemName = trackBtn.getAttribute('data-item') || trackBtn.id || '';
                const evtCode = trackBtn.getAttribute('data-track-click') || 'btn_click';

                // Tự động gán nhãn nhận diện cho 3 nút mẫu trọng tâm của Đề tài 17:
                const lowerText = elemText.toLowerCase();
                if (lowerText.includes('pdf') || lowerText.includes('tài liệu') || (trackBtn.href && trackBtn.href.toLowerCase().includes('.pdf'))) {
                    if (!itemName) itemName = 'tai_lieu_pdf';
                } else if (lowerText.includes('thêm vào giỏ') || lowerText.includes('chọn món') || trackBtn.classList.contains('btn-order-add')) {
                    if (!itemName) itemName = 'them_vao_gio';
                } else if (lowerText.includes('tư vấn') || lowerText.includes('đặt chỗ') || lowerText.includes('giữ bàn')) {
                    if (!itemName) itemName = 'dang_ky_tu_van';
                }

                sendEvent(evtCode, {
                    element_text: elemText || 'Bấm nút tương tác',
                    category: category,
                    item_name: itemName
                });
            }
        });

        // Tự động lắng nghe sự kiện bắt đầu điền biểu mẫu (form_start)
        const forms = document.querySelectorAll('form, .checkout-form');
        forms.forEach(f => {
            let started = false;
            f.addEventListener('focusin', function() {
                if (!started) {
                    started = true;
                    sendEvent('form_start', {
                        element_text: 'Bắt đầu tương tác biểu mẫu: ' + (f.id || 'form_order')
                    });
                }
            });
        });
    });

    // Xuất hàm toàn cục để các trang có thể chủ động kích hoạt nếu cần
    window.AnalyticsTracker = {
        track: sendEvent,
        getSessionId: getSessionId,
        getUserGender: getUserGender
    };
})();
