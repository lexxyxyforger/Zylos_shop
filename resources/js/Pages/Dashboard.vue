<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, Link } from "@inertiajs/vue3";
import { reactive, ref, computed } from "vue";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ users: 0, stores: 0, products: 0 }),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    recentProducts: {
        type: Array,
        default: () => [],
    },
    storeProfile: {
        type: Object,
        default: () => ({
            name: "",
            about: "",
            phone: "",
            city: "",
            address: "",
            postal_code: "",
            logo: "",
        }),
    },
    brandName: {
        type: String,
        default: "ZYLOS",
    },
    brandLogo: {
        type: String,
        default: "",
    },
});

const statsState = reactive({ ...props.stats });
const productsState = ref([...props.recentProducts]);
const storeProfileState = reactive({ ...props.storeProfile });

const showProductModal = ref(false);
const showProfileModal = ref(false);
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const editingUuid = ref(null);

// Search & Pagination
const searchQuery = ref("");
const currentPage = ref(1);
const perPage = 10;

const filteredProducts = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    if (!q) return productsState.value;
    return productsState.value.filter(
        (p) =>
            (p.name || "").toLowerCase().includes(q) ||
            (p.category?.name || "").toLowerCase().includes(q) ||
            (p.condition || "").toLowerCase().includes(q),
    );
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredProducts.value.length / perPage)),
);

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredProducts.value.slice(start, start + perPage);
});

const pageNumbers = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const pages = [];
    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
    } else {
        pages.push(1);
        if (current > 3) pages.push("...");
        for (
            let i = Math.max(2, current - 1);
            i <= Math.min(total - 1, current + 1);
            i++
        )
            pages.push(i);
        if (current < total - 2) pages.push("...");
        pages.push(total);
    }
    return pages;
});

const onSearch = () => {
    currentPage.value = 1;
};

const toasts = ref([]);
let toastId = 1;

const productForm = reactive({
    name: "",
    about: "",
    condition: "new",
    price: "",
    weight: "",
    stock: "",
    product_category_id: props.categories[0]?.uuid ?? "",
    image_url: "",
    image_file: null,
    image_preview: "",
});

const profileForm = reactive({
    name: props.storeProfile.name || "",
    about: props.storeProfile.about || "",
    phone: props.storeProfile.phone || "",
    city: props.storeProfile.city || "",
    address: props.storeProfile.address || "",
    postal_code: props.storeProfile.postal_code || "",
    logo_url: props.storeProfile.logo || "",
    logo_file: null,
    logo_preview: "",
});

const productErrors = ref({});
const profileErrors = ref({});
const productSaving = ref(false);
const profileSaving = ref(false);

const formatIDR = (value) =>
    `Rp ${new Intl.NumberFormat("id-ID").format(Number(value || 0))}`;

const formatDate = (value) =>
    new Intl.DateTimeFormat("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));

const pushToast = (type, message) => {
    const id = toastId++;
    toasts.value.push({ id, type, message });
    window.setTimeout(() => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    }, 3200);
};

// Image preview handlers
const onProductImageFileChange = (event) => {
    const file = event.target.files[0] || null;
    productForm.image_file = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            productForm.image_preview = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        productForm.image_preview = "";
    }
};

const onProductImageUrlInput = () => {
    productForm.image_preview = "";
};

const computedProductPreview = computed(
    () => productForm.image_preview || productForm.image_url || "",
);

const onLogoFileChange = (event) => {
    const file = event.target.files[0] || null;
    profileForm.logo_file = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            profileForm.logo_preview = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        profileForm.logo_preview = "";
    }
};

const computedLogoPreview = computed(
    () => profileForm.logo_preview || profileForm.logo_url || "",
);

const resetProductForm = () => {
    productForm.name = "";
    productForm.about = "";
    productForm.condition = "new";
    productForm.price = "";
    productForm.weight = "";
    productForm.stock = "";
    productForm.product_category_id = props.categories[0]?.uuid ?? "";
    productForm.image_url = "";
    productForm.image_file = null;
    productForm.image_preview = "";
    productErrors.value = {};
    editingUuid.value = null;
};

const openCreateProductModal = () => {
    resetProductForm();
    showProductModal.value = true;
};

const openEditProductModal = (product) => {
    editingUuid.value = product.uuid;
    productForm.name = product.name || "";
    productForm.about = product.about || "";
    productForm.condition = product.condition || "new";
    productForm.price = product.price || "";
    productForm.weight = product.weight || "";
    productForm.stock = product.stock || "";
    productForm.product_category_id =
        product.product_category_id || props.categories[0]?.uuid || "";
    productForm.image_url = product.image || "";
    productForm.image_file = null;
    productForm.image_preview = "";
    productErrors.value = {};
    showProductModal.value = true;
};

