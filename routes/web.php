<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    
    // === BERANDA & PRODUK (Guest/Public Routes) ===
    Route::get('/beranda', [HomeController::class, 'beranda'])->name('api.beranda');
    Route::get('/categories', [HomeController::class, 'getCategories'])->name('api.categories');
    Route::get('/categories/{categoryId}/products', [HomeController::class, 'getProductsByCategory'])->name('api.category.products');
    Route::get('/products/{productId}', [HomeController::class, 'getProduct'])->name('api.product.detail');
    Route::get('/search', [HomeController::class, 'searchProducts'])->name('api.search');
    
});
