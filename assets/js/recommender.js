/**
 * assets/js/recommender.js
 * Đề tài 17: Bộ máy gợi ý thông minh dựa trên hành vi Giới Tính (Nam -> Cà Phê, Nữ -> Trà Sữa)
 * Giao diện Sáng Màu (Light Theme) Tươi Tắn & Bắt buộc Đăng nhập giới tính khi vừa vào web
 */
const RecommenderEngine = {
    currentGender: localStorage.getItem('user_gender') || 'male',
    userName: localStorage.getItem('user_name') || 'Khách Quý',
    selectedModalGender: localStorage.getItem('user_gender') || 'male',

    init: function() {
        this.injectModalIfMissing();
        this.updateUserProfileUI();
        this.fetchAndRenderRecommendations();
        this.updateCartBadge();

        // BẮT BUỘC: Nếu trong phiên này chưa chọn giới tính -> Hiện modal ngay lập tức
        const hasChosenInSession = sessionStorage.getItem('gender_selected_session');
        const hasAuth = localStorage.getItem('user_gender_authenticated');

        if (!hasChosenInSession || !hasAuth) {
            setTimeout(() => {
                this.openGenderModal(true);
            }, 250);
        }
    },

    openGenderModal: function(isMandatory = false) {
        this.injectModalIfMissing();
        const overlay = document.getElementById('genderModalOverlay');
        if (overlay) {
            overlay.setAttribute('data-mandatory', isMandatory ? 'true' : 'false');
            overlay.classList.add('active');
            overlay.style.display = 'flex';

            const nameInput = document.getElementById('modalUserNameInput');
            if (nameInput) {
                nameInput.value = (this.userName && this.userName !== 'Khách Quý') ? this.userName : '';
            }
            this.selectModalGender(this.currentGender);
        }
    },

    closeGenderModal: function() {
        const overlay = document.getElementById('genderModalOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.style.display = 'none';
        }
    },

    updateUserProfileUI: function() {
        const userStatusWrap = document.getElementById('userProfileNavBadge');
        if (userStatusWrap) {
            const isFemale = (this.currentGender === 'female');
            const iconBadge = document.getElementById('userPersonaIcon');
            const nameBadge = document.getElementById('userPersonaName');
            if (iconBadge) iconBadge.innerText = isFemale ? '🌸' : '☕';
            if (nameBadge) nameBadge.innerText = this.userName || 'Khách Quý';
        }
    },

    fetchAndRenderRecommendations: function() {
        const container = document.getElementById('recommendationContainer');
        if (!container) return;

        container.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: var(--c-warm-beige, #D6C3A6);">
                <div style="font-size: 32px; animation: spin 1s linear infinite; display: inline-block;">☕</div>
                <p style="margin-top: 12px; font-size: 0.9rem; letter-spacing: 0.1em; text-transform: uppercase;">Đang phân tích hành vi và đề xuất món chuẩn gu...</p>
            </div>
            <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
        `;

        fetch(`../../api/recommendations.php?gender=${this.currentGender}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'SUCCESS') {
                    this.renderUI(data);
                }
            })
            .catch(err => {
                console.error('Lỗi khi tải gợi ý món:', err);
            });
    },

    confirmGenderLogin: async function(event) {
        if (event) event.preventDefault();

        const nameInput = document.getElementById('modalUserNameInput');
        const contactInput = document.getElementById('modalUserContactInput');
        const noteInput = document.getElementById('modalUserNoteInput');

        let enteredName = nameInput ? nameInput.value.trim() : '';
        const enteredContact = contactInput ? contactInput.value.trim() : '';
        const enteredNote = noteInput ? noteInput.value.trim() : '';

        const chosenGender = this.selectedModalGender || 'male';
        if (!enteredName) {
            enteredName = (chosenGender === 'female') ? 'Khách Nữ Quý Phái' : 'Khách Nam Lịch Lãm';
        }

        // Lấy các nốt hương được chọn
        const selectedNotes = Array.from(document.querySelectorAll('.modal-note-chip.selected'))
            .map(c => c.innerText)
            .join(', ');

        this.currentGender = chosenGender;
        this.userName = enteredName;

        // 1. Lưu CSDL
        try {
            await fetch('../../api/register_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    fullname: enteredName,
                    email: enteredContact || `${enteredName.toLowerCase().replace(/\s+/g, '')}@noircafe.vn`,
                    gender: chosenGender,
                    role: 'customer'
                })
            });
        } catch (err) {
            console.warn('Lỗi gọi API đăng ký:', err);
        }

        // 2. Lưu Storage
        localStorage.setItem('user_gender', chosenGender);
        localStorage.setItem('user_name', enteredName);
        localStorage.setItem('cafe_customer_name', enteredName);
        if (enteredContact) localStorage.setItem('user_contact', enteredContact);
        localStorage.setItem('user_gender_authenticated', 'true');
        sessionStorage.setItem('gender_selected_session', 'true');

        // 3. Track sự kiện
        if (window.AnalyticsTracker) {
            window.AnalyticsTracker.track('form_complete', {
                form_name: 'Khởi Tạo Thẻ Thành Viên NOIR Tại Chỗ',
                customer_name: enteredName,
                element_text: `Đăng ký Club Noir: ${enteredName} (${chosenGender === 'female' ? 'Nữ' : 'Nam'}) | Nốt hương: ${selectedNotes}`,
                category: 'auth',
                item_name: 'register_' + chosenGender
            });
        }

        this.closeGenderModal();
        this.updateUserProfileUI();
        this.fetchAndRenderRecommendations();

        const greeting = (chosenGender === 'female') 
            ? `🌸 Chào mừng ${enteredName}! Đã kích hoạt thực đơn Brown Butter Latte & Trà thanh mượt chuẩn gu!` 
            : `☕ Chào mừng ${enteredName}! Đã kích hoạt thực đơn Espresso mộc & Cold Brew ủ chậm chuẩn gu!`;
        this.showToast(greeting);
    },

    selectModalGender: function(gender) {
        this.selectedModalGender = gender;
        const cardMale = document.getElementById('modalCardMale');
        const cardFemale = document.getElementById('modalCardFemale');
        const previewText = document.getElementById('modalRecPreviewText');

        if (cardMale && cardFemale) {
            if (gender === 'female') {
                cardFemale.classList.add('active-female');
                cardFemale.classList.remove('inactive');
                cardMale.classList.remove('active-male');
                cardMale.classList.add('inactive');
                if (previewText) {
                    previewText.innerHTML = `Đề xuất ưu tiên: <strong style="color:#d977a6;">Brown Butter Latte béo ngậy, Classic Matcha Uji & Trà Sữa thanh mượt</strong>.`;
                }
            } else {
                cardMale.classList.add('active-male');
                cardMale.classList.remove('inactive');
                cardFemale.classList.remove('active-female');
                cardFemale.classList.add('inactive');
                if (previewText) {
                    previewText.innerHTML = `Đề xuất ưu tiên: <strong style="color:var(--c-amber);">Noir Espresso đậm đà, Sea Salt Cold Brew ủ chậm 18h & Cà Phê Mộc</strong>.`;
                }
            }
        }
    },

    injectModalIfMissing: function() {
        if (document.getElementById('genderModalOverlay')) return;

        const modalDiv = document.createElement('div');
        modalDiv.id = 'genderModalOverlay';
        modalDiv.className = 'gender-modal-overlay noir-member-modal-overlay';
        modalDiv.innerHTML = `
            <div class="gender-modal-card noir-member-modal-card">
                <button type="button" class="modal-close-noir-btn" onclick="RecommenderEngine.closeGenderModal()" aria-label="Đóng">&times;</button>
                
                <div style="text-align: center; margin-bottom: 24px; border-bottom: 1px solid rgba(242, 233, 218, 0.12); padding-bottom: 18px;">
                    <span style="font-size: 0.72rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--c-amber); font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                        <span>✦</span> CLUB NOIR MEMBERSHIP · ĐỀ TÀI 17
                    </span>
                    <h2 style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: clamp(1.8rem, 3.5vw, 2.3rem); color: #FAF6F0; margin-bottom: 6px; font-weight: 400;">
                        Gia Nhập <em style="font-style: italic; color: var(--c-warm-beige);">Club Noir</em>
                    </h2>
                    <p style="font-size: 0.88rem; color: rgba(242, 233, 218, 0.72); line-height: 1.55; max-width: 480px; margin: 0 auto; font-weight: 300;">
                        Đăng ký nhanh thẻ khách quý để hệ thống AI nhận diện gu thưởng thức, cá nhân hóa thực đơn và dành riêng cho bạn những nốt hương tinh tế nhất.
                    </p>
                </div>

                <form onsubmit="RecommenderEngine.confirmGenderLogin(event)">
                    <!-- Name & Contact Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 6px; font-weight: 600;">
                                Họ và Tên Khách Quý <span style="color:var(--c-amber);">*</span>
                            </label>
                            <input type="text" id="modalUserNameInput" class="noir-modal-input" placeholder="Ví dụ: Hoàng Minh Đức" autocomplete="name" required>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 6px; font-weight: 600;">
                                Email hoặc Số Điện Thoại
                            </label>
                            <input type="text" id="modalUserContactInput" class="noir-modal-input" placeholder="Ví dụ: 0912 345 678" autocomplete="tel">
                        </div>
                    </div>

                    <!-- Gender / Persona Selection -->
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 8px; font-weight: 600;">
                            Chọn Định Danh &amp; Gu Thưởng Thức <span style="color:var(--c-amber);">*</span>
                        </label>
                        <div class="gender-picker-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <!-- Thẻ Con Trai -->
                            <div id="modalCardMale" class="gender-pick-card noir-pick-card active-male" onclick="RecommenderEngine.selectModalGender('male')">
                                <span style="font-size: 24px; display: block; margin-bottom: 4px;">☕</span>
                                <div style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: 1.15rem; font-weight: 600; color: #FAF6F0; margin-bottom: 2px;">Phái Mạnh · Nam</div>
                                <span style="font-size: 0.72rem; color: var(--c-amber); font-weight: 600; display: block; margin-bottom: 4px;">⚡ Bứt Phá Năng Lượng</span>
                                <p style="font-size: 0.76rem; color: rgba(242, 233, 218, 0.65); line-height: 1.4; margin: 0;">Ưu tiên Noir Espresso, Sea Salt Cold Brew và cà phê mộc đậm đà.</p>
                            </div>

                            <!-- Thẻ Con Gái -->
                            <div id="modalCardFemale" class="gender-pick-card noir-pick-card" onclick="RecommenderEngine.selectModalGender('female')">
                                <span style="font-size: 24px; display: block; margin-bottom: 4px;">🌸</span>
                                <div style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: 1.15rem; font-weight: 600; color: #FAF6F0; margin-bottom: 2px;">Phái Đẹp · Nữ</div>
                                <span style="font-size: 0.72rem; color: #d977a6; font-weight: 600; display: block; margin-bottom: 4px;">💖 Ngọt Ngào &amp; Thanh Dịu</span>
                                <p style="font-size: 0.76rem; color: rgba(242, 233, 218, 0.65); line-height: 1.4; margin: 0;">Ưu tiên Brown Butter Latte, Classic Matcha Uji và Trà sữa thanh béo.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Flavor Notes Chips -->
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-warm-beige); margin-bottom: 6px; font-weight: 600;">
                            Nốt Hương Bạn Yêu Thích
                        </label>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <span class="modal-note-chip selected" onclick="this.classList.toggle('selected')">Socola Đen &amp; Hạnh Nhân</span>
                            <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Caramel &amp; Bơ Nâu Pháp</span>
                            <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Vỏ Cam Vàng Tươi Mát</span>
                            <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Kem Muối Biển Mịn</span>
                            <span class="modal-note-chip" onclick="this.classList.toggle('selected')">Uji Matcha Umami</span>
                        </div>
                    </div>

                    <!-- Live Recommendation Preview -->
                    <div style="background: rgba(22, 14, 10, 0.65); border: 1px solid rgba(242, 233, 218, 0.12); padding: 12px 16px; border-radius: 3px; font-size: 0.82rem; color: rgba(242, 233, 218, 0.75); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span style="color: var(--c-amber); font-size: 1.2rem;">✨</span>
                        <div id="modalRecPreviewText">
                            Đề xuất ưu tiên: <strong style="color:var(--c-amber);">Noir Espresso đậm đà, Sea Salt Cold Brew ủ chậm 18h & Cà Phê Mộc</strong>.
                        </div>
                    </div>

                    <button type="submit" class="modal-submit-noir-btn">
                        <span>✦</span> Khởi Tạo Thẻ Thành Viên &amp; Vào Quán
                    </button>

                    <div style="text-align: center; margin-top: 14px;">
                        <a href="javascript:void(0)" onclick="RecommenderEngine.closeGenderModal()" style="font-size: 0.76rem; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(242, 233, 218, 0.5); text-decoration: underline;">
                            Xem Quán Trước, Đăng Ký Sau &rarr;
                        </a>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modalDiv);

        modalDiv.addEventListener('click', (e) => {
            if (e.target === modalDiv && modalDiv.getAttribute('data-mandatory') !== 'true') {
                RecommenderEngine.closeGenderModal();
            }
        });
    },

    renderUI: function(data) {
        const container = document.getElementById('recommendationContainer');
        if (!container) return;

        const isFemale = (this.currentGender === 'female');
        const themeColor = isFemale ? '#c4688b' : '#B96D3A';
        const bgGradient = isFemale 
            ? 'linear-gradient(145deg, rgba(46, 26, 32, 0.95) 0%, rgba(26, 17, 13, 0.98) 100%)' 
            : 'linear-gradient(145deg, rgba(40, 26, 19, 0.95) 0%, rgba(22, 14, 10, 0.98) 100%)';
        const borderColor = isFemale ? 'rgba(217, 119, 166, 0.35)' : 'rgba(185, 109, 58, 0.35)';
        const glowColor = isFemale ? 'rgba(217, 119, 166, 0.12)' : 'rgba(185, 109, 58, 0.12)';

        let itemsHtml = '';
        data.recommendations.forEach(item => {
            const cleanName = (item.name || '').replace(/'/g, "\\'");
            const cleanDesc = (item.desc || '').replace(/'/g, "\\'");
            const cleanNotes = ((item.tags && item.tags.length) ? item.tags.join(' • ') : 'Nguyên liệu tuyển chọn tự nhiên').replace(/'/g, "\\'");

            itemsHtml += `
                <div class="product-card noir-rec-card ${isFemale ? 'card-female' : 'card-male'}" style="background: rgba(42, 28, 22, 0.75); border: 1px solid rgba(242, 233, 218, 0.12); border-radius: 3px; overflow: hidden; display: flex; flex-direction: column;">
                    <div class="product-badge" style="position: absolute; top: 12px; left: 12px; background: rgba(22, 14, 10, 0.88); backdrop-filter: blur(8px); padding: 4px 10px; border-radius: 2px; font-size: 11px; font-weight: 700; z-index: 2; border: 1px solid rgba(242, 233, 218, 0.2); color: ${themeColor};">
                        ${item.badge}
                    </div>
                    <div class="product-img-wrap" 
                        onclick="RecommenderEngine.openDetail('${item.id}', '${cleanName}', ${item.price}, '${item.image}', '${cleanDesc}', '${cleanNotes}', '${item.category}')"
                        style="height: 195px; width: 100%; overflow: hidden; position: relative; cursor: pointer;"
                        title="Bấm để xem chi tiết ${item.name}">
                        <img src="${item.image}" alt="${item.name}" loading="lazy" 
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=500&auto=format&fit=crop&q=80';"
                            style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.92); transition: transform 0.5s ease;">
                        <span style="position: absolute; bottom: 8px; right: 8px; background: rgba(22, 14, 10, 0.85); color: #FAF6F0; border: 1px solid rgba(242, 233, 218, 0.2); font-size: 10.5px; padding: 3px 8px; border-radius: 2px; display: flex; align-items: center; gap: 4px;">
                            👁️ Xem chi tiết
                        </span>
                    </div>
                    <div class="product-info" style="padding: 18px; display: flex; flex-direction: column; flex: 1; background: transparent; color: #FAF6F0;">
                        <div class="product-meta-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div class="product-tags" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                ${item.tags.map(t => `<span class="tag" style="background: rgba(242, 233, 218, 0.08); color: #D6C3A6; border: 1px solid rgba(242, 233, 218, 0.12); padding: 2px 7px; font-size: 11px; border-radius: 2px;">${t}</span>`).join('')}
                                <span class="tag tag-cal" style="background: rgba(185, 109, 58, 0.15); color: #FAF6F0; border: 1px solid rgba(185, 109, 58, 0.3); padding: 2px 7px; font-size: 11px; border-radius: 2px;">🔥 ${item.calories || '180 kcal'}</span>
                            </div>
                            <span class="product-stars" style="color: #fbbf24; font-size: 12px; font-weight: 700;">⭐ 4.9</span>
                        </div>
                        <h3 class="product-title" 
                            onclick="RecommenderEngine.openDetail('${item.id}', '${cleanName}', ${item.price}, '${item.image}', '${cleanDesc}', '${cleanNotes}', '${item.category}')"
                            style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: 1.35rem; color: #FAF6F0; margin-bottom: 6px; font-weight: 600; cursor: pointer; transition: color 0.2s;"
                            onmouseover="this.style.color='${themeColor}'"
                            onmouseout="this.style.color='#FAF6F0'">
                            ${item.name}
                        </h3>
                        <p class="product-desc" style="font-size: 0.85rem; color: rgba(242, 233, 218, 0.72); line-height: 1.5; margin-bottom: 16px; flex-grow: 1;">${item.desc}</p>
                        <div class="product-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(242, 233, 218, 0.1); padding-top: 14px; margin-top: auto; gap: 8px; flex-wrap: wrap;">
                            <div class="product-price-col">
                                <span class="product-price-label" style="display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(242, 233, 218, 0.5);">Giá niêm yết</span>
                                <span class="product-price" style="font-size: 1.15rem; font-weight: 700; color: ${themeColor}; white-space: nowrap;">${Number(item.price).toLocaleString('vi-VN')} đ</span>
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <button type="button" class="btn-detail btn-track" 
                                    data-category="${item.category}" 
                                    data-item="${item.name}"
                                    onclick="RecommenderEngine.openDetail('${item.id}', '${cleanName}', ${item.price}, '${item.image}', '${cleanDesc}', '${cleanNotes}', '${item.category}')"
                                    style="background: transparent; border: 1px solid rgba(242, 233, 218, 0.28); color: #FAF6F0; padding: 8px 12px; border-radius: 2px; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; transition: all 0.25s ease; white-space: nowrap;"
                                    onmouseover="this.style.background='rgba(242,233,218,0.12)';this.style.borderColor='#FAF6F0';"
                                    onmouseout="this.style.background='transparent';this.style.borderColor='rgba(242, 233, 218, 0.28)';">
                                    👁️ Chi Tiết Món
                                </button>
                                <button type="button" class="btn-add-cart btn-track" 
                                    data-category="${item.category}" 
                                    data-item="${item.name}"
                                    onclick="RecommenderEngine.addToCart('${item.id}', '${cleanName}', ${item.price}, '${item.category}', '${item.image}')"
                                    style="background: ${themeColor}; color: #ffffff; border: none; padding: 8px 14px; border-radius: 2px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; transition: all 0.25s ease; white-space: nowrap;">
                                    + Chọn Món
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        const rateValue = isFemale ? data.analytics.female_milktea_rate : data.analytics.male_coffee_rate;
        const rateLabel = isFemale ? 'Ưa Trà & Sữa' : 'Ưa Cà Phê Mộc';

        container.innerHTML = `
            <div class="recommendation-banner noir-rec-banner" style="background: ${bgGradient}; border: 1px solid ${borderColor}; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 40px ${glowColor}; border-radius: 4px; padding: 38px 34px; margin-bottom: 30px; position: relative; overflow: hidden;">
                <div class="banner-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <span class="banner-pill" style="background: rgba(22, 14, 10, 0.85); color: ${themeColor}; border: 1px solid ${borderColor}; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                            ${isFemale ? '🌸 DÀNH RIÊNG CHO PHÁI ĐẸP' : '☕ DÀNH RIÊNG CHO PHÁI MẠNH'}
                        </span>
                        <h2 class="banner-title" style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: clamp(1.8rem, 3.2vw, 2.5rem); font-weight: 400; color: #FAF6F0; line-height: 1.25; margin-bottom: 8px;">
                            ${data.headline}
                        </h2>
                        <p class="banner-sub" style="font-size: 0.95rem; color: rgba(242, 233, 218, 0.78); max-width: 720px; line-height: 1.65; font-weight: 300;">
                            Kính chào <strong style="color: #FAF6F0; font-weight: 600;">${this.userName}</strong>! ${data.subline}
                        </p>
                    </div>

                    <div class="banner-stat-box" style="display: flex; align-items: center; gap: 16px; background: rgba(22, 14, 10, 0.75); border: 1px solid rgba(242, 233, 218, 0.12); padding: 14px 20px; border-radius: 3px;">
                        <div class="stat-circle-gauge" style="width: 64px; height: 64px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 50%; background: rgba(36, 23, 17, 0.95); border: 2.5px solid ${themeColor};">
                            <span class="stat-number" style="font-size: 18px; font-weight: 800; color: #FAF6F0; line-height: 1;">${rateValue}%</span>
                            <span class="stat-label" style="font-size: 8.5px; text-transform: uppercase; font-weight: 700; color: rgba(242, 233, 218, 0.6); margin-top: 3px;">${rateLabel}</span>
                        </div>
                        <div class="stat-info-text" style="font-size: 12px; color: rgba(242, 233, 218, 0.65); max-width: 140px; line-height: 1.45;">
                            Độ chuẩn gu cá nhân hóa: <strong style="color: #FAF6F0; display: block; margin-top: 2px;">95% (Đề tài 17)</strong>
                        </div>
                    </div>
                </div>

                <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                    ${itemsHtml}
                </div>
            </div>
        `;
    },

    setGender: function(gender) {
        this.currentGender = gender;
        this.selectedModalGender = gender;
        localStorage.setItem('user_gender', gender);
        localStorage.setItem('user_gender_authenticated', 'true');
        sessionStorage.setItem('gender_selected_session', 'true');

        this.updateUserProfileUI();
        this.fetchAndRenderRecommendations();

        if (window.AnalyticsTracker) {
            window.AnalyticsTracker.track('btn_click', {
                element_text: 'Chuyển giới tính sang: ' + (gender === 'female' ? 'Nữ' : 'Nam'),
                category: 'profile',
                item_name: 'gender_switch'
            });
        }
    },

    openDetail: function(id, name, price, img, desc, notes, category) {
        if (typeof openItemQuickview === 'function') {
            openItemQuickview(id, name, price, img, desc, notes, category);
        } else {
            const existingModal = document.getElementById('recDetailModal');
            if (existingModal) existingModal.remove();

            const modalHtml = `
                <div id="recDetailModal" class="noir-modal-overlay open" onclick="if(event.target === this) this.remove()" style="position: fixed; inset: 0; background: rgba(10, 6, 5, 0.85); backdrop-filter: blur(10px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
                    <div style="background: #1e130e; border: 1px solid rgba(242, 233, 218, 0.2); max-width: 500px; width: 100%; border-radius: 4px; overflow: hidden; position: relative; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                        <button type="button" onclick="document.getElementById('recDetailModal').remove()" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); color: #FAF6F0; border: none; width: 34px; height: 34px; border-radius: 50%; font-size: 20px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">&times;</button>
                        <div style="height: 240px; width: 100%; position: relative;">
                            <img src="${img}" alt="${name}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 24px; color: #FAF6F0;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                                <h3 style="font-family: var(--font-serif, 'Cormorant Garamond', serif); font-size: 1.6rem; color: #FAF6F0; margin: 0;">${name}</h3>
                                <span style="font-size: 1.25rem; font-weight: 700; color: #B96D3A;">${Number(price).toLocaleString('vi-VN')} đ</span>
                            </div>
                            <p style="font-size: 0.9rem; color: rgba(242, 233, 218, 0.8); line-height: 1.6; margin-bottom: 14px;">${desc}</p>
                            <div style="background: rgba(242, 233, 218, 0.05); border: 1px solid rgba(242, 233, 218, 0.1); padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 0.85rem; color: #D6C3A6;">
                                <strong style="color: #FAF6F0; display: block; margin-bottom: 4px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">Điểm Nổi Bật & Nốt Hương:</strong>
                                ${notes}
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding: 10px 14px; background: rgba(242, 233, 218, 0.04); border: 1px solid rgba(242, 233, 218, 0.15); border-radius: 3px;">
                                <span style="font-size: 0.8rem; color: #FAF6F0;">Đánh giá món:</span>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" onclick="alert('👍 Cảm ơn bạn đã thích món ' + '${name}' + '!'); if(window.AnalyticsTracker) AnalyticsTracker.track('btn_click', { element_text: 'Thích món: ' + '${name}', category: 'reaction', item_name: '${id}' });" style="background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid #10b981; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer;">
                                        👍 Thích
                                    </button>
                                    <button type="button" onclick="alert('👎 Đã ghi nhận góp ý Like ngược cho món ' + '${name}' + '!'); if(window.AnalyticsTracker) AnalyticsTracker.track('btn_click', { element_text: 'Like ngược món: ' + '${name}', category: 'reaction', item_name: '${id}' });" style="background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid #ef4444; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer;">
                                        👎 Like ngược
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="RecommenderEngine.addToCart('${id}', '${name}', ${price}, '${category}', '${img}'); document.getElementById('recDetailModal').remove();" style="width: 100%; background: #B96D3A; color: #fff; border: none; padding: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; border-radius: 2px;">
                                🛒 Thêm Vào Giỏ Hàng
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        if (window.AnalyticsTracker) {
            AnalyticsTracker.track('btn_click', {
                element_text: 'Nhấp xem chi tiết: ' + name,
                category: category || 'general',
                item_name: id
            });
        }
    },



    getCart: function() {
        return JSON.parse(localStorage.getItem('cafe_cart') || '[]');
    },

    saveCart: function(cart) {
        localStorage.setItem('cafe_cart', JSON.stringify(cart));
        this.updateCartBadge();
    },

    addToCart: function(id, name, price, category, image) {
        const cart = this.getCart();
        const existing = cart.find(i => i.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ id, name, price, category, image, qty: 1 });
        }
        this.saveCart(cart);

        // Ghi nhận sự kiện thời gian thực khi người dùng bấm chọn món
        if (window.AnalyticsTracker) {
            const custName = localStorage.getItem('user_name') || localStorage.getItem('cafe_customer_name') || (this.currentGender === 'female' ? 'Khách Nữ (Trực tuyến)' : 'Khách Nam (Trực tuyến)');
            const custAddr = localStorage.getItem('user_address') || localStorage.getItem('cafe_customer_address') || 'Chưa cập nhật địa chỉ';
            const formName = (category === 'coffee') ? 'Thực Đơn Cà Phê Chuẩn Gu' : 'Thực Đơn Trà Sữa Đậm Vị';

            window.AnalyticsTracker.track('btn_click', {
                form_name: formName,
                customer_name: custName,
                customer_address: custAddr,
                element_text: `Thêm vào giỏ: ${name} (Giá: ${Number(price).toLocaleString('vi-VN')} đ)`,
                category: category,
                item_name: id
            });
        }

        this.showToast(`🎉 Đã thêm 1x "${name}" vào giỏ hàng!`);
    },

    updateCartBadge: function() {
        const badge = document.getElementById('cartCountBadge');
        if (badge) {
            const cart = this.getCart();
            const total = cart.reduce((sum, i) => sum + i.qty, 0);
            badge.innerText = total;
            badge.style.display = total > 0 ? 'inline-flex' : 'none';
        }
    },

    showToast: function(msg) {
        let toast = document.getElementById('appToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'appToast';
            toast.className = 'app-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span>✨</span> <div>${msg}</div>`;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3200);
    }
};

document.addEventListener('DOMContentLoaded', () => RecommenderEngine.init());
