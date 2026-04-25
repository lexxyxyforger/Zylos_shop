<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    store: Object,
    order: Object, // Dari controller
    urls: Object
});

const idr = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
</script>

<template>
    <Head title="DEPLOYMENT SUCCESSFUL" />
    <div class="min-h-screen bg-[#050B10] text-white flex items-center justify-center p-6 font-['DM_Sans']">
        
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-lime-400/10 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-2xl w-full relative z-10 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-lime-400 mb-8 shadow-[0_0_50px_rgba(163,230,53,0.4)] animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-6xl font-black font-['Syne'] tracking-tighter uppercase leading-none mb-4">
                DEPLOYMENT <br />
                <span class="text-lime-400 italic">SUCCESSFUL</span>
            </h1>
            
            <p class="text-white/40 font-bold uppercase tracking-[0.4em] text-[10px] mb-12">Transaction ID: {{ order?.id || 'ZYL-UNKNWN' }}</p>

            <div class="bg-white/[0.03] border border-white/10 rounded-[40px] p-8 mb-12 text-left backdrop-blur-xl">
                <div class="space-y-4">
                    <div v-for="item in order?.items" :key="item.name" class="flex justify-between items-center border-b border-white/5 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center gap-4">
                            <img :src="item.image" class="w-12 h-12 rounded-xl object-cover grayscale opacity-50" />
                            <div>
                                <p class="text-xs font-black uppercase tracking-widest text-white">{{ item.name }}</p>
                                <p class="text-[10px] text-white/30 font-bold uppercase">Qty {{ item.qty }}</p>
                            </div>
                        </div>
                        <p class="font-black text-sm text-lime-400">{{ idr(item.price) }}</p>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-end">
                    <p class="text-[10px] font-black uppercase tracking-widest text-white/30">Total Invested</p>
                    <p class="text-3xl font-black tracking-tighter text-white leading-none">{{ idr(order?.total) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                <Link :href="urls.home" class="px-10 py-5 bg-white text-black font-black rounded-3xl uppercase tracking-widest text-xs hover:bg-zinc-200 transition active:scale-95">
                    Return to Store
                </Link>
                <Link :href="urls.history" class="px-10 py-5 bg-white/5 border border-white/10 text-white font-black rounded-3xl uppercase tracking-widest text-xs hover:bg-white/10 transition active:scale-95">
                    Order History
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce {
    0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
    50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
</style>