<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    images: {
        type: Array,
        default: () => [],
    },
    sizes: {
        type: Array,
        default: () => [],
    },
    urls: {
        type: Object,
        required: true,
    },
});

const fallbackImage = 'https://placehold.co/900x900/e2e8f0/334155?text=ZYLOS';
const activeImage = ref(0);
const quantity = ref(1);
const saving = ref(false);
const toast = ref(null);

const imageList = computed(() => {
    const orderedImages = [props.product.image || fallbackImage, ...(props.images || [])].filter(Boolean);
    const uniqueImages = orderedImages.filter((image, index, list) => list.indexOf(image) === index);

    return uniqueImages.length ? uniqueImages : [fallbackImage];
});
const heroImage = computed(() => imageList.value[activeImage.value] || fallbackImage);

const availableSizes = computed(() =>
    props.sizes.length ? props.sizes : [{ uuid: null, label: 'All Size', stock: null }],
);
const selectedSize = ref(availableSizes.value.find((size) => size.stock !== 0) || availableSizes.value[0]);

const formattedPrice = computed(() =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.product.price || 0),
);

const selectedStock = computed(() => selectedSize.value?.stock ?? props.product.stock ?? null);
const maxQuantity = computed(() => Math.max(1, selectedStock.value || props.product.stock || 1));

const setToast = (type, message) => {
    toast.value = { type, message };
    window.setTimeout(() => {
        toast.value = null;
    }, 2600);
};

const selectSize = (size) => {
    if (size.stock === 0) return;

    selectedSize.value = size;
    quantity.value = Math.min(quantity.value, maxQuantity.value);
};

const updateQuantity = (nextQuantity) => {
    quantity.value = Math.min(Math.max(1, nextQuantity), maxQuantity.value);
};

const nextImage = () => {
    activeImage.value = (activeImage.value + 1) % imageList.value.length;
};

const previousImage = () => {
    activeImage.value = (activeImage.value - 1 + imageList.value.length) % imageList.value.length;
};

