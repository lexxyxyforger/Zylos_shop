<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia; // Tambahin ini
use Inertia\Response; // Tambahin ini

class CartController extends Controller
{
public function index(Request $request): Response
    {
        return Inertia::render('Storefront/Cart', [
            'cart' => $this->cartPayload($request),
            'urls' => [
                'store' => route('store.index'),
                'updateBase' => url('/cart/items'),
                'clear' => route('cart.clear'),
                'checkout' => route('checkout.index'),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_uuid' => 'required|uuid|exists:products,uuid',
            'product_size_id' => 'nullable|uuid|exists:product_sizes,uuid',
            'size' => 'nullable|string|max:40',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::query()
            ->with('category:uuid,name,slug')
            ->where('uuid', $data['product_uuid'])
            ->firstOrFail();

        $sizeId = $data['product_size_id'] ?? null;
        $sizeLabel = $data['size'] ?? 'All Size';
        $availableStock = (int) $product->stock;

        if ($sizeId) {
            $size = ProductSize::query()
                ->where('uuid', $sizeId)
                ->where('product_id', $product->uuid)
                ->firstOrFail();

            $sizeLabel = $size->size;
            $availableStock = $size->stock;
        }

        if ($availableStock < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak tersedia.',
            ]);
        }

        $key = sha1($product->uuid.'|'.$sizeId.'|'.$sizeLabel);
        $cart = $request->session()->get('cart', []);
        $currentQty = (int) ($cart[$key]['qty'] ?? 0);
        $quantity = min($currentQty + (int) $data['quantity'], $availableStock);

        $cart[$key] = [
            'key' => $key,
            'product_uuid' => $product->uuid,
            'product_size_id' => $sizeId,
            'size' => $sizeLabel,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $this->productImage($product),
            'qty' => $quantity,
            'quantity' => $quantity,
            'price' => (float) $product->price,
            'stock' => $availableStock,
            'category' => $product->category?->name,
        ];

        $request->session()->put('cart', $cart);

        return response()->json([
            'message' => 'Produk berhasil masuk keranjang.',
            'cart' => $this->cartPayload($request),
        ]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $request->session()->get('cart', []);
        abort_if(! isset($cart[$key]), 404);

        $stock = max(1, (int) ($cart[$key]['stock'] ?? $data['quantity']));
        $quantity = min((int) $data['quantity'], $stock);

        $cart[$key]['qty'] = $quantity;
        $cart[$key]['quantity'] = $quantity;

        $request->session()->put('cart', $cart);

        return response()->json([
            'message' => 'Keranjang diperbarui.',
            'cart' => $this->cartPayload($request),
        ]);
    }

    public function destroy(Request $request, string $key): JsonResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$key]);

        $request->session()->put('cart', $cart);

        return response()->json([
            'message' => 'Produk dihapus dari keranjang.',
            'cart' => $this->cartPayload($request),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('cart');

        return response()->json([
            'message' => 'Keranjang dikosongkan.',
            'cart' => $this->cartPayload($request),
        ]);
    }

    private function cartPayload(Request $request): array
    {
        $items = collect($request->session()->get('cart', []))
            ->map(function (array $item, string $key) {
                $quantity = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
                $price = max(0, (float) ($item['price'] ?? 0));

                return [
                    'key' => $item['key'] ?? $key,
                    'product_uuid' => $item['product_uuid'] ?? null,
                    'product_size_id' => $item['product_size_id'] ?? null,
                    'size' => $item['size'] ?? 'All Size',
                    'name' => $item['name'] ?? 'Produk',
                    'slug' => $item['slug'] ?? null,
                    'image' => $item['image'] ?? null,
                    'qty' => $quantity,
                    'quantity' => $quantity,
                    'price' => $price,
                    'stock' => max(1, (int) ($item['stock'] ?? $quantity)),
                    'category' => $item['category'] ?? null,
                    'line_total' => $price * $quantity,
                ];
            })
            ->values();

        $subtotal = (int) round($items->sum('line_total'));

        return [
            'items' => $items,
            'count' => $items->sum('qty'),
            'subtotal' => $subtotal,
            'tax' => (int) round($subtotal * 0.11),
            'checkout_url' => route('checkout.index'),
            'store_url' => route('store.index'),
        ];
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