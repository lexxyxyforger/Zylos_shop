<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps({
    store: { type: Object, required: true },
    products: { type: Array, required: true },
    cart: { type: Array, default: () => [] },
    wishlist: { type: Array, default: () => [] },
    orders: { type: Array, default: () => [] },
    urls: { type: Object, required: true },
});

const loading = ref(true);
const activeCategory = ref("Home");
const wishlistOpen = ref(false);
const heartPulse = ref(null);
const cartBounce = ref(null);
const toast = ref(null);
const localCart = ref([...props.cart]);

let liveRefreshTimer = null;
const REFRESH_INTERVAL = 6000;
const CATEGORIES = ["Home", "Men", "Women", "Accessories", "Winter", "Sale"];

watch(
    () => props.cart,
    (cart) => {
        localCart.value = [...cart];
    },
);

const fmt = (price) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price || 0);

const showcaseProducts = computed(() => props.products.slice(0, 4));
const heroProduct = computed(() => props.products[0] || null);
const backgroundImage = computed(
    () =>
        heroProduct.value?.image ||
        props.store.logo ||
        "https://placehold.co/1600x1000/0f172a/67e8f9?text=ZYLOS",
);
const totalProducts = computed(() => props.products.length);
const cartCount = computed(() =>
    localCart.value.reduce((t, i) => t + Number(i.qty || 1), 0),
);
const wishlistCount = computed(() => props.wishlist.length);
const recentOrders = computed(() => props.orders.slice(0, 5));
const latestOrder = computed(() => recentOrders.value[0] || null);

// Helper Wishlist
const isWishlisted = (id) =>
    props.wishlist.some((item) => item.product_id === id);

const setToast = (type, message) => {
    toast.value = { type, message };
    window.setTimeout(() => {
        toast.value = null;
    }, 2400);
};

const refreshProducts = () => {
    router.reload({
        only: ["products", "cart", "wishlist", "orders"],
        preserveScroll: true,
        preserveState: true,
    });
};

// TOGGLE WISHLIST FIX (Menggunakan UUID sesuai request hapus tadi)
const toggleWishlist = (product) => {
    heartPulse.value = product.uuid;
    window.setTimeout(() => {
        heartPulse.value = null;
    }, 220);

    if (isWishlisted(product.uuid)) {
        // Hapus menggunakan UUID langsung ke URL
        router.delete(`/wishlist/${product.uuid}`, {
            preserveScroll: true,
            only: ["wishlist"],
            onSuccess: () => setToast("success", "Dihapus dari wishlist."),
        });
        return;
    }

    // Tambah ke wishlist
    router.post(
        props.urls.wishlistStore,
        {
            product_id: product.uuid,
        },
        {
            preserveScroll: true,
            only: ["wishlist"],
            onSuccess: () => setToast("success", "Masuk ke wishlist!"),
        },
    );
};

const addToCart = async (product) => {
    cartBounce.value = product.uuid;
    window.setTimeout(() => {
        cartBounce.value = null;
    }, 260);
    try {
        const response = await window.axios.post(props.urls.cartAdd, {
            product_uuid: product.uuid || product.product_id,
            size: "All Size",
            quantity: 1,
        });
        localCart.value = response.data.cart.items || [];
        setToast("success", "Produk masuk keranjang.");
    } catch (error) {
        setToast(
            "error",
            error?.response?.data?.message || "Gagal menambahkan produk.",
        );
    }
};

const onVisibilityChange = () => {
    if (document.visibilityState === "visible") refreshProducts();
};

onMounted(() => {
    window.setTimeout(() => {
        loading.value = false;
    }, 450);
    refreshProducts();
    document.addEventListener("visibilitychange", onVisibilityChange);
    window.addEventListener("focus", refreshProducts);
    liveRefreshTimer = window.setInterval(() => {
        if (document.visibilityState === "visible") refreshProducts();
    }, REFRESH_INTERVAL);
});

