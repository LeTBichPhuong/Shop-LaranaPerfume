<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminBrandController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\ChatbotController;
use App\Models\Cart;

/*
|--------------------------------------------------------------------------
| Health Check Route
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toIso8601String(),
    ], 200);
});

// Trang chủ
Route::get('/', [ProductController::class, 'home'])->name('home');

// Auth + Account
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.post');
    Route::get('/dang-ky', [AuthController::class, 'showSignupForm'])->name('signup');
    Route::post('/dang-ky', [AuthController::class, 'signup'])->name('signup.post');
    Route::get('/quen-mat-khau', function () {
        return 'Chức năng quên mật khẩu đang được phát triển.';
    })->name('password.request');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/tai-khoan', [AccountController::class, 'index'])->name('account');
    Route::post('/tai-khoan/cap-nhap', [AccountController::class, 'update'])->name('account.update');
    Route::post('/tai-khoan/cap-nhap-dia-chi', [AccountController::class, 'updateAddress'])->name('account.update.address');
    Route::post('/tai-khoan/thay-doi-mat-khau', [AccountController::class, 'changePassword'])->name('account.changePassword');
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
});

// Admin
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/he-thong-quan-ly', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Products 
    Route::get('products', [AdminController::class, 'indexProducts'])->name('products.index');
    Route::post('products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::post('products/{id}', [AdminController::class, 'updateProduct'])->name('products.update'); 
    Route::delete('products/{id}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    Route::post('products/import', [AdminProductController::class, 'import'])->name('products.import'); 
    
    // Brands 
    Route::get('brands', [AdminController::class, 'indexBrands'])->name('brands.index');
    Route::post('brands', [AdminController::class, 'storeBrand'])->name('brands.store');
    Route::post('brands/{id}', [AdminController::class, 'updateBrand'])->name('brands.update'); 
    Route::delete('brands/{id}', [AdminController::class, 'destroyBrand'])->name('brands.destroy');

    // Users
    Route::resource('users', AdminUserController::class);
    
    // Orders
    Route::resource('orders', AdminOrderController::class);
    Route::post('orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/export', [AdminOrderController::class, 'export'])->name('orders.export');

    // thay may khẩu
    Route::post('change-password', [AdminController::class, 'changePassword'])->name('settings.change-password');
    Route::get('export-orders', [AdminController::class, 'exportOrders'])->name('settings.export-orders');
});

// Sản phẩm
Route::get('/san-pham/import', [ProductController::class, 'import'])->name('products.import');
Route::get('/san-pham-json', [ProductController::class, 'getJson'])->name('products.json');
Route::get('/san-pham/gioi-tinh/{gender}', [ProductController::class, 'showByGender'])
    ->where('gender', 'nam|khac|unisex')
    ->name('products.gender');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.list');
Route::get('/san-pham/{product}', [ProductController::class, 'show'])->name('products.show');

// Thương hiệu
Route::get('/thuong-hieu', [BrandController::class, 'brand'])->name('brand');
Route::get('/thuong-hieu/{brand}/san-pham-json', [BrandController::class, 'productsJson'])->name('brands.products.json'); 
Route::get('/thuong-hieu/{brand}', [BrandController::class, 'show'])->name('brands.show'); 
Route::get('/thuong-hieu-json', [BrandController::class, 'getJson'])->name('brand.json');

// Giới thiệu
Route::get('/gioi-thieu', function () {
    return view('About');
})->name('about');

// Blog
Route::get('/bai-viet', function () {
    return view('Blog');
})->name('blog');

// tìm kiếm
Route::get('/tim-kiem', [SearchController::class, 'search'])->name('search');
Route::get('/ajax/tim-kiem', [SearchController::class, 'ajaxSearch'])->name('ajax.search');

// Giỏ hàng
Route::middleware('auth')->group(function () {
    Route::get('/gio-hang', [CartController::class, 'index'])->name('cart');
    Route::post('/gio-hang/add/{id}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/gio-hang/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/gio-hang/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/gio-hang/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/gio-hang/thanh-toan', [CartController::class, 'showCheckout'])->name('checkout');

    // Đơn hàng
    Route::get('/don-hang', [AccountController::class, 'index'])->name('orders');
    Route::get('/don-hang/{id}', [AccountController::class, 'showOrder'])->name('orders.show');
    
    
    // hủy đơn hàng
    Route::post('/don-hang/cancel/{id}', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
});

// chatbot
Route::post('/chatbot', [ChatbotController::class, 'send']);

// Qr thanh toán
Route::get('/generate-qr', [CartController::class, 'generateQR'])->name('generate.qr');
Route::get('/proxy-qr', [CartController::class, 'proxyQR'])->name('proxy.qr');