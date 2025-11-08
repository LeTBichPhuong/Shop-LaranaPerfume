<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;

class AccountController extends Controller
{
    // Hiển thị trang tài khoản
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Load quan hệ items để lấy sản phẩm
        $orders = Order::where('user_id', $user->id)
                      ->with('items') 
                      ->orderBy('created_at', 'desc')
                      ->get();
        // $orders = $user->orders()->orderBy('created_at', 'desc')->get();

        return view('Account', compact('user', 'orders'));
    }

    // Cập nhật thông tin cơ bản của tài khoản
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Cập nhật thông tin tài khoản thành công!');
    }

    // Cập nhật địa chỉ người dùng
    public function updateAddress(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone' => 'required|string|max:15',
        ]);

        $user->address = $request->address;
        $user->city = $request->city;
        $user->phone = $request->phone;
        $user->save();

        return back()->with('success', 'Cập nhật địa chỉ thành công!');
    }

    // Đổi mật khẩu
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng!');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    // Xem chi tiết đơn hàng
    public function showOrder($id)
    {
        $order = Order::with('items')
                     ->where('user_id', Auth::id())
                     ->findOrFail($id);

        return view('order-detail', compact('order'));
    }

    // Hủy đơn hàng
    public function cancelOrder($id)
    {
        try {
            $order = Order::where('user_id', Auth::id())
                         ->where('id', $id)
                         ->firstOrFail();

            // Chỉ cho phép hủy đơn pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn hàng đã xử lý!'
                ], 400);
            }

            $order->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy đơn hàng thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
