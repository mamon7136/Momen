<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\PwaController;
use App\Http\Controllers\Storefront\ShopController;
use App\Http\Controllers\Storefront\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/products', [ShopController::class, 'products'])->name('products');
Route::get('/c/{slug}', [ShopController::class, 'category'])->name('category');
Route::get('/p/{slug}', [ShopController::class, 'product'])->name('product');

Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout/quote', [CheckoutController::class, 'quote'])->name('checkout.quote');
Route::post('/checkout', [CheckoutController::class, 'place'])->middleware('throttle:10,1')->name('checkout.place');

Route::get('/track', [TrackingController::class, 'lookup'])->name('track.lookup');
Route::get('/track/{token}', [TrackingController::class, 'show'])->name('track');
Route::post('/track/{token}/receipt', [TrackingController::class, 'receipt'])->middleware('throttle:6,1')->name('track.receipt');

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('manifest');
Route::get('/sw.js', [PwaController::class, 'worker'])->name('sw');
Route::get('/offline', [PwaController::class, 'offline'])->name('offline');
