<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    store: Object,
    cart: Object,
    wishlist: Array,
    orders: Array,
    urls: Object,
});

// Helper Format Mata Uang
const idr = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);

// Form State sesuai Validasi Backend
const form = useForm({
    recipient_name: '',
    phone: '',
    postal_code: '',
    province: '',
    city: '',
    address: '',
    shipping: 'JNE',
    shipping_service: 'reguler',
    payment_method: 'va',
});

// Computed Properties buat Summary
const items = computed(() => props.cart?.items || []);
const subtotal = computed(() => Number(props.cart?.subtotal || 0));
const tax = computed(() => Number(props.cart?.tax || 0));

const shippingCost = computed(() => {
    if (form.shipping_service === 'express') return 45000;
    if (form.shipping_service === 'hemat') return 10000;
    return 18000; // default reguler
});

const grandTotal = computed(() => subtotal.value + tax.value + shippingCost.value);

/**
 * LOGIC: Submit Checkout dengan Auto-Focus & Smooth Scroll
 */
const submitCheckout = () => {
    // 1. Definisikan field wajib dan labelnya untuk selector
    const requiredFields = [
        { key: 'recipient_name', label: 'Target Name' },
        { key: 'phone', label: 'Comm. Line' },
        { key: 'province', label: 'Province' },
        { key: 'city', label: 'City' },
        { key: 'postal_code', label: 'Postal Code' },
        { key: 'address', label: 'Detailed Address' }
    ];

    // 2. Bersihkan error sebelumnya
    form.clearErrors();

    // 3. Cari field pertama yang kosong
    const firstEmpty = requiredFields.find(field => !form[field.key] || form[field.key].trim() === '');

    if (firstEmpty) {
        // Set error ke Inertia Form biar muncul pesan error di UI
        form.setError(firstEmpty.key, `${firstEmpty.label} is required for deployment.`);

        // Cari elemen berdasarkan placeholder atau tag name (textarea khusus buat address)
        const selector = firstEmpty.key === 'address' 
            ? 'textarea' 
            : `input[placeholder*="${firstEmpty.label}"]`;
            
        const inputElement = document.querySelector(selector);
        
        if (inputElement) {
            // Scroll ke elemen biar keliatan sama user
            inputElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Kasih delay dikit biar scroll-nya kelar baru focus (biar smooth di Linux/Ubuntu)
            setTimeout(() => {
                inputElement.focus();
            }, 500);
        }
        return;
    }

    // 4. Kirim Data jika semua valid
    form.post(props.urls.process, {
        preserveScroll: true,
        onStart: () => console.log('Initiating Deployment...'),
        onSuccess: () => {
            console.log('Order Confirmed. Redirecting to Success Page...');
        },
        onError: (errors) => {
            // Jika ada error dari server (misal validasi backend gagal)
            console.error('Deployment Failed:', errors);
            // Fokus ke field pertama yang error dari server
            const firstErrorKey = Object.keys(errors)[0];
            const errorElement = document.querySelector(`input[name="${firstErrorKey}"], input`);
            if (errorElement) errorElement.focus();
        }
    });
};

onMounted(() => {
    console.log('Vault Data Loaded:', props.cart);
});
</script>