onBeforeUnmount(() => {
    document.removeEventListener("visibilitychange", onVisibilityChange);
    window.removeEventListener("focus", refreshProducts);
    if (liveRefreshTimer) window.clearInterval(liveRefreshTimer);
});
</script>

<template>
    <Head title="ZYLOS" />

    <div class="root">
        <div class="bg-glow" aria-hidden="true" />

        <Transition name="toast">
            <div
                v-if="toast"
                class="toast"
                :class="
                    toast.type === 'success' ? 'toast--success' : 'toast--error'
                "
            >
                {{ toast.message }}
            </div>
        </Transition>

        <div class="page-wrap">
            <!-- ── NAV ── -->
            <nav class="nav">
                <a :href="urls.home" class="nav-brand">
                    <div class="nav-logo">
                        <img
                            :src="
                                store.logo ||
                                'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'
                            "
                            :alt="store.name"
                            loading="lazy"
                        />
                    </div>
                    <div class="nav-brand-text">
                        <span class="nav-tag">{{
                            store.is_verified
                                ? "Verified Store"
                                : "Official Store"
                        }}</span>
                        <span class="nav-name">{{ store.name }}</span>
                    </div>
                </a>

                <div class="nav-actions">
                    <Link
                        :href="urls.cart"
                        class="btn btn--ghost btn--icon nav-cart"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="icon"
                        >
                            <path
                                d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.38 0 .705.269.779.642l.94 4.7a3.75 3.75 0 0 0 3.676 3.008h7.168a3.75 3.75 0 0 0 3.676-3.008l1.12-5.598a.75.75 0 0 0-.73-.892H6.12L5.82 3.146A2.25 2.25 0 0 0 3.636 1.75H2.25zm7.5 17.25a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm9 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"
                            />
                        </svg>
                        <span>Keranjang</span>
                        <span v-if="cartCount" class="badge badge--cyan">{{
                            cartCount
                        }}</span>
                    </Link>

                    <template v-if="$page.props.auth?.user">
                        <Link :href="urls.account" class="btn btn--solid">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="icon"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Zm-9 13.5a6.75 6.75 0 0 1 13.5 0"
                                />
                            </svg>
                            Account
                        </Link>
                    </template>
                    <template v-else>
                        <a :href="urls.login" class="btn btn--ghost">Login</a>
                        <a :href="urls.register" class="btn btn--solid"
                            >Register</a
                        >
                    </template>
                </div>
            </nav>

            <!-- ── HERO ── -->
            <main class="max-w-7xl mx-auto px-6 py-12">
                <div
                    class="grid lg:grid-cols-2 gap-12 items-center min-h-[60vh]"
                >
                    <div>
                        <div class="relative z-10 max-w-[650px]">
                            <p
                                class="text-xs font-black tracking-[0.5em] text-cyan-400 uppercase mb-4 flex items-center gap-3"
                            >
                                <span class="h-px w-8 bg-cyan-400"></span>
                                SIGNATURE PIECES
                            </p>

                            <h1
                                class="text-7xl md:text-8xl font-black font(['Syne']) tracking-tighter leading-[0.8] mb-8 uppercase"
                            >
                                <span class="block">RARE FINDS</span>

                                <span
                                    class="text-lime-400 italic block mt-2 tracking-[0.1em] translate-x-[-1%]"
                                >
                                    UNMATCHED
                                </span>

                                <span class="block mt-2">STYLE</span>
                            </h1>

                            <p
                                class="text-white/50 text-lg max-w-sm leading-relaxed mb-10 font-medium border-l-2 border-white/10 pl-6"
                            >
                                Curated fashion drops and confident essentials,
                                delivered through a seamless
                                <span class="text-white">premium shopping</span>
                                experience.
                            </p>
                        </div>
                        <div class="flex gap-4">
                            <button
                                class="px-10 py-5 bg-cyan-400 text-black font-black rounded-3xl hover:shadow-[0_0_30px_rgba(34,211,238,0.4)] transition uppercase tracking-widest text-sm"
                            >
                                Explore Collection
                            </button>
                            <Link
                                :href="route('store.history')"
                                class="px-10 py-5 border border-white/10 bg-white/5 font-black rounded-3xl hover:bg-white/10 transition uppercase tracking-widest text-sm text-white"
                                >Your Orders</Link
                            >
                        </div>
                    </div>

                    <div class="relative group">
                        <div
                            class="absolute -inset-4 bg-cyan-400/20 blur-[100px] rounded-full opacity-0 group-hover:opacity-100 transition duration-1000"
                        ></div>
                        <div
                            class="relative aspect-square rounded-[40px] overflow-hidden border border-white/10 shadow-2xl ml-11"
                        >
                            <img
                                :src="backgroundImage"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-[2s]"
                            />
                            <div
                                class="absolute bottom-8 left-8 right-8 bg-black/40 backdrop-blur-xl border border-white/10 p-6 rounded-3xl"
                            >
                                <p
                                    class="text-[10px] font-black text-cyan-400 uppercase tracking-widest mb-1"
                                >
                                    Drop Focus
                                </p>
                                <h3
                                    class="text-xl font-bold uppercase tracking-tight"
                                >
                                    Bold everyday pieces with product-first
                                    detail, wishlist & tracked purchase history.
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- ── PRODUCTS ── -->
            <section id="new-arrivals" class="section-card">
                <div class="section-card__head">
                    <div>
                        <p class="eyebrow eyebrow--cyan">New Arrivals</p>
                        <h2 class="section-card__title">
                            Fresh drops. Sharp fits. Built to move.
                        </h2>
                    </div>
                    <div class="section-card__tags">
                        <span>Limited Picks</span>
                        <span>Clean Detail</span>
                        <span>Fast Checkout</span>
                    </div>
                </div>

                <!-- Skeleton -->
                <div v-if="loading" class="product-grid">
                    <div v-for="n in 4" :key="n" class="skeleton">
                        <div class="skeleton__thumb" />
                        <div class="skeleton__body">
                            <div class="skeleton__line skeleton__line--w8" />
                            <div class="skeleton__line skeleton__line--w4" />
                            <div class="skeleton__btn" />
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div v-else-if="showcaseProducts.length" class="product-grid">
                    <article
                        v-for="product in showcaseProducts"
                        :key="product.uuid"
                        class="product-card"
                    >
                        <div class="product-card__thumb">
                            <a :href="product.url">
                                <img
                                    :src="product.image"
                                    :alt="product.name"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </a>
                            <span
                                class="badge-pill"
                                :class="
                                    product.condition === 'used'
                                        ? 'badge-pill--cyan'
                                        : 'badge-pill--lime'
                                "
                            >
                                {{
                                    product.condition === "used"
                                        ? "Preloved"
                                        : "New"
                                }}
                            </span>
                            <button
                                type="button"
                                class="wish-btn"
                                :class="[
                                    isWishlisted(product.uuid) &&
                                        'wish-btn--active',
                                    heartPulse === product.uuid &&
                                        'wish-btn--pulse',
                                ]"
                                @click="toggleWishlist(product)"
                                aria-label="Toggle wishlist"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    :fill="
                                        isWishlisted(product.uuid)
                                            ? 'currentColor'
                                            : 'none'
                                    "
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div class="product-card__info">
                            <a :href="product.url">
                                <h3 class="product-card__name">
                                    {{ product.name }}
                                </h3>
                                <p class="product-card__price">
                                    {{ fmt(product.price) }}
                                </p>
                            </a>
                            <button
                                type="button"
                                class="btn btn--add"
                                :class="
                                    cartBounce === product.uuid && 'btn--bounce'
                                "
                                @click="addToCart(product)"
                            >
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-products">Produk belum tersedia.</div>
            </section>
        </div>

        <!-- ── SIDEBAR (desktop) ── -->
        <aside class="sidebar">
            <button
                type="button"
                class="side-btn"
                aria-label="Wishlist"
                @click="wishlistOpen = true"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"
                    />
                </svg>
                <span v-if="wishlistCount" class="badge badge--lime">{{
                    wishlistCount
                }}</span>
            </button>
            <Link :href="urls.cart" class="side-btn" aria-label="Keranjang">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M6 6h15l-2 8H8L6 6Z" />
                    <path d="M6 6 5 3H2" />
                    <path d="M9 20h.01M18 20h.01" />
                </svg>
                <span v-if="cartCount" class="badge badge--cyan">{{
                    cartCount
                }}</span>
            </Link>
            <Link
                :href="urls.history"
                class="side-btn"
                aria-label="Purchase History"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 8v4l3 2" />
                    <path d="M21 12a9 9 0 1 1-9-9" />
                </svg>
                <span v-if="recentOrders.length" class="badge badge--white">{{
                    recentOrders.length
                }}</span>
            </Link>
        </aside>

        <!-- ── BOTTOM NAV ── -->
        <nav class="bottom-nav">
            <button
                v-for="cat in CATEGORIES"
                :key="cat"
                type="button"
                class="cat-btn"
                :class="activeCategory === cat && 'cat-btn--active'"
                @click="activeCategory = cat"
            >
                {{ cat }}
            </button>
        </nav>

        <!-- ── WISHLIST DRAWER ── -->
        <Transition name="overlay">
            <div
                v-if="wishlistOpen"
                class="drawer-overlay"
                @click="wishlistOpen = false"
            />
        </Transition>

        <Transition name="drawer">
            <aside v-if="wishlistOpen" class="drawer">
                <div class="drawer__head">
                    <div>
                        <p class="eyebrow eyebrow--cyan">Wishlist</p>
                        <h2 class="drawer__title">Saved Products</h2>
                    </div>
                    <button
                        type="button"
                        class="close-btn"
                        @click="wishlistOpen = false"
                        aria-label="Tutup"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="loading" class="drawer__list">
                    <div v-for="n in 3" :key="n" class="wish-skeleton">
                        <div class="wish-skeleton__img" />
                        <div class="wish-skeleton__body">
                            <div class="skeleton__line skeleton__line--w8" />
                            <div class="skeleton__line skeleton__line--w4" />
                        </div>
                    </div>
                </div>

                <div v-else-if="wishlist.length" class="drawer__list">
                    <article
                        v-for="item in wishlist"
                        :key="item.product_id"
                        class="wish-item"
                    >
                        <a :href="item.url" class="wish-item__img">
                            <img
                                :src="
                                    item.image ||
                                    'https://placehold.co/200x200/1e293b/64748b?text=IMG'
                                "
                                :alt="item.name"
                                loading="lazy"
                            />
                        </a>
                        <div class="wish-item__info">
                            <a :href="item.url" class="wish-item__name">{{
                                item.name
                            }}</a>
                            <p class="wish-item__price">
                                {{ fmt(item.price) }}
                            </p>
                            <button
                                type="button"
                                class="btn btn--cart-small"
                                @click="addToCart(item)"
                            >
                                Tambah ke Keranjang
                            </button>
                        </div>
                        <button
                            type="button"
                            class="wish-remove"
                            @click="toggleWishlist({ uuid: item.product_id })"
                            aria-label="Hapus"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </article>
                </div>

                <div v-else class="drawer__empty">
                    <p class="drawer__empty-title">
                        Wishlist kamu masih kosong
                    </p>
                    <p class="drawer__empty-desc">
                        Simpan produk favorit untuk dibeli nanti.
                    </p>
                    <button
                        type="button"
                        class="btn btn--primary"
                        style="margin-top: 1.25rem"
                        @click="wishlistOpen = false"
                    >
                        Cari produk
                    </button>
                </div>
            </aside>
        </Transition>
    </div>
