<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|uuid|exists:products,uuid',
        ]);

        $product = Product::query()
            ->where('uuid', $data['product_id'])
            ->firstOrFail();

        $wishlist = $request->session()->get('wishlist', []);

        $wishlist[$product->uuid] = [
            'product_id' => $product->uuid,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'image' => $this->productImage($product) ?: 'https://placehold.co/500x500/e2e8f0/334155?text=ZYLOS',
            'url' => route('product.show', $product->slug),
        ];

        $request->session()->put('wishlist', $wishlist);

        return back(303);
    }
public function destroy(Request $request, $uuid): RedirectResponse
{
    $wishlist = $request->session()->get('wishlist', []);
    
    // Hapus pake key UUID-nya
    if (isset($wishlist[$uuid])) {
        unset($wishlist[$uuid]);
    }

    $request->session()->put('wishlist', $wishlist);

    // Tetap pake 303 biar Inertia gak bingung pas redirect back
    return back(303);
}

    private function productImage(Product $product): ?string
    {
        return ProductImage::query()
            ->where('product_id', $product->uuid)
            ->orderByDesc('is_thumbnail')
            ->orderByDesc('created_at')
            ->value('image');
    }
}