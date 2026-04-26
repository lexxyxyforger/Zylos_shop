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
const activeCategory = ref("Semua");
const wishlistOpen = ref(false);
const mobileMenuOpen = ref(false);
const heartPulse = ref(null);
const cartBounce = ref(null);
const toast = ref(null);
const localCart = ref([...props.cart]);
const scrollY = ref(0);

let liveRefreshTimer = null;
const REFRESH_INTERVAL = 6000;
const normalizeCategory = (value) => (value || "").toString().trim().toLowerCase();

const categoryTabs = computed(() => {
    const categories = props.products
        .map((p) => p?.category?.name || p?.category?.slug)
        .filter(Boolean)
        .map((n) => n.toString().trim())
        .filter((n) => n.length > 0);
    return ["Semua", ...new Set(categories)];
});

watch(() => props.cart, (cart) => { localCart.value = [...cart]; });

const fmt = (price) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price || 0);

const filteredProducts = computed(() => {
    if (activeCategory.value === "Semua") return props.products;
    return props.products.filter((p) => {
        const cat = p?.category?.name || p?.category?.slug || "";
        return normalizeCategory(cat) === normalizeCategory(activeCategory.value);
    });
});

const showcaseProducts = computed(() => filteredProducts.value.slice(0, 8));
const heroProduct = computed(() => filteredProducts.value[0] || props.products[0] || null);
const backgroundImage = computed(() =>
    heroProduct.value?.image || props.store.logo || "https://placehold.co/1600x1000/0f172a/67e8f9?text=ZYLOS"
);
const cartCount = computed(() => localCart.value.reduce((t, i) => t + Number(i.qty || 1), 0));
const wishlistCount = computed(() => props.wishlist.length);
const recentOrders = computed(() => props.orders.slice(0, 5));
const navScrolled = computed(() => scrollY.value > 40);

watch(categoryTabs, (tabs) => {
    if (!tabs.includes(activeCategory.value)) activeCategory.value = "Semua";
}, { immediate: true });

const isWishlisted = (id) => props.wishlist.some((item) => item.product_id === id);

const setToast = (type, message) => {
    toast.value = { type, message };
    window.setTimeout(() => { toast.value = null; }, 2400);
};

const refreshProducts = () => {
    router.reload({
        only: ["products", "cart", "wishlist", "orders"],
        preserveScroll: true,
        preserveState: true,
    });
};

const toggleWishlist = (product) => {
    heartPulse.value = product.uuid;
    window.setTimeout(() => { heartPulse.value = null; }, 220);
    if (isWishlisted(product.uuid)) {
        router.delete(`/wishlist/${product.uuid}`, {
            preserveScroll: true,
            only: ["wishlist"],
            onSuccess: () => setToast("success", "Dihapus dari wishlist."),
        });
        return;
    }
    router.post(props.urls.wishlistStore, { product_id: product.uuid }, {
        preserveScroll: true,
        only: ["wishlist"],
        onSuccess: () => setToast("success", "Masuk ke wishlist!"),
    });
};

const addToCart = async (product) => {
    cartBounce.value = product.uuid;
    window.setTimeout(() => { cartBounce.value = null; }, 260);
    try {
        const response = await window.axios.post(props.urls.cartAdd, {
            product_uuid: product.uuid || product.product_id,
            size: "All Size",
            quantity: 1,
        });
        localCart.value = response.data.cart.items || [];
        setToast("success", "Produk masuk keranjang.");
    } catch (error) {
        setToast("error", error?.response?.data?.message || "Gagal menambahkan produk.");
    }
};

const onScroll = () => { scrollY.value = window.scrollY; };
const onVisibilityChange = () => { if (document.visibilityState === "visible") refreshProducts(); };

onMounted(() => {
    window.setTimeout(() => { loading.value = false; }, 450);
    refreshProducts();
    document.addEventListener("visibilitychange", onVisibilityChange);
    window.addEventListener("focus", refreshProducts);
    window.addEventListener("scroll", onScroll, { passive: true });
    liveRefreshTimer = window.setInterval(() => {
        if (document.visibilityState === "visible") refreshProducts();
    }, REFRESH_INTERVAL);
});

onBeforeUnmount(() => {
    document.removeEventListener("visibilitychange", onVisibilityChange);
    window.removeEventListener("focus", refreshProducts);
    window.removeEventListener("scroll", onScroll);
    if (liveRefreshTimer) window.clearInterval(liveRefreshTimer);
});
</script>

