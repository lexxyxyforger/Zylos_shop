<script setup>
import { ref } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[radial-gradient(circle_at_10%_0%,rgba(56,189,248,0.12),transparent_24%),radial-gradient(circle_at_90%_0%,rgba(250,204,21,0.1),transparent_28%),linear-gradient(180deg,#f8fafc_0%,#f8fafc_36%,#eef2ff_100%)] text-slate-900">
        <nav class="sticky top-0 z-50 px-4 pt-4 sm:px-6 lg:px-8">
            <div
                class="mx-auto w-full max-w-7xl rounded-[1.9rem] border border-slate-200/70 bg-[linear-gradient(180deg,rgba(7,11,21,0.98),rgba(11,16,28,0.94))] shadow-[0_30px_90px_-40px_rgba(15,23,42,0.9)] backdrop-blur-2xl"
            >
                <div
                    class="grid min-h-[5rem] grid-cols-1 items-center gap-4 px-4 py-3 sm:px-5 lg:grid-cols-[1fr_auto_1fr] lg:px-6"
                >
                    <Link
                        :href="route('dashboard')"
                        class="flex min-w-0 items-center gap-3"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-cyan-400/30 bg-slate-950/80 p-1 shadow-[0_0_0_1px_rgba(255,255,255,0.04)]"
                        >
                            <img
                                src="https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png"
                                alt="ZYLOS"
                                class="h-full w-full rounded-full object-cover"
                                loading="lazy"
                                decoding="async"
                            />
                        </div>
                        <div class="min-w-0 leading-tight">
                            <p
                                class="text-[0.62rem] font-semibold uppercase tracking-[0.34em] text-lime-300/90"
                            >
                                OFFICIAL STORE
                            </p>
                            <p
                                class="truncate text-sm font-black uppercase tracking-[0.3em] text-white sm:text-base"
                            >
                                ZYLOS
                            </p>
                        </div>
                    </Link>

                    <div class="hidden items-center justify-center lg:flex">
                        <div
                            class="inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/5 p-1"
                        >
                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-white/70 transition hover:text-white"
                                :class="
                                    route().current('dashboard')
                                        ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]'
                                        : 'hover:bg-white/8'
                                "
                            >
                                Dashboard
                            </NavLink>
                            <NavLink
                                :href="route('store.index')"
                                :active="route().current('store.index')"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-white/70 transition hover:text-white"
                                :class="
                                    route().current('store.index')
                                        ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]'
                                        : 'hover:bg-white/8'
                                "
                            >
                                Storefront
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden items-center justify-end sm:flex">
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-full">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)] transition hover:-translate-y-0.5 hover:bg-slate-100 focus:outline-none"
                                        >
                                            {{ $page.props.auth.user.name }}
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                class="h-4 w-4"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Profile
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="-me-2 flex items-center justify-end sm:hidden">
                        <button
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                            class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/5 p-2.5 text-white/70 transition hover:bg-white/10 hover:text-white focus:outline-none"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="border-t border-white/10 px-4 pb-4 pt-1 lg:hidden">
                    <div class="flex gap-2">
                        <NavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            class="flex-1 rounded-full px-4 py-2 text-center text-sm font-semibold transition"
                            :class="
                                route().current('dashboard')
                                    ? 'bg-white text-slate-900 shadow-[0_12px_28px_-18px_rgba(255,255,255,0.95)]'
                                    : 'border border-white/10 bg-white/5 text-white/75 hover:bg-white/10 hover:text-white'
                            "
                        >
                            Dashboard
                        </NavLink>
                        <Link
                            :href="route('store.index')"
                            class="flex-1 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-center text-sm font-semibold text-white/75 transition hover:bg-white/10 hover:text-white"
                        >
                            Storefront
                        </Link>
                    </div>
                </div>
            </div>

            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="sm:hidden px-4 pt-3"
            >
                <div
                    class="overflow-hidden rounded-[1.5rem] border border-slate-200/70 bg-white/90 shadow-[0_24px_70px_-30px_rgba(15,23,42,0.35)] backdrop-blur-xl"
                >
                    <div class="space-y-1 px-3 py-3">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('store.index')"
                            :active="route().current('store.index')"
                            class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            Storefront
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-slate-200/80 px-4 py-4">
                        <div class="text-base font-semibold text-slate-900">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-sm font-medium text-slate-500">
                            {{ $page.props.auth.user.email }}
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('profile.edit')"
                                class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header
            v-if="$slots.header"
            class="px-4 pb-4 pt-2 sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto max-w-7xl rounded-[1.5rem] border border-white/70 bg-white/85 px-4 py-5 shadow-[0_16px_40px_-24px_rgba(15,23,42,0.45)] backdrop-blur-xl sm:px-6"
            >
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="pb-10">
            <slot />
        </main>
    </div>
</template>
