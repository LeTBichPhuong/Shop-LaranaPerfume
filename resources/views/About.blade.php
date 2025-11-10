<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giới thiệu-LARANA PERFUME</title>
    <link rel="stylesheet" href="{{ asset('Home.css') }}">
    <link rel="website icon" href="{{ asset('img/Logo.png') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;700&display=swap" rel="stylesheet">
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
    <!-- End header -->

    <!-- Main -->
        <div class="about">
            <h3>Cảm ơn các bạn rất nhiều vì đã tin tưởng và lựa chọn Larana</h3>
            <p>Các bạn có thể đến với LARANA, tâm sự với chúng mình, cùng chia sẻ cảm nhận của các bạn về các
                loại nước hoa bạn thích. Với các bạn đang đắn đo hay sử dụng lần đầu cũng đừng ngại nhé,
                mình sẽ cố gắng trả lời các bạn nhiều nhất, review sản phẩm tốt nhất để các bạn chọn được
                hương thơm ưng ý nhất.</p>
            <span>LARANA love you!</span>
        </div>

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
</body>
</html>