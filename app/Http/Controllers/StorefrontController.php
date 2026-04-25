<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function index(Request $request): Response
    {
        $defaultBrandLogo = 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png';
        $activeCollection = $request->query('collection', 'luxury');

        if (! in_array($activeCollection, ['trending', 'newest', 'luxury'], true)) {
            $activeCollection = 'luxury';
        }

        $store = Store::query()
            ->orderByDesc('is_verified')
            ->latest()
            ->first();

        if (! $store) {
            $store = Store::make([
                'name' => 'ZYLOS',
                'logo' => $defaultBrandLogo,
                'is_verified' => false,
                'about' => 'Premium ecommerce storefront by ZYLOS',
            ]);
        } elseif (empty($store->logo)) {
            $store->logo = $defaultBrandLogo;
        }

        $productsQuery = Product::query()
            ->where('store_id', $store->uuid)
            ->select('products.*')
            ->addSelect([
                'image' => ProductImage::query()
                    ->select('image')
                    ->whereColumn('product_images.product_id', 'products.uuid')
                    ->orderByDesc('is_thumbnail')
                    ->orderByDesc('created_at')
                    ->limit(1),
            ]);

        if ($activeCollection === 'trending') {
            $productsQuery
                ->orderByDesc('stock')
                ->orderByDesc('created_at');
        } elseif ($activeCollection === 'newest') {
            $productsQuery->latest();
        } else {
            $productsQuery
                ->where('price', '>', 0)
                ->orderByDesc('price')
                ->orderByDesc('created_at');
        }

        $products = $productsQuery
            ->take(8)
            ->get()
            ->map(fn (Product $product) => [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'image' => $product->image ?: 'https://placehold.co/900x900/e2e8f0/334155?text=ZYLOS',
                'condition' => $product->condition,
                'stock' => (int) $product->stock,
                'url' => route('product.show', $product->slug),
            ])
            ->values();

        return Inertia::render('Storefront/ZylosLanding', [
            'store' => [
                'name' => $store->name,
                'logo' => $store->logo,
                'is_verified' => (bool) $store->is_verified,
            ],
            'products' => $products,
            'cart' => $this->cartItems($request),
            'wishlist' => $this->wishlistItems($request),
            'orders' => $this->orderItems($request),
            'urls' => [
                'home' => route('store.index'),
                'history' => route('store.history'),
                'login' => route('login'),
                'register' => route('register'),
                'cart' => route('cart.index'),
                'cartAdd' => route('cart.items.store'),
                'wishlistStore' => route('wishlist.items.store'),
                'wishlistDestroyBase' => url('/wishlist/items'),
                'account' => route('dashboard'),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->with([
                'category:uuid,name,slug',
                'images:uuid,product_id,image,is_thumbnail,created_at',
                'sizes:uuid,product_id,size,stock',
            ])
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

        $images = ProductImage::query()
            ->where('product_id', $product->uuid)
            ->orderByDesc('is_thumbnail')
            ->orderByDesc('created_at')
            ->pluck('image')
            ->filter()
            ->values();

        if ($images->isEmpty()) {
            $images = collect([$product->image ?: 'https://placehold.co/900x900/e2e8f0/334155?text=ZYLOS']);
        }

        $sizeOptions = $product->sizes
            ->sortBy('size', SORT_NATURAL)
            ->map(fn ($size) => [
                'uuid' => $size->uuid,
                'label' => $size->size,
                'stock' => $size->stock,
            ])
            ->values();

        if ($sizeOptions->isEmpty()) {
            $sizeOptions = collect($this->defaultSizesForCategory($product->category?->slug ?? $product->category?->name));
        }

        $productPayload = [
            'uuid' => $product->uuid,
            'name' => $product->name,
            'slug' => $product->slug,
            'about' => $product->about,
            'condition' => $product->condition,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'weight' => (int) $product->weight,
            'category' => $product->category ? [
                'uuid' => $product->category->uuid,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
        ];

        return view('landing.product-show', compact('product', 'productPayload', 'images', 'sizeOptions'));
    }

    public function history(Request $request): Response
    {
        $defaultBrandLogo = 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png';

        $store = Store::query()
            ->orderByDesc('is_verified')
            ->latest()
            ->first();

        if (! $store) {
            $store = Store::make([
                'name' => 'ZYLOS',
                'logo' => $defaultBrandLogo,
                'is_verified' => false,
                'about' => 'Premium ecommerce storefront by ZYLOS',
            ]);
        } elseif (empty($store->logo)) {
            $store->logo = $defaultBrandLogo;
        }

        return Inertia::render('Storefront/OrderHistory', [
            'store' => [
                'name' => $store->name,
                'logo' => $store->logo,
                'is_verified' => (bool) $store->is_verified,
            ],
            'orders' => $this->orderItems($request),
            'urls' => [
                'home' => route('store.index'),
                'cart' => route('cart.index'),
                'account' => route('dashboard'),
            ],
        ]);
    }

    private function defaultSizesForCategory(?string $category): array
    {
        $category = Str::lower((string) $category);

        if (Str::contains($category, ['sepatu', 'shoe', 'sneaker'])) {
            return collect(['35.5', '36', '36.5', '37', '37.5', '38', '38.5', '39', '39.5', '40', '40.5'])
                ->map(fn ($size) => ['uuid' => null, 'label' => $size, 'stock' => null])
                ->all();
        }

        if (Str::contains($category, ['baju', 'shirt', 'apparel', 'clothing'])) {
            return collect(['XS', 'S', 'M', 'L', 'XL', 'XXL'])
                ->map(fn ($size) => ['uuid' => null, 'label' => $size, 'stock' => null])
                ->all();
        }

        return [
            ['uuid' => null, 'label' => 'All Size', 'stock' => null],
        ];
    }

    private function cartItems(Request $request): array
    {
        return collect($request->session()->get('cart', []))
            ->map(function (array $item, string $key) {
                $quantity = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
                $price = max(0, (float) ($item['price'] ?? 0));

                return [
                    'key' => $item['key'] ?? $key,
                    'product_id' => $item['product_uuid'] ?? null,
                    'name' => $item['name'] ?? 'Produk',
                    'slug' => $item['slug'] ?? null,
                    'image' => $item['image'] ?? null,
                    'qty' => $quantity,
                    'price' => $price,
                    'line_total' => $price * $quantity,
                ];
            })
            ->values()
            ->all();
    }

    private function wishlistItems(Request $request): array
    {
        return collect($request->session()->get('wishlist', []))
            ->values()
            ->map(fn (array $item) => [
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? 'Produk',
                'slug' => $item['slug'] ?? null,
                'price' => (float) ($item['price'] ?? 0),
                'image' => $item['image'] ?? null,
                'url' => $item['url'] ?? null,
            ])
            ->all();
    }

    private function orderItems(Request $request): array
    {
        return collect($request->session()->get('orders', []))
            ->take(5)
            ->map(function (array $order) {
                return [
                    'id' => $order['id'] ?? 'ZYL-ORDER',
                    'status' => $order['status'] ?? 'pending',
                    'created_at' => $order['created_at'] ?? null,
                    'total' => (float) ($order['total'] ?? 0),
                    'items' => collect($order['items'] ?? [])
                        ->map(fn (array $item) => [
                            'name' => $item['name'] ?? 'Produk',
                            'qty' => max(1, (int) ($item['qty'] ?? 1)),
                            'image' => $item['image'] ?? null,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }
}