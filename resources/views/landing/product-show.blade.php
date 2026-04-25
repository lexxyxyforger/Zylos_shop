@extends('layouts.app')

@section('title', $product->name . ' | ZYLOS')

@section('content')
    <div class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="mb-6 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
            Kembali ke Store
        </a>

        <section class="grid gap-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2 md:p-7">
            <div class="aspect-square overflow-hidden rounded-2xl bg-gray-100">
                <img
                    src="{{ $product->image ?: 'https://placehold.co/800x800/e2e8f0/334155?text=ZYLOS' }}"
                    alt="{{ $product->name }}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                    decoding="async"
                >
            </div>

            <div class="space-y-4">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ strtolower($product->condition ?? '') === 'used' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ strtolower($product->condition ?? '') === 'used' ? 'Bekas' : 'Baru' }}
                </span>

                <h1 class="text-2xl font-black leading-tight text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
                <p class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>

                <div class="rounded-2xl bg-slate-50 p-4 text-sm leading-relaxed text-slate-600">
                    {{ $product->about }}
                </div>
            </div>
        </section>
    </div>
@endsection
