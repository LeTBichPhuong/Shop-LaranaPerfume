<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LARANA PERFUME</title>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo.png') }}"> 
    <link rel="stylesheet" href="{{ asset('Home.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="nav-menu">
            <ul class="menu">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li><a href="{{ route('about') }}">Giới thiệu</a></li>
                <li><a href="{{ route('brand') }}">Thương hiệu</a></li>
                <li class="logo"><a href="{{ route('home') }}"><img src="{{ asset('img/Logo.png') }}" alt="Logo"></a></li>
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
    <section class="hero">
        <div class="overlay">
            <a href="{{ route('home') }}"><img src="{{ asset('img/Banner.PNG') }}" alt="Banner"></a>
        </div>
    </section>
    <!-- End header -->

    <!-- Main -->
    <!-- Thương hiệu -->
    <section class="brands">
        <h2>Thương hiệu nổi tiếng</h2>
        <div class="brand-grid">
            <!-- Nội dung từ JS: Load /thuong-hieu-json -->
        </div>
    </section>
    <!-- End thương hiệu -->
    <hr class="section-divider">
    <!-- Sản phẩm -->
    <div class="product-section">
        <div class="product-box">
            <img src="{{ asset('img/Nuoc-hoa-nam.png') }}" alt="Nước hoa nam">
            <h3>Nước hoa nam</h3>
            <a href="{{ route('products.gender', 'nam') }}">KHÁM PHÁ</a>
        </div>
        <div class="product-box">
            <h3>Nước hoa unisex</h3>
            <a href="{{ route('products.gender', 'unisex') }}">KHÁM PHÁ</a>
            <img src="{{ asset('img/Nuoc-hoa-unisex.png') }}" alt="Nước hoa unisex">
        </div>
        <div class="product-box">
            <img src="{{ asset('img/Nuoc-hoa-nu.png') }}" alt="Nước hoa nữ">
            <h3>Nước hoa nữ</h3>
            <a href="{{ route('products.gender', 'khac') }}">KHÁM PHÁ</a>
        </div>
    </div>
    <hr class="section-divider">
    <section class="main-section">
        <h2 class="title">Sản phẩm nổi bật</h2>
        <div class="tabs">
            <span class="tab active" data-tab="nam" onclick="showTab('nam', event)">Nước hoa nam</span>
            <span class="tab" data-tab="nu" onclick="showTab('nu', event)">Nước hoa nữ</span>
            <span class="tab" data-tab="unisex" onclick="showTab('unisex', event)">Unisex</span>
        </div>
        <div class="slider-container"></div> 
    </section>
    <!-- End sản phẩm -->
    <hr class="section-divider">
    <!-- Giới thiệu -->
    <section class="why-choose">
        <h2 class="titles">Tại sao chọn Larana store</h2>
        <div class="benefits">
            <div class="benefit">
                <i class="fas fa-credit-card"></i>
                <h3>Thành viên thân thiết</h3>
                <p>Thành viên vàng sẽ được giảm 5%/đơn hàng. Với thành viên bạc khách được giảm 3%/đơn hàng.</p>
            </div>
            <div class="benefit">
                <i class="fas fa-shipping-fast"></i>
                <h3>Free ship toàn quốc</h3>
                <p>Larana áp dụng freeship cho tất cả các khách hàng trên toàn quốc. Chúng tôi chưa áp dụng hình thức giao hàng quốc tế tại thời điểm này.</p>
            </div>
            <div class="benefit">
                <i class="fas fa-award"></i>
                <h3>Sản phẩm chính hãng</h3>
                <p>Sản phẩm nước hoa được mua trực tiếp tại các store ở nước ngoài hoặc làm việc trực tiếp với các hãng, cam kết authentic 100%.</p>
            </div>
        </div>
    </section>
    <hr class="section-divider">
    <section class="contact">
        <h2 class="titles">Larana perfume</h2>
        <p><i class="fas fa-envelope"></i> Larana@gmail.com</p>
        <div class="stores">
            <p><i class="fas fa-map-marker-alt"></i> CS1: Hào Nam, Ô Chợ Dừa, TP. Hà Nội | Hotline: 0386759355 | Open: 8h30-21h</p>
        </div>
    </section>

    <!-- Chatbot Widget -->
    <div id="chatbot-container">
        <button id="chatbot-toggle-btn" onclick="toggleChatbot()">
            <i class='bx bx-message-alt-dots'></i>
        </button>
        <div id="chatbot-box">
            <div id="chatbot-header">
                <span><i class='bx bx-message-alt-dots'></i> Larana bot</span>
                <button id="chatbot-close" onclick="toggleChatbot()">×</button>
            </div>
            <div id="chatbot-messages"></div>
            <div id="chatbot-input-area">
                <input type="text" id="chatbot-input" placeholder="Nhập tin nhắn..." onkeydown="handleKey(event)" />
                <button onclick="sendMessage()">Gửi</button>
            </div>
        </div>
    </div>
    <!-- End main -->

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
    <!-- End footer -->

    <script src="{{ asset('JS/script.js') }}"></script>
    <script src="{{ asset('JS/Product.js') }}"></script>
</body>
</html>