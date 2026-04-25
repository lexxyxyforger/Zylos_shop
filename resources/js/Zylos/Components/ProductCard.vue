<script setup>
import { ref } from "vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    isWishlisted: {
        type: Boolean,
        default: false,
    },
    formatIDR: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["toggle-wishlist", "add-to-cart"]);

const imageLoaded = ref(false);
const heartPulse = ref(false);
const cartBounce = ref(false);

const toggleWishlist = () => {
    heartPulse.value = true;
    setTimeout(() => {
        heartPulse.value = false;
    }, 220);
    emit("toggle-wishlist", props.product);
};

const addToCart = () => {
    cartBounce.value = true;
    setTimeout(() => {
        cartBounce.value = false;
    }, 260);
    emit("add-to-cart", props.product);
};

const onImageLoad = () => {
    imageLoaded.value = true;
};
</script>

<template>
    <article
        class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/20 bg-white/10 backdrop-blur-xl shadow-2xl transition-all duration-300 hover:scale-[1.03] hover:border-white/30"
    >
        <!-- Image Container -->
        <div class="relative aspect-square overflow-hidden bg-slate-800/50">
            <!-- Skeleton Loader -->
            <div
                v-if="!imageLoaded"
                class="absolute inset-0 flex animate-pulse items-center justify-center"
            >
                <div class="h-full w-full bg-slate-700/50"></div>
            </div>

            <!-- Product Image -->
            <img
                :src="product.image"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
                @load="onImageLoad"
            />

            <!-- Badge -->
            <span
                v-if="product.condition === 'new' || product.stock > 0"
                class="absolute left-3 top-3 rounded-full bg-lime-300 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-black shadow-lg"
            >
                New
            </span>

            <!-- Wishlist Button -->
            <button
                type="button"
                class="absolute right-3 top-3 grid h-9 w-9 place-items-center rounded-full bg-black/50 backdrop-blur transition-all duration-200 hover:bg-black/70 active:scale-95"
                :class="[
                    isWishlisted ? 'text-rose-400' : 'text-white/70',
                    heartPulse ? 'scale-125' : 'scale-100',
                ]"
                :aria-label="
                    isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'
                "
                @click="toggleWishlist"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    :fill="isWishlisted ? 'currentColor' : 'none'"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"
                    />
                </svg>
            </button>
        </div>

        <!-- Card Content -->
        <div class="flex flex-1 flex-col gap-2.5 p-4">
            <!-- Product Name -->
            <h3 class="line-clamp-1 text-sm font-semibold text-white/90">
                {{ product.name }}
            </h3>

            <!-- Price -->
            <p class="text-lg font-black text-cyan-300">
                {{ formatIDR(product.price) }}
            </p>

            <!-- Add to Cart Button -->
            <button
                type="button"
                class="mt-auto w-full rounded-xl bg-gradient-to-r from-cyan-400 to-cyan-500 py-2.5 text-xs font-bold uppercase tracking-wider text-black shadow-lg transition-all duration-200 hover:from-lime-300 hover:to-lime-400 active:scale-95"
                :class="cartBounce ? 'scale-95' : 'scale-100'"
                @click="addToCart"
            >
                Tambah ke Keranjang
            </button>
        </div>
    </article>
</template>
