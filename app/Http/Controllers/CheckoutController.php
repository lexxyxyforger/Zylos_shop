<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ZylosStorefrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly ZylosStorefrontService $storefrontService,
    ) {
    }

    /**
     * Display the checkout page with cart items.
     */
  public function index(Request $request): Response
{
    return Inertia::render('Storefront/Checkout', [
        'store' => $this->storefrontService->storePayload(),
        'cart' => $this->storefrontService->cartPayload($request),
        'wishlist' => $this->storefrontService->wishlistPayload($request),
        'orders' => $this->storefrontService->ordersPayload($request),
        'urls' => [
            'home' => route('store.index'),
            'cart' => route('cart.index'),
            'process' => route('checkout.process'),
        ],
    ]);
}

    /**
     * Process the checkout transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'postal_code' => 'required|string|max:10',
            'province' => 'required|string|max:120',
            'city' => 'required|string|max:120',
            'address' => 'required|string|max:500',
            'shipping' => 'required|string|max:30',
            'shipping_service' => 'required|in:hemat,reguler,express',
            'payment_method' => 'required|in:va,ewallet,cod',
        ]);

        // Pastiin method checkout di service lu juga support data session
        $transaction = $this->storefrontService->checkout($request, $validated);

        return redirect()
            ->route('checkout.success', ['transaction' => $transaction->code])
            ->with('checkout_success', 'Order berhasil dibuat. Terima kasih sudah berbelanja di ZYLOS.');
    }

    /**
     * Display the success page.
     */
    public function success(Request $request): Response
    {
        $order = null;
        $transactionCode = $request->query('transaction');

        if ($transactionCode && $request->user()) {
            $buyer = $this->storefrontService->buyer($request);

            if ($buyer) {
                $transaction = Transaction::query()
                    ->where('buyer_id', $buyer->uuid)
                    ->where('code', $transactionCode)
                    ->with([
                        'details.product.images:uuid,product_id,image,is_thumbnail,created_at',
                    ])
                    ->first();

                if ($transaction) {
                    $order = [
                        'id' => $transaction->code,
                        'status' => $transaction->payment_status === 'paid' ? 'paid' : 'pending',
                        'created_at' => optional($transaction->created_at)?->toDateTimeString(),
                        'total' => (float) $transaction->grand_total,
                        'items' => $transaction->details->map(function ($detail) {
                            $product = $detail->product;
                            $image = $product ? $this->storefrontService->productImage($product) : null;

                            return [
                                'name' => $product?->name ?? 'Produk',
                                'qty' => max(1, (int) $detail->qty),
                                'image' => $image ?: 'https://placehold.co/80x80/e2e8f0/334155?text=ZYLOS',
                                'price' => (float) $detail->subtotal,
                            ];
                        })->values()->all(),
                    ];
                }
            }
        }

        return Inertia::render('Storefront/CheckoutSuccess', [
            'store' => $this->storefrontService->storePayload(),
            'order' => $order,
            'urls' => [
                'home' => route('store.index'),
                'history' => route('store.history'),
                'cart' => route('cart.index'),
            ],
        ]);
    }
}