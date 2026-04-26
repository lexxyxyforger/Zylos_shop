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
                'image' => $item['image'] ?? 'https://placehold.co/900x900/e2e8f0/334155?text=',
                'qty' => $qty,
                'price' => $price,
                'size' => $item['size'] ?? 'All Size',
                'line_total' => $price * $qty,
            ];
        });

        $subtotal = (int) round($items->sum('line_total'));

        return [
            'items' => $items,
            'count' => $items->sum('qty'),
            'subtotal' => $subtotal,
            'tax' => (int) round($subtotal * 0.11),
        ];
    }

    public function wishlistPayload(Request $request): array
    {
        $buyer = $this->buyer($request);
        if (!$buyer) return [];

        return Wishlist::where('buyer_id', $buyer->uuid)->with('product')->latest()->get()
            ->map(fn($w) => [
                'id' => $w->uuid,
                'name' => $w->product->name,
                'price' => (float)$w->product->price,
                'image' => $this->productImage($w->product),
                'url' => route('product.show', $w->product->slug),
            ])->all();
    }

   public function ordersPayload(Request $request): array
{
    $user = $request->user();
    if (!$user) return [];

    $buyerUuids = Buyer::where('user_id', $user->uuid)->pluck('uuid')->toArray();
    
    // Kalau tidak ada buyer sama sekali, return kosong
    if (empty($buyerUuids) && !$user->uuid) return [];

    $allIds = array_unique(array_merge($buyerUuids, [$user->uuid]));

    $transactions = Transaction::whereIn('buyer_id', $allIds)
        ->with(['details.product'])
        ->latest()
        ->get();

    // Temporary: log untuk debug
    \Log::info('Orders debug', [
        'user_uuid' => $user->uuid,
        'buyer_uuids' => $buyerUuids,
        'all_ids' => $allIds,
        'transaction_count' => $transactions->count(),
    ]);

    return $transactions->map(function($t) {
        return [
            'id' => $t->code,
            'status' => $t->payment_status === 'paid' ? 'paid' : ($t->payment_status === 'cancelled' ? 'cancelled' : 'pending'),
            'total' => (float)$t->grand_total,
            'created_at' => $t->created_at ? $t->created_at->diffForHumans() : 'Just now',
            'items' => $t->details->map(fn($d) => [
                'name' => $d->product->name ?? 'Product Deleted',
                'qty' => $d->qty,
                'image' => $this->productImage($d->product),
            ]),
        ];
    })->all();
}

    public function checkout(Request $request, array $validated): Transaction
    {
        $user = $request->user();
        if (!$user) throw ValidationException::withMessages(['auth' => 'Unauthorized.']);

        $buyer = Buyer::firstOrCreate(['user_id' => $user->uuid]);
        $sessionCart = $request->session()->get('cart', []);

        if (empty($sessionCart)) {
            throw ValidationException::withMessages(['cart' => 'Vault is empty.']);
        }

        return DB::transaction(function () use ($buyer, $sessionCart, $validated, $request) {
            $subtotal = collect($sessionCart)->sum(fn($i) => $i['price'] * $i['qty']);
            $shippingCost = self::SHIPPING_RATES[$validated['shipping_service']] ?? 18000;
            $tax = (int)($subtotal * 0.11);

            $transaction = Transaction::create([
                'uuid' => (string) Str::uuid(),
                'code' => 'ZYL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'buyer_id' => $buyer->uuid,
                'store_id' => Store::first()->uuid,
                'address_id' => 0,
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'shipping' => $validated['shipping'] ?? 'JNE',
                'shipping_type' => $validated['shipping_service'],
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'grand_total' => $subtotal + $tax + $shippingCost,
                'payment_status' => $validated['payment_method'] === 'cod' ? 'unpaid' : 'paid',
            ]);

            foreach ($sessionCart as $item) {
                TransactionDetail::create([
                    'uuid' => (string) Str::uuid(),
                    'transaction_id' => $transaction->uuid,
                    'product_id' => $item['product_uuid'] ?? $item['product_id'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            $request->session()->forget('cart');
            return $transaction->fresh(['details.product']);
        });
    }

    public function buyer(Request $request): ?Buyer
    {
        if (!$request?->user()) return null;
        return Buyer::firstOrCreate(['user_id' => $request->user()->uuid]);
    }

    public function productImage(?Product $product): string
    {
        if (!$product) return 'https://placehold.co/900x900/e2e8f0/334155?text=';
        return ProductImage::where('product_id', $product->uuid)->orderByDesc('is_thumbnail')->value('image')
               ?? 'https://placehold.co/900x900/e2e8f0/334155?text=';
    }

    public function wishlistItemIds(Request $request): array
    {
        $buyer = $this->buyer($request);
        return $buyer ? Wishlist::where('buyer_id', $buyer->uuid)->pluck('product_id')->all() : [];
    }
}