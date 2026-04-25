@extends('layouts.app')

@section('title', 'Checkout | ZYLOS')

@section('content')
    @php
        $rawCart = session('cart', []);

        $cartItems = collect($rawCart)
            ->map(function ($item, $key) {
                $entry = is_array($item) ? $item : (array) $item;
                $product = isset($entry['product']) && is_array($entry['product']) ? $entry['product'] : [];

                $name = $entry['name'] ?? $product['name'] ?? 'Produk #' . (is_string($key) ? $key : 'Item');
                $image = $entry['image'] ?? $product['image'] ?? null;
                $qty = (int) ($entry['qty'] ?? $entry['quantity'] ?? 1);
                $price = (float) ($entry['price'] ?? $product['price'] ?? 0);
                $size = $entry['size'] ?? $product['size'] ?? 'All Size';

                return [
                    'name' => $name,
                    'image' => $image,
                    'qty' => max(1, $qty),
                    'price' => max(0, $price),
                    'size' => $size,
                ];
            })
            ->values();

        $subtotal = (int) round($cartItems->sum(fn ($item) => $item['price'] * $item['qty']));
        $tax = (int) round($subtotal * 0.11);
        $defaultShipping = 18000;
    @endphp

    <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="{
            shippingService: '{{ old('shipping_service', 'reguler') }}',
            paymentMethod: '{{ old('payment_method', 'va') }}',
            shippingCosts: { reguler: 18000, hemat: 12000, express: 32000 },
            subtotal: {{ $subtotal }},
            tax: {{ $tax }},
            idr(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0); },
            get shippingCost() { return this.shippingCosts[this.shippingService] || 0; },
            get grandTotal() { return this.subtotal + this.tax + this.shippingCost; }
        }">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_24px_60px_-28px_rgba(15,23,42,0.4)]">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-6 py-6 text-white sm:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-300">Checkout</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight">Premium Checkout</h1>
                <p class="mt-2 text-sm text-slate-300">Ringkas, cepat, dan aman untuk menyelesaikan pesanan Anda.</p>
            </div>

            @if (session('checkout_success'))
                <div class="mx-6 mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 sm:mx-8">
                    {{ session('checkout_success') }}
                </div>
            @endif

            <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[1.15fr_0.85fr]">
                <form action="{{ route('checkout.process') }}" method="POST" class="space-y-6">
                    @csrf

                    <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <h2 class="text-base font-bold text-slate-900">Form Alamat + Pengiriman</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-1 sm:col-span-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Nama Penerima</span>
                                <input name="recipient_name" value="{{ old('recipient_name') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900" placeholder="Nama lengkap penerima">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">No. HP</span>
                                <input name="phone" value="{{ old('phone') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900" placeholder="08xxxxxxxxxx">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Kode Pos</span>
                                <input name="postal_code" value="{{ old('postal_code') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900" placeholder="40123">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Provinsi</span>
                                <input name="province" value="{{ old('province') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900" placeholder="Jawa Barat">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Kota/Kabupaten</span>
                                <input name="city" value="{{ old('city') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900" placeholder="Bandung">
                            </label>

                            <label class="space-y-1 sm:col-span-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Alamat Lengkap</span>
                                <textarea name="address" rows="3" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900" placeholder="Nama jalan, nomor rumah, patokan, dll">{{ old('address') }}</textarea>
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Kurir</span>
                                <select name="shipping" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900">
                                    <option value="JNE" @selected(old('shipping') === 'JNE')>JNE</option>
                                    <option value="J&T" @selected(old('shipping') === 'J&T')>J&T</option>
                                    <option value="SiCepat" @selected(old('shipping') === 'SiCepat')>SiCepat</option>
                                </select>
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Layanan</span>
                                <select name="shipping_service" x-model="shippingService" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-900">
                                    <option value="hemat">Hemat (2-4 hari)</option>
                                    <option value="reguler">Reguler (1-2 hari)</option>
                                    <option value="express">Express (same day)</option>
                                </select>
                            </label>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <h2 class="text-base font-bold text-slate-900">Payment Method</h2>
                        <p class="mt-1 text-sm text-slate-500">Pilih metode pembayaran untuk melanjutkan proses checkout.</p>
                        <input type="hidden" name="payment_method" :value="paymentMethod">

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <label class="cursor-pointer rounded-2xl border p-3 transition"
                                :class="paymentMethod === 'va' ? 'border-slate-900 bg-slate-900 text-white shadow-md' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                <input type="radio" value="va" x-model="paymentMethod" class="sr-only">
                                <p class="text-xs font-bold uppercase tracking-[0.2em]">VA</p>
                                <p class="mt-1 text-sm font-semibold">Virtual Account</p>
                            </label>

                            <label class="cursor-pointer rounded-2xl border p-3 transition"
                                :class="paymentMethod === 'ewallet' ? 'border-slate-900 bg-slate-900 text-white shadow-md' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                <input type="radio" value="ewallet" x-model="paymentMethod" class="sr-only">
                                <p class="text-xs font-bold uppercase tracking-[0.2em]">E-Wallet</p>
                                <p class="mt-1 text-sm font-semibold">OVO, GoPay, DANA</p>
                            </label>

                            <label class="cursor-pointer rounded-2xl border p-3 transition"
                                :class="paymentMethod === 'cod' ? 'border-slate-900 bg-slate-900 text-white shadow-md' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                <input type="radio" value="cod" x-model="paymentMethod" class="sr-only">
                                <p class="text-xs font-bold uppercase tracking-[0.2em]">COD</p>
                                <p class="mt-1 text-sm font-semibold">Bayar di Tempat</p>
                            </label>
                        </div>
                    </section>

                    <button type="submit" @disabled($cartItems->isEmpty()) class="inline-flex w-full items-center justify-center rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                        Lanjutkan Pembayaran
                    </button>
                </form>

                <aside class="space-y-4">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-base font-bold text-slate-900">Ringkasan Keranjang</h2>

                        @if ($cartItems->isEmpty())
                            <p class="mt-3 text-sm text-slate-500">Keranjang Anda kosong. Tambahkan produk dulu sebelum checkout.</p>
                            <a href="{{ route('store.index') }}" class="mt-4 inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Belanja Sekarang</a>
                        @else
                            <div class="mt-4 space-y-3">
                                @foreach ($cartItems as $item)
                                    <article class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <img src="{{ $item['image'] ?: 'https://placehold.co/120x120/e2e8f0/334155?text=ZYLOS' }}" alt="{{ $item['name'] }}" class="h-14 w-14 rounded-lg object-cover" loading="lazy" decoding="async">
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 text-sm font-semibold text-slate-800">{{ $item['name'] }}</p>
                                            <p class="mt-1 text-xs text-slate-500">Ukuran {{ $item['size'] }} - Qty {{ $item['qty'] }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-slate-900">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-slate-900 p-5 text-white shadow-sm">
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-300">Total Pembayaran</h3>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300">Subtotal</span>
                                <span x-text="idr(subtotal)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300">Pajak (11%)</span>
                                <span x-text="idr(tax)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300">Ongkir</span>
                                <span x-text="idr(shippingCost)"></span>
                            </div>
                            <div class="mt-3 border-t border-slate-700 pt-3">
                                <div class="flex items-center justify-between text-base font-extrabold">
                                    <span>Grand Total</span>
                                    <span class="text-lime-300" x-text="idr(grandTotal)"></span>
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </div>
@endsection
