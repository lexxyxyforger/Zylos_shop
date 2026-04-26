<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    store: { type: Object, required: true },
    orders: { type: Array, default: () => [] },
    urls: { type: Object, required: true },
});

const searchQuery = ref("");
const statusFilter = ref("all");

const formatIDR = (price) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price || 0);

const filteredOrders = computed(() => {
    return props.orders.filter((order) => {
        const matchesSearch = order.id.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === "all" || order.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

const getStatusTheme = (status) => {
    const themes = {
        paid: {
            label: "Success",
            bg: "bg-lime-400/20 text-lime-400 border-lime-400/30",
            icon: "M5 13l4 4L19 7"
        },
        cancelled: {
            label: "Failed",
            bg: "bg-rose-500/20 text-rose-400 border-rose-500/30",
            icon: "M6 18L18 6M6 6l12 12"
        },
        pending: {
            label: "Pending",
            bg: "bg-cyan-400/20 text-cyan-400 border-cyan-400/30",
            icon: "M12 8v4l3 3"
        }
    };
    return themes[status] || themes.pending;
};
</script>

<template>
    <Head title="Orders Vault | ZYLOS" />

    <div class="min-h-screen bg-[#050B10] text-white selection:bg-lime-400 selection:text-black font-['DM_Sans']">
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-cyan-500/10 rounded-full blur-[120px]"></div>
            <div class="absolute top-[60%] -right-[5%] w-[30%] h-[50%] bg-lime-400/5 rounded-full blur-[100px]"></div>
            <div class="absolute inset-0 opacity-20 brightness-50 contrast-150" style="background-image: url('https://grainy-gradients.vercel.app/noise.svg')"></div>
        </div>

        <main class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <header class="mb-12">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="h-px w-8 bg-cyan-400"></span>
                            <p class="text-[10px] font-black tracking-[0.5em] text-cyan-400 uppercase">Archive Protocol</p>
                        </div>
                        <h1 class="text-6xl font-black font-['Syne'] tracking-tighter leading-none uppercase">
                            ORDERS <span class="text-lime-400 italic">VAULT</span>
                        </h1>
                        <p class="mt-4 text-white/50 text-sm max-w-md font-medium leading-relaxed">
                            Accessing all deployment history records. Your verified purchases are securely stored in the ZYLOS encrypted archive.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="urls.home" class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl hover:bg-white/10 transition backdrop-blur-xl group">
                            <span class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Store
                            </span>
                        </Link>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 mb-8">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-white/20 group-focus-within:text-cyan-400 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search Deployment ID..."
                        class="w-full bg-white/[0.03] border border-white/10 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold focus:border-cyan-400 focus:ring-0 transition backdrop-blur-xl"
                    />
                </div>
                <div class="flex gap-2 p-1 bg-white/[0.03] border border-white/10 rounded-2xl backdrop-blur-xl">
                    <button
                        v-for="status in ['all', 'paid', 'pending', 'cancelled']"
                        :key="status"
                        @click="statusFilter = status"
                        class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition"
                        :class="statusFilter === status ? 'bg-white text-black shadow-lg shadow-white/10' : 'text-white/40 hover:text-white'"
                    >
                        {{ status }}
                    </button>
                </div>
            </div>

            <div class="bg-white/[0.02] border border-white/10 rounded-[2.5rem] overflow-hidden backdrop-blur-3xl shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/[0.02]">
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-white/30">Order ID</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-white/30">Deployment Date</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-white/30">Items Brief</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-white/30">Status</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-white/30 text-right">Investment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="order in filteredOrders" :key="order.id" class="group hover:bg-white/[0.03] transition duration-300">
                                <td class="px-8 py-6">
                                    <span class="text-sm font-black font-mono group-hover:text-cyan-400 transition">{{ order.id }}</span>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-white/50">
                                    {{ order.created_at || 'ARCHIVED' }}
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex -space-x-3">
                                        <div v-for="item in order.items" :key="item.name" class="relative group/img">
                                            <img
                                                :src="item.image || 'https://placehold.co/80x80/e2e8f0/334155?text='"
                                                class="w-10 h-10 rounded-full border-2 border-[#050B10] object-cover bg-zinc-900 grayscale group-hover/img:grayscale-0 transition duration-500"
                                            />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-[9px] font-black uppercase tracking-widest shadow-sm"
                                        :class="getStatusTheme(order.status).bg"
                                    >
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getStatusTheme(order.status).icon" />
                                        </svg>
                                        {{ getStatusTheme(order.status).label }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <p class="text-sm font-black text-lime-400">{{ formatIDR(order.total) }}</p>
                                </td>
                            </tr>

                            <tr v-if="!filteredOrders.length">
                                <td colspan="5" class="px-8 py-32 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-6 border border-white/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-black italic uppercase tracking-widest text-white/20">Archive Empty</h3>
                                        <p class="text-white/40 text-xs mt-2 uppercase tracking-widest">No matching deployment logs found.</p>
                                        <Link :href="urls.home" class="mt-8 px-8 py-3 bg-cyan-400 text-black font-black rounded-full text-[10px] uppercase tracking-[0.2em] hover:bg-lime-300 transition active:scale-95">
                                            Start Deployment
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="mt-12 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-white/10 pt-8">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-lime-400 rounded-full animate-pulse"></div>
                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-white/30">Archive Status: Online & Encrypted</p>
                </div>
                <div class="flex gap-8">
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-white/30 mb-1">Total Assets Deployments</p>
                        <p class="text-lg font-black font-mono">{{ orders.length }}</p>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>

<style scoped>
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(34, 211, 238, 0.5);
}
</style>