<template>
    <Head title="ZYLOS" />

    <div class="root">
        <div class="bg-glow" aria-hidden="true" />
        <div class="noise" aria-hidden="true" />

        <Transition name="toast">
            <div v-if="toast" class="toast" :class="toast.type === 'success' ? 'toast--success' : 'toast--error'">
                <span class="toast-icon">{{ toast.type === 'success' ? '✦' : '✕' }}</span>
                {{ toast.message }}
            </div>
        </Transition>

        <nav class="nav" :class="navScrolled && 'nav--scrolled'">
            <a :href="urls.home" class="nav-brand">
                <div class="nav-logo">
                    <img :src="store.logo || 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'" :alt="store.name" loading="lazy" />
                </div>
                <div class="nav-brand-text">
                    <span class="nav-tag">{{ store.is_verified ? "Verified Store" : "Official Store" }}</span>
                    <span class="nav-name">{{ store.name }}</span>
                </div>
            </a>

            <div class="nav-center">
                <button v-for="cat in categoryTabs.slice(0, 5)" :key="cat" type="button"
                    class="nav-cat" :class="activeCategory === cat && 'nav-cat--active'"
                    @click="activeCategory = cat">
                    {{ cat }}
                </button>
            </div>

            <div class="nav-actions">
                <Link :href="urls.cart" class="nav-icon-btn" aria-label="Keranjang">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 6h15l-2 8H8L6 6Z" /><path d="M6 6 5 3H2" /><circle cx="9" cy="20" r="1" /><circle cx="18" cy="20" r="1" />
                    </svg>
                    <span v-if="cartCount" class="nav-badge">{{ cartCount }}</span>
                </Link>
                <button type="button" class="nav-icon-btn" aria-label="Wishlist" @click="wishlistOpen = true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z" />
                    </svg>
                    <span v-if="wishlistCount" class="nav-badge nav-badge--lime">{{ wishlistCount }}</span>
                </button>
                <template v-if="$page.props.auth?.user">
                    <Link :href="urls.account" class="btn-pill btn-pill--solid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Zm-9 13.5a6.75 6.75 0 0 1 13.5 0" />
                        </svg>
                        Account
                    </Link>
                </template>
                <template v-else>
                    <a :href="urls.login" class="btn-pill btn-pill--ghost">Login</a>
                    <a :href="urls.register" class="btn-pill btn-pill--solid">Register</a>
                </template>
                <button type="button" class="hamburger" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Menu">
                    <span :class="mobileMenuOpen && 'open'" />
                    <span :class="mobileMenuOpen && 'open'" />
                    <span :class="mobileMenuOpen && 'open'" />
                </button>
            </div>
        </nav>

        <Transition name="mobile-menu">
            <div v-if="mobileMenuOpen" class="mobile-menu">
                <button v-for="cat in categoryTabs" :key="cat" type="button"
                    class="mobile-cat" :class="activeCategory === cat && 'mobile-cat--active'"
                    @click="activeCategory = cat; mobileMenuOpen = false">
                    {{ cat }}
                </button>
                <div class="mobile-divider" />
                <Link :href="urls.cart" class="mobile-link" @click="mobileMenuOpen = false">
                    Keranjang
                    <span v-if="cartCount" class="ml-2 text-cyan-400 font-black">{{ cartCount }}</span>
                </Link>
                <button type="button" class="mobile-link" @click="wishlistOpen = true; mobileMenuOpen = false">
                    Wishlist
                    <span v-if="wishlistCount" class="ml-2 text-lime-400 font-black">{{ wishlistCount }}</span>
                </button>
                <Link :href="urls.history" class="mobile-link" @click="mobileMenuOpen = false">Riwayat Pesanan</Link>
            </div>
        </Transition>

        <section class="hero">
            <div class="hero-content">
                <p class="hero-eyebrow">
                    <span class="eyebrow-line" />
                    SIGNATURE PIECES
                </p>
                <h1 class="hero-headline">
                    <span class="block">RARE</span>
                    <span class="block headline-accent">FINDS</span>
                    <span class="block">STYLE</span>
                </h1>
                <p class="hero-sub">
                    Curated fashion drops and confident essentials, delivered through a seamless
                    <em>premium shopping</em> experience.
                </p>
                <div class="hero-cta">
                    <a href="#new-arrivals" class="cta-primary">Explore Collection</a>
                    <Link :href="route('store.history')" class="cta-secondary">Your Orders</Link>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-num">{{ props.products.length }}</span>
                        <span class="stat-label">Produk</span>
                    </div>
                    <div class="stat-divider" />
                    <div class="stat">
                        <span class="stat-num">{{ recentOrders.length }}</span>
                        <span class="stat-label">Orders</span>
                    </div>
                    <div class="stat-divider" />
                    <div class="stat">
                        <span class="stat-num live-dot">LIVE</span>
                        <span class="stat-label">Realtime</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-img-wrap">
                    <img :src="backgroundImage" class="hero-img" alt="Hero product" />
                    <div class="hero-img-overlay" />
                    <div class="hero-card-float">
                        <p class="float-eyebrow">Drop Focus</p>
                        <p class="float-text">Bold everyday pieces with product-first detail.</p>
                    </div>
                    <div class="hero-badge-float">
                        <span class="pulse-ring" />
                        <span class="pulse-dot" />
                        <span class="badge-text">New Drop</span>
                    </div>
                </div>
                <div class="hero-visual-deco" />
            </div>
        </section>

        <section id="new-arrivals" class="products-section">
            <div class="products-header">
                <div>
                    <p class="eyebrow-sm">New Arrivals</p>
                    <h2 class="products-title">Fresh drops. Sharp fits.</h2>
                </div>
                <div class="products-meta">
                    <span class="meta-tag">{{ activeCategory }}</span>
                    <span class="meta-tag meta-tag--dim">{{ filteredProducts.length }} produk</span>
                </div>
            </div>

            <div class="cat-scroll">
                <button v-for="cat in categoryTabs" :key="cat" type="button"
                    class="cat-pill" :class="activeCategory === cat && 'cat-pill--active'"
                    @click="activeCategory = cat">
                    {{ cat }}
                </button>
            </div>

            <div v-if="loading" class="product-grid">
                <div v-for="n in 8" :key="n" class="skeleton-card">
                    <div class="skeleton-img" />
                    <div class="skeleton-body">
                        <div class="skeleton-line w-3/4" />
                        <div class="skeleton-line w-1/2" />
                        <div class="skeleton-btn" />
                    </div>
                </div>
            </div>

            <div v-else-if="showcaseProducts.length" class="product-grid">
                <article v-for="(product, i) in showcaseProducts" :key="product.uuid"
                    class="product-card" :style="`--delay: ${i * 60}ms`">
                    <div class="product-thumb">
                        <a :href="product.url" class="thumb-link">
                            <img :src="product.image" :alt="product.name" loading="lazy" decoding="async" />
                        </a>
                        <span class="condition-badge" :class="product.condition === 'used' ? 'condition-badge--cyan' : 'condition-badge--lime'">
                            {{ product.condition === "used" ? "Preloved" : "New" }}
                        </span>
                        <button type="button" class="wish-btn"
                            :class="[isWishlisted(product.uuid) && 'wish-btn--active', heartPulse === product.uuid && 'wish-btn--pulse']"
                            @click="toggleWishlist(product)" aria-label="Toggle wishlist">
                            <svg viewBox="0 0 24 24" :fill="isWishlisted(product.uuid) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
                                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z" />
                            </svg>
                        </button>
                    </div>
                    <div class="product-info">
                        <a :href="product.url" class="product-link">
                            <h3 class="product-name">{{ product.name }}</h3>
                            <p class="product-price">{{ fmt(product.price) }}</p>
                        </a>
                        <button type="button" class="add-btn" :class="cartBounce === product.uuid && 'add-btn--bounce'" @click="addToCart(product)">
                            + Keranjang
                        </button>
                    </div>
                </article>
            </div>

            <div v-else class="empty-state">
                <div class="empty-icon">◈</div>
                <p>Belum ada produk untuk kategori <strong>{{ activeCategory }}</strong></p>
            </div>
        </section>

        <div class="sidebar-float">
            <button type="button" class="float-btn" aria-label="Wishlist" @click="wishlistOpen = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z" />
                </svg>
                <span v-if="wishlistCount" class="float-badge float-badge--lime">{{ wishlistCount }}</span>
            </button>
            <Link :href="urls.cart" class="float-btn" aria-label="Keranjang">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 6h15l-2 8H8L6 6Z" /><path d="M6 6 5 3H2" /><circle cx="9" cy="20" r="1" /><circle cx="18" cy="20" r="1" />
                </svg>
                <span v-if="cartCount" class="float-badge">{{ cartCount }}</span>
            </Link>
            <Link :href="urls.history" class="float-btn" aria-label="Riwayat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v4l3 2" /><path d="M21 12a9 9 0 1 1-9-9" />
                </svg>
                <span v-if="recentOrders.length" class="float-badge float-badge--white">{{ recentOrders.length }}</span>
            </Link>
        </div>

        <Transition name="overlay">
            <div v-if="wishlistOpen" class="drawer-overlay" @click="wishlistOpen = false" />
        </Transition>

        <Transition name="drawer">
            <aside v-if="wishlistOpen" class="drawer">
                <div class="drawer-head">
                    <div>
                        <p class="eyebrow-sm">Wishlist</p>
                        <h2 class="drawer-title">Saved Products</h2>
                    </div>
                    <button type="button" class="close-btn" @click="wishlistOpen = false" aria-label="Tutup">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="loading" class="drawer-list">
                    <div v-for="n in 3" :key="n" class="wish-skeleton">
                        <div class="wish-skeleton-img" />
                        <div class="wish-skeleton-body">
                            <div class="skeleton-line w-3/4" />
                            <div class="skeleton-line w-1/2" />
                        </div>
                    </div>
                </div>

                <div v-else-if="wishlist.length" class="drawer-list">
                    <article v-for="item in wishlist" :key="item.product_id" class="wish-item">
                        <a :href="item.url" class="wish-img">
                            <img :src="item.image || 'https://placehold.co/200x200/1e293b/64748b?text=IMG'" :alt="item.name" loading="lazy" />
                        </a>
                        <div class="wish-info">
                            <a :href="item.url" class="wish-name">{{ item.name }}</a>
                            <p class="wish-price">{{ fmt(item.price) }}</p>
                            <button type="button" class="wish-cart-btn" @click="addToCart(item)">+ Keranjang</button>
                        </div>
                        <button type="button" class="wish-remove" @click="toggleWishlist({ uuid: item.product_id })" aria-label="Hapus">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </article>
                </div>

                <div v-else class="drawer-empty">
                    <div class="empty-icon-lg">♡</div>
                    <p class="drawer-empty-title">Wishlist masih kosong</p>
                    <p class="drawer-empty-desc">Simpan produk favorit untuk dibeli nanti.</p>
                    <button type="button" class="cta-primary mt-5" @click="wishlistOpen = false">Cari produk</button>
                </div>
            </aside>
        </Transition>

        <footer class="footer">
            <div class="footer-glow" aria-hidden="true" />
            <div class="footer-inner">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img :src="store.logo || 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'" :alt="store.name" />
                        </div>
                        <h2 class="footer-name">ZYLOS</h2>
                        <p class="footer-desc">Curated rare finds and signature drops for the bold.</p>
                        <div class="footer-socials">
                            <a href="#" class="social-btn" aria-label="Instagram">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                            <a href="#" class="social-btn" aria-label="Twitter">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.599 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Inventory</h4>
                        <ul class="footer-links">
                            <li><Link :href="urls.home" class="footer-link">New Arrivals</Link></li>
                            <li><a href="#" class="footer-link">Limited Picks</a></li>
                            <li><a href="#" class="footer-link">Archive Sale</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Support</h4>
                        <ul class="footer-links">
                            <li><a href="#" class="footer-link">Shipping Policy</a></li>
                            <li><a href="#" class="footer-link">Order Tracking</a></li>
                            <li><a href="#" class="footer-link">Contact Agent</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Newsletter</h4>
                        <p class="footer-newsletter-desc">Get deployment notifications.</p>
                        <div class="newsletter-form">
                            <input type="email" placeholder="Email Address" class="newsletter-input" />
                            <button type="button" class="newsletter-btn" aria-label="Subscribe">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="footer-bottom">
                    <p class="footer-copy">&copy; 2026 ZYLOS VAULT. ALL RIGHTS RESERVED.</p>
                    <div class="footer-status">
                        <span class="status-dot" />
                        <span class="status-text">System Status: Secure</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800;900&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.root {
    --navy: #070d18;
    --navy-2: #0e1a2e;
    --cyan: #22d3ee;
    --lime: #bef264;
    --white: #ffffff;
    --muted: rgba(255,255,255,0.5);
    --glass: rgba(255,255,255,0.055);
    --glass-2: rgba(255,255,255,0.09);
    --border: rgba(255,255,255,0.1);
    --r-sm: 14px;
    --r-md: 22px;
    --r-lg: 32px;
    --r-xl: 40px;
    --r-pill: 9999px;
    --font-display: 'Syne', sans-serif;
    --font-body: 'DM Sans', sans-serif;

    position: relative;
    min-height: 100vh;
    background: var(--navy);
    color: var(--white);
    font-family: var(--font-body);
    overflow-x: hidden;
}

