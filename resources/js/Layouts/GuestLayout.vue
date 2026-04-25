<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_15%_15%,rgba(56,189,248,0.14),transparent_28%),radial-gradient(circle_at_85%_10%,rgba(250,204,21,0.12),transparent_24%),linear-gradient(180deg,#f8fafc_0%,#eef2ff_55%,#f8fafc_100%)] px-4 py-8 sm:px-6"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.5),transparent_28%,rgba(255,255,255,0.18)_72%,transparent)]"
        ></div>

        <div class="relative z-10 mb-6">
            <Link
                href="/"
                class="inline-flex items-center gap-3 rounded-full border border-slate-200/80 bg-white/85 px-4 py-2 shadow-[0_18px_40px_-24px_rgba(15,23,42,0.25)] backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-lg"
            >
                <img
                    src="https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png"
                    alt="ZYLOS Brand"
                    class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200"
                />
                <span
                    class="text-xs font-black uppercase tracking-[0.25em] text-slate-700"
                >
                    ZYLOS Portal
                </span>
            </Link>
        </div>

        <div
            class="relative z-10 w-full max-w-md overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 px-6 py-6 shadow-[0_30px_70px_-30px_rgba(15,23,42,0.45)] backdrop-blur-xl sm:px-7 sm:py-7"
        >
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">
                    Secure Access
                </p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                    Welcome back
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Masuk untuk mengelola dashboard, produk, dan aktivitas store kamu.
                </p>
            </div>

            <slot />
        </div>
    </div>
</template>
