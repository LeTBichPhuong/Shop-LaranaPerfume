@extends('Product')

@push('MasterCSS')
<style>
    a {
        text-decoration: none;
    }
  /* Ẩn các phần không cần thiết  */
    #brands-filter, #brand-list { display: none; }
    .title { display: none; }

    /* Main */
    .container {
        max-width: 1200px; 
        margin: 0 auto;
    }
    .row { 
        display: flex; 
        gap: 50px; 
        margin-top: 20px;
        margin-bottom: 20px;
        width: 100% !important; 
    }

    /* Header */
    .brand-header { text-align: center; margin: 100px 0 40px 0; }
    .brand-name { font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; }
    .brand-description { font-size: 1.1rem; color: #6c757d; margin-bottom: 30px; }

    /* Sidebar */
    .search-section .search-input {
        padding: 12px 18px;
        border-radius: 25px;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        width: 100%;
    }
    .sidebar-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #333;
        margin: 25px 0 15px;
        text-transform: uppercase;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 5px;
    }
    .sidebar-list {
        max-height: 220px;
        overflow-y: auto;
        padding-right: 8px;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        background-color: #fafafa;
    }
    .sidebar-list::-webkit-scrollbar { width: 6px; }
    .sidebar-list::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
    .sidebar-link {
        display: block;
        padding: 10px 15px;
        color: #6c757d;
        text-decoration: none;
        font-size: 0.9rem;
        border-bottom: 1px solid #f8f9fa;
        transition: all 0.2s;
    }
    .sidebar-link:hover { color: black; background-color: #f8f9fa; padding-left: 20px; }

    .gender-btn {
        border-radius: 25px;
        margin-bottom: 8px;
        text-align: left;
        font-size: 0.9rem;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        color: #6c757d;
        transition: all 0.2s;
    }
    .gender-btn.active { background-color: black; color: white; border-color: black; }
    .gender-btn:hover { background-color: #e9ecef; }

    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin: 40px 0;
    }

    .product-item {
        width: 100%;
    }

    .product-card {
        position: relative;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        background: #fff;
    }

    .product-card:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        border-color: #333; 
    }

    /* Ảnh sản phẩm */
    .product-image-wrapper {
        width: 100%;
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-bottom: 1px solid #ddd;
    }


    .card-img-top {
        width: 100%;
        object-fit: contain;
        background: #fff;
        padding: 5px;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    /* icon yêu thích */
    .wishlist {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        background: #d4d4d4;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        z-index: 30;
    }

    .wishlist-icon {
        font-size: 18px;
        color: #fff;
        transition: color 0.2s ease;
    }

    .wishlist-icon.active {
        color: #c1232f !important;
    }

    /* giảm giá */
    .discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        z-index: 10;
        box-shadow: 0 2px 6px rgba(238, 90, 111, 0.4);
    }

    /* Card Body */
    .card-body {
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        justify-content: center;
    }

    .brand-title {
        font-size: 14px;
        font-weight: 500;
        color: black;
        text-transform: uppercase;
        margin-bottom: 2px;
        height: 2.2em;
        letter-spacing: 0.5px;
    }

    .product-title {
        font-size: 13px;
        font-weight: 500;
        color: #777777;
        margin-bottom: 4px;
        height: 2.2em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Giá */
    .price-section {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        text-align: center;
    }

    /* Giá gốc */
    .price-section .original-price {
        font-size: 14px;
        color: #888;
        text-decoration: line-through;
        line-height: 1;
    }

    /* Giá khuyến mãi */
    .price-section .discounted-price {
        font-size: 14px;
        font-weight: 600;
        color: #000;
        text-decoration: none;
        line-height: 1;
    }

    /* Khi sản phẩm không có giảm giá */
    .price-section .discounted-price:not(.has-discount) {
        margin-top: 0;
    }

    /* Nút thêm giỏ hàng */
    .add-to-cart-btn {
        width: 100%;
        padding: 8px 0;
        background: linear-gradient(135deg, #212122 0%, #515051 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-top: 6px;
    }

    .add-to-cart-btn:hover {
        background: linear-gradient(135deg, #676667 0%, #0b0b0b 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .add-to-cart-btn:active {
        transform: translateY(0);
    }

    .add-to-cart-btn i {
        font-size: 1rem;
    }

    /* Thông báo nếu không có sản phẩm */
    .empty-state-default,
    .empty-state-filter {
        grid-column: 1 / -1;
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state-default p,
    .empty-state-filter p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Animation khi thêm vào giỏ */
    @keyframes addToCart {
        0% { transform: scale(1); }
        50% { transform: scale(0.95); }
        100% { transform: scale(1); }
    }

    .add-to-cart-btn.adding {
        animation: addToCart 0.3s ease;
        background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
    }

    .empty-state-default, .empty-state-filter {
        grid-column: 1 / -1;
        padding: 60px 20px;
        text-align: center;
    }

    /* Toast container */
    #toast-container {
        position: fixed;
        top: 15px;
        right: 15px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
    }

    .toast {
        min-width: 260px;
        padding: 14px 18px;
        margin-bottom: 10px;
        border-radius: 6px;
        font-size: 14px;
        color: white;
        background: #333;
        opacity: 0;
        transform: translateX(100%);
        animation: slideIn 0.4s forwards, fadeOut 0.5s 2.8s forwards;
        box-shadow: 0 3px 8px rgba(0,0,0,0.25);
    }

    .toast.success { background: #4caf50; }
    .toast.error { background: #e74c3c; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100%); }
        to   { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeOut {
        to   { opacity: 0; transform: translateX(100%); }
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .card-img-top {
            height: 180px;
        }
    }

    @media (max-width: 480px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('master')
<div id="toast-container"></div>
<div class="container my-5">
    <!-- Header trang sản phẩm -->
    <div class="brand-header text-center mb-5">
        <h1 class="brand-name">{{ $title ?? 'SẢN PHẨM' }}</h1>
        <p class="brand-description">{{ $description ?? 'Khám phá bộ sưu tập nước hoa cao cấp từ Larana Perfume.' }}</p>
    </div>

    <!-- Main -->
    <div class="row">
        
        <!-- Sidebar -->
        <div class="col-md-3">
            
            <!-- List thương hiệu -->
            <div class="brand-list-sidebar mb-4">
                <h6 class="sidebar-title">THƯƠNG HIỆU</h6>
                <div class="search-section mb-4">
                    <input type="text" class="form-control search-input" placeholder="Tìm kiếm thương hiệu..." id="brand-search">
                </div>
                <ul class="list-unstyled sidebar-list" id="brand-list-filter">
                    @forelse($allBrands as $relatedBrand)
                        <li>
                            {{-- <a href="{{ route('brands.show', $relatedBrand->name) }}" class="sidebar-link">
                                {{ $relatedBrand->name }}
                            </a> --}}
                            <a href="{{ route('products.list', ['brand' => $relatedBrand->id]) }}"
                                class="sidebar-link {{ (isset($active_brand) && $active_brand == $relatedBrand->id) ? 'active' : '' }}">
                                    {{ $relatedBrand->name }}
                            </a>
                        </li>
                    @empty
                        <li class="text-muted">Chưa có sản phẩm của thương hiệu liên quan.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Filter giới tính -->
            <div class="gender-filter">
                <h6 class="sidebar-title">GIỚI TÍNH</h6>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary gender-btn active" data-gender="all">Tất cả</button>
                    <button type="button" class="btn btn-outline-secondary gender-btn" data-gender="unisex">Unisex</button>
                    <button type="button" class="btn btn-outline-secondary gender-btn" data-gender="nam">Nam</button>
                    <button type="button" class="btn btn-outline-secondary gender-btn" data-gender="khac">Nữ</button>
                </div>
            </div>
        </div>

        <!-- Nội dung sản phẩm bên phải -->
        <div class="col-md-9">
            <!-- Grid sản phẩm -->
            <div class="products-grid" id="products-filter">
                @forelse($products as $product)
                    <div class="product-item" data-gender="{{ $product->gender }}">
                        <div class="product-card card h-100">

                            <div class="product-image-wrapper">
                                @if(isset($product->original_price) && $product->original_price > $product->price)
                                    <span class="discount-badge">Giảm giá</span>
                                @endif

                                <div class="wishlist" onclick="toggleWishlist(this)">
                                    <i class="bx bxs-heart wishlist-icon"></i>
                                </div>

                                <a href="{{ route('products.show', $product->name) }}" class="text-decoration-none">
                                    <img src="{{ $product->image ?? asset('img/placeholder-product.png') }}" class="card-img-top" alt="{{ $product->name }}" 
                                        onerror="this.onerror=null; this.src='https://placehold.co/300x300/e0e0e0/555?text=Product+Image'">
                                </a>
                            </div>
                            
                            <div class="card-body">
                                <a href="{{ route('products.show', $product->name) }}" class="text-decoration-none">
                                    <h4 class="brand-title">{{ $product->brand->name ?? 'Không rõ' }}</h4>
                                    <h5 class="product-title">{{ $product->name }}</h5>
                                    <div class="price-section">
                                        @if($product->original_price && $product->original_price > $product->final_price)
                                            <div class="original-price">
                                                {{ number_format($product->original_price, 0, ',', '.') }} ₫
                                            </div>
                                            <div class="discounted-price has-discount">
                                                {{ number_format($product->final_price, 0, ',', '.') }} ₫
                                            </div>
                                        @else
                                            <div class="discounted-price">
                                                {{ number_format($product->final_price, 0, ',', '.') }} ₫
                                            </div>
                                        @endif
                                    </div>
                                </a>
                                
                                <!-- Nút thêm giỏ hàng -->
                                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="add-to-cart-form" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="add-to-cart-btn">
                                        <i class="fa fa-shopping-cart"></i>
                                        Thêm vào giỏ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-default">
                        <p class="text-center text-muted">Không tìm thấy sản phẩm nào.</p>
                    </div>
                @endforelse
                <div class="empty-state-filter" style="display: none;">
                    <p class="text-center text-muted">Không có sản phẩm phù hợp với bộ lọc này.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const genderButtons = document.querySelectorAll('.gender-btn');
        const brandLinks = document.querySelectorAll('#brand-list-filter .sidebar-link');
        const products = document.querySelectorAll('.product-item');
        const emptyStateFilter = document.querySelector('.empty-state-filter');
        const pagination = document.querySelector('.pagination-basic');

        let selectedGender = 'all';
        let selectedBrand = null;

        function filterProducts() {
            let visibleCount = 0;

            products.forEach(item => {
                const itemGender = item.dataset.gender?.toLowerCase();
                const itemBrand = item.querySelector('.brand-title')?.textContent.trim().toLowerCase();

                const matchGender = (selectedGender === 'all' || itemGender === selectedGender);
                const matchBrand = (!selectedBrand || itemBrand === selectedBrand);

                if (matchGender && matchBrand) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (emptyStateFilter) {
                emptyStateFilter.style.display = (visibleCount === 0 ? 'block' : 'none');
                if (pagination) pagination.style.display = (visibleCount === 0 ? 'none' : '');
            }
        }

        genderButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                genderButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedGender = this.dataset.gender.toLowerCase();
                filterProducts();
            });
        });

        brandLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                brandLinks.forEach(a => a.classList.remove('active'));
                this.classList.add('active');
                selectedBrand = this.textContent.trim().toLowerCase();
                filterProducts();
            });
        });

        // ============================
        // 🚀 CHẶN SUBMIT FORM + AJAX
        // ============================
        const cartForms = document.querySelectorAll('form[action*="cart"], form[action*="gio-hang"], .add-to-cart-form');

        cartForms.forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault(); // Ngăn reload trang

                const button = form.querySelector('.add-to-cart-btn');
                const action = form.getAttribute('action');
                const formData = new FormData(form);

                button.classList.add('adding');
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang thêm...';
                button.disabled = true;

                try {
                    const res = await fetch(action, {
                        method: 'POST',
                        body: formData,
                        headers: { 
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.status === 401 || res.redirected) {
                        showErrorToast('Vui lòng đăng nhập để tiếp tục!');
                        window.location.href = '/dang-nhap';
                        return;
                    }

                    if (res.status === 419) {
                        showErrorToast('Phiên làm việc hết hạn! Vui lòng tải lại trang.');
                        location.reload();
                        return;
                    }

                    const data = await res.json();

                    if (data.success) {
                        showSuccessToast('Đã thêm sản phẩm vào giỏ hàng!');
                        button.innerHTML = '<i class="fa fa-check"></i> Đã thêm';

                        setTimeout(() => {
                            button.classList.remove('adding');
                            button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
                            button.disabled = false;
                        }, 1500);

                    } else {
                        showErrorToast(data.message || 'Không thể thêm sản phẩm!');
                        button.classList.remove('adding');
                        button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
                        button.disabled = false;
                    }

                } catch (error) {
                    console.error(error);
                    showErrorToast('Lỗi kết nối server!');
                    button.classList.remove('adding');
                    button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
                    button.disabled = false;
                }
            });
        });

    });

    function showSuccessToast(message) {
        showToast(message, 'success');
    }

    function showErrorToast(message) {
        showToast(message, 'error');
    }

    function showToast(message, type) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');

        toast.classList.add('toast', type);
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3500);
    }
</script>

@endsection