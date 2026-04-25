@php
$brandLogo = $brandLogo ?? 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png';
$storeModel = $store ?? null;
$storeLogo = data_get($storeModel, 'logo') ?: $brandLogo;
$storeName = data_get($storeModel, 'name') ?: config('app.name', 'ZYLOS');
$storeTagline = data_get($storeModel, 'is_verified') ? 'VERIFIED STORE' : 'OFFICIAL STORE';
$accountUrl = auth()->check() ? route('dashboard') : route('login');
$showRegister = Route::has('register') && ! auth()->check();
$isDashboard = request()->routeIs('dashboard', 'admin.dashboard');
$isStorefront = request()->routeIs('store.index', 'store.history', 'product.show', 'cart.index', 'checkout.index');
@endphp

<header class="sticky top-0 z-50 px-4 pt-4 sm:px-6 lg:px-8">
    <div
        class="mx-auto w-full max-w-7xl rounded-[1.9rem] border border-slate-200/70 bg-[linear-gradient(180deg,rgba(7,11,21,0.98),rgba(11,16,28,0.94))] shadow-[0_30px_90px_-40px_rgba(15,23,42,0.9)] backdrop-blur-2xl">
        <div
            class="grid min-h-[5rem] grid-cols-1 items-center gap-4 px-4 py-3 sm:px-5 lg:grid-cols-[1fr_auto_1fr] lg:px-6">
            <a href="{{ route('store.index') }}" class="flex min-w-0 items-center gap-3">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-cyan-400/30 bg-slate-950/80 p-1 shadow-[0_0_0_1px_rgba(255,255,255,0.04)]">
                    <img src="{{ $storeLogo }}" alt="Brand {{ $storeName }}"
                        class="h-full w-full rounded-full object-cover" loading="lazy" decoding="async">
                </div>
                <div class="min-w-0 leading-tight">
                    <p class="text-[0.62rem] font-semibold uppercase tracking-[0.34em] text-lime-300/90">
                        {{ $storeTagline }}</p>
                    <p class="truncate text-sm font-black uppercase tracking-[0.3em] text-white sm:text-base">
                        {{ $storeName }}</p>
                </div>
            </a>

            <div class="hidden items-center justify-center lg:flex">
                <div
                    class="inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/5 p-1 shadow-[0_18px_40px_-24px_rgba(0,0,0,0.65)]">
                    <a href="{{ route('store.index') }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('store.index') ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        Store
                    </a>
                    <a href="{{ route('store.history') }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('store.history') ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        History
                    </a>
                    <a href="{{ route('cart.index') }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('cart.index') ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        Cart
                    </a>
                    @auth
                    <a href="{{ $accountUrl }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $isDashboard ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        Dashboard
                    </a>
                    @endauth
                </div>
            </div>

            <div class="hidden items-center justify-end sm:flex">
                @auth
                <a href="{{ $accountUrl }}"
                    class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)] transition hover:-translate-y-0.5 hover:bg-slate-100">
                    <span
                        class="grid h-8 w-8 place-items-center rounded-full bg-slate-900 text-xs font-black text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </span>
                    <span class="max-w-[10rem] truncate">{{ auth()->user()->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="h-4 w-4 text-slate-500">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                @else
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white/80 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/10 hover:text-white">
                        Login
                    </a>
                    @if ($showRegister)
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)] transition hover:-translate-y-0.5 hover:bg-slate-100">
                        Register
                    </a>
                    @endif
                </div>
                @endauth
            </div>

            <div class="flex items-center justify-end gap-2 sm:hidden">
                <a href="{{ route('cart.index') }}"
                    class="grid h-11 w-11 place-items-center rounded-full border border-white/10 bg-white/5 text-white/80 transition hover:bg-white/10 hover:text-white"
                    aria-label="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                        <path
                            d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.38 0 .705.269.779.642l.94 4.7a3.75 3.75 0 0 0 3.676 3.008h7.168a3.75 3.75 0 0 0 3.676-3.008l1.12-5.598a.75.75 0 0 0-.73-.892H6.12L5.82 3.146A2.25 2.25 0 0 0 3.636 1.75H2.25a.75.75 0 0 0 0 1.5Zm7.5 17.25a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm9 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>
                </a>
                @auth
                <a href="{{ $accountUrl }}"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]">
                    {{ auth()->user()->name }}
                </a>
                @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]">
                    Account
                </a>
                @endauth
            </div>
        </div>

        <div class="border-t border-white/10 px-4 pb-4 pt-1 lg:hidden">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('store.index') }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('store.index') ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'border border-white/10 bg-white/5 text-white/75 hover:bg-white/10 hover:text-white' }}">
                    Store
                </a>
                <a href="{{ route('store.history') }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('store.history') ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'border border-white/10 bg-white/5 text-white/75 hover:bg-white/10 hover:text-white' }}">
                    History
                </a>
                <a href="{{ route('cart.index') }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('cart.index') ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'border border-white/10 bg-white/5 text-white/75 hover:bg-white/10 hover:text-white' }}">
                    Cart
                </a>
                @auth
                <a href="{{ $accountUrl }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $isDashboard ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]' : 'border border-white/10 bg-white/5 text-white/75 hover:bg-white/10 hover:text-white' }}">
                    Dashboard
                </a>
                @endauth
            </div>
        </div>
    </div>
</header>