const addToCart = async (goToCart = false) => {
    saving.value = true;

    try {
        await window.axios.post(props.urls.addToCart, {
            product_uuid: props.product.uuid,
            product_size_id: selectedSize.value?.uuid || null,
            size: selectedSize.value?.label || 'All Size',
            quantity: quantity.value,
        });

        if (goToCart) {
            window.location.href = props.urls.cart;
            return;
        }

        setToast('success', 'Produk masuk keranjang.');
    } catch (error) {
        const message = error?.response?.data?.message || 'Gagal menambahkan produk.';
        setToast('error', message);
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-[#0F172A] text-white">
        <div
            class="fixed inset-0 scale-105 bg-cover bg-center opacity-35 blur-md"
            :style="{ backgroundImage: `url('${heroImage}')` }"
            aria-hidden="true"
        ></div>
        <div
            class="fixed inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(34,211,238,0.16),transparent_30%),radial-gradient(circle_at_85%_10%,rgba(190,242,100,0.12),transparent_25%),linear-gradient(180deg,rgba(15,23,42,0.55),rgba(15,23,42,0.92))]"
            aria-hidden="true"
        ></div>

        <div
            v-if="toast"
            class="fixed right-4 top-20 z-50 rounded-2xl border px-4 py-3 text-sm font-semibold shadow-2xl backdrop-blur-xl"
            :class="
                toast.type === 'success'
                    ? 'border-lime-300/40 bg-lime-300/20 text-lime-100'
                    : 'border-rose-300/40 bg-rose-300/20 text-rose-100'
            "
        >
            {{ toast.message }}
        </div>

        <div class="relative mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <a
                :href="urls.store"
                class="mb-6 inline-flex items-center rounded-full border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-2xl backdrop-blur-xl transition hover:bg-white/15"
            >
                Kembali ke Store
            </a>

            <section class="grid gap-8 rounded-[2rem] border border-white/10 bg-white/10 p-4 shadow-2xl backdrop-blur-2xl lg:grid-cols-[1.05fr_0.95fr] lg:p-7">
                <div class="space-y-3">
                    <div class="relative aspect-square overflow-hidden rounded-[2rem] bg-black/30">
                        <img
                            :src="heroImage"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A] via-[#0F172A]/15 to-transparent"></div>

                        <div v-if="imageList.length > 1" class="absolute inset-x-3 top-1/2 flex -translate-y-1/2 justify-between">
                            <button
                                type="button"
                                class="grid h-10 w-10 place-items-center rounded-full border border-white/10 bg-black/50 text-xl font-black text-white shadow-2xl backdrop-blur-xl transition hover:bg-black/70"
                                @click="previousImage"
                            >
                                &lsaquo;
                            </button>
                            <button
                                type="button"
                                class="grid h-10 w-10 place-items-center rounded-full border border-white/10 bg-black/50 text-xl font-black text-white shadow-2xl backdrop-blur-xl transition hover:bg-black/70"
                                @click="nextImage"
                            >
                                &rsaquo;
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-5 gap-2">
                        <button
                            v-for="(image, index) in imageList"
                            :key="`${image}-${index}`"
                            type="button"
                            class="aspect-square overflow-hidden rounded-2xl border bg-black/20 transition"
                            :class="activeImage === index ? 'border-lime-300 ring-2 ring-lime-300/20' : 'border-white/10 hover:border-white/30'"
                            @click="activeImage = index"
                        >
                            <img :src="image" :alt="`${product.name} ${index + 1}`" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold"
                                :class="product.condition === 'used' ? 'bg-cyan-300 text-[#0F172A]' : 'bg-lime-300 text-[#0F172A]'"
                            >
                                {{ product.condition === 'used' ? 'Bekas' : 'Baru' }}
                            </span>
                            <span v-if="product.category" class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-bold text-white/70">
                                {{ product.category.name }}
                            </span>
                        </div>

                        <h1 class="text-2xl font-black leading-tight text-white sm:text-4xl">
                            {{ product.name }}
                        </h1>
                        <p class="text-3xl font-black text-white">{{ formattedPrice }}</p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/10 p-4 text-sm leading-relaxed text-white/70 backdrop-blur-xl">
                        {{ product.about }}
                    </div>

                    <section class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-black uppercase tracking-[0.18em] text-cyan-200">Ukuran</h2>
                            <p class="text-xs font-semibold text-white/60">
                                Stok {{ selectedStock ?? product.stock }}
                            </p>
                        </div>

                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            <button
                                v-for="size in availableSizes"
                                :key="`${size.uuid || 'default'}-${size.label}`"
                                type="button"
                                class="min-h-11 rounded-2xl border px-3 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-40"
                                :class="
                                    selectedSize?.label === size.label
                                        ? 'border-lime-300 bg-lime-300 text-[#0F172A]'
                                        : 'border-white/10 bg-white/5 text-white/80 hover:border-white/25 hover:bg-white/10'
                                "
                                :disabled="size.stock === 0"
                                @click="selectSize(size)"
                            >
                                {{ size.label }}
                            </button>
                        </div>
                    </section>

                    <section class="flex flex-wrap items-center gap-3">
                        <div class="inline-flex h-12 items-center rounded-full border border-white/10 bg-black/20 backdrop-blur-xl">
                            <button
                                type="button"
                                class="grid h-12 w-12 place-items-center rounded-l-full text-xl font-black text-white/80 transition hover:bg-white/5"
                                @click="updateQuantity(quantity - 1)"
                            >
                                -
                            </button>
                            <input
                                v-model.number="quantity"
                                type="number"
                                min="1"
                                :max="maxQuantity"
                                class="h-12 w-16 border-0 bg-transparent p-0 text-center text-sm font-black text-white focus:ring-0"
                                @input="updateQuantity(Number(quantity || 1))"
                            />
                            <button
                                type="button"
                                class="grid h-12 w-12 place-items-center rounded-r-full text-xl font-black text-white/80 transition hover:bg-white/5"
                                @click="updateQuantity(quantity + 1)"
                            >
                                +
                            </button>
                        </div>
                    </section>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            class="rounded-full border border-white/10 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/15 disabled:opacity-50"
                            :disabled="saving"
                            @click="addToCart(false)"
                        >
                            Tambah Keranjang
                        </button>
                        <button
                            type="button"
                            class="rounded-full bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800 disabled:opacity-50"
                            :disabled="saving"
                            @click="addToCart(true)"
                        >
                            {{ saving ? 'Memproses...' : 'Beli Sekarang' }}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