</template>

<style scoped>
/* ── TOKENS ── */
.root {
    --navy: #0f172a;
    --cyan: #22d3ee;
    --lime: #bef264;
    --white: #ffffff;
    --muted: rgba(255, 255, 255, 0.6);
    --glass: rgba(255, 255, 255, 0.07);
    --glass-border: rgba(255, 255, 255, 0.11);
    --r-sm: 12px;
    --r-md: 20px;
    --r-lg: 28px;
    --r-pill: 9999px;
    --font-display: "Syne", sans-serif;
    --font-body: "DM Sans", sans-serif;

    position: relative;
    min-height: 100vh;
    background: var(--navy);
    color: var(--white);
    font-family: var(--font-body);
    font-size: 15px;
    line-height: 1.6;
    overflow-x: hidden;
}

/* ── BACKGROUND ── */
.bg-glow {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background:
        radial-gradient(
            ellipse 65% 55% at 12% 18%,
            rgba(34, 211, 238, 0.16) 0%,
            transparent 55%
        ),
        radial-gradient(
            ellipse 55% 42% at 86% 8%,
            rgba(190, 242, 100, 0.13) 0%,
            transparent 50%
        ),
        radial-gradient(
            ellipse 80% 50% at 50% 110%,
            rgba(34, 211, 238, 0.07) 0%,
            transparent 60%
        );
}

