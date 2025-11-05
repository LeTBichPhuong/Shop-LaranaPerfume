<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $users = User::withCount('orders');

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            $result = $users->get();
            return response()->json(['data' => $result]);
        }

        $users = $users->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm user thành công!',
                'data' => $user
            ], 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'Thêm user thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin' && $user->id == 1) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa admin chính!'
                ], 422);
            }
            return back()->with('error', 'Không thể xóa admin chính!');
        }

        $user->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công!'
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Xóa thành công!');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
        ]);

        $data = $request->only(['username', 'email', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công!',
                'data' => $user->fresh(['orders'])
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật thành công!');
    }
}