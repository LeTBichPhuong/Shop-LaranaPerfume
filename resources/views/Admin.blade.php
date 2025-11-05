<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản Lý Shop - Larana Perfume</title>
    <link rel="stylesheet" href="{{ asset('Admin.css') }}"> 
    <link rel="website icon" href="{{ asset('img/Logo.png') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Nút mở/đóng sidebar chỉ hiện trên responsive iPhone & iPad -->
    <div class="menu-toggle" id="menuToggle">
        <i class="fa fa-bars"></i>
    </div>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/Logo.png') }}" alt="" style="width: 40px; height: 40px;">
            <h3>LARANA</h3>
            <ul class="icon-list">
                <li class="icon-item notification-container">
                    <i id="bell-icon" class="fa fa-bell"></i>
                    <span id="notificationBadge" class="notification-badge">0</span>
                    <ul id="notificationList" class="notification-list"></ul>
                </li>
                <li class="icon-item">
                    <i id="cog-icon" class="bx bx-cog"></i>
                </li>
            </ul>
        </div>
        <ul class="sidebar-menu">
            <li data-section="dashboard" class="active"><i class="bx bx-home-alt"></i>Thống kê bán hàng</li>
            <li data-section="products"><i class="bx bx-package"></i> Quản lý sản phẩm</li>
            <li data-section="brands"><i class="bx bx-store"></i> Quản lý thương hiệu</li>
            <li data-section="users"><i class="bx bx-user"></i> Quản lý người dùng</li>
            <li data-section="orders"><i class="bx bx-cart"></i> Quản lý đơn hàng</li>
        </ul>
        <div class="sidebar-footer">
            <ul>
                <li id="help-icon"><i class='bx bx-chat'></i>Help & Support</li>
                <li id="user-icon">
                    <i class='bx bx-user-circle'></i>
                    <p id="userInfo" class="text-primary fw-bold"></p>
                    <i class='bx bx-chevron-up'></i>
                    <ul id="userDropdown" class="dropdown-menu">
                        <li id="logoutBtn" style="cursor: pointer;"><i class="bx bx-log-out"></i> Đăng xuất</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Main Content - Form Cài Đặt -->
        <section class="section" id="settings">
            <h2>Cài Đặt Shop</h2>
            <div class="settings-form">
                <!-- Quản lý tài khoản -->
                <section class="setting-section">
                    <h3>Quản Lý Tài Khoản</h3>
                    <button id="togglePasswordForm" class="btn-save">Đổi Mật Khẩu Admin</button>
                    <!-- Form đổi mật khẩu (ẩn mặc định) -->
                    <form id="passwordForm" class="hidden">
                        <div class="form-group">
                            <label for="currentPassword">Mật Khẩu Hiện Tại:</label>
                            <input type="password" id="currentPassword" name="currentPassword" placeholder="Nhập mật khẩu hiện tại" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Mật Khẩu Mới:</label>
                            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu mới" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Xác Nhận Mật Khẩu:</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Xác nhận mật khẩu" required>
                        </div>
                        <button type="submit" class="btn-save">Cập Nhật Mật Khẩu</button>
                        <button type="button" id="cancelPasswordForm" class="btn-out">Hủy</button>
                    </form>
                </section>
                <!-- Xuất dữ liệu -->
                <section class="setting-section">
                    <h3>Xuất Dữ Liệu Shop</h3>
                    <button id="exportExcelBtn" class="btn-save">Xuất File Excel Đơn Hàng</button>
                </section>
                <!-- Xóa dữ liệu -->
                <section class="setting-section">
                    <h3>Xóa Dữ Liệu</h3>
                    <button data-section="clear-data" id="clearDataBtnSettings" class="btn-out">Xóa Tất Cả Dữ Liệu Shop</button>
                </section>
            </div>
        </section>

        <!-- Thống kê bán hàng -->
        <section id="dashboard" class="section active">
            <h2>Thống kê bán hàng</h2>
            <div class="date-range-picker">
                <form onsubmit="event.preventDefault(); loadDataByDateRange();"> 
                    <div class="form-group">
                        <label for="dateStart">Từ ngày</label>
                        <input type="date" class="form-control" id="dateStart" required>
                    </div>
                    <div class="form-group">
                        <label for="dateEnd">Đến ngày</label>
                        <input type="date" class="form-control" id="dateEnd" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Xem báo cáo</button>
                    <button class="btn btn-outline-secondary" type="button" onclick="setLast30Days()">30 ngày gần nhất</button>
                </form>
            </div>

            <!-- Thẻ thống kê -->
            <div class="stats-grid">
                <div class="stat-card income">
                    <div class="stat-title">Tổng Đơn Hàng</div>
                    <div class="stat-value" id="totalOrders">0</div>
                    <small class="text-muted">Trong khoảng thời gian đã chọn</small>
                </div>
                <div class="stat-card expense">
                    <div class="stat-title">Doanh Thu (₫)</div>
                    <div class="stat-value" id="totalRevenue">0 VND</div>
                    <small class="text-muted">Trong khoảng thời gian đã chọn</small>
                </div>
                <div class="stat-card savings">
                    <div class="stat-title">Số Lượng Sản Phẩm</div>
                    <div class="stat-value" id="totalProducts">0</div>
                    <small class="text-muted">Tổng sản phẩm có sẵn</small>
                </div>
            </div>

            <!-- Biểu đồ -->
            <div class="unified-chart">
                <h5 class="card-title mb-4">Biểu đồ Tổng hợp Đơn Hàng & Doanh Thu</h5>
                <div class="chart-container">
                    <canvas id="unifiedChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color income-color"></div>
                        <span>Đơn Hàng</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color expense-color"></div>
                        <span>Doanh Thu</span>
                    </div>
                </div>
            </div>

            <!-- Phân tích đơn hàng và sản phẩm -->
            <div class="breakdown-container">
                <div class="breakdown-card">
                    <div class="card-body">
                        <h5 class="card-title">Chi Tiết Đơn Hàng</h5>
                        <ul class="list-group" id="ordersBreakdown">
                            <!-- Thiết kế bằng JavaScript từ DB -->
                        </ul>
                    </div>
                </div>
                <div class="breakdown-card">
                    <div class="card-body">
                        <h5 class="card-title">Sản Phẩm Bán Chạy</h5>
                        <ul class="list-group" id="productsBreakdown">
                            <!-- Thiết kế bằng JavaScript từ DB -->
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products - Quản lý sản phẩm -->
       <section id="products" class="section">
            <h2>Quản lý sản phẩm</h2>

            <button id="toggleProductFormBtn" class="btn btn-success mb-3">
                <i class="bi bi-plus-circle"></i> Thêm sản phẩm
            </button>
            
            <form id="productForm" style="display: none;">
                <div class="form-group mb-3">
                    <label for="productName">Tên sản phẩm</label>
                    <input type="text" class="form-control" id="productName" placeholder="Tên sản phẩm" required>
                </div>

                <div class="form-group mb-3">
                    <label for="productPrice">Giá</label>
                    <input type="number" class="form-control" id="productPrice" placeholder="Giá" step="0.01" required>
                </div>

                <div class="form-group mb-3">
                    <label for="productCategory">Thương hiệu</label>
                    <select class="form-select" id="productCategory" required>
                        <option value="" disabled selected>-- Chọn thương hiệu --</option>
                        <!-- Load từ DB -->
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="productGender">Giới tính</label>
                    <select class="form-select" id="productGender">
                        <option value="" disabled selected>-- Chọn giới tính --</option>
                        <option value="nam">Nam</option>
                        <option value="khac">Nữ</option>
                        <option value="unisex">Unisex</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="productDescription">Mô tả</label>
                    <textarea class="form-control" id="productDescription" placeholder="Mô tả sản phẩm" rows="3"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="productImage">Hình ảnh</label>
                    <input type="file" class="form-control" id="productImage" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Thêm Sản Phẩm</button>
            </form>

            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Thương Hiệu</th>
                            <th>Giá</th>
                            <th>Giới Tính</th>
                            <th>Mô Tả</th>
                            <th>Hình Ảnh</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="productTable">
                        <!-- Load từ DB bằng JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Brands - Quản lý thương hiệu -->
        <section id="brands" class="section">
            <h2>Quản lý thương hiệu</h2>

           <button id="toggleBrandFormBtn" class="btn btn-success mb-3">
                <i class="bi bi-plus-circle"></i> Thêm thương hiệu
            </button>

            <form id="brandForm" style="display: none;">
                <div class="form-group">
                    <label for="brandName">Tên thương hiệu</label>
                    <input type="text" class="form-control" id="brandName" placeholder="Tên thương hiệu" required>
                </div>
                <div class="form-group">
                    <label for="brandLogo">Logo</label>
                    <input type="file" class="form-control" id="brandLogo" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Thêm Thương Hiệu</button>
            </form>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Logo</th>
                            <th>Số Sản Phẩm</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="brandTable">
                        <!-- Load từ DB bằng JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Users - Quản lý người dùng -->
        <section id="users" class="section">
            <h2>Quản lý người dùng</h2>

            <button id="toggleUserFormBtn" class="btn btn-success mb-3">
                <i class="bi bi-plus-circle"></i> Thêm người dùng
            </button>

            <form id="userForm" style="display: none;">
                <div class="form-group mb-3">
                    <label for="userName">Tên đầy đủ *</label>
                    <input type="text" class="form-control" id="userName" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="form-group mb-3">
                    <label for="userUsername">Tên đăng nhập *</label>
                    <input type="text" class="form-control" id="userUsername" placeholder="nguyenvana" required>
                </div>
                <div class="form-group mb-3">
                    <label for="userEmail">Email *</label>
                    <input type="email" class="form-control" id="userEmail" placeholder="email@example.com" required>
                </div>
                <div class="form-group mb-3">
                    <label for="userPassword">Mật khẩu *</label>
                    <input type="password" class="form-control" id="userPassword" placeholder="Tối thiểu 6 ký tự" required minlength="6">
                </div>
                <div class="form-group mb-3">
                    <label for="userRole">Vai trò</label>
                    <select class="form-select" id="userRole">
                        <option value="user">Người dùng</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Thêm Người Dùng</button>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Số Đơn Hàng</th>
                            <th>Vai Trò</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <!-- Load từ DB bằng JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Orders - Quản lý đơn hàng -->
        <section id="orders" class="section">
            <h2>Quản lý đơn hàng</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách Hàng</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Phương Thức TT</th>
                            <th>Mã giao dịch</th>
                            <th>Ngày Tạo</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="orderTable">
                        <!-- Load từ DB bằng JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Xóa dữ liệu -->
        <section id="clear-data" class="section">
            <h2>Xóa dữ liệu</h2>
            <div class="clear-data-container">
                <p class="text-warning">Cảnh báo: Hành động này sẽ xóa tất cả dữ liệu shop!</p>
                <button class="btn btn-danger" id="clearDataBtn">Xóa Tất Cả Dữ Liệu</button>
            </div>
        </section>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('JS/Admin.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</body>
</html>