/* ── PAGE WRAP ── */
.page-wrap {
    position: relative;
    z-index: 1;
    max-width: 1280px;
    margin: 0 auto;
    padding: 1.25rem 1.25rem 7rem;
    animation: fadeUp 0.65s ease-out both;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ── TOAST ── */
.toast {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 300;
    padding: 0.6rem 1rem;
    border-radius: var(--r-sm);
    font-size: 0.82rem;
    font-weight: 700;
    backdrop-filter: blur(16px);
}

.toast--success {
    background: rgba(190, 242, 100, 0.15);
    border: 1px solid rgba(190, 242, 100, 0.3);
    color: #d9f99d;
}

.toast--error {
    background: rgba(248, 113, 113, 0.15);
    border: 1px solid rgba(248, 113, 113, 0.3);
    color: #fca5a5;
}

.toast-enter-active,
.toast-leave-active {
    transition:
        opacity 0.25s,
        transform 0.25s;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(10px);
}

/* ── NAV ── */
.nav {
    position: sticky;
    top: 1rem;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.7rem 1.25rem;
    background: rgba(5, 9, 16, 0.92);
    border: 1px solid var(--glass-border);
    border-radius: var(--r-pill);
    backdrop-filter: blur(20px);
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    flex-shrink: 0;
}

.nav-logo {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid rgba(34, 211, 238, 0.28);
    overflow: hidden;
    flex-shrink: 0;
    background: #111827;
}

.nav-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.nav-brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.nav-tag {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--lime);
    opacity: 0.9;
}

