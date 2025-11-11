@extends('Brand')

@section('brand-products')
<div class="container my-5">
    <!-- Header thương hiệu -->
    <div class="brand-header text-center mb-5">
        <h1 class="brand-name">{{ $brand->name }}</h1>
        <p class="brand-description">Khám phá bộ sưu tập sản phẩm cao cấp từ {{ $brand->name }}.</p>
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
                        <li class="text-muted">Chưa có thương hiệu liên quan.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Filter giới tính -->
            <div class="gender-filter">
                <h6 class="sidebar-title">GIỚI TÍNH</h6>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary gender-btn active" data-gender="all">Tất cả</button>
                    <button type="button" class="btn btn-outline-secondary gender-btn" data-gender="nam">Nam</button>
                    <button type="button" class="btn btn-outline-secondary gender-btn" data-gender="khac">Nữ</button>
                    <button type="button" class="btn btn-outline-secondary gender-btn" data-gender="unisex">Unisex</button>
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
                            @if($product->original_price && $product->original_price != $product->price)
                                <span class="discount-badge">Khuyến mãi</span>
                            @endif

                            <div class="card-body">
                                <a href="{{ route('products.show', $product->name) }}" class="text-decoration-none">
                                    <img src="{{ $product->image ?? asset('img/placeholder-product.png') }}" class="card-img-top" alt="{{ $product->name }}" 
                                        onerror="this.onerror=null; this.src='https://placehold.co/300x300/e0e0e0/555?text=Product+Image'">
                                    <div class="card-body">
                                        <h4 class="brand-title">{{ $brand->name }}</h4>
                                        <h5 class="product-title">{{ $product->name }}</h5>
                                        <div class="price-section">
                                            @if($product->original_price && $product->original_price != $product->price)
                                                <div class="original-price">{{ $product->original_price }}</div>
                                                <div class="discounted-price">{{ $product->price }}</div>
                                            @else
                                                <div class="discounted-price">{{ $product->price }}</div>
                                            @endif
                                        </div>
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
                        <p class="text-center text-muted">Chưa có sản phẩm nào cho thương hiệu {{ $brand->name }}.</p>
                    </div>
                @endforelse
                <div class="empty-state-filter" style="display: none;">
                    <p class="text-center text-muted">Không có sản phẩm phù hợp với bộ lọc này.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    .brand-header { text-align: center; }
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
    .sidebar-list::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
    .sidebar-list::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; transition: background 0.2s; }
    .sidebar-list::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    .sidebar-link {
        display: block;
        padding: 10px 15px;
        color: #6c757d;
        text-decoration: none;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border-bottom: 1px solid #f8f9fa;
        transition: all 0.2s;
    }
    .sidebar-link:last-child { 
        border-bottom: none; 
    }
    .sidebar-link:hover { 
        color: black;
        background-color: #f8f9fa; 
        padding-left: 20px; 
    }
    .gender-btn {
        border-radius: 25px;
        margin-bottom: 8px;
        text-align: left;
        font-size: 0.9rem;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
        color: #6c757d; 
    }
    .gender-btn.active { 
        background-color: black; 
        color: white; 
        border-color: black; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.2); 
    }
    .gender-btn:hover { 
        background-color: #e9ecef;
        color: #6c757d; 
    }

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
        gap: 2px;
        align-items: flex-start;
    }

    .original-price {
        color: #dc3545;
        font-size: 0.8rem;
        font-weight: 500;
        margin: 0;
        text-decoration: line-through;
    }

    .discounted-price {
        font-size: 0.95rem;
        font-weight: bold;
        color: #000;
        margin-top: 2px;
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

    /* Phân trang */
    .pagination-section {
        display: none;
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

    /* Responsive Design */
    @media (max-width: 992px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .col-md-3 {
            order: -1;
            margin-bottom: 20px;
        }
        .row {
            gap: 0; 
        }
    }
</style>

<script>
    // Search
    document.getElementById('brand-search').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const links = document.querySelectorAll('#brand-list-filter .sidebar-link');
        links.forEach(link => {
            const text = link.textContent.toLowerCase();
            if (text.includes(query)) {
                link.style.display = 'block';
                link.parentElement.style.display = 'block';
            } else {
                link.style.display = 'none';
                link.parentElement.style.display = 'none';
            }
        });
    });

    // Filter giới tính
    document.addEventListener('DOMContentLoaded', function() {
        const genderButtons = document.querySelectorAll('.gender-btn');
        const productItems = document.querySelectorAll('.product-item');
        const emptyStateFilter = document.querySelector('.empty-state-filter');
        const emptyStateDefault = document.querySelector('.empty-state-default');
        const pagination = document.querySelector('.pagination-basic');

        genderButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                genderButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const selectedGender = this.dataset.gender.toLowerCase();
                let visibleCount = 0;

                productItems.forEach(item => {
                    let productGender = item.dataset.gender.toLowerCase();

                    if (selectedGender === 'khac') {
                        item.style.display = (productGender === 'khac') ? 'block' : 'none';
                    } else if (selectedGender === 'all' || productGender === selectedGender) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }

                    if (item.style.display === 'block') visibleCount++;
                });

                emptyStateFilter.style.display = visibleCount ? 'none' : 'block';
                emptyStateDefault.style.display = 'none';
                pagination.style.display = visibleCount ? 'flex' : 'none';

                if (pagination) {
                    pagination.style.display = visibleCount ? 'flex' : 'none';
                }
            });
        });
    });
</script>
@endsection