.bg-glow {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background:
        radial-gradient(ellipse 70% 60% at 8% 15%, rgba(34,211,238,0.14) 0%, transparent 55%),
        radial-gradient(ellipse 55% 45% at 90% 5%, rgba(190,242,100,0.11) 0%, transparent 50%),
        radial-gradient(ellipse 90% 50% at 50% 115%, rgba(34,211,238,0.06) 0%, transparent 60%);
}

.noise {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 0.025;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    background-repeat: repeat;
    background-size: 200px 200px;
}

.toast {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: var(--r-sm);
    font-size: 0.82rem;
    font-weight: 600;
    backdrop-filter: blur(16px);
    font-family: var(--font-body);
}
.toast--success { background: rgba(190,242,100,0.12); border: 1px solid rgba(190,242,100,0.3); color: #d9f99d; }
.toast--error { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.3); color: #fca5a5; }
.toast-icon { font-size: 0.7rem; }
.toast-enter-active, .toast-leave-active { transition: opacity 0.25s, transform 0.25s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(12px); }

.nav {
    position: sticky;
    top: 0.75rem;
    z-index: 200;
    margin: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.6rem 1rem 0.6rem 0.75rem;
    background: rgba(7,13,24,0.85);
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    backdrop-filter: blur(24px);
    transition: background 0.3s, box-shadow 0.3s;
}
.nav--scrolled { background: rgba(7,13,24,0.97); box-shadow: 0 8px 40px rgba(0,0,0,0.4); }

.nav-brand {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    text-decoration: none;
    flex-shrink: 0;
}
.nav-logo {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid rgba(34,211,238,0.3);
    overflow: hidden;
    background: var(--navy-2);
}
.nav-logo img { width: 100%; height: 100%; object-fit: cover; }
.nav-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
.nav-tag { font-size: 0.52rem; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; color: var(--lime); }
.nav-name { font-size: 0.82rem; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; font-family: var(--font-display); }

.nav-center {
    display: none;
    align-items: center;
    gap: 0.25rem;
}
@media (min-width: 900px) { .nav-center { display: flex; } }

.nav-cat {
    padding: 0.35rem 0.8rem;
    border-radius: var(--r-pill);
    border: none;
    background: transparent;
    color: var(--muted);
    font-family: var(--font-body);
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.nav-cat:hover { color: var(--white); background: var(--glass-2); }
.nav-cat--active { background: rgba(190,242,100,0.15); color: var(--lime); font-weight: 700; }

.nav-actions { display: flex; align-items: center; gap: 0.4rem; }

.nav-icon-btn {
    position: relative;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--glass);
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
    flex-shrink: 0;
}
.nav-icon-btn svg { width: 17px; height: 17px; }
.nav-icon-btn:hover { background: var(--glass-2); color: var(--white); }

.nav-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    min-width: 17px;
    height: 17px;
    padding: 0 3px;
    border-radius: var(--r-pill);
    font-size: 9px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cyan);
    color: var(--navy);
}
.nav-badge--lime { background: var(--lime); }