.nav-name {
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--white);
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-cart {
    display: none;
}
@media (min-width: 640px) {
    .nav-cart {
        display: inline-flex;
    }
}

/* ── BUTTONS ── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-family: var(--font-body);
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition:
        background 0.2s,
        color 0.2s,
        transform 0.15s,
        box-shadow 0.2s;
    white-space: nowrap;
    position: relative;
}

.btn--ghost {
    padding: 0.5rem 1rem;
    border-radius: var(--r-pill);
    border: 1px solid var(--glass-border);
    background: var(--glass);
    color: rgba(255, 255, 255, 0.75);
}
.btn--ghost:hover {
    background: rgba(255, 255, 255, 0.14);
    color: var(--white);
    transform: translateY(-1px);
}

.btn--solid {
    padding: 0.55rem 1.1rem;
    border-radius: var(--r-pill);
    background: var(--white);
    color: var(--navy);
    font-weight: 700;
}
.btn--solid:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

.btn--primary {
    padding: 0.75rem 1.5rem;
    border-radius: var(--r-pill);
    background: var(--cyan);
    color: var(--navy);
    font-weight: 700;
    font-size: 0.875rem;
    box-shadow: 0 14px 36px -16px rgba(34, 211, 238, 0.85);
}
.btn--primary:hover {
    background: var(--lime);
    transform: scale(1.04);
}

.btn--secondary {
    padding: 0.75rem 1.5rem;
    border-radius: var(--r-pill);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--white);
    font-size: 0.875rem;
    backdrop-filter: blur(10px);
}
.btn--secondary:hover {
    background: rgba(255, 255, 255, 0.18);
    transform: scale(1.04);
}

.btn--add {
    width: 100%;
    padding: 0.45rem 0;
    border-radius: var(--r-pill);
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid var(--glass-border);
    color: var(--white);
    font-size: 0.75rem;
    justify-content: center;
}
.btn--add:hover {
    background: var(--cyan);
    color: var(--navy);
    font-weight: 700;
    transform: scale(1.02);
}
.btn--bounce {
    transform: scale(1.05);
}

.btn--cart-small {
    padding: 0.3rem 0.75rem;
    border-radius: var(--r-pill);
    background: var(--cyan);
    color: var(--navy);
    font-size: 0.72rem;
    font-weight: 700;
}
.btn--cart-small:hover {
    background: var(--lime);
    transform: scale(1.04);
}

.btn--icon {
    position: relative;
}

/* ── BADGE ── */
.badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 18px;
    height: 18px;
    padding: 0 3px;
    border-radius: var(--r-pill);
    font-size: 9px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--navy);
}
.badge--lime {
    background: var(--lime);
}
.badge--cyan {
    background: var(--cyan);
}
.badge--white {
    background: var(--white);
}

