<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WishlistController;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\User;
use App\Services\ZylosStorefrontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', [StorefrontController::class, 'index'])->name('store.index');
Route::get('/products/{slug}', [StorefrontController::class, 'show'])->name('product.show');

Route::middleware('auth')->group(function () {
    Route::get('/history', [StorefrontController::class, 'history'])->name('store.history');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{key}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{key}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.items.store');
    Route::delete('/wishlist/{uuid}', [WishlistController::class, 'destroy'])->name('wishlist.items.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    $getOrCreateStore = function (Request $request): Store {
        return Store::query()->firstOrCreate(
            ['user_id' => $request->user()->uuid],
            [
                'name' => 'ZYLOS Official',
                'logo' => 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png',
                'about' => 'Premium curated lifestyle products from ZYLOS.',
                'city' => 'Bandung',
                'address' => 'Jl. Braga No. 12',
                'postal_code' => '40111',
                'is_verified' => true,
            ]
        );
    };

    $serializeProduct = function (Product $product): array {
        $image = ProductImage::query()
            ->where('product_id', $product->uuid)
            ->where('is_thumbnail', true)
            ->value('image');

        $product->loadMissing('category:uuid,name');

        return [
            'uuid' => $product->uuid,
            'name' => $product->name,
            'slug' => $product->slug,
            'about' => $product->about,
            'condition' => $product->condition,
            'price' => (float) $product->price,
            'weight' => (int) $product->weight,
            'stock' => (int) $product->stock,
            'product_category_id' => $product->product_category_id,
            'category' => $product->category ? [
                'uuid' => $product->category->uuid,
                'name' => $product->category->name,
            ] : null,
            'image' => $image,
            'created_at' => $product->created_at,
        ];
    };

    $dashboardPayload = function (Request $request) use ($getOrCreateStore, $serializeProduct): array {
        $store = $getOrCreateStore($request);

        $products = Product::query()
            ->where('store_id', $store->uuid)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (Product $product) => $serializeProduct($product))
            ->values();

        return [
            'stats' => [
                'users' => User::query()->count(),
                'stores' => Store::query()->count(),
                'products' => Product::query()->count(),
            ],
            'categories' => ProductCategory::query()
                ->select(['uuid', 'name'])
                ->orderBy('name')
                ->get(),
            'storeProfile' => [
                'name' => $store->name,
                'about' => $store->about,
                'phone' => $store->phone,
                'city' => $store->city,
                'address' => $store->address,
                'postal_code' => $store->postal_code,
                'logo' => $store->logo,
            ],
            'recentProducts' => $products,
        ];
    };

    Route::get('/dashboard', function (Request $request) use ($dashboardPayload) {
        return Inertia::render('Dashboard', $dashboardPayload($request));
    })->name('dashboard');

    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('admin.dashboard');

    Route::post('/dashboard/store-profile', function (Request $request) use ($getOrCreateStore) {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'about' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:30',
            'city' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:12',
            'logo_url' => 'nullable|url|max:1000',
            'logo_file' => 'nullable|image|max:5120',
        ]);

        $store = $getOrCreateStore($request);

        $logo = $store->logo;
        if ($request->hasFile('logo_file')) {
            $logo = Storage::url($request->file('logo_file')->store('store-logos', 'public'));
        } elseif (! empty($data['logo_url'])) {
            $logo = $data['logo_url'];
        }

        $store->update([
            'name' => $data['name'],
            'about' => $data['about'] ?? null,
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'logo' => $logo,
        ]);

        return response()->json([
            'message' => 'Profile store berhasil diperbarui.',
            'storeProfile' => [
                'name' => $store->name,
                'about' => $store->about,
                'phone' => $store->phone,
                'city' => $store->city,
                'address' => $store->address,
                'postal_code' => $store->postal_code,
                'logo' => $store->logo,
            ],
        ]);
    })->name('dashboard.store-profile.update');

    Route::get('/dashboard/products/create', function () {
        return Inertia::render('Dashboard/ProductForm', [
            'mode' => 'create',
            'categories' => ProductCategory::query()
                ->select(['uuid', 'name'])
                ->orderBy('name')
                ->get(),
            'product' => null,
        ]);
    })->name('dashboard.products.create');

    Route::get('/dashboard/products/{product:uuid}/edit', function (Request $request, Product $product) use ($getOrCreateStore) {
        $store = $getOrCreateStore($request);

        abort_unless($product->store_id === $store->uuid, 403);

        $thumbnail = ProductImage::query()
            ->where('product_id', $product->uuid)
            ->where('is_thumbnail', true)
            ->value('image');

        $extraImages = ProductImage::query()
            ->where('product_id', $product->uuid)
            ->where('is_thumbnail', false)
            ->orderByDesc('created_at')
            ->pluck('image')
            ->filter()
            ->values();

        return Inertia::render('Dashboard/ProductForm', [
            'mode' => 'edit',
            'categories' => ProductCategory::query()
                ->select(['uuid', 'name'])
                ->orderBy('name')
                ->get(),
            'product' => [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'about' => $product->about,
                'condition' => $product->condition,
                'price' => (float) $product->price,
                'weight' => (int) $product->weight,
                'stock' => (int) $product->stock,
                'product_category_id' => $product->product_category_id,
                'image' => $thumbnail,
                'extra_images' => $extraImages,
            ],
        ]);
    })->name('dashboard.products.edit');

    Route::post('/dashboard/products', function (Request $request) use ($getOrCreateStore, $serializeProduct) {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'about' => 'required|string|max:2000',
            'condition' => 'required|in:new,used',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url|max:1000',
            'image_file' => 'nullable|image|max:5120',
            'extra_image_urls' => 'nullable|array|max:8',
            'extra_image_urls.*' => 'nullable|url|max:1000',
            'extra_image_files' => 'nullable|array|max:8',
            'extra_image_files.*' => 'nullable|image|max:5120',
            'product_category_id' => 'required|uuid|exists:product_categories,uuid',
        ]);

        $product = DB::transaction(function () use ($data, $getOrCreateStore, $request) {
            $store = $getOrCreateStore($request);

            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 2;

            while (Product::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $product = Product::query()->create([
                'store_id' => $store->uuid,
                'product_category_id' => $data['product_category_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'about' => $data['about'],
                'condition' => $data['condition'],
                'price' => $data['price'],
                'weight' => $data['weight'],
                'stock' => $data['stock'],
            ]);

            $image = null;
            if ($request->hasFile('image_file')) {
                $image = Storage::url($request->file('image_file')->store('products', 'public'));
            } elseif (! empty($data['image_url'])) {
                $image = $data['image_url'];
            }

            if ($image) {
                ProductImage::query()->create([
                    'product_id' => $product->uuid,
                    'image' => $image,
                    'is_thumbnail' => true,
                ]);
            }

            $extraImages = collect($data['extra_image_urls'] ?? [])
                ->filter(fn ($url) => is_string($url) && trim($url) !== '')
                ->values()
                ->all();

            foreach ($request->file('extra_image_files', []) as $file) {
                if ($file) {
                    $extraImages[] = Storage::url($file->store('products', 'public'));
                }
            }

            foreach ($extraImages as $extraImage) {
                ProductImage::query()->create([
                    'product_id' => $product->uuid,
                    'image' => $extraImage,
                    'is_thumbnail' => false,
                ]);
            }

            return $product->fresh();
        });

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $serializeProduct($product),
            'stats' => [
                'users' => User::query()->count(),
                'stores' => Store::query()->count(),
                'products' => Product::query()->count(),
            ],
        ]);
    })->name('dashboard.products.store');

    Route::put('/dashboard/products/{product:uuid}', function (Request $request, Product $product) use ($serializeProduct) {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'about' => 'required|string|max:2000',
            'condition' => 'required|in:new,used',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url|max:1000',
            'image_file' => 'nullable|image|max:5120',
            'extra_image_urls' => 'nullable|array|max:8',
            'extra_image_urls.*' => 'nullable|url|max:1000',
            'extra_image_files' => 'nullable|array|max:8',
            'extra_image_files.*' => 'nullable|image|max:5120',
            'product_category_id' => 'required|uuid|exists:product_categories,uuid',
        ]);

        $product = DB::transaction(function () use ($data, $product, $request) {
            $slug = Str::slug($data['name']);
            $baseSlug = $slug;
            $counter = 2;

            while (Product::query()->where('slug', $slug)->where('uuid', '!=', $product->uuid)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $product->update([
                'product_category_id' => $data['product_category_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'about' => $data['about'],
                'condition' => $data['condition'],
                'price' => $data['price'],
                'weight' => $data['weight'],
                'stock' => $data['stock'],
            ]);

            $image = null;
            if ($request->hasFile('image_file')) {
                $image = Storage::url($request->file('image_file')->store('products', 'public'));
            } elseif (! empty($data['image_url'])) {
                $image = $data['image_url'];
            }

            if ($image) {
                ProductImage::query()->updateOrCreate(
                    ['product_id' => $product->uuid, 'is_thumbnail' => true],
                    ['image' => $image]
                );
            }

            $extraImages = collect($data['extra_image_urls'] ?? [])
                ->filter(fn ($url) => is_string($url) && trim($url) !== '')
                ->values()
                ->all();

            foreach ($request->file('extra_image_files', []) as $file) {
                if ($file) {
                    $extraImages[] = Storage::url($file->store('products', 'public'));
                }
            }

            ProductImage::query()
                ->where('product_id', $product->uuid)
                ->where('is_thumbnail', false)
                ->delete();

            foreach ($extraImages as $extraImage) {
                ProductImage::query()->create([
                    'product_id' => $product->uuid,
                    'image' => $extraImage,
                    'is_thumbnail' => false,
                ]);
            }

            return $product->fresh();
        });

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'product' => $serializeProduct($product),
            'stats' => [
                'users' => User::query()->count(),
                'stores' => Store::query()->count(),
                'products' => Product::query()->count(),
            ],
        ]);
    })->name('dashboard.products.update');

    Route::delete('/dashboard/products/{product:uuid}', function (Product $product) {
        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
            'stats' => [
                'users' => User::query()->count(),
                'stores' => Store::query()->count(),
                'products' => Product::query()->count(),
            ],
        ]);
    })->name('dashboard.products.destroy');
});

require __DIR__.'/auth.php';