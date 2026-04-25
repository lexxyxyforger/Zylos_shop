@extends('layouts.app')

@section('title', 'Dashboard Admin | ZYLOS')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
<style>
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}
</style>
@endpush

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <section
        class="rounded-3xl border border-slate-200 bg-gradient-to-br from-sky-50 via-white to-amber-50 p-6 shadow-sm sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Admin Panel</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Dashboard Admin</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-600">Pantau performa toko, produk, dan aktivitas terbaru dari satu
            tempat.</p>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Total Store</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['stores']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Total Produk</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['products']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Total User</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['users']) }}</p>
        </article>
    </section>

    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-bold text-slate-900">Produk Terbaru</h2>
        </div>

        @if ($recentProducts->isEmpty())
        <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada data produk.</div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Nama Produk</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Kondisi</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Harga</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentProducts as $product)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $product->name }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ strtolower($product->condition ?? '') === 'used' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ strtolower($product->condition ?? '') === 'used' ? 'Bekas' : 'Baru' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-800">Rp
                            {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $product->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
</div>
@endsection