/* ── EYEBROW ── */
.eyebrow {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}
.eyebrow--cyan {
    color: rgba(34, 211, 238, 0.9);
}

/* ── HERO ── */
.hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2.5rem;
    align-items: center;
    padding: 4rem 0 3rem;
}

@media (max-width: 768px) {
    .hero {
        grid-template-columns: 1fr;
        padding: 2.5rem 0 2rem;
    }
    .hero__visual {
        order: -1;
    }
}

.hero__headline {
    font-family: var(--font-display);
    font-size: clamp(3.5rem, 8vw, 6.5rem);
    line-height: 0.92;
    letter-spacing: 0.02em;
    color: var(--white);
    margin-bottom: 1.25rem;
}

.accent {
    color: var(--lime);
    text-shadow: 0 0 30px rgba(190, 242, 100, 0.4);
}

.hero__sub {
    font-size: 0.9rem;
    line-height: 1.75;
    color: var(--muted);
    max-width: 400px;
    margin-bottom: 1.75rem;
}

.hero__cta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

/* ── STATS ── */
.stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.stat-card {
    padding: 1rem;
    border-radius: var(--r-sm);
    border: 1px solid var(--glass-border);
    background: var(--glass);
    backdrop-filter: blur(16px);
}

.stat-label {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}
.stat-label--lime {
    color: var(--lime);
}
.stat-label--cyan {
    color: rgba(34, 211, 238, 0.9);
}

.stat-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--white);
}
.stat-desc {
    font-size: 0.72rem;
    color: var(--muted);
    margin-top: 0.2rem;
}

/* ── HERO CARD ── */
.hero-card {
    border-radius: var(--r-lg);
    border: 1px solid var(--glass-border);
    background: var(--glass);
    backdrop-filter: blur(20px);
    overflow: hidden;
}

.hero-card__img {
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-bottom: 1px solid var(--glass-border);
}

.hero-card__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
    display: block;
}

.hero-card:hover .hero-card__img img {
    transform: scale(1.04);
}

.hero-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(15, 23, 42, 0.65) 0%,
        transparent 55%
    );
}

.hero-card__body {
    padding: 1.25rem 1.5rem 1.5rem;
}

