<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tài khoản của tôi-LARANA PERFUME</title>
    <link rel="stylesheet" href="{{ asset('Account.css') }}">
    <link rel="website icon" href="{{ asset('img/Logo.png') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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

    <!-- Nội dung tài khoản -->
    
    <main class="account-container">
        <div class="account-info">
            Tài khoản của tôi
        </div>
        <aside class="account-sidebar">
            <ul>
                <li><a href="#" data-section="orders">Đơn hàng</a></li>
                <li><a href="#" data-section="downloads">Tệp tải xuống</a></li>
                <li><a href="#" data-section="address">Địa chỉ</a></li>
                <li><a href="#" data-section="info" class="active">Tài khoản</a></li>
                <li><a href="#" data-section="logout">Đăng xuất</a></li>
            </ul>
        </aside>

        <section class="account-main">
            <!-- Đơn hàng -->
            <div id="orders" class="content-section" style="display:none;">
                <h2>Đơn hàng của tôi</h2>
                
                @if($orders->isEmpty())
                    <div class="empty-state">
                        <i class="fa fa-shopping-bag fa-3x text-muted mb-3"></i>
                        <p>Bạn chưa có đơn hàng nào.</p>
                        <a href="{{ route('products.list') }}" class="btn btn-primary">Mua sắm ngay</a>
                    </div>
                @else
                    @foreach($orders as $order)
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3>Đơn hàng #{{ $order->id }}</h3>
                                    <p class="order-date">
                                        <i class="fa fa-calendar"></i> 
                                        Ngày đặt: {{ $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="order-status">
                                    <span class="badge badge-{{ $order->status }}">
                                        @switch($order->status)
                                            @case('pending')
                                                Chờ xử lý
                                                @break
                                            @case('processing')
                                                Đang xử lý
                                                @break
                                            @case('shipping')
                                                Đang giao
                                                @break
                                            @case('completed')
                                                Hoàn thành
                                                @break
                                            @case('cancelled')
                                                Đã hủy
                                                @break
                                            @default
                                                {{ ucfirst($order->status) }}
                                        @endswitch
                                    </span>
                                </div>
                            </div>

                            @if($order->items && $order->items->count() > 0)
                                <table class="order-items-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Ảnh</th>
                                            <th>Tên sản phẩm</th>
                                            <th style="width: 100px;">Số lượng</th>
                                            <th style="width: 150px;">Giá</th>
                                            <th style="width: 150px;">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <img src="{{ asset($item->product_image ?? 'img/placeholder-product.png') }}" 
                                                    alt="{{ $item->product_name }}" 
                                                    class="product-thumb">
                                            </td>
                                            <td>
                                                <strong>{{ $item->product_name }}</strong>
                                            </td>
                                            <td class="text-center">
                                                x{{ $item->quantity }}
                                            </td>
                                            <td>
                                                {{ \App\Helpers\Helpers::format($order->total) }}
                                            </td>
                                            <td>
                                                <strong>{{ \App\Helpers\Helpers::format($order->total) }}</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted">Không có sản phẩm trong đơn hàng này.</p>
                            @endif

                            <div class="order-footer">
                                <div class="order-total">
                                    <span>Phương thức thanh toán:</span>
                                    <strong>
                                        @if($order->payment_method == 'COD')
                                            Thanh toán khi nhận hàng
                                        @elseif($order->payment_method == 'QR')
                                            Thanh toán QR
                                        @else
                                            {{ $order->payment_method }}
                                        @endif
                                    </strong>
                                </div>
                                <div class="order-total">
                                    <span>Tổng cộng:</span>
                                    <strong class="text-danger">{{ \App\Helpers\Helpers::format($order->total) }}</strong>
                                </div>
                                
                                @if($order->status == 'pending')
                                    <button class="btn btn-outline-danger btn-sm cancel-order" 
                                            data-order-id="{{ $order->id }}">
                                        <i class="fa fa-times"></i> Hủy đơn
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Tệp tải xuống -->
            <div id="downloads" class="account-section">
                <h2>Tệp tải xuống</h2>
                <p>Chưa có tệp nào được tải xuống.</p>
            </div>

            <!-- Địa chỉ -->
            <div id="address" class="account-section">
                <h2>Cập nhật địa chỉ</h2>
                @if(empty($user->address))
                    <p>Chưa thiết lập địa chỉ.</p>
                @else
                    <p><strong>Địa chỉ:</strong> {{ $user->address }}</p>
                    <p><strong>Thành phố:</strong> {{ $user->city }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $user->phone }}</p>
                @endif

                <button type="button" onclick="toggleForm('addressForm')">Thêm / Cập nhật địa chỉ</button>

                <form id="addressForm" action="{{ route('account.update.address') }}" method="POST" class="hidden">
                    @csrf
                    <label>Địa chỉ</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" required>

                    <label>Thành phố</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" required>

                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required>

                    <button type="submit">Lưu thay đổi</button>
                </form>
            </div>

            <!-- Thông tin tài khoản -->
            <div id="info" class="account-section">
                <h2>Thông tin tài khoản</h2>
                <p><strong>Tên:</strong> {{ Auth::user()->username }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>

                <button type="button" onclick="toggleForm('passwordForm')">Đổi mật khẩu</button>

                <form id="passwordForm" action="{{ route('account.changePassword') }}" method="POST" class="hidden">
                    @csrf
                    <label>Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" required>

                    <label>Mật khẩu mới</label>
                    <input type="password" name="new_password" required>

                    <label>Nhập lại mật khẩu mới</label>
                    <input type="password" name="new_password_confirmation" required>

                    <button type="submit">Xác nhận đổi mật khẩu</button>
                </form>
            </div>

            <!-- Đăng xuất -->
            <div id="logout" class="account-section">
                <h2>Đăng xuất</h2>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Đăng xuất</button>
                </form>
            </div>
        </section>
    </main>

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
