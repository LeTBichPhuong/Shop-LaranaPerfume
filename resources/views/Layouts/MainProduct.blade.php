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
        gap: 30px; 
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
        margin-bottom: 30px;
    }

    .product-item {
        width: 100%;
    }

    .product-card {
        position: relative;
        border: none;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: #fff;
    }

    .product-card:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    /* Ảnh sản phẩm */
    .product-image-wrapper {
        position: relative;
        overflow: hidden;
    }

    .card-img-top {
        width: 100%;
        height: 180px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-card:hover .card-img-top {
        transform: scale(1.05);
    }

    /* Discount Badge */
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
        font-size: 0.75rem;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }

    .product-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #333;
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
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    /* Giá gốc */
    .price-section .original-price {
        font-size: 14px;
        color: #888;
        text-decoration: line-through;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    /* Giá giảm */
    .price-section .discounted-price {
        font-size: 18px;
        font-weight: 700;
        color: #000;
        line-height: 1.2;
    }

    /* Khi KHÔNG có giảm giá */
    .price-section .discounted-price:not(.has-discount) {
        margin-top: 6px;
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

    /* Empty State */
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

    /* Thông tin sản phẩm (Brand name, Title, Price) */
    .product-card a {
        text-decoration: none;
        color: inherit;
    }

    .product-card .brand-title,
    .product-card .product-title,
    .product-card .price-section {
        color: inherit !important;
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
                            <a href="{{ route('brands.show', $relatedBrand->name) }}" class="sidebar-link">
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
                                        @if(isset($product->original_price) && $product->original_price > $product->price)
                                            <div class="original-price">{{ $product->original_price }}</div>
                                            <div class="discounted-price has-discount">{{ $product->price }}</div>
                                        @else
                                            <div class="discounted-price">{{ $product->price }}</div>
                                        @endif
                                    </div>
                                </a>
                                
                                <!-- Nút thêm giỏ hàng -->
                                <form action="{{ route('cart.add', $product->id) }}" method="POST" style="margin: 0;">
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

        // Biến lưu trạng thái hiện tại
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

            // Hiển thị trạng thái trống
            if (emptyStateFilter) {
                if (visibleCount === 0) {
                    emptyStateFilter.style.display = 'block';
                    if (pagination) pagination.style.display = 'none';
                } else {
                    emptyStateFilter.style.display = 'none';
                    if (pagination) pagination.style.display = '';
                }
            }
        }

        // Sự kiện lọc giới tính
        genderButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                genderButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedGender = this.dataset.gender.toLowerCase();
                filterProducts();
            });
        });

        // Sự kiện lọc thương hiệu
        brandLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                brandLinks.forEach(a => a.classList.remove('active'));
                this.classList.add('active');
                selectedBrand = this.textContent.trim().toLowerCase();
                filterProducts();
            });
        });
    });

    // Optional: Highlight active page khi load (nếu cần)
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
        const activeLink = document.querySelector(`.page-link[href*='page=${currentPage}']`);
        if (activeLink) {
            activeLink.parentElement.classList.add('active');
            activeLink.classList.add('active');  
        }
    });

    // Hàm thêm vào giỏ hàng
    async function addToCart(productId, button) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            alert('Lỗi bảo mật! Vui lòng tải lại trang.');
            return;
        }
        
        // Animation nút
        button.classList.add('adding');
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang thêm...';
        button.disabled = true;
        
        try {
            const res = await fetch(`/gio-hang/add/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin' 
            });
            
            // Kiểm tra nếu bị redirect về login (401 hoặc 302)
            if (res.status === 401 || res.redirected) {
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                window.location.href = '/dang-nhap';
                return;
            }
            
            // Kiểm tra lỗi 419 (CSRF)
            if (res.status === 419) {
                alert('Phiên làm việc đã hết hạn. Vui lòng tải lại trang!');
                location.reload();
                return;
            }
            
            // Kiểm tra lỗi 500
            if (res.status === 500) {
                const text = await res.text();
                console.error('Server error:', text);
                alert('Lỗi server! Vui lòng kiểm tra console.');
                button.classList.remove('adding');
                button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
                button.disabled = false;
                return;
            }
            
            const data = await res.json();
            
            if (data.success) {
                // Hiển thị thông báo thành công
                button.innerHTML = '<i class="fa fa-check"></i> Đã thêm';
                setTimeout(() => {
                    button.classList.remove('adding');
                    button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
                    button.disabled = false;
                }, 1500);
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
                button.classList.remove('adding');
                button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra! Vui lòng kiểm tra console để biết chi tiết.');
            button.classList.remove('adding');
            button.innerHTML = '<i class="fa fa-shopping-cart"></i> Thêm vào giỏ';
            button.disabled = false;
        }
    }
</script>
@endsection