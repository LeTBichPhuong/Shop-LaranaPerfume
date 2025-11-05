<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand; 
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Helper function để tải tất cả các thương hiệu
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadAllBrands()
    {
        if (class_exists(Brand::class) && method_exists(Brand::class, 'has')) {
            return Brand::has('products')->get();
        }
        return collect([]); 
    }   

    /**
     * Hiển thị trang chủ 
     * @return \Illuminate\View\View
     */
    public function home()
    {
        // Debug: Kiểm tra view có tồn tại không
        $viewPath = resource_path('views/Home.blade.php');
        
        if (!file_exists($viewPath)) {
            return response()->json([
                'error' => 'View not found',
                'path' => $viewPath,
                'views_dir' => scandir(resource_path('views'))
            ], 404);
        }
        
        return view('home');
    }
    
    // Hiển thị danh sách sản phẩm 
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $products = Product::with('brand')->get();
            return response()->json($products);
        }
        $products = Product::all();
        $allBrands = Brand::all();

        return view('Layouts.MainProduct', [
            'products' => $products,
            'allBrands' => $allBrands,
            'title' => 'SẢN PHẨM',
            'description' => 'Khám phá bộ sưu tập nước hoa cao cấp từ Larana Perfume.',
        ]);
    }

    // Import dữ liệu từ file JSON 
    public function import()
    {
        $path = storage_path('app/products.json');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Không tìm thấy file products.json'], 404);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        foreach ($data as $brandData) {
            $brand = Brand::firstOrCreate(
                ['name' => $brandData['name']],
                ['logo' => $brandData['logo'] ?? null]
            );

            if (!isset($brandData['products']) || !is_array($brandData['products'])) continue;

            foreach ($brandData['products'] as $productData) {
                Product::create([
                    'brand_id'   => $brand->id,
                    'name'       => $productData['name'],
                    'price'      => $productData['price'] ?? '0',
                    'description'=> $productData['description'] ?? '',
                    'image'      => $productData['image'] ?? null,
                    'gender'      => $productData['gender'] ?? 'unisex',
                ]);
            }
        }

        return response()->json(['message' => 'Import thành công dữ liệu sản phẩm!']);
    }

    // API trả về JSON file crawl gốc
    public function getJson()
    {
        $path = storage_path('app/products.json');
        if (!file_exists($path)) {
            return response()->json(['error' => 'File JSON không tồn tại'], 404);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Lỗi parse JSON: ' . json_last_error_msg()], 500);
        }

        return response()->json($data);
    }

    // Method show() cho chi tiết sản phẩm
    public function show(Product $product) {
        $featuredProducts = Product::inRandomOrder()->where('id','!=',$product->id)->take(12)->get();
        return view('Layouts.MainShow', [
            'product' => $product,
            'featuredProducts' => $featuredProducts
        ]);
    }

    // phân biệt sản phẩm theo giới tính
    public function showByGender($gender) 
    {
        $products = Product::with('brand')
            ->where('gender', $gender)
            ->paginate(12);
        
        $allBrands = Brand::has('products')->get();

        $gender_map = [
            'all' => 'Tất cả sản phẩm',
            'nam' => 'Nước hoa nam',
            'khac' => 'Nước hoa nữ', 
            'unisex' => 'Nước hoa unisex'
        ];
        
        $title = $gender_map[$gender] ?? 'SẢN PHẨM';
        $description = "Bộ sưu tập " . strtolower($title) . " sang trọng, độc đáo từ Larana Perfume.";

        return view('Layouts.MainProduct', [
            'products' => $products,
            'allBrands' => $allBrands, 
            'title' => $title,
            'description' => $description,
            'active_gender' => $gender,
        ]);
    }

    // Tìm kiếm sản phẩm 
    public function search(Request $request)
    {
        $keyword = $request->get('keyword', '');

        if (empty($keyword)) {
            return response()->json(['products' => [], 'brands' => []]);
        }

        // Lấy dữ liệu trực tiếp từ MySQL
        $products = Product::with('brand')
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->take(10)
            ->get(['id', 'brand_id', 'name', 'price', 'image']);

        $brand = Brand::where('name', 'like', "%{$keyword}%")
            ->take(5)
            ->get(['id', 'name', 'logo']);

        return response()->json([
            'products' => $products,
            'brand' => $brand,
        ]);
    }

    public function add($id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
            }
            
            $userId = Auth::id();
            $product = Product::findOrFail($id);
            
            $cartItem = Cart::where('user_id', $userId)
                            ->where('product_id', $id)
                            ->first();
            
            if ($cartItem) {
                $cartItem->quantity += 1;
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'price' => $product->price,
                    'quantity' => 1,
                ]);
            }
            
            return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng!');
            
        } catch (\Exception $e) {
            Log::error('Add to cart error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }
}