.btn-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.9rem;
    border-radius: var(--r-pill);
    font-family: var(--font-body);
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: background 0.2s, transform 0.15s;
    white-space: nowrap;
}
.btn-pill--ghost {
    background: var(--glass);
    border: 1px solid var(--border);
    color: var(--muted);
}
.btn-pill--ghost:hover { background: var(--glass-2); color: var(--white); }
.btn-pill--solid { background: var(--white); color: var(--navy); }
.btn-pill--solid:hover { background: #e2e8f0; transform: translateY(-1px); }

@media (max-width: 640px) { .btn-pill { display: none; } }

.hamburger {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--glass);
    cursor: pointer;
    padding: 0;
    align-items: center;
}
.hamburger span {
    display: block;
    width: 16px;
    height: 1.5px;
    background: var(--muted);
    border-radius: 2px;
    transition: all 0.25s;
    transform-origin: center;
}
.hamburger span.open:nth-child(1) { transform: rotate(45deg) translate(4px, 4px); }
.hamburger span.open:nth-child(2) { opacity: 0; transform: scaleX(0); }
.hamburger span.open:nth-child(3) { transform: rotate(-45deg) translate(4px, -4px); }
@media (min-width: 900px) { .hamburger { display: none; } }

.mobile-menu {
    position: fixed;
    top: 5rem;
    left: 0.75rem;
    right: 0.75rem;
    z-index: 190;
    background: rgba(7,13,24,0.98);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 1rem;
    backdrop-filter: blur(24px);
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.mobile-cat {
    padding: 0.65rem 1rem;
    border-radius: var(--r-sm);
    border: none;
    background: transparent;
    color: var(--muted);
    font-family: var(--font-body);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    text-align: left;
    transition: background 0.2s, color 0.2s;
}
.mobile-cat:hover { background: var(--glass-2); color: var(--white); }
.mobile-cat--active { background: rgba(190,242,100,0.12); color: var(--lime); font-weight: 700; }
.mobile-divider { height: 1px; background: var(--border); margin: 0.5rem 0; }
.mobile-link {
    padding: 0.65rem 1rem;
    border-radius: var(--r-sm);
    background: transparent;
    color: var(--muted);
    font-family: var(--font-body);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    border: none;
    text-align: left;
    display: flex;
    align-items: center;
    transition: background 0.2s, color 0.2s;
}
.mobile-link:hover { background: var(--glass-2); color: var(--white); }

.mobile-menu-enter-active, .mobile-menu-leave-active { transition: opacity 0.2s, transform 0.2s; }
.mobile-menu-enter-from, .mobile-menu-leave-to { opacity: 0; transform: translateY(-8px); }

.hero {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
    min-height: 90vh;
    padding: 2rem 2rem 4rem;
    max-width: 1280px;
    margin: 0 auto;
}
@media (max-width: 900px) {
    .hero { grid-template-columns: 1fr; min-height: auto; padding: 1.5rem 1rem 3rem; gap: 2rem; }
}

.hero-content { display: flex; flex-direction: column; gap: 1.5rem; }

.hero-eyebrow {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.6rem;
    font-weight: 800;
    letter-spacing: 0.45em;
    text-transform: uppercase;
    color: var(--cyan);
}
.eyebrow-line { display: block; width: 2rem; height: 1px; background: var(--cyan); }

.hero-headline {
    font-family: var(--font-display);
    font-size: clamp(4rem, 9vw, 7.5rem);
    font-weight: 900;
    line-height: 0.88;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    color: var(--white);
}
.headline-accent {
    color: var(--lime);
    font-style: italic;
    letter-spacing: 0.04em;
    text-shadow: 0 0 60px rgba(190,242,100,0.35);
}

.hero-sub {
    font-size: 0.95rem;
    line-height: 1.8;
    color: var(--muted);
    max-width: 380px;
    padding-left: 1rem;
    border-left: 2px solid rgba(255,255,255,0.1);
}
.hero-sub em { font-style: normal; color: var(--white); }

.hero-cta { display: flex; flex-wrap: wrap; gap: 0.75rem; }

.cta-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2rem;
    border-radius: var(--r-pill);
    background: var(--cyan);
    color: var(--navy);
    font-family: var(--font-body);
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 16px 40px -12px rgba(34,211,238,0.5);
}
.cta-primary:hover { background: var(--lime); transform: translateY(-2px); box-shadow: 0 20px 50px -12px rgba(190,242,100,0.5); }

