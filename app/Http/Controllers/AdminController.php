<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\OrdersExport;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard()
    {
        $soldOrdersCount = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])->count();
        $revenue = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])->sum('total');
        $customerCount = User::whereHas('orders')->count();
        $totalOrders = Order::count();
        $monthlyRevenue = Order::select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as revenue'))
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->groupBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        return view('admin', compact('soldOrdersCount', 'revenue', 'customerCount', 'totalOrders', 'monthlyRevenue'));
    }

// Products
    public function indexProducts()
    {
        $products = Product::with('brand')->get();
        return response()->json(['data' => $products]);
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name', 
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'gender' => 'nullable|in:nam,khac,unisex',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0', // default set in code
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name', 'brand_id', 'price', 'gender', 'description', 'stock']);
        // Sanitize and default values
        $rawPrice = $request->input('price', 0);
        $data['price'] = (int) preg_replace('/[^\d]/', '', (string) $rawPrice);
        $data['stock'] = (int) $request->input('stock', 0);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json(['message' => 'Sản phẩm đã được tạo thành công', 'data' => $product]);
    }

   public function updateProduct(Request $request, $id) // Use $id, find manually
    {
        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', "unique:products,name,{$product->id}"],
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'gender' => 'nullable|in:nam,khac,unisex',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name', 'brand_id', 'price', 'gender', 'description', 'stock']);
        $rawPrice = $request->input('price', 0);
        $data['price'] = (int) preg_replace('/[^\d]/', '', (string) $rawPrice);
        $data['stock'] = (int) $request->input('stock', 0);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json(['message' => 'Sản phẩm đã được cập nhật thành công', 'data' => $product]);
    }

    public function destroyProduct($id) // Use $id to avoid binding issues
    {
        try {
            $product = Product::findOrFail($id);
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            return response()->json(['message' => 'Sản phẩm đã được xóa thành công']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }
    }

    // Brands
    public function indexBrands()
    {
        $brands = Brand::withCount('products')->get();
        return response()->json(['data' => $brands]);
    }

    public function storeBrand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'logo' => 'nullable|image|max:2048'
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        return response()->json(['message' => 'Thương hiệu đã được tạo thành công', 'data' => $brand]);
    }

    public function updateBrand(Request $request, $id)
    {
        try {
            $brand = Brand::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Thương hiệu không tồn tại'], 404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', "unique:brands,name,{$brand->id}"],
            'logo' => 'nullable|image|max:2048'
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        return response()->json(['message' => 'Thương hiệu đã được cập nhật thành công', 'data' => $brand]);
    }

    public function destroyBrand($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $brand->delete();
            return response()->json(['message' => 'Thương hiệu đã được xóa thành công']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Thương hiệu không tồn tại'], 404);
        }
    }

    // Orders
    public function indexOrders()
    {
        $orders = Order::with(['user', 'items.product'])->get();
        return response()->json(['data' => $orders]);
    }

    public function updateOrderStatus(Order $order, Request $request)
    {
        $vietnameseToEnglish = [
            'Chờ xử lý' => 'pending',
            'Đã xác nhận' => 'confirmed',
            'Đang giao' => 'shipped',
            'Đã giao' => 'delivered',
            'Đã hủy' => 'cancelled'
        ];

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys($vietnameseToEnglish))
        ]);

        $englishStatus = $vietnameseToEnglish[$request->status] ?? $request->status;
        $order->update(['status' => $englishStatus]);

        return response()->json(['message' => 'Trạng thái đã được cập nhật thành công']);
    }

    public function destroyOrder(Order $order)
    {
        $order->items()->delete();
        $order->delete();

        return response()->json(['message' => 'Đơn hàng đã được xóa thành công']);
    }

    // Settings
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Mật khẩu đã được thay đổi thành công']);
    }

    public function exportOrders()
    {
        $orders = Order::with(['user', 'items.product'])->get();
        return Excel::download(new OrdersExport($orders), 'don-hang-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function clearData(Request $request)
    {
        if (!$request->isMethod('delete')) {
            abort(405, 'Method Not Allowed');
        }

        Order::truncate();
        OrderItem::truncate();
        Product::truncate();
        Brand::truncate();
        User::where('role', '!=', 'admin')->delete();

        return response()->json(['message' => 'Dữ liệu đã được xóa thành công']);
    }
}