<template>
    <Head title="ZYLOS - Secure Checkout" />
    <div class="min-h-screen bg-[#050B10] text-white selection:bg-lime-400 selection:text-black font-['DM_Sans']">
        
        <main class="max-w-7xl mx-auto px-6 py-16 grid lg:grid-cols-[1fr_450px] gap-12">
            
            <section class="space-y-10">
                <div>
                    <h1 class="text-6xl font-black font-['Syne'] tracking-tighter leading-none uppercase">
                        SECURE <span class="text-lime-400 italic">CHECKOUT</span>
                    </h1>
                    <p class="mt-4 text-white/40 font-bold uppercase tracking-[0.3em] text-xs">Deployment destination protocol</p>
                </div>

                <div class="space-y-8">
                    <div class="p-8 bg-white/[0.02] border border-white/10 rounded-[40px] space-y-6 shadow-2xl">
                        <h2 class="text-xl font-black font-['Syne'] italic tracking-tight text-white">RECIPIENT INFO</h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-white/30">Target Name</label>
                                <input v-model="form.recipient_name" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-lime-400 focus:ring-0 transition text-sm" placeholder="Ada Wong">
                                <p v-if="form.errors.recipient_name" class="text-rose-500 text-[10px] font-bold uppercase">{{ form.errors.recipient_name }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-white/30">Comm. Line (Phone)</label>
                                <input v-model="form.phone" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-lime-400 focus:ring-0 transition text-sm" placeholder="+62...">
                            </div>
                        </div>
                    </div>

                    <div class="p-8 bg-white/[0.02] border border-white/10 rounded-[40px] space-y-6 shadow-2xl">
                        <h2 class="text-xl font-black font-['Syne'] italic tracking-tight text-white">LOCATION TAG</h2>
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-white/30">Province</label>
                                <input v-model="form.province" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-lime-400 focus:ring-0 transition text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-white/30">City</label>
                                <input v-model="form.city" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-lime-400 focus:ring-0 transition text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-white/30">Postal Code</label>
                                <input v-model="form.postal_code" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-lime-400 focus:ring-0 transition text-sm">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-white/30">Detailed Address</label>
                            <textarea v-model="form.address" rows="3" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-lime-400 focus:ring-0 transition text-sm resize-none"></textarea>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="p-8 bg-white/[0.02] border border-white/10 rounded-[40px] space-y-6">
                            <h2 class="text-xl font-black font-['Syne'] italic tracking-tight text-cyan-400">LOGISTICS</h2>
                            <select v-model="form.shipping_service" class="w-full bg-[#050B10] border border-white/10 rounded-2xl px-5 py-4 text-xs font-black uppercase tracking-widest focus:border-cyan-400 focus:ring-0">
                                <option value="reguler">Standard Protocol (2-3 Days)</option>
                                <option value="express">Express Insertion (1 Day)</option>
                                <option value="hemat">Low Priority (5-7 Days)</option>
                            </select>
                        </div>
                        <div class="p-8 bg-white/[0.02] border border-white/10 rounded-[40px] space-y-6">
                            <h2 class="text-xl font-black font-['Syne'] italic tracking-tight text-lime-400">CURRENCY</h2>
                            <select v-model="form.payment_method" class="w-full bg-[#050B10] border border-white/10 rounded-2xl px-5 py-4 text-xs font-black uppercase tracking-widest focus:border-lime-400 focus:ring-0">
                                <option value="va">Virtual Account</option>
                                <option value="ewallet">E-Wallet / QRIS</option>
                                <option value="cod">Cash on Delivery</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="h-fit lg:sticky lg:top-10">
                <div class="bg-white/[0.03] border border-white/10 rounded-[40px] p-8 backdrop-blur-3xl relative overflow-hidden shadow-[0_40px_100px_-20px_rgba(0,0,0,0.8)]">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-lime-400/5 rounded-full blur-3xl"></div>

                    <h2 class="text-2xl font-black font-['Syne'] tracking-tight mb-8">ORDER SUMMARY</h2>
                    
                    <div class="space-y-4 mb-8 max-h-[280px] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="item in items" :key="item.key" class="flex gap-4 p-4 bg-white/5 rounded-3xl border border-white/5 group hover:border-white/20 transition-all duration-500">
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-black border border-white/10 shrink-0">
                                <img :src="item.image" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[11px] font-black uppercase truncate text-white/90 group-hover:text-lime-400 transition">{{ item.name }}</h4>
                                <p class="text-[9px] font-bold text-white/30 mt-1 italic tracking-widest">SZ {{ item.size }} × {{ item.qty }}</p>
                                <p class="text-xs font-black mt-2 text-white">{{ idr(item.price * item.qty) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-white/10 my-8"></div>

                    <div class="space-y-4 mb-10">
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-[0.2em] text-white/30">
                            <span>Subtotal</span>
                            <span class="text-white">{{ idr(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-[0.2em] text-white/30">
                            <span>Taxation (11%)</span>
                            <span class="text-white">{{ idr(tax) }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-[0.2em] text-white/30">
                            <span>Logistic Fee</span>
                            <span class="text-cyan-400">{{ idr(shippingCost) }}</span>
                        </div>
                        <div class="flex justify-between items-end pt-6 border-t border-white/5">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 mb-1">Final Amount</span>
                                <span class="text-4xl font-black text-lime-400 tracking-tighter leading-none">{{ idr(grandTotal) }}</span>
                            </div>
                        </div>
                    </div>

                    <button @click="submitCheckout" :disabled="form.processing"
                            class="group relative w-full bg-lime-400 py-6 rounded-[2rem] overflow-hidden transition-all duration-500 hover:shadow-[0_20px_50px_rgba(163,230,53,0.3)] hover:-translate-y-1 active:scale-[0.98] disabled:opacity-50">
                        <div class="relative z-10 flex items-center justify-center gap-3 text-black font-black uppercase tracking-[0.2em] text-sm">
                            <span>{{ form.processing ? 'Processing...' : 'Confirm Order' }}</span>
                            <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-white translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                    </button>

                    <Link :href="urls.cart" class="mt-6 block text-center text-[10px] font-black text-white/20 hover:text-white transition uppercase tracking-[0.3em] italic">
                        ← Back to Vault Items
                    </Link>
                </div>
            </aside>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }

input:focus, textarea:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.1);
}
</style>