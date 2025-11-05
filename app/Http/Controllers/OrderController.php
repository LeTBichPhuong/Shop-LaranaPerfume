<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    // Bắt buộc đăng nhập
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Lấy danh sách đơn hàng của người dùng hiện tại
     */
    public function index(Request $request)
    {
        // Lấy user hiện tại
        $user = Auth::user();

        // Lấy đơn hàng của người đó
        $orders = Order::with(['items.product', 'user'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        if ($request->ajax()) {
            return response()->json(['data' => $orders]);
        }

        return view('orders', compact('orders'));
    }

    /**
     * Xem chi tiết 1 đơn hàng
     */
    public function show(Order $order)
    {
        // Kiểm tra quyền
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $order->load(['items.product', 'user']);
        return view('orders.show', compact('order'));
    }
}
