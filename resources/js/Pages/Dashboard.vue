<script setup>
import { reactive, ref, computed } from "vue";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ users: 0, stores: 0, products: 0 }),
    },
    categories: { type: Array, default: () => [] },
    recentProducts: { type: Array, default: () => [] },
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
    brandName: { type: String, default: "ZYLOS" },
    brandLogo: { type: String, default: "" },
});

const statsState = reactive({ ...props.stats });
const productsState = ref([...props.recentProducts]);
const storeProfileState = reactive({ ...props.storeProfile });

const showProductModal = ref(false);
const showProfileModal = ref(false);
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const editingUuid = ref(null);

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
    new Intl.DateTimeFormat("id-ID", { dateStyle: "medium" }).format(
        new Date(value),
    );

const pushToast = (type, message) => {
    const id = toastId++;
    toasts.value.push({ id, type, message });
    window.setTimeout(() => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }, 3200);
};

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
    window.location.href = route("dashboard.products.create");
};

const openEditProductModal = (product) => {
    window.location.href = route("dashboard.products.edit", product.uuid);
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
        if (productForm.image_url)
            payload.append("image_url", productForm.image_url);
        if (productForm.image_file)
            payload.append("image_file", productForm.image_file);

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
            if (index !== -1)
                productsState.value[index] = response.data.product;
        } else {
            response = await window.axios.post(
                route("dashboard.products.store"),
                payload,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
            productsState.value.unshift(response.data.product);
            if (productsState.value.length > 20)
                productsState.value = productsState.value.slice(0, 20);
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
    window.location.href = route("profile.edit");
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
        if (profileForm.logo_url)
            payload.append("logo_url", profileForm.logo_url);
        if (profileForm.logo_file)
            payload.append("logo_file", profileForm.logo_file);

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
    <div class="dash-root">
        <div class="dash-glow" aria-hidden="true" />

        <Transition name="toast">
            <div v-if="toasts.length" class="toast-stack">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="toast-item"
                    :class="
                        toast.type === 'success' ? 'toast--ok' : 'toast--err'
                    "
                >
                    <span class="toast-dot" />
                    {{ toast.message }}
                </div>
            </div>
        </Transition>

        <header class="dash-header">
            <div class="dash-header__brand">
                <img
                    v-if="brandLogo"
                    :src="brandLogo"
                    alt="logo"
                    class="brand-img"
                />
                <span v-else class="brand-text">{{ brandName }}</span>
                <span class="brand-pill">Admin</span>
            </div>
            <div class="dash-header__center">
                <span class="header-label">Control Panel</span>
            </div>
            <div class="dash-header__actions">
                <button type="button" class="hdr-btn" @click="openProfileModal">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        />
                    </svg>
                    Store
                </button>
            </div>
        </header>

        <main class="dash-main">
            <section class="stat-row">
                <article class="stat-card stat-card--a">
                    <div class="stat-card__icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Total User</p>
                        <p class="stat-card__value">
                            {{
                                Number(statsState.users || 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                        </p>
                    </div>
                    <div class="stat-card__bar" style="--pct: 72%" />
                </article>

                <article class="stat-card stat-card--b">
                    <div class="stat-card__icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Total Store</p>
                        <p class="stat-card__value">
                            {{
                                Number(statsState.stores || 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                        </p>
                    </div>
                    <div class="stat-card__bar" style="--pct: 48%" />
                </article>

                <article class="stat-card stat-card--c">
                    <div class="stat-card__icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Total Produk</p>
                        <p class="stat-card__value">
                            {{
                                Number(statsState.products || 0).toLocaleString(
                                    "id-ID",
                                )
                            }}
                        </p>
                    </div>
                    <div class="stat-card__bar" style="--pct: 91%" />
                </article>

                <article
                    class="stat-card stat-card--d"
                    @click="openProfileModal"
                    style="cursor: pointer"
                >
                    <img
                        :src="
                            storeProfileState.logo ||
                            'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'
                        "
                        alt="store logo"
                        class="store-logo"
                    />
                    <div class="store-meta">
                        <p class="stat-card__label">Store Aktif</p>
                        <p class="store-name">
                            {{ storeProfileState.name || brandName }}
                        </p>
                        <p class="store-city">
                            {{ storeProfileState.city || "Indonesia" }}
                        </p>
                    </div>
                    <span class="store-edit-tag">Edit →</span>
                </article>
            </section>

            <section class="table-section">
                <div class="table-head">
                    <div class="table-head__left">
                        <h2 class="table-title">Manajemen Produk</h2>
                        <span class="table-count"
                            >{{ filteredProducts.length }} item</span
                        >
                    </div>
                    <div class="table-head__right">
                        <div class="search-wrap">
                            <svg
                                class="search-ico"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
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
                                class="search-input"
                            />
                        </div>
                        <button
                            type="button"
                            class="add-btn"
                            @click="openCreateProductModal"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>
                            Tambah
                        </button>
                    </div>
                </div>

                <div v-if="filteredProducts.length === 0" class="table-empty">
                    <div class="empty-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"
                            />
                        </svg>
                    </div>
                    <p class="empty-text">
                        {{
                            searchQuery
                                ? `Tidak ada hasil untuk "${searchQuery}"`
                                : "Belum ada produk. Mulai tambahkan sekarang."
                        }}
                    </p>
                </div>

                <div v-else class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Kondisi</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in paginatedProducts"
                                :key="product.uuid"
                                class="data-row"
                            >
                                <td>
                                    <div class="prod-cell">
                                        <img
                                            :src="
                                                product.image ||
                                                'https://img.sanishtech.com/u/7bff45bea5098b102ff2d2be40ee0b4d.png'
                                            "
                                            alt="prod"
                                            class="prod-img"
                                        />
                                        <div>
                                            <p class="prod-name">
                                                {{ product.name }}
                                            </p>
                                            <p class="prod-slug">
                                                {{ product.slug }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="cat-tag">{{
                                        product.category?.name || "—"
                                    }}</span>
                                </td>
                                <td>
                                    <span
                                        class="cond-pill"
                                        :class="
                                            (
                                                product.condition || ''
                                            ).toLowerCase() === 'used'
                                                ? 'cond-used'
                                                : 'cond-new'
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
                                <td class="num-cell">
                                    {{
                                        Number(
                                            product.stock || 0,
                                        ).toLocaleString("id-ID")
                                    }}
                                </td>
                                <td class="price-cell">
                                    {{ formatIDR(product.price) }}
                                </td>
                                <td class="date-cell">
                                    {{ formatDate(product.created_at) }}
                                </td>
                                <td>
                                    <div class="act-wrap">
                                        <button
                                            type="button"
                                            class="act-edit"
                                            @click="
                                                openEditProductModal(product)
                                            "
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="act-del"
                                            @click="
                                                confirmDeleteProduct(product)
                                            "
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="totalPages > 1" class="pagination">
                    <span class="page-info"
                        >Hal. {{ currentPage }} / {{ totalPages }}</span
                    >
                    <div class="page-btns">
                        <button
                            type="button"
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                            class="page-btn"
                        >
                            &#8249;
                        </button>
                        <template v-for="(page, i) in pageNumbers" :key="i">
                            <span v-if="page === '...'" class="page-dots"
                                >&#8230;</span
                            >
                            <button
                                v-else
                                type="button"
                                @click="currentPage = page"
                                class="page-btn"
                                :class="
                                    currentPage === page
                                        ? 'page-btn--active'
                                        : ''
                                "
                            >
                                {{ page }}
                            </button>
                        </template>
                        <button
                            type="button"
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                            class="page-btn"
                        >
                            &#8250;
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showProductModal"
                    class="overlay"
                    @click.self="showProductModal = false"
                >
                    <div class="modal">
                        <div class="modal__head">
                            <div>
                                <p class="modal__eyebrow">
                                    {{ editingUuid ? "Edit" : "Tambah" }}
                                </p>
                                <h3 class="modal__title">
                                    {{
                                        editingUuid
                                            ? "Edit Produk"
                                            : "Produk Baru"
                                    }}
                                </h3>
                            </div>
                            <button
                                type="button"
                                class="modal__close"
                                @click="showProductModal = false"
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

                        <form
                            class="modal__body"
                            @submit.prevent="submitProduct"
                        >
                            <label class="field field--full">
                                <span class="field__label">Nama Produk</span>
                                <input
                                    v-model="productForm.name"
                                    type="text"
                                    class="field__input"
                                    required
                                />
                                <span
                                    v-if="productErrors.name?.[0]"
                                    class="field__err"
                                    >{{ productErrors.name[0] }}</span
                                >
                            </label>

                            <label class="field field--full">
                                <span class="field__label">Deskripsi</span>
                                <textarea
                                    v-model="productForm.about"
                                    rows="3"
                                    class="field__input field__textarea"
                                    required
                                ></textarea>
                                <span
                                    v-if="productErrors.about?.[0]"
                                    class="field__err"
                                    >{{ productErrors.about[0] }}</span
                                >
                            </label>

                            <label class="field">
                                <span class="field__label">Kategori</span>
                                <select
                                    v-model="productForm.product_category_id"
                                    class="field__input field__select"
                                    required
                                >
                                    <option
                                        v-for="cat in categories"
                                        :key="cat.uuid"
                                        :value="cat.uuid"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <span
                                    v-if="
                                        productErrors.product_category_id?.[0]
                                    "
                                    class="field__err"
                                    >{{
                                        productErrors.product_category_id[0]
                                    }}</span
                                >
                            </label>

                            <label class="field">
                                <span class="field__label">Kondisi</span>
                                <select
                                    v-model="productForm.condition"
                                    class="field__input field__select"
                                    required
                                >
                                    <option value="new">Baru</option>
                                    <option value="used">Bekas</option>
                                </select>
                                <span
                                    v-if="productErrors.condition?.[0]"
                                    class="field__err"
                                    >{{ productErrors.condition[0] }}</span
                                >
                            </label>

                            <label class="field">
                                <span class="field__label">Harga (Rp)</span>
                                <input
                                    v-model="productForm.price"
                                    type="number"
                                    min="0"
                                    class="field__input"
                                    required
                                />
                                <span
                                    v-if="productErrors.price?.[0]"
                                    class="field__err"
                                    >{{ productErrors.price[0] }}</span
                                >
                            </label>

                            <label class="field">
                                <span class="field__label">Berat (gram)</span>
                                <input
                                    v-model="productForm.weight"
                                    type="number"
                                    min="1"
                                    class="field__input"
                                    required
                                />
                                <span
                                    v-if="productErrors.weight?.[0]"
                                    class="field__err"
                                    >{{ productErrors.weight[0] }}</span
                                >
                            </label>

                            <label class="field">
                                <span class="field__label">Stok</span>
                                <input
                                    v-model="productForm.stock"
                                    type="number"
                                    min="0"
                                    class="field__input"
                                    required
                                />
                                <span
                                    v-if="productErrors.stock?.[0]"
                                    class="field__err"
                                    >{{ productErrors.stock[0] }}</span
                                >
                            </label>

                            <div class="field field--full">
                                <p class="field__label">Gambar Produk</p>
                                <div class="img-fields">
                                    <label class="field">
                                        <span
                                            class="field__label field__label--sm"
                                            >URL Gambar</span
                                        >
                                        <input
                                            v-model="productForm.image_url"
                                            @input="onProductImageUrlInput"
                                            type="url"
                                            class="field__input"
                                            placeholder="https://..."
                                        />
                                    </label>
                                    <label class="field">
                                        <span
                                            class="field__label field__label--sm"
                                            >Upload File</span
                                        >
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="field__input field__file"
                                            @change="onProductImageFileChange"
                                        />
                                    </label>
                                </div>
                                <div
                                    v-if="computedProductPreview"
                                    class="img-preview"
                                >
                                    <img
                                        :src="computedProductPreview"
                                        alt="preview"
                                    />
                                    <span>Preview</span>
                                </div>
                            </div>

                            <div class="modal__foot">
                                <button
                                    type="button"
                                    class="foot-btn foot-btn--ghost"
                                    @click="showProductModal = false"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="productSaving"
                                    class="foot-btn foot-btn--primary"
                                >
                                    {{
                                        productSaving
                                            ? "Menyimpan..."
                                            : editingUuid
                                              ? "Update"
                                              : "Simpan"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>

            <Transition name="modal">
                <div
                    v-if="showProfileModal"
                    class="overlay"
                    @click.self="showProfileModal = false"
                >
                    <div class="modal">
                        <div class="modal__head">
                            <div>
                                <p class="modal__eyebrow">Pengaturan</p>
                                <h3 class="modal__title">Profile Store</h3>
                            </div>
                            <button
                                type="button"
                                class="modal__close"
                                @click="showProfileModal = false"
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

                        <form
                            class="modal__body"
                            @submit.prevent="submitProfile"
                        >
                            <label class="field field--full">
                                <span class="field__label">Nama Store</span>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    class="field__input"
                                    required
                                />
                                <span
                                    v-if="profileErrors.name?.[0]"
                                    class="field__err"
                                    >{{ profileErrors.name[0] }}</span
                                >
                            </label>

                            <label class="field field--full">
                                <span class="field__label">About</span>
                                <textarea
                                    v-model="profileForm.about"
                                    rows="3"
                                    class="field__input field__textarea"
                                ></textarea>
                            </label>

                            <label class="field">
                                <span class="field__label">Telepon</span>
                                <input
                                    v-model="profileForm.phone"
                                    type="text"
                                    class="field__input"
                                />
                            </label>

                            <label class="field">
                                <span class="field__label">Kota</span>
                                <input
                                    v-model="profileForm.city"
                                    type="text"
                                    class="field__input"
                                />
                            </label>

                            <label class="field field--full">
                                <span class="field__label">Alamat</span>
                                <textarea
                                    v-model="profileForm.address"
                                    rows="2"
                                    class="field__input field__textarea"
                                ></textarea>
                            </label>

                            <label class="field">
                                <span class="field__label">Kode Pos</span>
                                <input
                                    v-model="profileForm.postal_code"
                                    type="text"
                                    class="field__input"
                                />
                            </label>

                            <div class="field field--full">
                                <p class="field__label">Logo Store</p>
                                <div class="img-fields">
                                    <label class="field">
                                        <span
                                            class="field__label field__label--sm"
                                            >Logo URL</span
                                        >
                                        <input
                                            v-model="profileForm.logo_url"
                                            type="url"
                                            class="field__input"
                                            placeholder="https://..."
                                        />
                                    </label>
                                    <label class="field">
                                        <span
                                            class="field__label field__label--sm"
                                            >Upload File</span
                                        >
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="field__input field__file"
                                            @change="onLogoFileChange"
                                        />
                                    </label>
                                </div>
                                <div
                                    v-if="computedLogoPreview"
                                    class="img-preview"
                                >
                                    <img
                                        :src="computedLogoPreview"
                                        alt="logo preview"
                                    />
                                    <span>Preview</span>
                                </div>
                            </div>

                            <div class="modal__foot">
                                <button
                                    type="button"
                                    class="foot-btn foot-btn--ghost"
                                    @click="showProfileModal = false"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="profileSaving"
                                    class="foot-btn foot-btn--primary"
                                >
                                    {{
                                        profileSaving
                                            ? "Menyimpan..."
                                            : "Simpan Profile"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>

            <Transition name="modal">
                <div
                    v-if="showDeleteModal"
                    class="overlay"
                    @click.self="cancelDelete"
                >
                    <div class="modal modal--sm">
                        <div class="del-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                                />
                            </svg>
                        </div>
                        <h3 class="del-title">Hapus Produk?</h3>
                        <p class="del-desc">
                            Produk
                            <strong>{{ productToDelete?.name }}</strong> akan
                            dihapus permanen dan tidak bisa dikembalikan.
                        </p>
                        <div class="modal__foot modal__foot--center">
                            <button
                                type="button"
                                class="foot-btn foot-btn--ghost"
                                @click="cancelDelete"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                class="foot-btn foot-btn--danger"
                                @click="deleteProduct"
                            >
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,500;0,9..40,700;1,9..40,400&display=swap");

.dash-root {
    --navy: #080d18;
    --navy-2: #0d1424;
    --navy-3: #111c2e;
    --border: rgba(255, 255, 255, 0.08);
    --border-hi: rgba(255, 255, 255, 0.16);
    --cyan: #22d3ee;
    --lime: #bef264;
    --amber: #fbbf24;
    --rose: #fb7185;
    --white: #f0f6ff;
    --muted: rgba(240, 246, 255, 0.45);
    --glass: rgba(255, 255, 255, 0.04);
    --glass-hi: rgba(255, 255, 255, 0.08);
    --r-xs: 8px;
    --r-sm: 12px;
    --r-md: 18px;
    --r-lg: 24px;
    --r-xl: 32px;
    --r-pill: 9999px;
    --font-d: "Syne", sans-serif;
    --font-b: "DM Sans", sans-serif;

    position: relative;
    min-height: 100vh;
    background: var(--navy);
    color: var(--white);
    font-family: var(--font-b);
    font-size: 14px;
    line-height: 1.6;
    overflow-x: hidden;
}

.dash-glow {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background:
        radial-gradient(
            ellipse 70% 45% at 0% 0%,
            rgba(34, 211, 238, 0.13) 0%,
            transparent 55%
        ),
        radial-gradient(
            ellipse 50% 40% at 100% 100%,
            rgba(190, 242, 100, 0.1) 0%,
            transparent 50%
        ),
        radial-gradient(
            ellipse 60% 35% at 50% 110%,
            rgba(34, 211, 238, 0.06) 0%,
            transparent 55%
        );
}

.dash-header {
    position: sticky;
    top: 0;
    z-index: 80;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 2rem;
    background: rgba(8, 13, 24, 0.9);
    border-bottom: 1px solid var(--border);
    backdrop-filter: blur(20px);
    animation: slideDown 0.5s ease-out both;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dash-header__brand {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.brand-img {
    height: 32px;
    width: auto;
    border-radius: 8px;
}

.brand-text {
    font-family: var(--font-d);
    font-size: 1.15rem;
    font-weight: 900;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--white);
}

.brand-pill {
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    padding: 0.18rem 0.5rem;
    border-radius: var(--r-pill);
    background: rgba(34, 211, 238, 0.15);
    border: 1px solid rgba(34, 211, 238, 0.3);
    color: var(--cyan);
}

.dash-header__center {
    display: flex;
    justify-content: center;
}

.header-label {
    font-family: var(--font-d);
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.38em;
    text-transform: uppercase;
    color: var(--muted);
}

.dash-header__actions {
    display: flex;
    justify-content: flex-end;
}

.hdr-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border-radius: var(--r-pill);
    border: 1px solid var(--border-hi);
    background: var(--glass-hi);
    color: var(--white);
    font-family: var(--font-b);
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.2s,
        transform 0.15s;
}
.hdr-btn svg {
    width: 15px;
    height: 15px;
}
.hdr-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-1px);
}

.dash-main {
    position: relative;
    z-index: 1;
    max-width: 1320px;
    margin: 0 auto;
    padding: 2rem 1.5rem 3rem;
    animation: fadeUp 0.6s 0.1s ease-out both;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1024px) {
    .stat-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 540px) {
    .stat-row {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    position: relative;
    border-radius: var(--r-lg);
    border: 1px solid var(--border);
    background: var(--glass);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    overflow: hidden;
    transition:
        transform 0.25s,
        border-color 0.25s;
}
.stat-card:hover {
    transform: translateY(-3px);
    border-color: var(--border-hi);
}

.stat-card--a {
    border-top: 2px solid var(--cyan);
}
.stat-card--b {
    border-top: 2px solid var(--lime);
}
.stat-card--c {
    border-top: 2px solid var(--amber);
}
.stat-card--d {
    flex-direction: row;
    align-items: center;
    gap: 0.75rem;
    border-top: 2px solid var(--rose);
    background: linear-gradient(
        135deg,
        rgba(251, 113, 133, 0.08) 0%,
        transparent 60%
    );
}

.stat-card__icon {
    width: 36px;
    height: 36px;
    border-radius: var(--r-sm);
    background: var(--glass-hi);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.25rem;
}
.stat-card__icon svg {
    width: 18px;
    height: 18px;
}
.stat-card--a .stat-card__icon {
    color: var(--cyan);
}
.stat-card--b .stat-card__icon {
    color: var(--lime);
}
.stat-card--c .stat-card__icon {
    color: var(--amber);
}

.stat-card__label {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--muted);
}

.stat-card__value {
    font-family: var(--font-d);
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    color: var(--white);
    letter-spacing: -0.02em;
}

.stat-card__bar {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    width: var(--pct);
    background: currentColor;
    opacity: 0.35;
    border-radius: 0 var(--r-pill) var(--r-pill) 0;
}
.stat-card--a .stat-card__bar {
    color: var(--cyan);
}
.stat-card--b .stat-card__bar {
    color: var(--lime);
}
.stat-card--c .stat-card__bar {
    color: var(--amber);
}

.store-logo {
    width: 48px;
    height: 48px;
    border-radius: var(--r-sm);
    object-fit: cover;
    border: 1px solid var(--border-hi);
    flex-shrink: 0;
}

.store-meta {
    flex: 1;
    min-width: 0;
}

.store-name {
    font-family: var(--font-d);
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--white);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.store-city {
    font-size: 0.72rem;
    color: var(--muted);
}

.store-edit-tag {
    font-size: 0.62rem;
    font-weight: 700;
    color: var(--rose);
    opacity: 0.8;
    flex-shrink: 0;
    align-self: flex-start;
    margin-top: 0.2rem;
}

.table-section {
    border-radius: var(--r-xl);
    border: 1px solid var(--border);
    background: var(--glass);
    overflow: hidden;
}

.table-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.02);
}

.table-head__left {
    display: flex;
    align-items: baseline;
    gap: 0.6rem;
}

.table-title {
    font-family: var(--font-d);
    font-size: 1.1rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: var(--white);
}

.table-count {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--muted);
    padding: 0.15rem 0.55rem;
    border-radius: var(--r-pill);
    background: var(--glass-hi);
    border: 1px solid var(--border);
}

.table-head__right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-wrap {
    position: relative;
}

.search-ico {
    position: absolute;
    left: 0.65rem;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: var(--muted);
    pointer-events: none;
}

.search-input {
    background: var(--glass-hi);
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    color: var(--white);
    font-family: var(--font-b);
    font-size: 0.78rem;
    padding: 0.45rem 0.9rem 0.45rem 2rem;
    outline: none;
    width: 200px;
    transition: border-color 0.2s;
}
.search-input::placeholder {
    color: var(--muted);
}
.search-input:focus {
    border-color: var(--border-hi);
}

.add-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.45rem 0.9rem;
    border-radius: var(--r-pill);
    background: var(--cyan);
    color: #080d18;
    font-family: var(--font-b);
    font-size: 0.78rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition:
        background 0.2s,
        transform 0.15s;
}
.add-btn svg {
    width: 13px;
    height: 13px;
}
.add-btn:hover {
    background: var(--lime);
    transform: scale(1.04);
}

.table-empty {
    padding: 4rem 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.empty-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--r-md);
    background: var(--glass-hi);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
}
.empty-icon svg {
    width: 28px;
    height: 28px;
}

.empty-text {
    font-size: 0.85rem;
    color: var(--muted);
    max-width: 320px;
}

.table-scroll {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}

.data-table thead tr {
    border-bottom: 1px solid var(--border);
}

.data-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--muted);
    background: rgba(255, 255, 255, 0.02);
    white-space: nowrap;
}

.data-row {
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    transition: background 0.15s;
}
.data-row:hover {
    background: rgba(255, 255, 255, 0.03);
}
.data-row:last-child {
    border-bottom: none;
}

.data-table td {
    padding: 0.7rem 1rem;
    vertical-align: middle;
    color: rgba(240, 246, 255, 0.75);
}

.prod-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.prod-img {
    width: 44px;
    height: 44px;
    border-radius: var(--r-sm);
    object-fit: cover;
    border: 1px solid var(--border);
    flex-shrink: 0;
}

.prod-name {
    font-weight: 700;
    color: var(--white);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
    font-size: 0.83rem;
}

.prod-slug {
    font-size: 0.68rem;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
}

.cat-tag {
    font-size: 0.72rem;
    padding: 0.2rem 0.6rem;
    border-radius: var(--r-pill);
    background: var(--glass-hi);
    border: 1px solid var(--border);
    color: var(--muted);
    white-space: nowrap;
}

.cond-pill {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--r-pill);
}
.cond-new {
    background: rgba(190, 242, 100, 0.15);
    color: var(--lime);
    border: 1px solid rgba(190, 242, 100, 0.3);
}
.cond-used {
    background: rgba(251, 191, 36, 0.15);
    color: var(--amber);
    border: 1px solid rgba(251, 191, 36, 0.3);
}

.num-cell {
    font-variant-numeric: tabular-nums;
}
.price-cell {
    font-family: var(--font-d);
    font-weight: 700;
    color: var(--white);
    white-space: nowrap;
}
.date-cell {
    white-space: nowrap;
}

.act-wrap {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.act-edit {
    padding: 0.28rem 0.75rem;
    border-radius: var(--r-pill);
    border: 1px solid var(--border-hi);
    background: transparent;
    color: var(--white);
    font-family: var(--font-b);
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.act-edit:hover {
    background: var(--glass-hi);
}

.act-del {
    padding: 0.28rem 0.75rem;
    border-radius: var(--r-pill);
    border: 1px solid rgba(251, 113, 133, 0.3);
    background: rgba(251, 113, 133, 0.07);
    color: var(--rose);
    font-family: var(--font-b);
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.act-del:hover {
    background: rgba(251, 113, 133, 0.18);
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.9rem 1.5rem;
    border-top: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.01);
}

.page-info {
    font-size: 0.72rem;
    color: var(--muted);
}

.page-btns {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.page-btn {
    min-width: 28px;
    height: 28px;
    padding: 0 0.4rem;
    border-radius: var(--r-xs);
    border: 1px solid var(--border);
    background: transparent;
    color: var(--muted);
    font-family: var(--font-b);
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.15s,
        color 0.15s,
        border-color 0.15s;
}
.page-btn:hover:not(:disabled) {
    background: var(--glass-hi);
    color: var(--white);
}
.page-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}
.page-btn--active {
    background: var(--cyan);
    border-color: var(--cyan);
    color: #080d18;
}

.page-dots {
    font-size: 0.75rem;
    color: var(--muted);
    padding: 0 0.2rem;
}

.toast-stack {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 500;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    max-width: 320px;
}

.toast-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem 1rem;
    border-radius: var(--r-sm);
    font-size: 0.8rem;
    font-weight: 600;
    backdrop-filter: blur(16px);
    animation: toastIn 0.25s ease-out both;
}

@keyframes toastIn {
    from {
        opacity: 0;
        transform: translateX(16px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.toast-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.toast--ok {
    background: rgba(190, 242, 100, 0.12);
    border: 1px solid rgba(190, 242, 100, 0.25);
    color: #d9f99d;
}
.toast--ok .toast-dot {
    background: var(--lime);
}

.toast--err {
    background: rgba(251, 113, 133, 0.12);
    border: 1px solid rgba(251, 113, 133, 0.25);
    color: #fca5a5;
}
.toast--err .toast-dot {
    background: var(--rose);
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
    transform: translateX(14px);
}

.overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal {
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: var(--r-xl);
    background: var(--navy-2);
    border: 1px solid var(--border-hi);
    box-shadow: 0 40px 80px rgba(0, 0, 0, 0.6);
    padding: 1.75rem;
    animation: modalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.modal--sm {
    max-width: 400px;
    text-align: center;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.92) translateY(16px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.modal__eyebrow {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--cyan);
    margin-bottom: 0.2rem;
}

.modal__title {
    font-family: var(--font-d);
    font-size: 1.3rem;
    font-weight: 900;
    color: var(--white);
    letter-spacing: -0.02em;
}

.modal__close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--glass);
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition:
        background 0.2s,
        color 0.2s;
}
.modal__close svg {
    width: 14px;
    height: 14px;
}
.modal__close:hover {
    background: var(--glass-hi);
    color: var(--white);
}

.modal__body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.field--full {
    grid-column: 1 / -1;
}

.field__label {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--muted);
}
.field__label--sm {
    letter-spacing: 0.12em;
    text-transform: none;
    font-size: 0.72rem;
}

.field__input {
    background: var(--navy-3);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    color: var(--white);
    font-family: var(--font-b);
    font-size: 0.83rem;
    padding: 0.6rem 0.85rem;
    outline: none;
    transition: border-color 0.2s;
    width: 100%;
    box-sizing: border-box;
}
.field__input:focus {
    border-color: var(--cyan);
}
.field__input::placeholder {
    color: rgba(240, 246, 255, 0.25);
}
.field__textarea {
    resize: vertical;
}
.field__select {
    cursor: pointer;
}
.field__select option {
    background: var(--navy-2);
}
.field__file {
    padding: 0.5rem 0.85rem;
    cursor: pointer;
}

.field__err {
    font-size: 0.7rem;
    color: var(--rose);
    font-weight: 600;
}

.img-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.img-preview {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
    padding: 0.75rem;
    border-radius: var(--r-sm);
    border: 1px dashed var(--border-hi);
    background: var(--glass);
}
.img-preview img {
    width: 64px;
    height: 64px;
    border-radius: var(--r-sm);
    object-fit: cover;
    border: 1px solid var(--border);
}
.img-preview span {
    font-size: 0.72rem;
    color: var(--muted);
}

.modal__foot {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    padding-top: 0.5rem;
    margin-top: 0.25rem;
    border-top: 1px solid var(--border);
}
.modal__foot--center {
    justify-content: center;
}

.foot-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.55rem 1.25rem;
    border-radius: var(--r-pill);
    font-family: var(--font-b);
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition:
        background 0.2s,
        transform 0.15s;
}

.foot-btn--ghost {
    border: 1px solid var(--border-hi);
    background: transparent;
    color: var(--muted);
}
.foot-btn--ghost:hover {
    background: var(--glass-hi);
    color: var(--white);
}

.foot-btn--primary {
    border: none;
    background: var(--cyan);
    color: #080d18;
}
.foot-btn--primary:hover {
    background: var(--lime);
    transform: scale(1.03);
}
.foot-btn--primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.foot-btn--danger {
    border: none;
    background: var(--rose);
    color: #fff;
}
.foot-btn--danger:hover {
    background: #f43f5e;
    transform: scale(1.03);
}

.del-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--r-md);
    background: rgba(251, 113, 133, 0.12);
    border: 1px solid rgba(251, 113, 133, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: var(--rose);
}
.del-icon svg {
    width: 26px;
    height: 26px;
}

.del-title {
    font-family: var(--font-d);
    font-size: 1.15rem;
    font-weight: 900;
    color: var(--white);
    margin-bottom: 0.5rem;
}

.del-desc {
    font-size: 0.82rem;
    color: var(--muted);
    line-height: 1.6;
    margin-bottom: 1.25rem;
}
.del-desc strong {
    color: var(--white);
    font-weight: 700;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