.cta-secondary {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2rem;
    border-radius: var(--r-pill);
    background: var(--glass);
    border: 1px solid var(--border);
    color: var(--white);
    font-family: var(--font-body);
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    backdrop-filter: blur(12px);
}
.cta-secondary:hover { background: var(--glass-2); transform: translateY(-2px); }

.hero-stats {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-top: 0.5rem;
}
.stat { display: flex; flex-direction: column; gap: 0.15rem; }
.stat-num {
    font-family: var(--font-display);
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--white);
    line-height: 1;
}
.stat-label { font-size: 0.58rem; font-weight: 700; letter-spacing: 0.25em; text-transform: uppercase; color: var(--muted); }
.stat-divider { width: 1px; height: 2rem; background: var(--border); }
.live-dot {
    font-size: 0.65rem !important;
    color: var(--lime) !important;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.live-dot::before {
    content: '';
    width: 6px;
    height: 6px;
    background: var(--lime);
    border-radius: 50%;
    animation: livePulse 1.5s ease-in-out infinite;
}
@keyframes livePulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(0.7); } }

.hero-visual { position: relative; display: flex; align-items: center; justify-content: center; }
@media (max-width: 900px) { .hero-visual { order: -1; } }

.hero-img-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    border-radius: var(--r-xl);
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: 0 40px 80px rgba(0,0,0,0.4);
}
.hero-img { width: 100%; height: 100%; object-fit: cover; transition: transform 2s ease; display: block; }
.hero-img-wrap:hover .hero-img { transform: scale(1.05); }
.hero-img-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(7,13,24,0.7) 0%, transparent 55%);
}

