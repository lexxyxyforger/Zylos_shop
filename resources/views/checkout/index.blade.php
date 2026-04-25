@extends('layouts.app')

@section('title', 'Checkout | ZYLOS')

@section('content')
    <div class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-500">Checkout</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Premium Checkout</h1>
            <p class="mt-2 text-sm text-slate-600">Halaman checkout sudah siap. Lanjutkan integrasi cart dan payment gateway untuk pengalaman belanja penuh.</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">Order Summary</p>
                    <p class="mt-1 text-sm text-slate-500">Belum ada item dalam keranjang.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">Payment</p>
                    <p class="mt-1 text-sm text-slate-500">Pilih metode pembayaran saat integrasi selesai.</p>
                </div>
            </div>

            <a href="{{ route('store.index') }}" class="mt-6 inline-flex rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                Kembali ke Store
            </a>
        </section>
    </div>
@endsection
