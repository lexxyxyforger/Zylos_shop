<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    cart: { type: Object, required: true },
    urls: { type: Object, required: true },
    storeLogo: { type: String, default: 'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png' }
});

// --- LOGIC SECTION (Tetap Utuh) ---
const cartState = ref(props.cart);
const savingKey = ref(null);
const removingKey = ref(null);
const toast = ref(null);

const items = computed(() => cartState.value.items || []);
const hasItems = computed(() => items.value.length > 0);
const subtotal = computed(() => cartState.value.subtotal || 0);
const tax = computed(() => cartState.value.tax || 0);
const shippingEstimate = computed(() => (hasItems.value ? 18000 : 0));
const grandTotal = computed(() => subtotal.value + tax.value + shippingEstimate.value);

const idr = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);

const setToast = (type, message) => {
    toast.value = { type, message };
    window.setTimeout(() => { toast.value = null; }, 2500);
};

const itemUrl = (item) => `${props.urls.updateBase}/${item.key}`;

const syncCart = (response) => {
    cartState.value = response.data.cart;
    setToast('success', response.data.message || 'Keranjang diperbarui.');
};

const updateQuantity = async (item, quantity) => {
    const nextQuantity = Math.min(Math.max(1, quantity), item.stock || 99);
    savingKey.value = item.key;
    try {
        const response = await window.axios.patch(itemUrl(item), { quantity: nextQuantity });
        syncCart(response);
    } catch (error) {
        setToast('error', error?.response?.data?.message || 'Gagal update.');
    } finally {
        savingKey.value = null;
    }
};

const removeItem = async (item) => {
    removingKey.value = item.key;
    savingKey.value = item.key;
    try {
        await new Promise((resolve) => window.setTimeout(resolve, 180));
        const response = await window.axios.delete(itemUrl(item));
        syncCart(response);
    } catch (error) {
        setToast('error', 'Gagal menghapus.');
    } finally {
        removingKey.value = null;
        savingKey.value = null;
    }
};

const clearCart = async () => {
    savingKey.value = 'clear';
    try {
        const response = await window.axios.delete(props.urls.clear);
        syncCart(response);
    } catch (error) {
        setToast('error', 'Gagal mengosongkan.');
    } finally {
        savingKey.value = null;
    }
};
</script>

