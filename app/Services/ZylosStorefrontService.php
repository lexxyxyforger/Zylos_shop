<?php

namespace App\Services;

use App\Models\{Buyer, Cart, CartItem, Product, ProductImage, Store, Transaction, TransactionDetail, Wishlist};
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ZylosStorefrontService
{
    private const SHIPPING_RATES = [
        'hemat' => 12000,
        'reguler' => 18000,
        'express' => 32000,
    ];

    public function storePayload(): array
    {
        $store = Store::query()->orderByDesc('is_verified')->latest()->first() 
                 ?? new Store(['name' => 'ZYLOS', 'logo' => 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png']);
        
        return [
            'name' => $store->name,
            'logo' => $store->logo ?: 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png',
            'is_verified' => (bool) $store->is_verified,
        ];
    }

    public function cartPayload(Request $request): array
    {
        $sessionCart = collect($request->session()->get('cart', []));
        
        $items = $sessionCart->values()->map(function ($item) {
            $price = (float) ($item['price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            return [
                'key' => $item['key'] ?? Str::uuid()->toString(),
                'product_uuid' => $item['product_uuid'] ?? $item['product_id'],
                'name' => $item['name'] ?? 'Unknown Product',
                'image' => $item['image'] ?? 'https://placehold.co/500x500?text=ZYLOS',
                'qty' => $qty,
                'price' => $price,
                'size' => $item['size'] ?? 'All Size',
                'line_total' => $price * $qty,
            ];
        });

        $subtotal = $items->sum('line_total');

        return [
            'items' => $items,
            'count' => $items->sum('qty'),
            'subtotal' => $subtotal,
            'tax' => (int)($subtotal * 0.11),
        ];
    }

    public function wishlistPayload(Request $request): array
    {
        $buyer = $this->buyer($request);
        if (!$buyer) return [];

        return Wishlist::where('buyer_id', $buyer->uuid)->with('product')->latest()->get()
            ->map(fn($w) => [
                'product_id' => $w->product_id,
                'name' => $w->product->name,
                'price' => (float)$w->product->price,
                'image' => $this->productImage($w->product),
                'url' => route('product.show', $w->product->slug),
            ])->all();
    }

    public function ordersPayload(Request $request): array
    {
        $buyer = $this->buyer($request);
        if (!$buyer) return [];

        return Transaction::where('buyer_id', $buyer->uuid)->latest()->take(5)->get()
            ->map(fn($t) => [
                'id' => $t->code,
                'status' => $t->payment_status,
                'total' => (float)$t->grand_total,
                'created_at' => $t->created_at->diffForHumans(),
            ])->all();
    }

    public function checkout(Request $request, array $validated): Transaction
    {
        $buyer = $this->buyer($request);
        $cart = $request->session()->get('cart', []);

        if (!$buyer || empty($cart)) {
            throw ValidationException::withMessages(['cart' => 'Vault is empty.']);
        }

        return DB::transaction(function () use ($buyer, $cart, $validated, $request) {
            $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
            $shipping = self::SHIPPING_RATES[$validated['shipping_service']] ?? 18000;
            $tax = (int)($subtotal * 0.11);

          $transaction = Transaction::create([
    'code' => 'ZYL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
    'buyer_id' => $buyer->uuid,
    'store_id' => Store::first()->uuid,
    'address_id' => 0,
    'address' => $validated['address'],
    'city' => $validated['city'],
    'province' => $validated['province'],
    'postal_code' => $validated['postal_code'],
    
    // TAMBAHKAN BARIS INI (Nama Kurirnya)
    'shipping' => $validated['shipping'] ?? 'JNE', 
    
    'shipping_type' => $validated['shipping_service'], // misal: reguler
    'shipping_cost' => $shipping,
    'tax' => $tax,
    'grand_total' => $subtotal + $tax + $shipping,
    'payment_status' => $validated['payment_method'] === 'cod' ? 'unpaid' : 'paid',
]);

            foreach ($cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->uuid,
                    'product_id' => $item['product_uuid'] ?? $item['product_id'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            $request->session()->forget('cart');
            return $transaction;
        });
    }

    public function buyer(Request $request): ?Buyer
    {
        return $request->user() ? Buyer::firstOrCreate(['user_id' => $request->user()->uuid]) : null;
    }

    public function productImage(Product $product): string
    {
        return ProductImage::where('product_id', $product->uuid)->orderByDesc('is_thumbnail')->value('image') 
               ?? 'https://placehold.co/500x500?text=ZYLOS';
    }
}