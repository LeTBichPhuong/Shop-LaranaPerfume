<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function getBrands()
    {
        $brands = Brand::select('id', 'name', 'logo')->orderBy('name')->get();
        return response()->json($brands);
    }

    // Hiển thị trang giao diện thương hiệu
    public function brand()
    {
        return view('Brand');
    }

    // Trả dữ liệu JSON
    public function getJson()
    {
        return response()->json(Brand::select('id', 'name', 'logo')->get());
    }

    // Hiển thị trang chi tiết thương hiệu với sản phẩm (lấy thêm all brands cho sidebar)
    public function show(Brand $brand)
    {
        $products = Cache::remember("brand_{$brand->id}_products", 3600, function () use ($brand) {
            return $brand->products()->with('brand')->paginate(12);
        });

        // Lấy tất cả brands cho sidebar list (trừ brand hiện tại nếu muốn)
        $allBrands = Brand::where('id', '!=', $brand->id)->orderBy('name')->get();

        return view('Layouts.MainBrand', compact('brand', 'products', 'allBrands'));
    }

    // API JSON sản phẩm cho brand cụ thể
    public function productsJson(Brand $brand, Request $request)
    {
        $products = Cache::remember("brand_{$brand->id}_products_page_{$request->get('page', 1)}", 3600, function () use ($brand) {
            return $brand->products()->with('brand')->paginate(12);
        });

        return response()->json([
            'brand' => $brand,
            'products' => $products
        ]);
    }
}