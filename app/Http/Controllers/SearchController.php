<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;

class SearchController extends Controller
{
    // Trang kết quả tìm kiếm (nhấn Enter)
    public function search(Request $request)
    {
        $keyword = trim($request->input('keyword'));

        if (empty($keyword)) {
            return redirect()->route('home');
        }

        $products = Product::with('brand')
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->paginate(12);

        $brands = Brand::where('name', 'like', "%{$keyword}%")->get();

        return view('SearchResult', compact('keyword', 'products', 'brands'));
    }

    // API realtime trả JSON (cho JS gọi fetch)
    public function ajaxSearch(Request $request)
    {
        $keyword = trim($request->get('keyword', ''));

        if (empty($keyword)) {
            return response()->json(['products' => [], 'brands' => []]);
        }

        $products = Product::with('brand')
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->take(10)
            ->get(['id', 'brand_id', 'name', 'price', 'image']);

        $brands = Brand::where('name', 'like', "%{$keyword}%")
            ->take(5)
            ->get(['id', 'name', 'logo']);

        return response()->json([
            'products' => $products,
            'brands' => $brands,
        ]);
    }
}