const submitProduct = async () => {
    productSaving.value = true;
    productErrors.value = {};

    try {
        const payload = new FormData();
        payload.append("name", productForm.name);
        payload.append("about", productForm.about);
        payload.append("condition", productForm.condition);
        payload.append("price", productForm.price);
        payload.append("weight", productForm.weight);
        payload.append("stock", productForm.stock);
        payload.append("product_category_id", productForm.product_category_id);
        if (productForm.image_url) {
            payload.append("image_url", productForm.image_url);
        }
        if (productForm.image_file) {
            payload.append("image_file", productForm.image_file);
        }

        let response;
        if (editingUuid.value) {
            payload.append("_method", "PUT");
            response = await window.axios.post(
                route("dashboard.products.update", editingUuid.value),
                payload,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
            const index = productsState.value.findIndex(
                (item) => item.uuid === editingUuid.value,
            );
            if (index !== -1) {
                productsState.value[index] = response.data.product;
            }
        } else {
            response = await window.axios.post(
                route("dashboard.products.store"),
                payload,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
            productsState.value.unshift(response.data.product);
            if (productsState.value.length > 20) {
                productsState.value = productsState.value.slice(0, 20);
            }
        }

        Object.assign(statsState, response.data.stats || {});
        pushToast("success", response.data.message || "Aksi berhasil.");
        showProductModal.value = false;
        resetProductForm();
    } catch (error) {
        if (error?.response?.status === 422) {
            productErrors.value = error.response.data.errors || {};
            pushToast("error", "Validasi gagal, cek form produk.");
        } else {
            pushToast("error", "Terjadi error saat menyimpan produk.");
        }
    } finally {
        productSaving.value = false;
    }
};

// Custom delete modal
const confirmDeleteProduct = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const cancelDelete = () => {
    productToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteProduct = async () => {
    const product = productToDelete.value;
    if (!product) return;
    showDeleteModal.value = false;

    try {
        const response = await window.axios.delete(
            route("dashboard.products.destroy", product.uuid),
        );
        productsState.value = productsState.value.filter(
            (item) => item.uuid !== product.uuid,
        );
        Object.assign(statsState, response.data.stats || {});
        pushToast("success", response.data.message || "Produk dihapus.");
    } catch {
        pushToast("error", "Gagal menghapus produk.");
    } finally {
        productToDelete.value = null;
    }
};

const openProfileModal = () => {
    profileForm.name = storeProfileState.name || "";
    profileForm.about = storeProfileState.about || "";
    profileForm.phone = storeProfileState.phone || "";
    profileForm.city = storeProfileState.city || "";
    profileForm.address = storeProfileState.address || "";
    profileForm.postal_code = storeProfileState.postal_code || "";
    profileForm.logo_url = storeProfileState.logo || "";
    profileForm.logo_file = null;
    profileForm.logo_preview = "";
    profileErrors.value = {};
    showProfileModal.value = true;
};

const submitProfile = async () => {
    profileSaving.value = true;
    profileErrors.value = {};

    try {
        const payload = new FormData();
        payload.append("name", profileForm.name);
        payload.append("about", profileForm.about || "");
        payload.append("phone", profileForm.phone || "");
        payload.append("city", profileForm.city || "");
        payload.append("address", profileForm.address || "");
        payload.append("postal_code", profileForm.postal_code || "");
        if (profileForm.logo_url) {
            payload.append("logo_url", profileForm.logo_url);
        }
        if (profileForm.logo_file) {
            payload.append("logo_file", profileForm.logo_file);
        }

        const response = await window.axios.post(
            route("dashboard.store-profile.update"),
            payload,
            { headers: { "Content-Type": "multipart/form-data" } },
        );

        Object.assign(storeProfileState, response.data.storeProfile || {});
        showProfileModal.value = false;
        pushToast(
            "success",
            response.data.message || "Profil store berhasil diperbarui.",
        );
    } catch (error) {
        if (error?.response?.status === 422) {
            profileErrors.value = error.response.data.errors || {};
            pushToast("error", "Validasi profil gagal.");
        } else {
            pushToast("error", "Gagal memperbarui profil.");
        }
    } finally {
        profileSaving.value = false;
    }
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <!-- Brand slot — ganti logo Laravel dengan nama merek kamu -->
        <template #logo>
            <div class="flex items-center gap-2">
                <img
                    v-if="brandLogo"
                    :src="brandLogo"
                    alt="Brand logo"
                    class="h-8 w-auto"
                />
                <span
                    v-else
                    class="text-lg font-black tracking-tight text-slate-900"
                >
                    {{ brandName }}
                </span>
            </div>
        </template>

        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500"
                    >
                        Admin Workspace
                    </p>
                    <h2
                        class="text-2xl font-black leading-tight text-slate-900"
                    >
                        Dashboard Admin
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="openProfileModal"
                        class="inline-flex rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow"
                    >
                        Profile Store
                    </button>
                    <button
                        type="button"
                        @click="openCreateProductModal"
                        class="inline-flex rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >
                        Tambah Produk
                    </button>
                    <Link
                        :href="route('store.index')"
                        class="inline-flex rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow"
                    >
                        Buka Store
                    </Link>
                </div>
            </div>
        </template>

        <div
            class="bg-[radial-gradient(circle_at_10%_0%,rgba(14,165,233,0.12),transparent_26%),radial-gradient(circle_at_92%_0%,rgba(234,179,8,0.12),transparent_30%)] py-10"
        >
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats -->
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500"
                        >
                            Total User
                        </p>
                        <p class="mt-3 text-3xl font-black text-slate-900">
                            {{
                                Number(statsState.users || 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500"
                        >
                            Total Store
                        </p>
                        <p class="mt-3 text-3xl font-black text-slate-900">
                            {{
                                Number(statsState.stores || 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500"
                        >
                            Total Produk
                        </p>
                        <p class="mt-3 text-3xl font-black text-slate-900">
                            {{
                                Number(statsState.products || 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-5 text-white shadow-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-300"
                        >
                            Store Profile
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <img
                                :src="
                                    storeProfileState.logo ||
                                    'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'
                                "
                                alt="Store logo"
                                class="h-11 w-11 rounded-xl object-cover ring-1 ring-white/25"
                            />
                            <div>
                                <p class="text-sm font-bold">
                                    {{
                                        storeProfileState.name || brandName
                                    }}
                                </p>
                                <p class="text-xs text-slate-300">
                                    {{ storeProfileState.city || "Indonesia" }}
                                </p>
                            </div>
                        </div>
                    </article>
                </section>

                <!-- Product Table -->
                <section
                    class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4"
                    >
                        <h3 class="text-base font-bold text-slate-900">
                            Produk Dashboard
                            <span class="ml-1 text-sm font-normal text-slate-400"
                                >({{ filteredProducts.length }})</span
                            >
                        </h3>
                        <div class="flex items-center gap-2">
                            <!-- Realtime Search -->
                            <div class="relative">
                                <svg
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"
                                    />
                                </svg>
                                <input
                                    v-model="searchQuery"
                                    @input="onSearch"
                                    type="text"
                                    placeholder="Cari produk..."
                                    class="rounded-full border border-slate-200 py-1.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-0 w-48"
                                />
                            </div>
                            <button
                                type="button"
                                @click="openCreateProductModal"
                                class="rounded-full bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-800"
                            >
                                + Produk
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="filteredProducts.length === 0"
                        class="px-6 py-10 text-sm text-slate-500"
                    >
                        {{
                            searchQuery
                                ? `Tidak ada produk dengan kata kunci "${searchQuery}".`
                                : "Belum ada produk. Klik tombol + Produk untuk menambahkan."
                        }}
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-slate-200 text-sm"
                        >
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Produk
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Kategori
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Kondisi
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Stok
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Harga
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-slate-600"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="product in paginatedProducts"
                                    :key="product.uuid"
                                    class="hover:bg-slate-50/70"
                                >
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <img
                                                :src="
                                                    product.image ||
                                                    'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'
                                                "
                                                alt="Product image"
                                                class="h-12 w-12 rounded-xl object-cover"
                                            />
                                            <div class="min-w-0">
                                                <p
                                                    class="line-clamp-1 font-semibold text-slate-800"
                                                >
                                                    {{ product.name }}
                                                </p>
                                                <p
                                                    class="line-clamp-1 text-xs text-slate-500"
                                                >
                                                    {{ product.slug }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-slate-700">
                                        {{ product.category?.name || "-" }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="
                                                (
                                                    product.condition || ''
                                                ).toLowerCase() === 'used'
                                                    ? 'bg-amber-100 text-amber-700'
                                                    : 'bg-emerald-100 text-emerald-700'
                                            "
                                        >
                                            {{
                                                (
                                                    product.condition || ""
                                                ).toLowerCase() === "used"
                                                    ? "Bekas"
                                                    : "Baru"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-700">
                                        {{
                                            Number(
                                                product.stock || 0,
                                            ).toLocaleString("id-ID")
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-3 font-semibold text-slate-800"
                                    >
                                        {{ formatIDR(product.price) }}
                                    </td>
                                    <td class="px-6 py-3 text-slate-600">
                                        {{ formatDate(product.created_at) }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="
                                                    openEditProductModal(product)
                                                "
                                                class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                @click="
                                                    confirmDeleteProduct(product)
                                                "
                                                class="rounded-full border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="totalPages > 1"
                        class="flex items-center justify-between border-t border-slate-100 px-6 py-3"
                    >
                        <p class="text-xs text-slate-500">
                            Halaman {{ currentPage }} dari {{ totalPages }}
                            &bull;
                            {{ filteredProducts.length }} produk
                        </p>
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                :disabled="currentPage === 1"
                                @click="currentPage--"
                                class="rounded-full border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 disabled:opacity-40 hover:bg-slate-50"
                            >
                                ‹
                            </button>
                            <template
                                v-for="(page, i) in pageNumbers"
                                :key="i"
                            >
                                <span
                                    v-if="page === '...'"
                                    class="px-1 text-xs text-slate-400"
                                    >…</span
                                >
                                <button
                                    v-else
                                    type="button"
                                    @click="currentPage = page"
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold transition"
                                    :class="
                                        currentPage === page
                                            ? 'bg-slate-900 text-white'
                                            : 'border border-slate-200 text-slate-600 hover:bg-slate-50'
                                    "
                                >
                                    {{ page }}
                                </button>
                            </template>
                            <button
                                type="button"
                                :disabled="currentPage === totalPages"
                                @click="currentPage++"
                                class="rounded-full border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 disabled:opacity-40 hover:bg-slate-50"
                            >
                                ›
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Product Modal -->
        <div
            v-if="showProductModal"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 flex items-start justify-center"
        >
            <div
                class="w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl my-auto"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-900">
                        {{ editingUuid ? "Edit Produk" : "Tambah Produk Baru" }}
                    </h3>
                    <button
                        type="button"
                        @click="showProductModal = false"
                        class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700"
                    >
                        Tutup
                    </button>
                </div>

                <form
                    class="grid gap-4 md:grid-cols-2"
                    @submit.prevent="submitProduct"
                >
                    <label class="space-y-1 md:col-span-2">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Nama Produk</span
                        >
                        <input
                            v-model="productForm.name"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        />
                        <InputError :message="productErrors.name?.[0]" />
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Deskripsi</span
                        >
                        <textarea
                            v-model="productForm.about"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        ></textarea>
                        <InputError :message="productErrors.about?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Kategori</span
                        >
                        <select
                            v-model="productForm.product_category_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        >
                            <option
                                v-for="category in categories"
                                :key="category.uuid"
                                :value="category.uuid"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError
                            :message="productErrors.product_category_id?.[0]"
                        />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Kondisi</span
                        >
                        <select
                            v-model="productForm.condition"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        >
                            <option value="new">Baru</option>
                            <option value="used">Bekas</option>
                        </select>
                        <InputError :message="productErrors.condition?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Harga</span
                        >
                        <input
                            v-model="productForm.price"
                            type="number"
                            min="0"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        />
                        <InputError :message="productErrors.price?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Berat (gram)</span
                        >
                        <input
                            v-model="productForm.weight"
                            type="number"
                            min="1"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        />
                        <InputError :message="productErrors.weight?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Stok</span
                        >
                        <input
                            v-model="productForm.stock"
                            type="number"
                            min="0"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        />
                        <InputError :message="productErrors.stock?.[0]" />
                    </label>

                    <!-- Image Input & Preview -->
                    <div class="space-y-1 md:col-span-2">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                        >
                            Gambar Produk
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="space-y-1">
                                <span class="text-xs text-slate-500"
                                    >URL Gambar</span
                                >
                                <input
                                    v-model="productForm.image_url"
                                    @input="onProductImageUrlInput"
                                    type="url"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                    placeholder="https://..."
                                />
                                <InputError
                                    :message="productErrors.image_url?.[0]"
                                />
                            </label>
                            <label class="space-y-1">
                                <span class="text-xs text-slate-500"
                                    >Upload File</span
                                >
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                    @change="onProductImageFileChange"
                                />
                                <InputError
                                    :message="productErrors.image_file?.[0]"
                                />
                            </label>
                        </div>
                        <!-- Preview -->
                        <div
                            v-if="computedProductPreview"
                            class="mt-2 flex items-center gap-3"
                        >
                            <img
                                :src="computedProductPreview"
                                alt="Preview gambar produk"
                                class="h-20 w-20 rounded-xl border border-slate-200 object-cover"
                            />
                            <p class="text-xs text-slate-500">Preview gambar</p>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showProductModal = false"
                            class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="productSaving"
                            class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {{
                                productSaving
                                    ? "Menyimpan..."
                                    : editingUuid
                                      ? "Update Produk"
                                      : "Simpan Produk"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Modal -->
        <div
            v-if="showProfileModal"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 flex items-start justify-center"
        >
            <div
                class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl my-auto"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-900">
                        Profile Store
                    </h3>
                    <button
                        type="button"
                        @click="showProfileModal = false"
                        class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700"
                    >
                        Tutup
                    </button>
                </div>

                <form
                    class="grid gap-4 md:grid-cols-2"
                    @submit.prevent="submitProfile"
                >
                    <label class="space-y-1 md:col-span-2">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Nama Store</span
                        >
                        <input
                            v-model="profileForm.name"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                            required
                        />
                        <InputError :message="profileErrors.name?.[0]" />
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >About</span
                        >
                        <textarea
                            v-model="profileForm.about"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                        ></textarea>
                        <InputError :message="profileErrors.about?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Phone</span
                        >
                        <input
                            v-model="profileForm.phone"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                        />
                        <InputError :message="profileErrors.phone?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >City</span
                        >
                        <input
                            v-model="profileForm.city"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                        />
                        <InputError :message="profileErrors.city?.[0]" />
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Address</span
                        >
                        <textarea
                            v-model="profileForm.address"
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                        ></textarea>
                        <InputError :message="profileErrors.address?.[0]" />
                    </label>

                    <label class="space-y-1">
                        <span
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >Postal Code</span
                        >
                        <input
                            v-model="profileForm.postal_code"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                        />
                        <InputError
                            :message="profileErrors.postal_code?.[0]"
                        />
                    </label>

                    <!-- Logo Input & Preview -->
                    <div class="space-y-1 md:col-span-2">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                        >
                            Logo Store
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="space-y-1">
                                <span class="text-xs text-slate-500"
                                    >Logo URL</span
                                >
                                <input
                                    v-model="profileForm.logo_url"
                                    type="url"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                    placeholder="https://..."
                                />
                                <InputError
                                    :message="profileErrors.logo_url?.[0]"
                                />
                            </label>
                            <label class="space-y-1">
                                <span class="text-xs text-slate-500"
                                    >Upload Logo File</span
                                >
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"
                                    @change="onLogoFileChange"
                                />
                                <InputError
                                    :message="profileErrors.logo_file?.[0]"
                                />
                            </label>
                        </div>
                        <!-- Logo Preview -->
                        <div
                            v-if="computedLogoPreview"
                            class="mt-2 flex items-center gap-3"
                        >
                            <img
                                :src="computedLogoPreview"
                                alt="Preview logo"
                                class="h-16 w-16 rounded-xl border border-slate-200 object-cover"
                            />
                            <p class="text-xs text-slate-500">Preview logo</p>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showProfileModal = false"
                            class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="profileSaving"
                            class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {{
                                profileSaving ? "Menyimpan..." : "Simpan Profile"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Custom Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 flex items-start justify-center"
        >
            <div
                class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl my-auto"
            >
                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100"
                >
                    <svg
                        class="h-6 w-6 text-rose-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-8 0h10"
                        />
                    </svg>
                </div>
                <h3 class="text-base font-black text-slate-900">
                    Hapus Produk
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Yakin ingin menghapus
                    <span class="font-semibold text-slate-800">{{
                        productToDelete?.name
                    }}</span
                    >? Aksi ini tidak dapat dibatalkan.
                </p>
                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="cancelDelete"
                        class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="deleteProduct"
                        class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div
            class="pointer-events-none fixed right-4 top-20 z-[60] flex w-full max-w-sm flex-col gap-2"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto rounded-xl border px-4 py-3 text-sm shadow-lg backdrop-blur"
                :class="
                    toast.type === 'success'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-rose-200 bg-rose-50 text-rose-700'
                "
            >
                {{ toast.message }}
            </div>
        </div>
    </AuthenticatedLayout>
</template>