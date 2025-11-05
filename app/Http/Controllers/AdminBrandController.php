<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class AdminBrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $brands = Brand::withCount('products');

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            $result = $brands->get();
            return response()->json(['data' => $result]);
        }

        $brands = $brands->paginate(20);
        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:brands',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm thương hiệu thành công!',
                'data' => $brand
            ], 201);
        }

        return redirect()->route('admin.brands.index')->with('success', 'Thêm thương hiệu thành công!');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa thương hiệu có sản phẩm!'
                ], 422);
            }
            return back()->with('error', 'Không thể xóa thương hiệu có sản phẩm!');
        }

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công!'
            ]);
        }

        return redirect()->route('admin.brands.index')->with('success', 'Xóa thành công!');
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|unique:brands,name,' . $brand->id,
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Cập nhật thành công!');
    }
}