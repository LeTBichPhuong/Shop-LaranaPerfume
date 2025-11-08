<?php

namespace App\Http\Controllers;

use App\Helper\helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

class CartController extends Controller
{
    // Hiển thị giỏ hàng
    public function index()
    {
        $userId = Auth::id();
        $cartItems = Cart::where('user_id', $userId)->get();

        $total = 0;
        foreach ($cartItems as $item) {
            $price = $this->parsePrice($item->price);
            Log::info("Cart item {$item->id}: raw price='{$item->price}', parsed={$price}");
            $total += $price * $item->quantity;
        }

        Log::info("Cart total: {$total}");  

        return view('Cart', compact('cartItems', 'total'));
    }

    // Thêm sản phẩm vào giỏ
    public function add($id)
    {
        $userId = Auth::id();
        $product = Product::findOrFail($id);

        $cartItem = Cart::where('user_id', $userId)
                        ->where('product_id', $id)
                        ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->image,
                'quantity' => 1,
                'price' => $product->price ?? '0', // Lưu dạng string như database
            ]);
        }

        return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    // Xóa sản phẩm khỏi giỏ
    public function remove($id)
    {
        $userId = Auth::id();
        Cart::where('user_id', $userId)->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm!']);
    }

    // Cập nhật số lượng sản phẩm
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $cartItem = Cart::where('user_id', $userId)->where('id', $id)->first();

        if ($cartItem && $request->quantity > 0) {
            $cartItem->update(['quantity' => $request->quantity]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    // Thanh toán
    public function checkout(Request $request)
{
    try {
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập!'], 401);
        }
        
        $cartItems = Cart::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống!']);
        }

        // Validate đầu vào
        $validated = $request->validate([
            'payment_method'   => 'required|in:COD,QR',
            'transaction_code' => 'nullable|string|max:50',
        ]);

        // Tính tổng
        $total = 0;
        foreach ($cartItems as $item) {
            $price = $this->parsePrice($item->price);
            Log::info("Checkout item {$item->id}: raw price='{$item->price}', parsed={$price}, qty={$item->quantity}");
            $total += $price * $item->quantity;
        }

        Log::info("Checkout total: {$total}");

        $paymentMethod   = $validated['payment_method'];
        $transactionCode = null;

        // Xử lý thanh toán QR
        if ($paymentMethod === 'QR') {
            $inputCode   = strtoupper($validated['transaction_code'] ?? '');
            $storedCode  = strtoupper(session('qr_code') ?? '');
            $expireTime  = session('qr_expire') ?? null;

            if (empty($inputCode)) {
                return response()->json(['success' => false, 'message' => 'Vui lòng nhập mã giao dịch!'], 400);
            }

            if ($inputCode !== $storedCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giao dịch không hợp lệ! Mã đúng: ' . $storedCode
                ], 400);
            }

            if (!empty($expireTime) && now()->greaterThan($expireTime)) {
                return response()->json(['success' => false, 'message' => 'Mã QR đã hết hạn!'], 400);
            }

            // Nếu mã đúng, lưu transaction_code
            $transactionCode = $inputCode;

            // Xoá session QR sau khi xác nhận thành công
            session()->forget(['qr_code', 'qr_expire']);

            Log::info("QR verified", [
                'user_id' => $userId,
                'code' => $transactionCode
            ]);
        }

        // Lưu đơn hàng
        $order = Order::create([
            'user_id'          => $userId,
            'status'           => 'pending',
            'total'            => $total,
            'payment_method'   => $paymentMethod,
            'transaction_code' => $transactionCode,
        ]);

        // Lưu chi tiết đơn hàng
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_name'  => $item->product_name,
                'product_image' => $item->product_image,
                'quantity'      => $item->quantity,
                'price'         => $item->price,
            ]);
        }

        // Xoá giỏ hàng sau khi đặt thành công
        Cart::where('user_id', $userId)->delete();

        Log::info('Order created successfully', [
            'order_id' => $order->id,
            'user_id' => $userId,
            'payment_method' => $paymentMethod,
            'transaction_code' => $transactionCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đặt hàng thành công!',
            'order_id' => $order->id,
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ!',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        Log::error('Checkout error', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
    }
}

    // Helper method để parse price 
    private function parsePrice($priceString)
    {
        // Loại bỏ dấu chấm, ₫, etc.
        $cleanPrice = preg_replace('/[^\d]/', '', (string) $priceString);
        $price = (float) $cleanPrice;

        // Nếu price >= 10.000.000 và chia hết cho 100
        if ($price >= 10000000 && $price % 100 === 0) {
            $price /= 100;
        }

        return $price;
    }

    /**
     * Tạo QR bằng Google Charts và convert sang base64 để tránh CORS
     */
    public function generateQR(Request $request)
    {
        try {
            Log::info('QR Generation started');

            // Thông tin thanh toán
            $bankAccount = env('BANK_ACCOUNT', '270929249');
            $bankName = env('BANK_NAME', 'VPBank');
            $bankBin = env('BANK_BIN', '970432');
            $accountName = env('ACCOUNT_NAME', 'LE THI BICH PHUONG'); 
            $amount = session('checkout_amount', 500000);

            // Tạo mã giao dịch DUY NHẤT
            $randomCode = strtoupper(substr(md5(time() . rand()), 0, 8));
            $expireTime = now()->addMinutes(5);
            
            // Nội dung chuyển khoản 
            $transferMessage = "DH" . $randomCode;
            
            // Tạo QR bằng VietQR API 
            $vietQRUrl = "https://img.vietqr.io/image/{$bankBin}-{$bankAccount}-compact2.jpg?" . http_build_query([
                'amount' => $amount,
                'addInfo' => $transferMessage,
                'accountName' => $accountName
            ]);
            
            // Nội dung hiển thị cho user
            $qrContent = "Ngân hàng: {$bankName}\nSố TK: {$bankAccount}\nChủ TK: {$accountName}\nSố tiền: " . number_format($amount) . " VND\nNội dung CK: {$transferMessage}";

            // Lưu session (Lưu đầy đủ thông tin)
            session([
                'qr_code' => $randomCode,
                'qr_transfer_code' => $transferMessage, 
                'qr_expire' => $expireTime->format('Y-m-d H:i:s'),
            ]);

            Log::info('VietQR generated', [
                'random_code' => $randomCode,
                'transfer_code' => $transferMessage,
                'url' => $vietQRUrl,
                'amount' => $amount
            ]);

            return response()->json([
                'success' => true,
                'qr_image' => $vietQRUrl,
                'content' => "Ngân hàng: {$bankName}\nSố TK: {$bankAccount}\nSố tiền: " . number_format($amount) . " VND\nNội dung CK: {$transferMessage}",
                'expire_time' => $expireTime->format('Y-m-d H:i:s'),
                'qr_code' => $randomCode,
                'transfer_code' => $transferMessage, 
                'bank_info' => [
                    'bank' => $bankName,
                    'account' => $bankAccount,
                    'account_name' => $accountName,
                    'amount' => $amount
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('QR Generation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo mã QR. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * Proxy để load ảnh QR
     */
    public function proxyQR(Request $request)
    {
        $url = $request->query('url');
        
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response('Invalid URL', 400);
        }

        try {
            $imageContent = Http::timeout(5)->get($url)->body();
            return response($imageContent)->header('Content-Type', 'image/png');
        } catch (\Exception $e) {
            return response('Failed to load QR', 500);
        }
    }

    /**
     * Hiển thị trang checkout
     */
    public function showCheckout()
    {
        $user = Auth::user();
        
        // Lấy giỏ hàng
        $cartItems = Cart::where('user_id', $user->id)->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Giỏ hàng trống!');
        }
        
        // Tính tổng (FIX: dùng parsePrice thay price_numeric)
        $total = $cartItems->sum(function($item) {
            return $this->parsePrice($item->price) * $item->quantity;
        });
        
        // Lưu total vào session cho QR
        session(['checkout_amount' => $total]);
        
        // Lấy phương thức thanh toán đã chọn
        $paymentMethod = session('payment_method', 'COD');
        
        return view('Layouts.MainCheckOut', compact('cartItems', 'total', 'user', 'paymentMethod'));
    }
}