.hero-card__title {
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.35;
    color: var(--white);
    max-width: 320px;
    margin-top: 0.4rem;
}

.hero-card__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: 0.875rem;
    border-top: 1px solid var(--glass-border);
    font-size: 0.8rem;
    color: var(--muted);
}

.hero-card__link {
    color: var(--cyan);
    font-weight: 700;
    text-decoration: none;
    transition: color 0.2s;
}
.hero-card__link:hover {
    color: var(--lime);
}

/* ── SECTION CARD ── */
.section-card {
    border-radius: var(--r-lg);
    border: 1px solid var(--glass-border);
    background: var(--glass);
    backdrop-filter: blur(16px);
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.section-card__head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.section-card__title {
    font-size: clamp(1.1rem, 3vw, 1.55rem);
    font-weight: 700;
    line-height: 1.25;
    color: var(--white);
    margin-top: 0.25rem;
}

.section-card__tags {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.15rem;
}
.section-card__tags span {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.55);
}

/* ── PRODUCT GRID ── */
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.875rem;
}

@media (max-width: 900px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 480px) {
    .product-grid {
        gap: 0.6rem;
    }
}

/* ── PRODUCT CARD ── */
.product-card {
    border-radius: var(--r-sm);
    border: 1px solid var(--glass-border);
    background: rgba(255, 255, 255, 0.06);
    overflow: hidden;
    transition:
        transform 0.25s ease,
        border-color 0.25s,
        background 0.25s;
}

.product-card:hover {
    transform: translateY(-4px) scale(1.02);
    border-color: rgba(190, 242, 100, 0.38);
    background: rgba(255, 255, 255, 0.1);
}

.product-card__thumb {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.05);
}

.product-card__thumb a {
    display: block;
    height: 100%;
}

.product-card__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    display: block;
}
.relative.group {
    z-index: 299; /* Gambar di bawah teks dikit */
}

.product-card:hover .product-card__thumb img {
    transform: scale(1.06);
}

.badge-pill {
    position: absolute;
    top: 0.6rem;
    left: 0.6rem;
    padding: 0.2rem 0.55rem;
    border-radius: var(--r-pill);
    font-size: 0.62rem;
    font-weight: 700;
}
.badge-pill--lime {
    background: var(--lime);
    color: var(--navy);
}
.badge-pill--cyan {
    background: var(--cyan);
    color: var(--navy);
}

.wish-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid var(--glass-border);
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.65);
    transition:
        background 0.2s,
        transform 0.15s,
        color 0.2s;
}
.wish-btn svg {
    width: 15px;
    height: 15px;
}
.wish-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: var(--white);
    transform: scale(1.1);
}
.wish-btn--active {
    color: var(--cyan);
}
.wish-btn--pulse {
    transform: scale(1.25);
}

.product-card__info {
    padding: 0.75rem;
}

.product-card__name {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--white);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.25rem;
    display: block;
    text-decoration: none;
}

.product-card__price {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--lime);
    margin-bottom: 0.6rem;
}

/* ── SKELETON ── */
.skeleton {
    border-radius: var(--r-sm);
    border: 1px solid var(--glass-border);
    background: rgba(255, 255, 255, 0.06);
    overflow: hidden;
}

.skeleton__thumb {
    aspect-ratio: 1;
    background: rgba(255, 255, 255, 0.09);
    animation: pulse 1.4s ease-in-out infinite;
}

.skeleton__body {
    padding: 0.75rem;
}

.skeleton__line {
    height: 10px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.09);
    animation: pulse 1.4s ease-in-out infinite;
    margin-bottom: 0.5rem;
}
.skeleton__line--w8 {
    width: 80%;
}
.skeleton__line--w4 {
    width: 40%;
}
.skeleton__btn {
    height: 28px;
    border-radius: 99px;
    background: rgba(255, 255, 255, 0.06);
    animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
}

