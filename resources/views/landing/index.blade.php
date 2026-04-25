@extends('layouts.app')

@section('title', $store->name . ' | ZYLOS')

@php
$brandLogo = 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png';
@endphp

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
<style>
[x-cloak] {
    display: none !important;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.room-interior {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.65), transparent 30%),
        radial-gradient(circle at 88% 16%, rgba(255, 255, 255, 0.35), transparent 28%),
        linear-gradient(170deg, rgba(236, 241, 248, 0.92) 0%, rgba(225, 232, 242, 0.9) 52%, rgba(248, 249, 251, 0.92) 100%);
}

.room-wall-panel {
    position: absolute;
    top: 1.5rem;
    bottom: 8.5rem;
    width: 28%;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.54), rgba(205, 214, 228, 0.2));
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 1.25rem;
    backdrop-filter: blur(2px);
}

.room-wall-left {
    left: -8%;
    transform: rotate(-1.5deg);
}

.room-wall-right {
    right: -8%;
    transform: rotate(1.5deg);
}

.room-floor {
    position: absolute;
    left: -5%;
    right: -5%;
    bottom: -2.25rem;
    height: 10.5rem;
    border-radius: 50%;
    background:
        radial-gradient(ellipse at center, rgba(116, 138, 175, 0.3) 0%, rgba(116, 138, 175, 0.12) 32%, rgba(116, 138, 175, 0.02) 68%, transparent 100%);
}

.room-floor-wood {
    position: absolute;
    left: 4%;
    right: 4%;
    bottom: 0.5rem;
    height: 8.5rem;
    border-radius: 1.75rem;
    transform: perspective(900px) rotateX(60deg);
    transform-origin: center bottom;
    background:
        linear-gradient(180deg, rgba(165, 132, 101, 0.1), rgba(128, 99, 72, 0.16)),
        repeating-linear-gradient(90deg,
            rgba(131, 102, 74, 0.22) 0px,
            rgba(131, 102, 74, 0.22) 2px,
            rgba(196, 164, 132, 0.18) 2px,
            rgba(196, 164, 132, 0.18) 22px);
    opacity: 0.45;
    filter: blur(0.2px);
}

.room-panel-shadow {
    position: absolute;
    left: 9%;
    right: 9%;
    bottom: 2.75rem;
    height: 9rem;
    border-radius: 9999px;
    transform: perspective(900px) rotateX(72deg);
    transform-origin: center;
    background: radial-gradient(ellipse at center, rgba(26, 36, 56, 0.3) 0%, rgba(26, 36, 56, 0.18) 40%, transparent 78%);
    filter: blur(4px);
}

.room-sofa {
    position: absolute;
    bottom: 1.75rem;
    width: 10.5rem;
    height: 5.25rem;
    border-radius: 2rem;
    background: linear-gradient(165deg, rgba(241, 244, 250, 0.86), rgba(195, 205, 220, 0.45));
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: 0 18px 28px -20px rgba(50, 65, 88, 0.35);
}

.room-sofa-left {
    left: -2rem;
    transform: rotate(-8deg);
}

.room-sofa-right {
    right: -2rem;
    transform: rotate(8deg);
}

.room-light {
    position: absolute;
    top: 0.8rem;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 0 8px rgba(255, 255, 255, 0.12), 0 0 36px rgba(255, 255, 255, 0.5);
}

.room-light-1 {
    left: 22%;
}

.room-light-2 {
    left: 48%;
}

.room-light-3 {
    left: 74%;
}

@media (max-width: 1024px) {

    .room-wall-panel,
    .room-sofa {
        display: none;
    }
}
</style>
@endpush