.hero-card-float {
    position: absolute;
    bottom: 1.5rem;
    left: 1.5rem;
    right: 1.5rem;
    background: rgba(7,13,24,0.6);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    padding: 1rem 1.25rem;
    border-radius: var(--r-md);
}
.float-eyebrow { font-size: 0.55rem; font-weight: 800; letter-spacing: 0.35em; text-transform: uppercase; color: var(--cyan); margin-bottom: 0.3rem; }
.float-text { font-size: 0.85rem; font-weight: 700; color: var(--white); line-height: 1.4; }

.hero-badge-float {
    position: absolute;
    top: 1.25rem;
    right: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(7,13,24,0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(190,242,100,0.25);
    padding: 0.4rem 0.8rem 0.4rem 0.6rem;
    border-radius: var(--r-pill);
}
.pulse-ring {
    position: relative;
    width: 10px;
    height: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pulse-ring::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1.5px solid var(--lime);
    opacity: 0;
    animation: ring 1.8s ease-out infinite;
}
.pulse-dot { width: 8px; height: 8px; background: var(--lime); border-radius: 50%; position: relative; z-index: 1; }
.badge-text { font-size: 0.65rem; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: var(--lime); }
@keyframes ring { 0% { transform: scale(0.5); opacity: 0.8; } 100% { transform: scale(1.8); opacity: 0; } }

.hero-visual-deco {
    position: absolute;
    inset: -2rem;
    z-index: -1;
    border-radius: var(--r-xl);
    background: radial-gradient(ellipse at center, rgba(34,211,238,0.08) 0%, transparent 70%);
    pointer-events: none;
}

.products-section {
    position: relative;
    z-index: 1;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 2rem 4rem;
}
@media (max-width: 640px) { .products-section { padding: 0 1rem 5rem; } }

.products-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}
.eyebrow-sm {
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--cyan);
    margin-bottom: 0.4rem;
}
.products-title {
    font-family: var(--font-display);
    font-size: clamp(1.4rem, 3vw, 2rem);
    font-weight: 800;
    color: var(--white);
    line-height: 1.2;
}

.products-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem; }
.meta-tag {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--cyan);
    background: rgba(34,211,238,0.08);
    border: 1px solid rgba(34,211,238,0.18);
    padding: 0.2rem 0.6rem;
    border-radius: var(--r-pill);
}
.meta-tag--dim { color: var(--muted); background: var(--glass); border-color: var(--border); }

.cat-scroll {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    scrollbar-width: none;
}
.cat-scroll::-webkit-scrollbar { display: none; }

.cat-pill {
    flex-shrink: 0;
    padding: 0.4rem 1rem;
    border-radius: var(--r-pill);
    border: 1px solid var(--border);
    background: var(--glass);
    color: var(--muted);
    font-family: var(--font-body);
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.cat-pill:hover { background: var(--glass-2); color: var(--white); border-color: rgba(255,255,255,0.2); }
.cat-pill--active { background: var(--lime); color: var(--navy); border-color: var(--lime); font-weight: 800; }

.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}
@media (max-width: 1000px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 700px) { .product-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; } }

