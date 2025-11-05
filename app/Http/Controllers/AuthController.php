<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class AuthController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function home()
    {
        $user = Auth::user();
        return view('home', ['user' => $user]);
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Admin vào thẳng trang quản lý
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Chào mừng đến với bảng điều khiển admin!');
            } 

            // User thường vào trang tài khoản
            $intended = $request->session()->get('url.intended', route('account'));
            return redirect($intended)->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'username' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ])->withInput();
    }

    /**
     * Hiển thị form đăng ký
     */
    public function showSignupForm()
    {
        return view('signup');
    }

    /**
     * Xử lý đăng ký
     */
    public function signup(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);

            Auth::login($user);
            return redirect()->route('account')->with('success', 'Đăng ký thành công!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Lỗi đăng ký: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi khi đăng ký. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Đăng xuất thành công!');
    }

    /**
     * Hiển thị form quên mật khẩu
     */
    public function showForgotPasswordForm()
    {
        return view('forgot-password');
    }

    /**
     * Hiển thị trang tài khoản
     */
    public function account()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect('/dang-nhap')->with('error', 'Vui lòng đăng nhập.');
        }

        $orders = $user->orders()->orderBy('created_at', 'desc')->get(); 
        return view('account', [
            'user' => $user,
            'orders' => $orders
        ]);
    }

    /**
     * Cập nhật địa chỉ
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
        ]);

        $user = Auth::user();
        dd($user);
        $user->update([
            'address' => $request->address,
            'city' => $request->city,
        ]);

        return redirect()->back()->with('success', 'Địa chỉ đã được cập nhật!');
    }
}