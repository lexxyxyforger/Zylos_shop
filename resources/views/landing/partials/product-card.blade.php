<article class="group relative overflow-hidden rounded-3xl border border-slate-800/70 bg-[#1f242f]/95 p-3 text-white shadow-[0_18px_35px_-18px_rgba(17,24,39,0.9)] transition-all duration-300 ease-out hover:-translate-y-1 hover:scale-105 hover:shadow-[0_24px_45px_-20px_rgba(17,24,39,0.95)] active:scale-95 [will-change:transform]">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-10 bg-gradient-to-r from-lime-300/20 via-transparent to-cyan-300/15"></div>
    <a href="{{ route('product.show', $product->slug) }}" class="block">
        <div class="relative aspect-square overflow-hidden rounded-xl bg-slate-800/60">
            <img
                src="{{ $product->image ?: 'https://placehold.co/640x640/e2e8f0/334155?text=ZYLOS' }}"
                alt="{{ $product->name }}"
                class="lazy-blur h-full w-full object-cover opacity-70 transition duration-500 blur-sm"
                loading="lazy"
                decoding="async"
            >
            <span class="absolute left-2 top-2 rounded-full px-2 py-1 text-xs font-semibold backdrop-blur-sm {{ strtolower($product->condition ?? '') === 'used' ? 'bg-amber-200/95 text-amber-900' : 'bg-lime-300/95 text-[#1f232f]' }}">
                {{ strtolower($product->condition ?? '') === 'used' ? 'Bekas' : 'Baru' }}
            </span>
        </div>

        <div class="mt-3 space-y-2 px-1 pb-1">
            <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-white/90">
                {{ $product->name }}
            </h3>
            <p class="text-sm font-black tracking-wide text-lime-300">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
        </div>
    </a>
</article>