<template>
    <div class="root min-h-screen bg-[#050B10] text-white selection:bg-lime-400 selection:text-black font-['DM_Sans']">
        
        <Transition name="fade">
            <div v-if="toast" 
                 class="fixed top-8 right-8 z-[100] px-6 py-3 rounded-2xl border backdrop-blur-2xl shadow-2xl transition-all duration-300"
                 :class="toast.type === 'success' ? 'border-lime-400/30 bg-lime-400/10 text-lime-400' : 'border-red-400/30 bg-red-400/10 text-red-400'">
                {{ toast.message }}
            </div>
        </Transition>

        <header class="sticky top-0 z-50 px-4 pt-4 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-7xl rounded-[1.9rem] border border-white/10 bg-[linear-gradient(180deg,rgba(7,11,21,0.98),rgba(11,16,28,0.94))] shadow-[0_30px_90px_-40px_rgba(15,23,42,0.9)] backdrop-blur-2xl">
                <div class="flex min-h-[5rem] items-center justify-between gap-4 px-4 py-3 sm:px-5 lg:px-6">
                    
                    <Link :href="route('store.index')" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-cyan-400/30 bg-slate-950/80 p-1 shadow-[0_0_0_1px_rgba(255,255,255,0.04)]">
                            <img :src="props.storeLogo" alt="Brand Logo" class="h-full w-full rounded-full object-cover">
                        </div>
                        <div class="min-w-0 leading-tight">
                            <p class="text-[0.62rem] font-semibold uppercase tracking-[0.34em] text-lime-300/90 italic">VERIFIED STORE</p>
                            <p class="truncate text-sm font-black uppercase tracking-[0.3em] text-white sm:text-base">ZYLOS OFFICIAL</p>
                        </div>
                    </Link>

                    <div class="hidden items-center justify-center lg:flex">
                        <div class="inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/5 p-1">
                            <Link :href="route('store.index')" 
                                  class="rounded-full px-4 py-2 text-sm font-semibold transition"
                                  :class="route().current('store.index') ? 'bg-white text-slate-900 shadow-xl' : 'text-white/75 hover:bg-white/10'">
                                Store
                            </Link>
                            <Link :href="route('store.history')" 
                                  class="rounded-full px-4 py-2 text-sm font-semibold transition"
                                  :class="route().current('store.history') ? 'bg-white text-slate-900 shadow-xl' : 'text-white/75 hover:bg-white/10'">
                                History
                            </Link>
                            <Link :href="route('cart.index')" 
                                  class="rounded-full px-4 py-2 text-sm font-semibold transition"
                                  :class="route().current('cart.index') ? 'bg-white text-slate-900 shadow-xl' : 'text-white/75 hover:bg-white/10'">
                                Cart
                            </Link>
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')" 
                                  class="rounded-full px-4 py-2 text-sm font-semibold transition text-white/75 hover:bg-white/10">
                                Dashboard
                            </Link>
                        </div>
                    </div>

                    <div class="hidden items-center justify-end sm:flex">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                              class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-xl transition hover:-translate-y-0.5">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-900 text-xs font-black text-white">
                                {{ $page.props.auth.user.name.substring(0,1).toUpperCase() }}
                            </span>
                            <span class="max-w-[10rem] truncate">{{ $page.props.auth.user.name }}</span>
                        </Link>
                        <Link v-else :href="route('login')" class="rounded-full bg-white px-6 py-2.5 text-sm font-black text-black">
                            LOGIN
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-16 grid lg:grid-cols-[1fr_400px] gap-12">
            
            <section>
                <div class="mb-10">
                    <p class="text-xs font-black uppercase tracking-[0.4em] text-cyan-400 mb-2">Vault Collection</p>
                    <h1 class="text-6xl font-black font-['Syne'] tracking-tighter leading-none uppercase">YOUR <span class="text-lime-400 italic">CART</span></h1>
                </div>

                <div v-if="!hasItems" class="py-24 border border-dashed border-white/10 rounded-[40px] text-center bg-white/[0.02]">
                    <p class="text-2xl font-bold text-white/30 italic">Your vault is currently empty.</p>
                    <Link :href="urls.store" class="mt-6 inline-block text-lime-400 underline underline-offset-8 font-black uppercase tracking-widest text-sm">Explore Store</Link>
                </div>

                <div v-else class="space-y-4">
                    <TransitionGroup name="list">
                        <article v-for="item in items" :key="item.key" 
                                 class="group flex flex-col sm:flex-row gap-6 p-6 bg-white/[0.03] border border-white/5 rounded-[32px] backdrop-blur-sm transition-all duration-500 hover:bg-white/[0.06]"
                                 :class="{'opacity-0 scale-95 pointer-events-none': removingKey === item.key}">
                            
                            <div class="w-full sm:w-32 h-32 rounded-2xl overflow-hidden bg-zinc-900 border border-white/10">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                            </div>

                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xl font-bold leading-tight group-hover:text-lime-400 transition duration-300 uppercase">{{ item.name }}</h3>
                                    <div class="flex gap-3 mt-3">
                                        <span class="text-[10px] font-black uppercase tracking-widest bg-white/5 px-3 py-1 rounded-full text-white/60 border border-white/5 italic">Size {{ item.size }}</span>
                                        <span class="text-[10px] font-black uppercase tracking-widest bg-white/5 px-3 py-1 rounded-full text-white/60 border border-white/5 italic">{{ item.category }}</span>
                                    </div>
                                </div>
                                <p class="text-lg font-black text-white/80 mt-4">{{ idr(item.price) }} <span class="text-[10px] text-white/20 uppercase">/ unit</span></p>
                            </div>

                            <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between">
                                <div class="flex items-center bg-black/40 border border-white/10 rounded-full p-1 h-fit">
                                    <button @click="updateQuantity(item, item.qty - 1)" 
                                            class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center font-bold"
                                            :disabled="savingKey === item.key">-</button>
                                    <span class="w-10 text-center font-mono font-bold">{{ item.qty }}</span>
                                    <button @click="updateQuantity(item, item.qty + 1)" 
                                            class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center font-bold"
                                            :disabled="savingKey === item.key">+</button>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-black text-lime-400">{{ idr(item.line_total) }}</p>
                                    <button @click="removeItem(item)" class="text-[10px] font-black text-rose-500/50 hover:text-rose-500 transition uppercase tracking-widest">Remove</button>
                                </div>
                            </div>
                        </article>
                    </TransitionGroup>
                </div>
            </section>

            <aside class="h-fit lg:sticky lg:top-28">
                <div class="bg-white/[0.03] border border-white/10 rounded-[40px] p-8 backdrop-blur-3xl relative overflow-hidden">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-black font-['Syne'] tracking-tight">SUMMARY</h2>
                        <button v-if="hasItems" @click="clearCart" class="text-[10px] font-black tracking-widest text-white/20 hover:text-rose-500 transition uppercase">Clear All</button>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-white/40 text-xs font-black uppercase tracking-widest">
                            <span>Subtotal</span>
                            <span class="text-white font-bold">{{ idr(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-white/40 text-xs font-black uppercase tracking-widest">
                            <span>Tax (11%)</span>
                            <span class="text-white font-bold">{{ idr(tax) }}</span>
                        </div>
                        <div class="flex justify-between text-white/40 text-xs font-black uppercase tracking-widest">
                            <span>Shipping Est.</span>
                            <span class="text-white font-bold font-mono">{{ idr(shippingEstimate) }}</span>
                        </div>
                        <div class="h-px bg-white/10 my-6"></div>
                        <div class="flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/20">Total Amount</span>
                            <span class="text-4xl font-black text-lime-400 tracking-tighter leading-none">{{ idr(grandTotal) }}</span>
                        </div>
                    </div>

                    <a :href="props.urls.checkout" 
                       class="group relative w-full block bg-cyan-400 py-6 rounded-3xl overflow-hidden transition-all duration-500 hover:shadow-[0_0_30px_rgba(34,211,238,0.4)]">
                        <div class="relative z-10 text-center text-black font-black uppercase tracking-widest flex items-center justify-center gap-2">
                            Secure Checkout 
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-white translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    </a>
                    
                    <p class="text-[9px] text-center text-white/20 mt-6 font-bold uppercase tracking-[0.2em]">
                        🔒 Zylos Secured Transaction
                    </p>
                </div>
            </aside>

        </main>
    </div>
</template>

<style scoped>
.root { scroll-behavior: smooth; }
.list-enter-active, .list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(30px); }
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-20px); }

input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>