@section('content')
<div
    class="relative isolate overflow-hidden bg-[radial-gradient(circle_at_10%_5%,rgba(56,189,248,0.15),transparent_28%),radial-gradient(circle_at_92%_10%,rgba(250,204,21,0.14),transparent_30%)]">
    <div x-data="{ loading: true }" x-init="window.addEventListener('load', () => { loading = false })"
        class="mx-auto w-full max-w-7xl px-4 pb-10 pt-6 sm:px-6 lg:px-8">
        <section
            class="room-interior mb-7 rounded-3xl border border-cyan-100 p-5 shadow-[0_30px_60px_-24px_rgba(15,23,42,0.45),0_14px_24px_-16px_rgba(15,23,42,0.35)] sm:p-7">
            <div class="pointer-events-none" aria-hidden="true">
                <span class="room-wall-panel room-wall-left"></span>
                <span class="room-wall-panel room-wall-right"></span>
                <span class="room-panel-shadow"></span>
                <span class="room-floor"></span>
                <span class="room-floor-wood"></span>
                <span class="room-sofa room-sofa-left"></span>
                <span class="room-sofa room-sofa-right"></span>
                <span class="room-light room-light-1"></span>
                <span class="room-light room-light-2"></span>
                <span class="room-light room-light-3"></span>
            </div>

            <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="flex items-start gap-4 sm:items-center sm:gap-5">
                    <div
                        class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-white ring-1 ring-cyan-100 sm:h-24 sm:w-24">
                        <img src="{{ $store->logo ?: $brandLogo }}" alt="{{ $store->name }}"
                            class="h-full w-full object-cover" loading="lazy" decoding="async">
                    </div>

                    <div class="min-w-0 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                                {{ $store->name }}
                            </h2>
                            @if ($store->is_verified === true)
                            <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                                Verified
                            </span>
                            @endif
                        </div>

                        <p class="max-w-2xl text-sm text-slate-600 sm:text-base">
                            {{ $store->about ?: 'Destinasi belanja online dengan kurasi produk berkualitas, pengalaman belanja cepat, dan tampilan premium khas ZYLOS.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                        {{ $products->count() }} Produk
                    </span>
                    <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                        Fast Checkout
                    </span>
                </div>
            </div>
        </section>

        <section aria-label="Product Grid" class="space-y-5">
            @php
            $spotlight = $products->first();
            @endphp

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500">Collection </p>
                    <h3 class="text-2xl font-extrabold tracking-tight text-slate-900">Pilihan Produk Premium</h3>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <a href="{{ route('store.index', ['collection' => 'trending']) }}#product-grid"
                        class="rounded-full px-3 py-1 shadow-sm transition {{ $activeCollection === 'trending' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                        Trending
                    </a>
                    <a href="{{ route('store.index', ['collection' => 'newest']) }}#product-grid"
                        class="rounded-full px-3 py-1 shadow-sm transition {{ $activeCollection === 'newest' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                        Newest
                    </a>
                    <a href="{{ route('store.index', ['collection' => 'luxury']) }}#product-grid"
                        class="rounded-full px-3 py-1 shadow-sm transition {{ $activeCollection === 'luxury' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                        Luxury Picks
                    </a>
                </div>
            </div>

            @if ($spotlight)
            <article
                class="group overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-4 text-white shadow-[0_20px_55px_-30px_rgba(15,23,42,0.85)] sm:p-6">
                <div class="grid items-center gap-5 sm:grid-cols-[180px_1fr_auto]">
                    <a href="{{ route('product.show', $spotlight->slug) }}"
                        class="block overflow-hidden rounded-2xl bg-slate-700/60">
                        <img src="{{ $spotlight->image ?: 'https://placehold.co/700x700/e2e8f0/334155?text=ZYLOS' }}"
                            alt="{{ $spotlight->name }}"
                            class="lazy-blur aspect-square w-full object-cover opacity-70 transition duration-500 blur-sm"
                            loading="lazy" decoding="async">
                    </a>
                    <div class="space-y-2">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-lime-300">Spotlight Product</p>
                        <h4 class="line-clamp-2 text-xl font-black sm:text-2xl">{{ $spotlight->name }}</h4>
                        <p class="line-clamp-2 text-sm text-white/70">
                            {{ $store->about ?: 'Produk pilihan dengan kualitas premium dan pengalaman belanja kelas atas.' }}
                        </p>
                        <p class="text-lg font-black text-lime-300">Rp
                            {{ number_format($spotlight->price, 0, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('product.show', $spotlight->slug) }}"
                        class="inline-flex rounded-full bg-lime-300 px-4 py-2 text-sm font-bold text-slate-900 transition hover:bg-lime-200">Shop
                        Now</a>
                </div>
            </article>
            @endif

            <div x-show="loading" x-cloak class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                @for ($i = 0; $i < 12; $i++) <article
                    class="animate-pulse rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="aspect-square rounded-xl bg-gray-200"></div>
                    <div class="mt-3 space-y-2">
                        <div class="h-3 w-4/5 rounded bg-gray-200"></div>
                        <div class="h-3 w-3/5 rounded bg-gray-200"></div>
                        <div class="h-4 w-1/2 rounded bg-gray-200"></div>
                    </div>
                    </article>
                    @endfor
            </div>

            <div x-show="!loading" x-cloak>
                @if ($products->isEmpty())
                <div
                    class="mx-auto flex max-w-xl flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <div
                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path
                                d="M3.375 3a1.125 1.125 0 00-1.125 1.125V6.75c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125V4.125A1.125 1.125 0 0020.625 3H3.375zM2.25 10.5a1.125 1.125 0 011.125-1.125h17.25A1.125 1.125 0 0121.75 10.5v9.375A1.125 1.125 0 0120.625 21H3.375a1.125 1.125 0 01-1.125-1.125V10.5z" />
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-slate-800">Produk Belum Tersedia</p>
                    <p class="mt-1 text-sm text-slate-500">Silakan cek kembali dalam beberapa saat.</p>
                </div>
                @else
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                    @foreach ($products as $product)
                    @include('landing.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
function clearLazyBlur(img) {
    img.classList.remove('blur-sm', 'opacity-70');
    img.classList.add('opacity-100');
}

window.addEventListener('load', function() {
    document.querySelectorAll('img.lazy-blur').forEach(function(img) {
        if (img.complete) {
            clearLazyBlur(img);
            return;
        }

        img.addEventListener('load', function() {
            clearLazyBlur(img);
        }, {
            once: true
        });
    });
});
</script>
@endpush