/* ── EMPTY ── */
.empty-products {
    padding: 3rem 1.25rem;
    text-align: center;
    border: 1px dashed var(--glass-border);
    border-radius: var(--r-sm);
    color: var(--muted);
}

/* ── SIDEBAR ── */
.sidebar {
    position: fixed;
    left: 1rem;
    top: 50%;
    z-index: 90;
    transform: translateY(-50%);
    display: none;
    flex-direction: column;
    gap: 0.4rem;
    padding: 0.5rem;
    border-radius: var(--r-pill);
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(16px);
}

@media (min-width: 1120px) {
    .sidebar {
        display: flex;
    }
}

.side-btn {
    position: relative;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s;
    text-decoration: none;
}
.side-btn svg {
    width: 19px;
    height: 19px;
}
.side-btn:hover {
    background: rgba(255, 255, 255, 0.14);
    color: var(--white);
}

/* ── BOTTOM NAV ── */
.bottom-nav {
    position: fixed;
    bottom: 1rem;
    left: 50%;
    z-index: 90;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.45rem 0.75rem;
    border-radius: var(--r-pill);
    background: rgba(5, 9, 16, 0.92);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(24px);
    max-width: calc(100vw - 2rem);
    overflow-x: auto;
}

.cat-btn {
    flex-shrink: 0;
    padding: 0.4rem 0.9rem;
    border-radius: var(--r-pill);
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.5);
    font-family: var(--font-body);
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s;
}
.cat-btn:hover {
    color: var(--white);
    background: rgba(255, 255, 255, 0.07);
}
.cat-btn--active {
    background: var(--lime);
    color: var(--navy);
    font-weight: 700;
}

/* ── DRAWER OVERLAY ── */
.drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(3px);
}

.overlay-enter-active,
.overlay-leave-active {
    transition: opacity 0.28s;
}
.overlay-enter-from,
.overlay-leave-to {
    opacity: 0;
}

/* ── DRAWER ── */
.drawer {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 201;
    width: min(380px, 92vw);
    background: rgba(10, 14, 22, 0.97);
    border-right: 1px solid var(--glass-border);
    border-radius: 0 var(--r-lg) var(--r-lg) 0;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(24px);
    overflow-y: auto;
}

.drawer-enter-active,
.drawer-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.drawer-enter-from,
.drawer-leave-to {
    transform: translateX(-100%);
}

.drawer__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.drawer__title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--white);
}

.close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--glass);
    border: 1px solid var(--glass-border);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s;
}
.close-btn:hover {
    background: rgba(255, 255, 255, 0.16);
}
.close-btn svg {
    width: 16px;
    height: 16px;
}

.drawer__list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    flex: 1;
}

/* ── WISH ITEM ── */
.wish-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--r-sm);
    border: 1px solid var(--glass-border);
    background: var(--glass);
}

.wish-item__img {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.07);
    display: block;
}
.wish-item__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wish-item__info {
    flex: 1;
    min-width: 0;
}

.wish-item__name {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--white);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.3rem;
    text-decoration: none;
}

.wish-item__price {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--lime);
    margin-bottom: 0.5rem;
}

.wish-remove {
    flex-shrink: 0;
    align-self: flex-start;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition:
        background 0.2s,
        color 0.2s;
}
.wish-remove:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--white);
}
.wish-remove svg {
    width: 14px;
    height: 14px;
}

/* ── WISH SKELETON ── */
.wish-skeleton {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--r-sm);
    border: 1px solid var(--glass-border);
    background: var(--glass);
}

.wish-skeleton__img {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.09);
    animation: pulse 1.4s ease-in-out infinite;
    flex-shrink: 0;
}

.wish-skeleton__body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    justify-content: center;
}

/* ── DRAWER EMPTY ── */
.drawer__empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem 0;
}

.drawer__empty-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 0.4rem;
}
.drawer__empty-desc {
    font-size: 0.82rem;
    color: var(--muted);
}
</style>
