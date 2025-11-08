@extends('Checkout')

@section('checkout')
<style>
    /* ẨN PHẦN GIỎ HÀNG */
    .main-content {
        display: none;
    }

    /* main checkout */
    .brand-header { 
        text-align: center; 
    }

    .brand-name { 
        font-size: 2.5rem; 
        font-weight: bold; 
        margin: 20px 0;
    }

    .checkout-wrapper {
        min-height: 100vh;
        padding: 60px 20px;
    }

    .checkout-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 60px 80px;
    }

    .section-header {
        font-size: 20px;
        font-weight: 600;
        color: #2c2c2c;
        margin-bottom: 30px;
        letter-spacing: 0.5px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 30px;
    }

    .form-group-full {
        margin-bottom: 30px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        color: #666;
        margin-bottom: 12px;
        font-weight: 400;
    }

    .required {
        color: #e74c3c;
    }

    .form-input {
        width: 100%;
        padding: 12px 0;
        font-size: 15px;
        color: #2c2c2c;
        background: transparent;
        border: none;
        border-bottom: 1px solid #e0e0e0;
        outline: none;
        transition: border-color 0.3s ease;
    }

    .form-input:focus {
        border-bottom-color: #2c2c2c;
    }

    .form-input::placeholder {
        color: #bbb;
        font-weight: 300;
    }

    textarea.form-input {
        resize: vertical;
        min-height: 120px;
        padding: 12px 0;
        font-family: inherit;
        line-height: 1.6;
    }

    .checkbox-group {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .checkbox-input {
        width: 18px;
        height: 18px;
        margin-right: 12px;
        margin-top: 2px;
        cursor: pointer;
        accent-color: #2c2c2c;
    }

    .checkbox-label {
        font-size: 13px;
        color: #666;
        line-height: 1.5;
        cursor: pointer;
    }

    .radio-group {
        margin-bottom: 20px;
    }

    .radio-item {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .radio-input {
        width: 18px;
        height: 18px;
        margin-right: 12px;
        cursor: pointer;
        accent-color: #2c2c2c;
    }

    .radio-label {
        font-size: 14px;
        color: #2c2c2c;
        font-weight: 500;
        cursor: pointer;
    }

    .radio-desc {
        font-size: 12px;
        color: #999;
        margin-left: 30px;
        display: block;
        margin-top: -8px;
        margin-bottom: 12px;
    }

    .checkout-section {
        margin-bottom: 50px;
    }

    .shipping-section {
        margin-top: 50px;
    }

    .btn-submit {
        width: 100%;
        max-width: 430px;
        display: block;
        margin: 40px auto 0;
        padding: 16px;
        background: #5a5a5a;
        color: white;
        border: none;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.3s ease;
        letter-spacing: 0.5px;
    }

    .btn-submit:hover {
        background: #404040;
    }

    .btn-submit:disabled {
        background: #999;
        cursor: not-allowed;
    }

    .payment-section {
        margin-top: 50px;
        padding-top: 50px;
        border-top: 1px solid #e0e0e0;
    }

    .payment-options {
        margin-top: 20px;
    }

    .payment-bar {
        display: flex;
        align-items: center;
        padding: 20px;
        background: #f9f9f9;
        margin-bottom: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-bar:hover {
        background: #f5f5f5;
    }

    .payment-bar.active {
        background: #f0f0f0;
        border-left: 3px solid #2c2c2c;
    }

    .payment-bar input[type="radio"] {
        margin-right: 16px;
    }

    .payment-info h6 {
        font-size: 14px;
        font-weight: 600;
        color: #2c2c2c;
        margin-bottom: 4px;
    }

    .payment-info p {
        font-size: 12px;
        color: #999;
        margin: 0;
    }

    .qr-section {
        display: none;
        margin-top: 30px;
        padding: 40px 30px;
        background: #fafafa;
        border: 1px solid #e0e0e0;
        text-align: center;
    }

    .qr-section.active {
        display: block;
    }

    .qr-section h5 {
        font-size: 16px;
        font-weight: 600;
        color: #2c2c2c;
    }

    .qr-section img {
        max-width: 300px;
        width: 100%;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .qr-section p {
        font-size: 13px;
        color: #999;
        margin-bottom: 16px;
    }

    .qr-alert {
        background: #e3f2fd;
        border: 1px solid #90caf9;
        padding: 12px 20px;
        border-radius: 4px;
        font-size: 13px;
        color: #1976d2;
        margin-top: 20px;
    }

    .qr-alert strong {
        font-weight: 600;
    }

    .success-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .success-overlay.active {
        display: flex;
    }

    .success-box {
        background: white;
        padding: 60px 50px;
        text-align: center;
        max-width: 500px;
        animation: slideUp 0.4s ease;
    }

    .success-icon {
        width: 70px;
        height: 70px;
        background: #27ae60;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }

    .success-icon i {
        font-size: 35px;
        color: white;
    }

    .success-box h3 {
        font-size: 24px;
        color: #2c2c2c;
        margin-bottom: 16px;
        font-weight: 600;
    }

    .success-box p {
        color: #666;
        font-size: 14px;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .btn-home {
        padding: 14px 40px;
        background: #2c2c2c;
        color: white;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn-home:hover {
        background: #404040;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .checkout-container {
            padding: 40px 30px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .btn-submit {
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .checkout-container {
            padding: 30px 20px;
        }

        .section-header {
            font-size: 18px;
        }
    }
</style>

<div class="checkout-wrapper">
    <div class="checkout-container">
        <div class="brand-header text-center mb-5">
            <h1 class="brand-name">Thanh toán</h1>
        </div>
        <form id="checkout-form">
            @csrf
            <!-- Thông tin thanh toán -->
            <div class="checkout-section">
                <h2 class="section-header">Thông tin thanh toán</h2>
                
                <div class="form-row">
                    <div>
                        <label class="form-label">Tên <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name" 
                               value="{{ auth()->user()->name ?? '' }}" 
                               placeholder="họ và tên" required>
                    </div>
                    <div>
                        <label class="form-label">Địa chỉ email <span class="required">*</span></label>
                        <input type="email" class="form-input" name="email" 
                               value="{{ auth()->user()->email ?? '' }}" 
                               placeholder="daobichhanh22@gmail.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Số điện thoại <span class="required">*</span></label>
                        <input type="tel" class="form-input" name="phone" 
                               value="{{ auth()->user()->phone ?? '' }}" 
                               placeholder="số điện thoại" required>
                    </div>
                    <div>
                        <label class="form-label">Địa chỉ <span class="required">*</span></label>
                        <input type="text" class="form-input" name="address" 
                               value="{{ auth()->user()->address ?? '' }}" 
                               placeholder="địa chỉ" required>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" class="checkbox-input" id="save-gift" name="save_info">
                    <label class="checkbox-label" for="save-gift">Lưu để làm quà</label>
                </div>

                <div class="form-group-full">
                    <label class="form-label">Lời nhắn</label>
                    <textarea class="form-input" name="note" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn."></textarea>
                </div>
            </div>

            <!-- Giao đến địa chỉ -->
            <div class="shipping-section">
                <h2 class="section-header">Giao đến địa chỉ</h2>
                
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" class="radio-input" id="default-address" name="shipping_type" value="default" checked>
                        <label class="radio-label" for="default-address">Để địa chỉ mặc định</label>
                    </div>
                </div>

                <div class="form-group-full">
                    <label class="form-label">Ghi chú về đơn hàng</label>
                    <textarea class="form-input" name="shipping_note" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn."></textarea>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" class="checkbox-input" id="accept-terms" name="accept_terms" required>
                    <label class="checkbox-label" for="accept-terms">
                        Tôi đã đọc và đồng ý với điều khoản và điều kiện của website <span class="required">*</span>
                    </label>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="payment-section">
                <h2 class="section-header">Phương thức thanh toán</h2>
                
                <div class="payment-options">
                    <label class="payment-bar active" data-method="COD">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <div class="payment-info">
                            <h6>THANH TOÁN KHI NHẬN HÀNG</h6>
                            <p>Giao tận nơi tại Hà Nội</p>
                        </div>
                    </label>

                    <label class="payment-bar" data-method="QR">
                        <input type="radio" name="payment_method" value="QR">
                        <div class="payment-info">
                            <h6>THANH TOÁN QR</h6>
                            <p>Visa, Master, JCB</p>
                        </div>
                    </label>
                </div>

                <div class="qr-section" id="qr-section">
                    <h5>Quét mã QR để thanh toán</h5>
                    
                    <div class="qr-container" style="text-align: center">
                        <img id="qr-img" 
                            src="" 
                            alt="QR Code" 
                            style="max-width: 300px; width: 100%; height: auto; display: none; border: 2px solid #ddd; padding: 10px; border-radius: 8px; background: white;">
                        
                        <div id="qr-loading" style="padding: 30px; display: block;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-2">Đang tạo mã QR...</p>
                        </div>
                        
                        <p id="qr-info" style="margin-top: 10px; color: #666; font-size: 14px;">
                            Đang tải mã QR...
                        </p>
                    </div>
                    
                    <div style="text-align: center;">
                        <label for="transaction-code" style="display: block; margin-bottom: 5px; font-weight: bold;">
                            Nhập mã giao dịch (Nội dung chuyển khoản):
                        </label>
                        <input type="text" 
                            id="transaction-code" 
                            name="transaction_code"
                            placeholder="VD: DH12345678" 
                            class="form-control" 
                            style="max-width: 300px; margin: 0 auto; text-transform: uppercase; font-size: 18px; font-weight: bold; text-align: center; letter-spacing: 2px;">
                    </div>
                </div>
            </div>
            <button type="button" class="btn-submit" id="submit-order">Đặt hàng</button>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div class="success-overlay" id="success-modal">
    <div class="success-box">
        <div class="success-icon">
            <i class="fa fa-check"></i>
        </div>
        <h3>Đặt hàng thành công!</h3>
        <p>Cảm ơn bạn đã đặt hàng. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.</p>
        <button class="btn-home" onclick="window.location.href='{{ route('home') }}'">
            <i class="fa fa-home"></i> Về trang chủ
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentBars = document.querySelectorAll('.payment-bar');
    const qrSection = document.getElementById('qr-section');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const transactionInput = document.getElementById('transaction-code');

    const generateQRUrl = document.body.dataset.generateQrUrl || '/generate-qr';
    const checkoutUrl = document.body.dataset.checkoutUrl || '/gio-hang/checkout';

    let qrLoaded = false;
    let qrInterval;

    // Xử lý click vào payment bar
    paymentBars.forEach(bar => {
        bar.addEventListener('click', function() {
            paymentBars.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            
            const method = this.dataset.method;
            if (method === 'QR') {
                qrSection.classList.add('active');
                if (transactionInput) transactionInput.required = true;
                if (!qrLoaded) loadQR();
            } else {
                qrSection.classList.remove('active');
                if (transactionInput) {
                    transactionInput.required = false; 
                    transactionInput.value = ''; 
                }
                if (qrInterval) clearInterval(qrInterval);
            }
        });
    });

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'QR') {
                qrSection.classList.add('active');
                if (transactionInput) transactionInput.required = true;
                if (!qrLoaded) loadQR();
            } else {
                qrSection.classList.remove('active');
                if (transactionInput) {
                    transactionInput.required = false;
                    transactionInput.value = '';
                }
                if (qrInterval) clearInterval(qrInterval);
            }
        });
    });

    // Load QR - Tạo QR bằng Canvas (client-side)
    function loadQR() {
        const qrContainer = document.getElementById('qr-img')?.parentElement || qrSection;
        const qrInfo = document.getElementById('qr-info');
        const qrLoading = document.getElementById('qr-loading');
        
        if (!qrContainer || !qrInfo) {
            console.error('QR elements not found');
            return;
        }

        qrLoaded = true;

        if (qrLoading) qrLoading.style.display = 'block';
        qrInfo.textContent = 'Đang tạo mã QR...';

        // Fetch thông tin từ backend
        fetch(generateQRUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`Lỗi server: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (qrLoading) qrLoading.style.display = 'none';

            if (data.success) {
                console.log('QR data received:', data);

                // Xóa QR cũ nếu có
                const oldQR = document.getElementById('qrcode-canvas');
                if (oldQR) oldQR.remove();

                const oldImg = document.getElementById('qr-img');
                if (oldImg) oldImg.style.display = 'none';

                // Tạo container cho QR canvas
                const qrDiv = document.createElement('div');
                qrDiv.id = 'qrcode-canvas';
                qrDiv.style.cssText = 'display: inline-block; margin: 10px auto; text-align: center;';
                
                // Insert vào đúng vị trí
                if (oldImg) {
                    oldImg.parentNode.insertBefore(qrDiv, oldImg);
                } else {
                    qrContainer.insertBefore(qrDiv, qrInfo);
                }

                // Sử dụng VietQR direct image
                try {
                    if (data.qr_image && data.qr_image.includes('vietqr.io')) {
                        // VietQR - hiển thị trực tiếp
                        const img = document.createElement('img');
                        img.src = data.qr_image;
                        img.style.cssText = 'max-width: 300px; width: 100%; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                        img.onload = () => console.log('VietQR loaded');
                        img.onerror = () => {
                            console.error('VietQR failed, generating QR from URL');
                            // Fallback: Tạo QR từ URL
                            new QRCode(qrDiv, {
                                text: data.qr_url || data.content,
                                width: 300,
                                height: 300,
                                colorDark: '#000000',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.L
                            });
                        };
                        qrDiv.appendChild(img);
                    } else {
                        // Fallback: Tạo QR từ content
                        new QRCode(qrDiv, {
                            text: data.qr_url || data.content,
                            width: 300,
                            height: 300,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.L
                        });
                    }

                    console.log('QR code generated successfully');

                    // Hiển thị thông tin
                    const expireDate = new Date(data.expire_time);
                    qrInfo.innerHTML = `
                        <div style="text-align: center; margin-top: 5px;">
                            <p style="margin: 5px 0; font-size: 16px;"><strong>Nội dung CK:</strong> <span style="color: #d32f2f; font-weight: bold;">${data.transfer_code || data.qr_code}</span></p>
                            <p style="margin: 10px 0; font-size: 12px; color: #666;">Hết hạn: ${expireDate.toLocaleString('vi-VN')}</p>
                        </div>
                    `;

                    // Lưu mã giao dịch
                    if (data.transfer_code) {
                        sessionStorage.setItem('qr_transfer_code', data.transfer_code);
                        sessionStorage.setItem('qr_code', data.qr_code);
                        console.log('Transfer Code saved:', data.transfer_code);
                        console.log('QR Code saved:', data.qr_code);
                        showQRCode(data.transfer_code, qrInfo);
                    } else if (data.qr_code) {
                        sessionStorage.setItem('qr_code', data.qr_code);
                        console.log('QR Code saved:', data.qr_code);
                        showQRCode(data.qr_code, qrInfo);
                    }

                    // Auto refresh sau 5 phút
                    if (qrInterval) clearInterval(qrInterval);
                    qrInterval = setInterval(() => {
                        qrLoaded = false;
                        loadQR();
                    }, 300000);

                } catch (error) {
                    console.error('QRCode.js error:', error);
                    
                    // Fallback: Dùng Google Charts API trực tiếp
                    generateQRFallback(data.content, qrDiv, data.qr_code, qrInfo);
                }

            } else {
                throw new Error(data.message || 'Không thể tạo mã QR');
            }
        })
        .catch(error => {
            console.error('QR Error:', error);
            if (qrLoading) qrLoading.style.display = 'none';
            showQRError(error.message);
        });
    }

    // Fallback: Tạo QR bằng API external
    function generateQRFallback(content, container, code, infoElement) {
        console.log('Using fallback QR generation');
        
        // Tạo img tag với proxy
        const img = document.createElement('img');
        img.style.cssText = 'max-width: 300px; width: 100%; border: 2px solid #ddd; padding: 10px; border-radius: 8px;';
        
        // Dùng API khác không bị CORS
        const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(content)}`;
        
        img.onload = function() {
            console.log('Fallback QR loaded');
        };
        
        img.onerror = function() {
            console.error('Fallback QR also failed');
            showQRError('Không thể tạo mã QR. Vui lòng chọn phương thức thanh toán khác.');
        };
        
        img.src = apiUrl;
        container.innerHTML = '';
        container.appendChild(img);

        if (code) {
            sessionStorage.setItem('qr_code', code);
            showQRCode(code, infoElement);
        }
    }

    // Hiển thị mã QR cho user
    function showQRCode(code, infoElement) {
        const existingDisplay = document.getElementById('qr-code-display');
        if (existingDisplay) existingDisplay.remove();

        const codeDisplay = document.createElement('div');
        codeDisplay.id = 'qr-code-display';
        
        infoElement.parentNode.insertBefore(codeDisplay, infoElement.nextSibling);
    }

    // Hiển thị lỗi
    function showQRError(message) {
        const qrInfo = document.getElementById('qr-info');
        const oldQR = document.getElementById('qrcode-canvas');
        const oldImg = document.getElementById('qr-img');
        
        if (oldQR) oldQR.style.display = 'none';
        if (oldImg) oldImg.style.display = 'none';
        
        if (qrInfo) {
            qrInfo.innerHTML = `
                <div class="alert alert-danger" style="max-width: 400px; margin: 20px auto;">
                    <strong>Lỗi tạo mã QR</strong><br>
                    <small>${message}</small><br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                        Tải lại trang
                    </button>
                </div>
            `;
        }

        qrLoaded = false;
    }

    // Auto load QR nếu đã active
    if (qrSection && qrSection.classList.contains('active')) {
        setTimeout(loadQR, 500);
        if (transactionInput) transactionInput.required = true;
    } else {
        // Đảm bảo input không required khi load trang
        if (transactionInput) transactionInput.required = false;
    }

    // Cleanup
    window.addEventListener('beforeunload', () => {
        if (qrInterval) clearInterval(qrInterval);
    });

    // Submit order
    const submitBtn = document.getElementById('submit-order');
    const form = document.getElementById('checkout-form');

    if (submitBtn && form) {
        submitBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            // Kiểm tra form validity
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Verify QR code CHỈ KHI chọn phương thức QR
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            
            if (paymentMethod === 'QR') {
                if (!transactionInput) {
                    alert('Lỗi: Không tìm thấy ô nhập mã giao dịch!');
                    return;
                }

                const inputCode = transactionInput.value.trim().toUpperCase();
                
                // Lấy cả 2 loại mã để so sánh
                const storedTransferCode = sessionStorage.getItem('qr_transfer_code');
                const storedQRCode = sessionStorage.getItem('qr_code');
                
                if (!inputCode) {
                    alert('Vui lòng nhập mã giao dịch!');
                    transactionInput.focus();
                    return;
                }

                // Check cả 2 dạng mã
                const isValid = (inputCode === storedTransferCode) || 
                               (inputCode === storedQRCode) ||
                               (inputCode === 'DH' + storedQRCode);

                if (!isValid) {
                    alert('Mã giao dịch không đúng!\n\n✓ Mã đúng: ${storedTransferCode || storedQRCode}\n✗ Bạn nhập: ${inputCode}\n\nVui lòng nhập đúng nội dung chuyển khoản!');
                    transactionInput.focus();
                    transactionInput.select();
                    return;
                }

                console.log('Transaction verified:', inputCode);
            } else {
                console.log('Payment method:', paymentMethod, '- Skip QR verification');
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';

            try {
                const formData = new FormData(form);
                const response = await fetch(checkoutUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const successModal = document.getElementById('success-modal');
                    if (successModal) {
                        successModal.classList.add('active');
                    } else {
                        alert('Đặt hàng thành công!');
                        window.location.href = '/';
                    }
                } else {
                    alert('Lỗi' + (data.message || 'Có lỗi xảy ra!'));
                }
            } catch (error) {
                console.error('Submit error:', error);
                alert('Lỗi kết nối!');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Đặt hàng';
            }
        });
    }
});
</script>
@endsection