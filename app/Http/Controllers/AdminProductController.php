<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class AdminProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $products = Product::with('brand')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            $result = $products->get();
            return response()->json(['data' => $result]);
        }

        $products = $products->paginate(20);
        $brands = Brand::all();
        return view('admin.products.index', compact('products', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|string',
            'gender' => 'required|in:nam,khac,unisex',
            'brand_id' => 'required|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'brand_id'    => $request->brand_id,
            'name'        => $request->name,
            'price'       => $request->price ?? '0',
            'description' => $request->description ?? '',
            'image'       => null,
            'gender'      => $request->gender ?? 'unisex',
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được thêm thành công!',
                'data' => $product->load('brand')
            ], 201);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được thêm thành công!');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|string',
            'gender' => 'required|in:nam,khac,unisex',
            'brand_id' => 'required|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'brand_id'    => $request->brand_id,
            'name'        => $request->name,
            'price'       => $request->price ?? '0',
            'description' => $request->description ?? '',
            'gender'      => $request->gender ?? 'unisex',
        ];

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được cập nhật thành công!',
                'data' => $product->load('brand')
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được xóa thành công!'
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
    }

    public function create()
    {
        $brands = Brand::all();
        return view('admin.products.create', compact('brands'));
    }

    public function show(Product $product)
    {
        $product->load('brand');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $brands = Brand::all();
        return view('admin.products.edit', compact('product', 'brands'));
    }

    public function import(Request $request)
    {
        $path = storage_path('app/products.json');
        if (!file_exists($path)) {
            return back()->with('error', 'File products.json không tồn tại!');
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Lỗi parse JSON: ' . json_last_error_msg());
        }

        $imported = 0;
        foreach ($data as $brandData) {
            $brand = Brand::firstOrCreate(
                ['name' => $brandData['name']],
                ['logo' => $brandData['logo'] ?? null]
            );

            if (isset($brandData['products']) && is_array($brandData['products'])) {
                foreach ($brandData['products'] as $productData) {
                    Product::create([
                        'brand_id'    => $brand->id,
                        'name'        => $productData['name'],
                        'price'       => $productData['price'] ?? '0',
                        'description' => $productData['description'] ?? '',
                        'image'       => $productData['image'] ?? null,
                        'gender'      => $productData['gender'] ?? 'unisex',
                    ]);
                    $imported++;
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Import thành công {$imported} sản phẩm!");
    }
}