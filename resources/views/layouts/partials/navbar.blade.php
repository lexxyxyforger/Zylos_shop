<header class="sticky top-0 z-50 border-b border-white/40 bg-white/80 backdrop-blur-md">
    <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('store.index') }}" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-sm font-extrabold text-slate-700 ring-1 ring-slate-200">
                Z
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Official Store</p>
                <p class="text-sm font-bold text-slate-900">ZYLOS</p>
            </div>
        </a>

        <nav class="flex items-center gap-2 text-sm font-semibold">
            <a href="{{ route('checkout.index') }}" class="hidden rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow sm:block">Checkout</a>
            @guest
                <a href="{{ route('login') }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow">Sign In</a>
                <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-3 py-1 text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow">Sign Up</a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow">Admin</a>
            @endguest
        </nav>
    </div>
</header>
