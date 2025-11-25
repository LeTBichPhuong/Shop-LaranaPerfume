<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giỏ hàng - LARANA PERFUME</title>
    <link rel="stylesheet" href="{{ asset('Cart.css') }}">
    <link rel="website icon" href="{{ asset('img/Logo.png') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>
<body>
    <!-- Header -->
        <header>
            <nav class="nav-menu">
                <ul class="menu">
                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a href="{{ route('about') }}">Giới thiệu</a></li>
                    <li><a href="{{ route('brand') }}">Thương hiệu</a></li>
                    <li class="logo"><a href="{{ route('home') }}"><img src="img/Logo.png" alt=""></a></a></li>
                    <li class="nav-item">
                        <a href="{{ route('products.list') }}">Sản phẩm</a>
                        <ul class="submenu">
                            <li><a href="{{ route('products.gender', ['gender' => 'nam']) }}">Nước hoa nam</a></li>
                            <li><a href="{{ route('products.gender', ['gender' => 'khac']) }}">Nước hoa nữ</a></li>
                            <li><a href="{{ route('products.gender', ['gender' => 'unisex']) }}">Nước hoa unisex</a></li>
                        </ul>
                    </li>
                    <li><a href="{{ route('blog') }}">Blog</a></li>
                    <!-- Thanh tìm kiếm ẩn -->
                    <li class="search-container" style="position:relative;">
                        <a href="#" id="searchIcon"><i class="fa fa-search"></i></a>
                        <form id="searchForm" class="search-form" action="#" method="get" onsubmit="return false;">
                            <div class="search-box">
                                <input type="text" name="keyword" placeholder="Tìm kiếm sản phẩm hoặc thương hiệu..." autocomplete="off" />
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </form>
                        <div id="searchResults" class="search-results" aria-live="polite"></div>
                    </li>
                    <!-- Tài khoản -->
                    <li>
                        @if(auth()->check())
                            <a href="{{ route('account') }}"><i class="fa fa-user"></i></a>
                        @else
                            <a href="{{ route('login') }}"><i class="fa fa-user"></i></a>
                        @endif
                    </li>
                    <li><a href="{{ route('cart') }}"><i class="fa fa-shopping-cart"></i></a></li>
                </ul>
            </nav> 
        </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="cart-container">
            <h2 class="mb-4">Giỏ hàng </h2>

            @if(isset($cartItems) && $cartItems->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th style="width: 150px;">Giá</th>
                                    <th style="width: 120px;">Số lượng</th>
                                    <th style="width: 150px;">Tạm tính</th>
                                    <th style="width: 100px;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cartItems as $item)
                                    <tr data-id="{{ $item->id }}">
                                        <td>
                                            <img src="{{ $item->product_image ?? asset('img/placeholder-product.png') }}" 
                                                 width="60" 
                                                 class="img-thumbnail" 
                                                 alt="{{ $item->product_name }}">
                                        </td>
                                        <td><strong>{{ $item->product_name }}</strong></td>
                                        <td class="price" data-price="{{ $item->price_numeric }}" data-price-raw="{{ $item->price }}">
                                            <span class="text-danger fw-bold">{{ \App\Helper\helpers::format($item->price_numeric) }}</span>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   value="{{ $item->quantity }}" 
                                                   min="1" 
                                                   class="form-control form-control-sm quantity" 
                                                   data-id="{{ $item->id }}" 
                                                   style="width: 80px;">
                                        </td>
                                        <td class="subtotal">
                                            <span class="fw-bold">{{ \App\Helper\helpers::format($item->subtotal) }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-item" data-id="{{ $item->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- tổng giá và thanh toán -->
                        <div class="total-section">
                            <div class="total-price">
                                <h5 class="total-title">Tổng cộng giỏ hàng</h5>
                                <div class="summary-row">
                                    <span class="label">Tạm tính:</span>
                                    <span class="value">{{ \App\Helpers\PriceHelper::format($total) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="label">Tổng:</span>
                                    <span class="value">{{ \App\Helpers\PriceHelper::format($total) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-buy">
                                        <button type="button" class="btn btn-light btn-lg w-100" id="checkout-btn"
                                        onclick="window.location.href='{{ route('checkout') }}'">
                                            Tiến hành thanh toán
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa fa-shopping-cart fa-5x text-muted mb-4"></i>
                    <h3>Giỏ hàng của bạn đang trống</h3>
                    <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                    <a href="{{ route('products.list') }}" class="btn btn-primary btn-lg mt-3">
                        <i class="fa fa-shopping-bag"></i> Mua sắm ngay
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="footer-top">
            <div class="title-sub">
                <h2>Đăng ký thành viên để nhận khuyến mãi</h2>
                <p>Theo dõi chúng tôi để nhận nhiều ưu đãi</p>
            </div>
            <div class="subscribe">
                <input type="email" placeholder="Nhập email, SĐT của bạn">
                <button class="btn-sub"><i class='bx bxs-navigation'></i></button>
            </div>
        </div>
        <hr class="section-divider">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Kết nối với Larana</h3>
                <div class="social-icons">
                    <i class="fab fa-facebook-f"></i>
                    <i class="fab fa-x-twitter"></i>
                    <i class="fab fa-pinterest"></i>
                    <i class="fab fa-instagram"></i>
                </div>
                <p>TỔNG ĐÀI TƯ VẤN MIỄN PHÍ</p>
                <p>Chi nhánh HÀ NỘI<br>0386759355</p>
            </div>

            <div class="footer-section">
                <h3>Chính sách bán hàng</h3>
                <ul>
                    <li>Chính sách và quy định chung</li>
                    <li>Chính sách bảo mật</li>
                    <li>Vận chuyển và giao nhận</li>
                    <li>Mua hàng và thanh toán</li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Thông tin chung</h3>
                <ul>
                    <li>Giới thiệu</li>
                    <li>Blog</li>
                    <li>Liên hệ</li>
                    <li>Sản phẩm</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© Larana 2025 | all rights reserved</p>
        </div>
    </footer>

    <script src="{{ asset('JS/Product.js') }}"></script>
    <script src="{{ asset('JS/script.js') }}"></script>
    <script src="{{ asset('JS/Cart.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</body>
</html>