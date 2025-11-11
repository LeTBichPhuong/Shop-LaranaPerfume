<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand; 
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\PriceHelper;

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
        
        return view('Home');
    }

    // Tách giá
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
    
    // Hiển thị danh sách sản phẩm 
    public function index(Request $request)
    {
        $query = Product::with('brand');

        // Lọc theo thương hiệu
        if ($request->brand) {
            $query->where('brand_id', $request->brand);
        }

        // Lọc theo giới tính (nếu bạn muốn giữ)
        if ($request->gender && $request->gender !== 'all') {
            $map = [
                'nam' => 'Nam',
                'unisex' => 'Unisex',
                'khac' => 'Khác'
            ];

            if (isset($map[$request->gender])) {
                $query->where('gender', $map[$request->gender]);
            }
        }

        // Phân trang 12 sản phẩm
        $products = $query->paginate(12)->appends($request->all());

        // Parse giá cho từng sản phẩm
        foreach ($products as $p) {
            [$p->original_price, $p->final_price] = $this->parsePrice($p->price);
        }

        // Lấy list brand cho sidebar
        $allBrands = Brand::has('products')->get();

        return view('Layouts.MainProduct', [
            'products' => $products,
            'allBrands' => $allBrands,
            'title' => 'SẢN PHẨM',
            'description' => 'Bộ sưu tập sản phẩm cao cấp Larana Perfume',
            'active_gender' => $request->gender ?? 'all',
            'active_brand' => $request->brand ?? null,
        ]);
    }

    // Lọc sản phẩm thêm thương hiệu
    public function filterByBrand($brandId)
    {
        // Lấy thương hiệu
        $brand = Brand::findOrFail($brandId);

        // Lọc sản phẩm theo brand và phân trang
        $products = Product::where('brand_id', $brandId)->paginate(12);

        // Parse giá
        foreach ($products as $p) {
            [$p->original_price, $p->final_price] = $this->parsePrice($p->price);
        }

        // Lấy tất cả brand để hiển thị menu bên trái
        $allBrands = Brand::has('products')->get();

        return view('Layouts.MainProduct', [
            'products' => $products,
            'allBrands' => $allBrands,
            'title' => 'Thương hiệu: ' . $brand->name,
            'description' => 'Các sản phẩm đến từ thương hiệu ' . $brand->name,
            'active_brand' => $brandId,
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
        // public function getJson()
        // {
        //     $path = storage_path('app/products.json');
        //     if (!file_exists($path)) {
        //         return response()->json(['error' => 'File JSON không tồn tại'], 404);
        //     }

        //     $json = file_get_contents($path);
        //     $data = json_decode($json, true);

        //     if (json_last_error() !== JSON_ERROR_NONE) {
        //         return response()->json(['error' => 'Lỗi parse JSON: ' . json_last_error_msg()], 500);
        //     }

        //     return response()->json($data);
        // }
        public function getJson()
        {
            try {
                $products = Product::with('brand')->get();

                if ($products->isEmpty()) {
                    return response()->json(['error' => 'Không có sản phẩm nào trong cơ sở dữ liệu.'], 404);
                }

                // Gom nhóm theo brand
                $grouped = $products->groupBy('brand.name')->map(function ($items, $brandName) {
                    return [
                        'name' => $brandName,
                        'products' => $items->map(function ($p) {
                            return [
                                'name' => $p->name,
                                'price' => $p->price,
                                'image' => $p->image,
                                'gender' => $p->gender,
                                'brand' => $p->brand->name,
                            ];
                        })->toArray()
                    ];
                })->values();

                return response()->json($grouped);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

    // Method show() cho chi tiết sản phẩm
    public function show(Product $product) {

        // Tách giá cho sản phẩm chính
        [$product->original_price, $product->final_price] = $this->parsePrice($product->price);

        // Lấy sản phẩm gợi ý
        $featuredProducts = Product::inRandomOrder()
            ->where('id', '!=', $product->id)
            ->take(12)
            ->get();

        // Tách giá cho từng featured product
        foreach ($featuredProducts as $p) {
            [$p->original_price, $p->final_price] = $this->parsePrice($p->price);
        }

        return view('Layouts.MainShow', [
            'product' => $product,
            'featuredProducts' => $featuredProducts
        ]);
    }


    // phân biệt sản phẩm theo giới tính
        // public function showByGender($gender) 
        // {
        //     $products = Product::with('brand')
        //         ->where('gender', $gender)
        //         ->paginate(12);
            
        //     $allBrands = Brand::has('products')->get();

        //     $gender_map = [
        //         'all' => 'Tất cả sản phẩm',
        //         'nam' => 'Nước hoa nam',
        //         'khac' => 'Nước hoa nữ', 
        //         'unisex' => 'Nước hoa unisex'
        //     ];
            
        //     $title = $gender_map[$gender] ?? 'SẢN PHẨM';
        //     $description = "Bộ sưu tập " . strtolower($title) . " sang trọng, độc đáo từ Larana Perfume.";

        //     return view('Layouts.MainProduct', [
        //         'products' => $products,
        //         'allBrands' => $allBrands, 
        //         'title' => $title,
        //         'description' => $description,
        //         'active_gender' => $gender,
        //     ]);
        // }
    public function showByGender($gender) 
    {
        // Map route gender -> database gender
        $map = [
            'nam' => 'Nam',
            'unisex' => 'Unisex',
            'khac' => 'Khác'
        ];

        // Nếu không tồn tại trong map thì trả về 404
        if (!isset($map[$gender])) {
            abort(404);
        }

        $dbGender = $map[$gender];

        $products = Product::with('brand')
            ->where('gender', $dbGender)
            ->paginate(12);

        foreach ($products as $p) {
            [$p->original_price, $p->final_price] = $this->parsePrice($p->price);
        }

        $allBrands = Brand::has('products')->get();

        $gender_map = [
            'nam' => 'Nước hoa nam',
            'khac' => 'Nước hoa nữ',
            'unisex' => 'Nước hoa unisex'
        ];

        return view('Layouts.MainProduct', [
            'products' => $products,
            'allBrands' => $allBrands, 
            'title' => $gender_map[$gender],
            'description' => "Bộ sưu tập " . strtolower($gender_map[$gender]) . " sang trọng, độc đáo từ Larana Perfume.",
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