<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Helpers\PriceHelper;

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

    // tách giá
    private function parsePrice($priceString)
    {
        if (!$priceString) return [null, null];

        $parts = preg_split('/đ/', $priceString);

        $clean = fn($x) => (int) str_replace('.', '', trim($x));

        if (count($parts) >= 2 && trim($parts[1]) !== '') {
            return [
                $clean($parts[0]),
                $clean($parts[1])
            ];
        }

        return [null, $clean($parts[0])];
    }

    // Hiển thị trang chi tiết thương hiệu với sản phẩm
    public function show(Brand $brand)
    {
        // Lấy sản phẩm có phân trang
        $products = Cache::remember("brand_{$brand->id}_products_page_" . request('page', 1), 3600, function () use ($brand) {
            return $brand->products()->with('brand')->paginate(12);
        });

        // tách giá
        foreach ($products as $p) {
            [$p->original_price, $p->final_price] = $this->parsePrice($p->price);
        }

        // Lấy danh sách brand hiển thị sidebar
        $allBrands = Brand::where('id', '!=', $brand->id)
                        ->orderBy('name')
                        ->get();

        return view('Layouts.MainBrand', [
            'brand'      => $brand,
            'products'   => $products,
            'allBrands'  => $allBrands
        ]);
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