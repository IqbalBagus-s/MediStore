<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Menampilkan semua produk di beranda berdasarkan kategori
     * Endpoint untuk guest yang belum login
     * 
     * @return JsonResponse
     */
    public function beranda(): JsonResponse
    {
        try {
            // Ambil kategori beserta produknya yang memiliki stock > 0
            $categories = Category::with(['products' => function($query) {
                $query->where('stock', '>', 0)
                      ->select('product_id', 'category_id', 'name', 'description', 'image', 'price', 'stock');
            }])
            ->whereHas('products', function($query) {
                $query->where('stock', '>', 0);
            })
            ->select('category_id', 'category_name')
            ->get();

            // Format response sesuai struktur yang diinginkan
            $response = $categories->map(function($category) {
                return [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                    'products' => $category->products->map(function($product) {
                        return [
                            'product_id' => $product->product_id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'image' => $product->image,
                            'price' => (int) $product->price,
                            'stock' => (int) $product->stock
                        ];
                    })
                ];
            });

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan semua kategori (tanpa produk)
     * 
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::select('category_id', 'category_name')
                ->withCount(['products as total_products' => function($query) {
                    $query->where('stock', '>', 0);
                }])
                ->having('total_products', '>', 0)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan produk berdasarkan kategori
     * 
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getProductsByCategory($categoryId): JsonResponse
    {
        try {
            $category = Category::where('category_id', $categoryId)->first();
            
            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $products = Product::where('category_id', $categoryId)
                ->where('stock', '>', 0)
                ->select('product_id', 'name', 'description', 'image', 'price', 'stock')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                    'products' => $products->map(function($product) {
                        return [
                            'product_id' => $product->product_id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'image' => $product->image,
                            'price' => (int) $product->price,
                            'stock' => (int) $product->stock
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail produk
     * 
     * @param int $productId
     * @return JsonResponse
     */
    public function getProduct($productId): JsonResponse
    {
        try {
            $product = Product::with('category:category_id,category_name')
                ->where('product_id', $productId)
                ->select('product_id', 'category_id', 'name', 'description', 'image', 'price', 'stock')
                ->first();

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->image,
                    'price' => (int) $product->price,
                    'stock' => (int) $product->stock,
                    'category' => [
                        'category_id' => $product->category->category_id,
                        'category_name' => $product->category->category_name
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari produk berdasarkan keyword
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchProducts(Request $request): JsonResponse
    {
        try {
            $keyword = $request->query('q');
            
            if (!$keyword) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter pencarian (q) diperlukan'
                ], 400);
            }

            // Pisahkan keyword menjadi kata-kata individual untuk pencarian yang lebih fleksibel
            $keywords = explode(' ', trim($keyword));
            
            $products = Product::with('category:category_id,category_name')
                ->where('stock', '>', 0)
                ->where(function($query) use ($keywords) {
                    foreach ($keywords as $word) {
                        $word = trim($word);
                        if (!empty($word)) {
                            $query->where(function($subQuery) use ($word) {
                                // Cari di nama produk
                                $subQuery->where('name', 'LIKE', "%{$word}%")
                                         // Cari di deskripsi produk (setiap kata)
                                         ->orWhere('description', 'LIKE', "%{$word}%")
                                         // Cari di nama kategori
                                         ->orWhereHas('category', function($categoryQuery) use ($word) {
                                             $categoryQuery->where('category_name', 'LIKE', "%{$word}%");
                                         });
                            });
                        }
                    }
                })
                ->select('product_id', 'category_id', 'name', 'description', 'image', 'price', 'stock')
                ->get();

            // Jika tidak ada hasil pencarian
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Barang yang anda cari tidak ada',
                    'keyword' => $keyword,
                    'total' => 0,
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pencarian berhasil',
                'keyword' => $keyword,
                'total' => $products->count(),
                'data' => $products->map(function($product) {
                    return [
                        'product_id' => $product->product_id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'image' => $product->image,
                        'price' => (int) $product->price,
                        'stock' => (int) $product->stock,
                        'category' => [
                            'category_id' => $product->category->category_id,
                            'category_name' => $product->category->category_name
                        ]
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal melakukan pencarian',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}