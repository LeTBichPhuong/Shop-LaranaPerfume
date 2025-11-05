document.addEventListener('DOMContentLoaded', () => {
    // Lấy CSRF token từ meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Xử lý chọn phương thức thanh toán
    document.querySelectorAll('input[name="payment-method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Cập nhật giá trị vào select ẩn
            document.getElementById('payment-method').value = this.value;
            
            // Ẩn/hiện QR code
            const qrSection = document.getElementById('qr-section');
            if (this.value === 'QR') {
                if (!qrSection) {
                    // Tạo section QR code nếu chưa có
                    createQRSection();
                } else {
                    qrSection.style.display = 'block';
                }
            } else {
                if (qrSection) {
                    qrSection.style.display = 'none';
                }
            }
        });
    });
    
    // Tạo section hiển thị QR code
    function createQRSection() {
        const qrHTML = `
            <div id="qr-section" class="card mt-3">
                <div class="card-body text-center">
                    <h5 class="mb-3">Quét mã QR để thanh toán</h5>
                    <img src="/img/qr-payment.jpg" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                    <p class="mt-3 text-muted">Quét mã QR bằng ứng dụng ngân hàng của bạn</p>
                    <div class="alert alert-info mt-2" style="padding: 10px 0;">
                        <strong>Lưu ý:</strong> Sau khi chuyển khoản, vui lòng nhấn "Thanh toán ngay"
                    </div>
                </div>
            </div>
        `;
        document.querySelector('.card-payment').insertAdjacentHTML('afterend', qrHTML);
    }
    
    // Cập nhật số lượng
    document.querySelectorAll('.quantity').forEach(input => {
        input.addEventListener('change', async function() {
            const id = this.dataset.id;
            const quantity = parseInt(this.value);
            
            if (quantity < 1) {
                this.value = 1;
                return;
            }
            
            try {
                const res = await fetch(`/gio-hang/update/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ quantity })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    const row = this.closest('tr');
                    const price = parseFloat(row.querySelector('.price').dataset.price);
                    const subtotal = price * quantity;
                    
                    row.querySelector('.subtotal span').textContent = formatPrice(subtotal);
                    updateTotal();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            }
        });
    });
    
    // Xóa sản phẩm
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
            
            const id = this.dataset.id;
            
            try {
                const res = await fetch(`/gio-hang/remove/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await res.json();
                
                if (data.success) {
                    const row = this.closest('tr');
                    row.remove();
                    updateTotal();
                    
                    if (document.querySelectorAll('tbody tr').length === 0) {
                        location.reload();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
    
    // Thanh toán
    // document.getElementById('checkout-btn')?.addEventListener('click', async () => {
    //     const paymentMethod = document.getElementById('payment-method').value;
        
    //     // Kiểm tra đã chọn phương thức thanh toán chưa
    //     if (!paymentMethod) {
    //         alert('Vui lòng chọn phương thức thanh toán!');
    //         return;
    //     }
        
    //     if (!confirm('Xác nhận đặt hàng?')) return;
        
    //     try {
    //         const res = await fetch('/gio-hang/checkout', {
    //             method: 'POST',
    //             headers: {
    //                 'X-CSRF-TOKEN': csrfToken,
    //                 'Content-Type': 'application/json'
    //             },
    //             body: JSON.stringify({ payment_method: paymentMethod })
    //         });
            
    //         // Kiểm tra lỗi 419
    //         if (res.status === 419) {
    //             alert('Phiên làm việc đã hết hạn. Vui lòng tải lại trang!');
    //             location.reload();
    //             return;
    //         }
            
    //         const data = await res.json();
    //         alert(data.message);
            
    //         if (data.success) {
    //             window.location.href = '/don-hang';
    //         }
    //     } catch (error) {
    //         console.error('Error:', error);
    //         alert('Có lỗi xảy ra! Vui lòng thử lại.');
    //     }
    // });
    
    // Cập nhật tổng
    function updateTotal() {
        let total = 0;

        document.querySelectorAll('tbody tr').forEach(row => {
            const priceEl = row.querySelector('.price');
            const qtyEl = row.querySelector('.quantity');
            if (!priceEl || !qtyEl) return;

            // Lấy giá từ data-price
            let price = parseFloat(priceEl.dataset.price);
            console.log('Raw price from data-price:', price);  
            
            const priceStr = price.toString().replace('.0', ''); 
            if (price && priceStr.length >= 8 && price % 100 === 0) {
                price = price / 100;
                console.log('Divided price:', price);
            }
            
            if (!price || isNaN(price)) {
                const cleanText = priceEl.textContent.replace(/[^\d]/g, '');
                price = Number(cleanText) || 0;
                const textStr = price.toString().replace('.0', '');
                if (price && textStr.length >= 8 && price % 100 === 0) {
                    price = price / 100;
                }
                console.log('Fallback price from text:', price); 
            }

            const qty = parseInt(qtyEl.value) || 1;
            console.log('Price * Qty:', price, '*', qty, '=', price * qty);

            total += price * qty;
        });

        console.log('Final total:', total);

        // Cập nhật tổng
        document.querySelectorAll('.total-section .value').forEach(el => {
            el.textContent = formatPrice(total);
        });
    }

    // Format tiền
    function formatPrice(number) {
        const formatted = Math.round(number).toLocaleString('vi-VN');
        return formatted + ' ₫';
    }

    // Gọi updateTotal ban đầu
    updateTotal();
});