.skeleton-card {
    border-radius: var(--r-sm);
    border: 1px solid var(--border);
    background: var(--glass);
    overflow: hidden;
}
.skeleton-img { aspect-ratio: 1; background: var(--glass-2); animation: pulse 1.4s ease-in-out infinite; }
.skeleton-body { padding: 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; }
.skeleton-line {
    height: 9px;
    border-radius: 6px;
    background: var(--glass-2);
    animation: pulse 1.4s ease-in-out infinite;
}
.skeleton-btn { height: 28px; border-radius: var(--r-pill); background: var(--glass); animation: pulse 1.4s ease-in-out infinite; margin-top: 0.25rem; }
@keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }

.product-card {
    border-radius: var(--r-sm);
    border: 1px solid var(--border);
    background: var(--glass);
    overflow: hidden;
    transition: transform 0.25s ease, border-color 0.25s, background 0.25s, box-shadow 0.25s;
    animation: fadeUpIn 0.5s ease both;
    animation-delay: var(--delay, 0ms);
}
@keyframes fadeUpIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.product-card:hover {
    transform: translateY(-5px);
    border-color: rgba(190,242,100,0.3);
    background: var(--glass-2);
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.product-thumb {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--glass);
}
.thumb-link { display: block; height: 100%; }
.product-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; display: block; }
.product-card:hover .product-thumb img { transform: scale(1.07); }

.condition-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    padding: 0.18rem 0.5rem;
    border-radius: var(--r-pill);
    font-size: 0.6rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.condition-badge--lime { background: var(--lime); color: var(--navy); }
.condition-badge--cyan { background: var(--cyan); color: var(--navy); }

.wish-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: rgba(255,255,255,0.6);
    transition: background 0.2s, transform 0.15s, color 0.2s;
}
.wish-btn svg { width: 14px; height: 14px; }
.wish-btn:hover { background: rgba(255,255,255,0.15); color: var(--white); }
.wish-btn--active { color: var(--cyan); }
.wish-btn--pulse { transform: scale(1.3); }

.product-info { padding: 0.75rem; }
.product-link { text-decoration: none; display: block; margin-bottom: 0.6rem; }
.product-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--white);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.25rem;
}
.product-price { font-size: 0.82rem; font-weight: 800; color: var(--lime); }

.add-btn {
    width: 100%;
    padding: 0.45rem 0;
    border-radius: var(--r-pill);
    border: 1px solid var(--border);
    background: var(--glass);
    color: var(--muted);
    font-family: var(--font-body);
    font-size: 0.72rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.add-btn:hover { background: var(--cyan); color: var(--navy); border-color: var(--cyan); }
.add-btn--bounce { transform: scale(1.04); }

.empty-state {
    padding: 4rem 1.5rem;
    text-align: center;
    border: 1px dashed var(--border);
    border-radius: var(--r-md);
    color: var(--muted);
}
.empty-icon { font-size: 2rem; margin-bottom: 1rem; color: var(--border); }
.empty-state p { font-size: 0.9rem; }

.sidebar-float {
    position: fixed;
    left: 1rem;
    top: 50%;
    z-index: 90;
    transform: translateY(-50%);
    display: none;
    flex-direction: column;
    gap: 0.35rem;
    padding: 0.45rem;
    border-radius: var(--r-pill);
    background: var(--glass);
    border: 1px solid var(--border);
    backdrop-filter: blur(20px);
}
@media (min-width: 1120px) { .sidebar-float { display: flex; } }

.float-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.float-btn svg { width: 18px; height: 18px; }
.float-btn:hover { background: var(--glass-2); color: var(--white); }
.float-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 16px;
    height: 16px;
    padding: 0 3px;
    border-radius: var(--r-pill);
    font-size: 8px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cyan);
    color: var(--navy);
}
.float-badge--lime { background: var(--lime); }
.float-badge--white { background: var(--white); }

.drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: 300;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
}
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.28s; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.drawer {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 301;
    width: min(380px, 92vw);
    background: rgba(9,15,26,0.98);
    border-right: 1px solid var(--border);
    border-radius: 0 var(--r-lg) var(--r-lg) 0;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0;
    backdrop-filter: blur(24px);
    overflow-y: auto;
}
.drawer-enter-active, .drawer-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.drawer-enter-from, .drawer-leave-to { transform: translateX(-100%); }

.drawer-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.drawer-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 800; color: var(--white); margin-top: 0.3rem; }

.close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--glass);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s;
}
.close-btn svg { width: 15px; height: 15px; }
.close-btn:hover { background: var(--glass-2); }

.drawer-list { display: flex; flex-direction: column; gap: 0.6rem; flex: 1; }

.wish-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--r-sm);
    border: 1px solid var(--border);
    background: var(--glass);
    transition: border-color 0.2s;
}
.wish-item:hover { border-color: rgba(255,255,255,0.18); }

.wish-img {
    width: 68px;
    height: 68px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--glass-2);
    display: block;
}
.wish-img img { width: 100%; height: 100%; object-fit: cover; }

