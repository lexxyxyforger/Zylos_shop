<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;

class StorefrontController extends Controller
{
    public function index()
    {
        $store = Store::query()
            ->orderByDesc('is_verified')
            ->latest()
            ->first();

        if (! $store) {
            $store = Store::make([
                'name' => 'ZYLOS',
                'logo' => null,
                'is_verified' => false,
                'about' => 'Premium ecommerce storefront by ZYLOS',
            ]);
        }

        $products = Product::query()
            ->where('store_id', $store->uuid)
            ->select('products.*')
            ->addSelect([
                'image' => ProductImage::query()
                    ->select('image')
                    ->whereColumn('product_images.product_id', 'products.uuid')
                    ->orderByDesc('is_thumbnail')
                    ->orderByDesc('created_at')
                    ->limit(1),
            ])
            ->latest()
            ->take(30)
            ->get();

        return view('landing.index', compact('store', 'products'));
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->select('products.*')
            ->addSelect([
                'image' => ProductImage::query()
                    ->select('image')
                    ->whereColumn('product_images.product_id', 'products.uuid')
                    ->orderByDesc('is_thumbnail')
                    ->orderByDesc('created_at')
                    ->limit(1),
            ])
            ->firstOrFail();

        return view('landing.product-show', compact('product'));
    }
}
