<footer class="mt-12 border-t border-slate-200 bg-white/90">
    <div class="mx-auto grid w-full max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div>
            <h3 class="text-base font-extrabold tracking-tight text-slate-900">ZYLOS Commerce</h3>
            <p class="mt-2 max-w-lg text-sm text-slate-600">
                Platform ecommerce modern dengan pengalaman belanja cepat, desain premium, dan katalog produk berkualitas.
            </p>
        </div>

        <div class="flex flex-col gap-2 text-sm text-slate-600 lg:items-end">
            <a href="{{ route('store.index') }}" class="transition hover:text-slate-900">Store</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-slate-900">Dashboard Admin</a>
            @endauth
            <p class="pt-2 text-xs text-slate-500">© {{ now()->year }} ZYLOS. All rights reserved.</p>
        </div>
    </div>
</footer>