.wish-info { flex: 1; min-width: 0; }
.wish-name {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--white);
    text-decoration: none;
    margin-bottom: 0.25rem;
}
.wish-price { font-size: 0.78rem; font-weight: 800; color: var(--lime); margin-bottom: 0.5rem; }
.wish-cart-btn {
    padding: 0.28rem 0.7rem;
    border-radius: var(--r-pill);
    border: none;
    background: var(--cyan);
    color: var(--navy);
    font-size: 0.68rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.2s;
}
.wish-cart-btn:hover { background: var(--lime); }

.wish-remove {
    flex-shrink: 0;
    align-self: flex-start;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.wish-remove svg { width: 13px; height: 13px; }
.wish-remove:hover { background: var(--glass-2); color: var(--white); }

.wish-skeleton {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--r-sm);
    border: 1px solid var(--border);
    background: var(--glass);
}
.wish-skeleton-img { width: 68px; height: 68px; border-radius: 10px; background: var(--glass-2); animation: pulse 1.4s ease-in-out infinite; flex-shrink: 0; }
.wish-skeleton-body { flex: 1; display: flex; flex-direction: column; gap: 0.4rem; justify-content: center; }

.drawer-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem 0;
}
.empty-icon-lg { font-size: 3rem; color: var(--muted); margin-bottom: 1rem; line-height: 1; }
.drawer-empty-title { font-size: 1rem; font-weight: 700; color: var(--white); margin-bottom: 0.4rem; }
.drawer-empty-desc { font-size: 0.82rem; color: var(--muted); }
.mt-5 { margin-top: 1.25rem; }

.footer {
    position: relative;
    margin-top: 4rem;
    border-top: 1px solid var(--border);
    background: rgba(5,9,14,0.95);
    overflow: hidden;
}
.footer-glow {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 600px;
    height: 300px;
    background: radial-gradient(ellipse at center bottom, rgba(34,211,238,0.06) 0%, transparent 70%);
    pointer-events: none;
}
.footer-inner {
    position: relative;
    z-index: 1;
    max-width: 1280px;
    margin: 0 auto;
    padding: 4rem 2rem 2rem;
}
@media (max-width: 640px) { .footer-inner { padding: 3rem 1rem 2rem; } }

.footer-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1.2fr;
    gap: 3rem;
    margin-bottom: 3rem;
}
@media (max-width: 900px) { .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; } }
@media (max-width: 520px) { .footer-grid { grid-template-columns: 1fr; gap: 1.5rem; } }

.footer-brand { display: flex; flex-direction: column; gap: 0; }
.footer-logo {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid rgba(190,242,100,0.25);
    overflow: hidden;
    background: var(--navy-2);
    margin-bottom: 0.75rem;
}
.footer-logo img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.3); }
.footer-name {
    font-family: var(--font-display);
    font-size: 1.4rem;
    font-weight: 900;
    text-transform: uppercase;
    font-style: italic;
    letter-spacing: 0.05em;
    margin-bottom: 0.65rem;
}
.footer-desc { font-size: 0.8rem; color: var(--muted); line-height: 1.7; margin-bottom: 1.25rem; }

.footer-socials { display: flex; gap: 0.5rem; }
.social-btn {
    width: 36px;
    height: 36px;
    border-radius: var(--r-sm);
    background: var(--glass);
    border: 1px solid var(--border);
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.social-btn:hover { background: var(--glass-2); color: var(--lime); }

.footer-col {}
.footer-col-title {
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.25);
    margin-bottom: 1.25rem;
}
.footer-links { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; }
.footer-link {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--muted);
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    transition: color 0.2s;
}
.footer-link:hover { color: var(--lime); }

.footer-newsletter-desc {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: var(--muted);
    margin-bottom: 1rem;
}
.newsletter-form { display: flex; gap: 0.5rem; }
.newsletter-input {
    flex: 1;
    background: var(--glass);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: 0.65rem 1rem;
    font-size: 0.78rem;
    color: var(--white);
    font-family: var(--font-body);
    outline: none;
    transition: border-color 0.2s;
}
.newsletter-input::placeholder { color: var(--muted); }
.newsletter-input:focus { border-color: var(--cyan); }
.newsletter-btn {
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: none;
    background: var(--cyan);
    color: var(--navy);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}
.newsletter-btn:hover { background: var(--lime); }

.footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.05);
    flex-wrap: wrap;
    gap: 1rem;
}
.footer-copy { font-size: 0.6rem; font-weight: 800; letter-spacing: 0.25em; text-transform: uppercase; color: rgba(255,255,255,0.2); }
.footer-status { display: flex; align-items: center; gap: 0.5rem; }
.status-dot {
    width: 7px;
    height: 7px;
    background: var(--lime);
    border-radius: 50%;
    animation: livePulse 2s ease-in-out infinite;
}
.status-text { font-size: 0.6rem; font-weight: 800; letter-spacing: 0.25em; text-transform: uppercase; color: rgba(255,255,255,0.3); }

.w-3\/4 { width: 75%; }
.w-1\/2 { width: 50%; }
.ml-2 { margin-left: 0.5rem; }
</style>