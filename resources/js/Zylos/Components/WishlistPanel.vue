<script setup>
import { ref } from "vue";

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    wishlist: {
        type: Array,
        default: () => [],
    },
    formatIDR: {
        type: Function,
        required: true,
    },
    wishlistUrl: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["close", "toggle-wishlist", "add-to-cart"]);

const removing = ref(null);

const handleToggle = (product) => {
    removing.value = product.product_id;
    emit("toggle-wishlist", product);
    setTimeout(() => {
        removing.value = null;
    }, 300);
};

const handleAddToCart = (product) => {
    emit("add-to-cart", product);
};

const closePanel = () => {
    emit("close");
};
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition name="fade">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
                @click="closePanel"
            ></div>
        </Transition>

        <!-- Panel -->
        <Transition name="slide-left">
            <aside
                v-if="isOpen"
                class="fixed right-0 top-0 z-50 flex h-full w-full max-w-sm flex-col overflow-hidden rounded-l-3xl border-l border-white/20 bg-[#0F172A]/95 shadow-2xl backdrop-blur-xl"
            >
                <!-- Header -->
                <header
                    class="flex items-center justify-between border-b border-white/10 px-5 py-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid h-10 w-10 place-items-center rounded-full bg-rose-400/20"
                        >
                            <svg
                                class="h-5 w-5 text-rose-400"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white">
                                Wishlist
                            </h2>
                            <p class="text-xs text-white/50">
                                {{ wishlist.length }} item{{
                                    wishlist.length !== 1 ? "s" : ""
                                }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white/70 transition hover:bg-white/20 hover:text-white active:scale-95"
                        @click="closePanel"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </header>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-5">
                    <!-- Empty State -->
                    <div
                        v-if="wishlist.length === 0"
                        class="flex h-full flex-col items-center justify-center text-center"
                    >
                        <div class="mb-4 rounded-full bg-white/5 p-6">
                            <svg
                                class="h-16 w-16 text-white/20"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-white/80">
                            Wishlist kosong
                        </h3>
                        <p class="text-sm text-white/50">
                            Wishlist kamu masih kosong
                        </p>
                    </div>

                    <!-- Wishlist Items -->
                    <ul v-else class="space-y-4">
                        <li
                            v-for="item in wishlist"
                            :key="item.product_id"
                            class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-4 transition-all duration-300 hover:border-white/20"
                            :class="
                                removing === item.product_id
                                    ? 'opacity-50 scale-95'
                                    : 'opacity-100'
                            "
                        >
                            <div class="flex gap-4">
                                <!-- Image -->
                                <div
                                    class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl bg-slate-800/50"
                                >
                                    <img
                                        :src="
                                            item.image ||
                                            'https://placehold.co/200x200/1e293b/64748b?text=IMG'
                                        "
                                        :alt="item.name"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>

                                <!-- Details -->
                                <div
                                    class="flex flex-1 flex-col justify-between"
                                >
                                    <div>
                                        <h4
                                            class="line-clamp-1 text-sm font-semibold text-white/90"
                                        >
                                            {{ item.name }}
                                        </h4>
                                        <p
                                            class="mt-1 text-sm font-bold text-cyan-300"
                                        >
                                            {{ formatIDR(item.price) }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="flex-1 rounded-lg bg-cyan-400 py-2 text-xs font-bold uppercase tracking-wide text-black transition hover:bg-lime-300 active:scale-95"
                                            @click="handleAddToCart(item)"
                                        >
                                            + Keranjang
                                        </button>
                                        <button
                                            type="button"
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-rose-400/20 text-rose-400 transition hover:bg-rose-400/30 active:scale-95"
                                            :aria-label="
                                                'Remove ' +
                                                item.name +
                                                ' from wishlist'
                                            "
                                            @click="handleToggle(item)"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    d="M18 6 6 18M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Footer -->
                <footer
                    v-if="wishlist.length > 0"
                    class="border-t border-white/10 p-5"
                >
                    <p class="text-center text-xs text-white/40">
                        Tahan item untuk swipe/hapus
                    </p>
                </footer>
            </aside>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active {
    transition:
        transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.35s ease;
}

.slide-left-enter-from,
.slide-left-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>
