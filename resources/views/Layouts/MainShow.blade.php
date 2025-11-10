@extends('Product')

@push('MasterCSS')
    <style>
    a {
        text-decoration: none;
    }
    
    .product-detail.container {
        max-width: 1200px;
        margin: 120px auto 60px;
        padding: 40px 20px;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-evenly;
    }

    /* ảnh sản phẩm */
    .image-section {
        text-align: center;
        position: relative;
    }

    .image-section img.main-image {
        width: 100%;
        max-width: 500px;
        height: auto;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 20px;
        border: 1px solid #eee;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .image-section img.main-image:hover {
        transform: scale(1.05);
    }

    /* thông tin sp */
    .info-section {
        padding: 20px 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .brand-name {
        font-size: 14px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .product-name {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #222;
        line-height: 1.3;
    }

    .gender {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gender i {
        font-size: 20px;
    }

    /* giá */
    .price-box {
        margin: 10px 0;
        display: inline-block;
    }

    .price-box .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 10px;
        margin-bottom: 5px;
    }

    .price-box .discounted-price {
        color: #000;
        font-size: 20px;
        font-weight: 700;
    }

    /* Dung tích */
    .volume-section {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .volume-section input[type="radio"] {
        display: none;
    }

    .volume-section label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 400;
        user-select: none;
        font-size: 12px;
    }

    .volume-section label:hover {
        border-color: #000;
        background: #f8f9fa;
    }

    .volume-section input[type="radio"]:checked + label,
    .volume-section label:has(input[type="radio"]:checked) {
        background-color: #000;
        color: #fff;
        border-color: #000;
    }

    /* Form nút */
    .form-cart {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 20px;
    }

    /* Số lượng*/
    .quantity-section {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        border: 2px solid #333;
        border-radius: 40px;
        overflow: hidden;
        width: fit-content;
        margin: 25px 0;
    }

    .quantity-btn {
        width: 30px;
        height: 30px;
        font-weight: bold;
        font-size: 20px;
        border: none !important;
        border-radius: 0;
        background: #fff;
        color: #333;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    #quantity {
        width: 50px !important;
        height: 50px;
        font-size: 18px;
        font-weight: 600;
        text-align: center;
        border: none !important;
        border-radius: 0;
        outline: none;
        background: #fff;
    }

    #quantity:focus {
        outline: none;
    }

    /* ẩn nút tăng sẵn có */
    #quantity::-webkit-outer-spin-button,
    #quantity::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    /* nút thêm */
    .info-section form button[type="submit"] {
        background: linear-gradient(135deg, #212122 0%, #515051 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 15px 30px;
        width: 100%;
        font-size: 18px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
    }

    .info-section form button[type="submit"]:hover {
        background: linear-gradient(135deg, #676667 0%, #0b0b0b 100%);
        transform: translateY(-3px);
    }

    .info-section form button[type="submit"]:active {
        transform: translateY(0);
    }

    /* liên hệ */
    .contact {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .contact i {
        color: #d78944;
        font-size: 25px;
    }

    /* MÔ TÁ SẢN PHẨM */
    .product-description {
        margin-top: 10px;
        padding: 30px;
    }

    .product-description h4 {
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 20px;
        color: #222;
        border-bottom: 3px solid #4a4a4c;
        display: inline-block;
        padding-bottom: 10px;
    }

    .product-description p {
        color: #666;
        line-height: 1.8;
        font-size: 16px;
    }

    /* SẢN PHẨM NỔI BẬT */
    .featured-products {
        margin-top: 10px;
        text-align: center; 
        padding: 30px;
    }

    .featured-products h4 {
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 40px;
        color: #222;
        position: relative;
        display: inline-block;
        border-bottom: 3px solid #4a4a4c;
    }

    .featured-products h4::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        border-radius: 2px;
    }

    .featured-products .row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }

    .featured-products .col-md-2 {
        flex: 0 0 calc(16.66% - 20px);
        max-width: calc(16.66% - 20px);
    }

    /* sản phẩm nổi bật */
    .product-card {
        border: #000;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #fff;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
        min-width: 230px;
        max-width: 280px;
        margin: auto;
    }

    .product-card:hover {
        transform: translateY(1px);
    }

    .product-card img {
        width: 100%;
        height: 250px;
        object-fit: contain;
        padding: 15px;
        transition: transform 0.3s ease;
    }

    .product-card:hover img {
        transform: scale(1.05);
    }

    /* Nội dung */
    .product-card .card-body {
        padding: 15px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .brand-title {
        font-size: 13px;
        margin: 10px 0;
        color: #333;
        text-decoration: none;
    }

    .product-title {
        font-size: 14px;
        margin: 10px 0;
        color: #333;
        font-weight: 600;
        min-height: 38px;       
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Gía */
    .product-card .price-box {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        min-height: 28px;
        margin-top: 5px;
    }

    /* Giá gốc */
    .product-card .price-box .original-price {
        font-size: 14px;
        color: #999;
        text-decoration: line-through;
        line-height: 1;
    }

    /* Giá giảm */
    .product-card .price-box .discounted-price {
        font-size: 16px;
        color: #000;
        font-weight: 700;
        line-height: 1;
    }

    /* Không có giảm giá → đứng 1 mình */
    .product-card .price-box .discounted-price:not(.has-discount) {
        margin-top: 0;
    }

    /* Giảm giá */
    .product-card .price-box .discounted-price.has-discount {
        color: #000;
    }

    /* RESPONSIVE */
    @media (max-width: 992px) {
        .featured-products .col-md-2 {
            flex: 0 0 calc(25% - 20px);
            max-width: calc(25% - 20px);
        }
        
        .product-name {
            font-size: 26px;
        }
    }

    @media (max-width: 768px) {
        .product-detail.container {
            margin-top: 80px;
        }
        
        .featured-products .col-md-2 {
            flex: 0 0 calc(33.33% - 20px);
            max-width: calc(33.33% - 20px);
        }
        
        .product-name {
            font-size: 24px;
        }
        
        .price-box .discounted-price {
            font-size: 26px;
        }
        
        .info-section {
            margin-top: 30px;
            padding: 20px 15px;
        }
    }

    @media (max-width: 576px) {
        .featured-products .col-md-2 {
            flex: 0 0 calc(50% - 20px);
            max-width: calc(50% - 20px);
        }
        
        .product-card img {
            height: 150px;
        }
        
        .product-name {
            font-size: 20px;
        }
    }

    /* ANIMATION KHI TẢI TRANG */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .product-detail .image-section,
    .product-detail .info-section {
        animation: fadeInUp 0.6s ease;
    }

    .product-detail .info-section {
        animation-delay: 0.2s;
    }
</style>
@endpush

@section('content')
<div class="product-detail container my-5">
    <div class="row">
        <!-- Ảnh sản phẩm -->
        <div class="col-md-6 image-section">
            <img src="{{ $product->image ?? asset('img/placeholder-product.png') }}" 
                 alt="{{ $product->name }}" 
                 class="img-fluid main-image rounded shadow-sm"
                 onerror="this.onerror=null;this.src='https://placehold.co/500x500/e0e0e0/555?text=Product+Image';">
        </div>

        <!-- Thông tin sản phẩm -->
        <div class="col-md-6 info-section">
            <p class="brand-name"><strong>{{ $product->brand->name ?? 'Không rõ' }}</strong></p>
            <h2 class="product-name">{{ $product->name }}</h2>
            <p class="gender"><strong>
                @php
                    $gender = strtolower(trim($product->gender));
                @endphp

                @if($gender === 'nam')
                    <i class='bx bx-male-sign'></i> Nam
                @elseif($gender === 'unisex')
                    <i class='bx bx-male-female'></i> Unisex
                @else
                    <i class='bx bx-female-sign'></i> Nữ
                @endif
            </strong></p>
            <div class="price-box">
                @if(isset($product->original_price) && $product->original_price > $product->price)
                    <div class="original-price">{{ $product->original_price }}</div>
                    <div class="discounted-price has-discount">{{ $product->price }}</div>
                @else
                    <div class="discounted-price">{{ $product->price }}</div>
                @endif
            </div>

            <!-- Dung tích -->
            <div class="volume-section my-3">
                <h4 class="me-3" style="padding:10px 0">Dung tích:</h4>
                
                <label>
                    <input type="radio" name="volume" checked>
                    100ml
                </label>
                
                <label>
                    <input type="radio" name="volume">
                    200ml
                </label>
                
                <label>
                    <input type="radio" name="volume">
                    500ml
                </label>
            </div>

            <div class="form-cart">
                <!-- Số lượng -->
                <div class="quantity-section my-3 d-flex align-items-center">
                    <button class="btn btn-outline-dark quantity-btn" id="decrease">-</button>
                    <input type="number" id="quantity" class="form-control text-center mx-2" value="1" min="1" style="width:70px;">
                    <button class="btn btn-outline-dark quantity-btn" id="increase">+</button>
                </div>

                <!-- Nút thêm giỏ hàng -->
                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-dark btn-lg w-100 mt-3">Thêm vào giỏ hàng</button>
                </form>
            </div>

            <!-- Liên hệ -->
            <p class="contact mt-4 text-muted">
                Gọi ngay <i class='bx bxs-phone'></i> <strong> 0386759355 (8:30 AM - 21:00PM)</strong>
                <h5 style="margin: 10px 0; text-align: center;">Tư vấn miễn phí</h5>
            </p>
        </div>
    </div>
    <hr style="color:#4a4a4c;">
    <!-- Mô tả sản phẩm -->
    <div class="product-description mt-5">
        <h4>Mô tả sản phẩm</h4>
        <p>{{ $product->description ?? 'Sản phẩm chưa có mô tả chi tiết.' }}</p>
    </div>

    <!-- Sản phẩm nổi bật -->
    <div class="featured-products mt-5">
        <h4 class="mb-4 text-center">Sản phẩm nổi bật</h4>
        <div class="row g-3">
            @php
                // Lấy 12 sản phẩm ngẫu nhiên, trừ sản phẩm hiện tại
                $randomProducts = \App\Models\Product::inRandomOrder()
                    ->where('id', '!=', $product->id)
                    ->take(12)
                    ->get();
            @endphp

            @foreach($randomProducts as $item)
                <div class="col-md-2 col-6">
                    <div class="card product-card h-100">
                        <a href="{{ route('products.show', $item->name) }}" class="text-decoration-none text-dark">
                            <img src="{{ $item->image ?? asset('img/placeholder-product.png') }}" 
                                class="card-img-top" alt="{{ $item->name }}"
                                onerror="this.onerror=null;this.src='https://placehold.co/200x200/e0e0e0/555?text=Product';">
                            <div class="card-body p-2 text-center">
                                <h6 class="brand-title">{{ $product->brand->name ?? 'Không rõ' }}</h6>
                                <h6 class="product-title">{{ \Illuminate\Support\Str::limit($item->name, 40) }}</h6>
                                <div class="price-box">
                                    @if(isset($product->original_price) && $product->original_price > $product->price)
                                        <div class="original-price">{{ $product->original_price }}</div>
                                        <div class="discounted-price has-discount">{{ $product->price }}</div>
                                    @else
                                        <div class="discounted-price">{{ $product->price }}</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    // Nút tăng giảm số lượng
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('quantity');
        document.getElementById('increase').addEventListener('click', () => input.value++);
        document.getElementById('decrease').addEventListener('click', () => {
            if (input.value > 1) input.value--;
        });
    });
</script>
@endsection