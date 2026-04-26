<script setup>
import { Link, router } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";

const props = defineProps({
    mode: { type: String, default: "create" },
    categories: { type: Array, default: () => [] },
    product: { type: Object, default: null },
});

const isEdit = computed(() => props.mode === "edit" && !!props.product?.uuid);

const form = reactive({
    name: props.product?.name || "",
    about: props.product?.about || "",
    condition: props.product?.condition || "new",
    price: props.product?.price || "",
    weight: props.product?.weight || "",
    stock: props.product?.stock || "",
    product_category_id:
        props.product?.product_category_id || props.categories[0]?.uuid || "",
    image_url: props.product?.image || "",
    image_file: null,
});

const mainPreview = ref(form.image_url || "");
const extraImageUrlFields = ref(
    props.product?.extra_images?.length
        ? [...props.product.extra_images]
        : [""],
);
const extraImageFiles = ref([]);
const extraImageFilePreviews = ref([]);
const saving = ref(false);
const errors = ref({});

const urlExtraPreviews = computed(() =>
    extraImageUrlFields.value
        .map((url) => (url || "").trim())
        .filter((url) => url.length > 0),
);

const allExtraPreviews = computed(() => [
    ...urlExtraPreviews.value,
    ...extraImageFilePreviews.value,
]);

const addExtraUrlField = () => {
    if (extraImageUrlFields.value.length >= 8) return;
    extraImageUrlFields.value.push("");
};

const removeExtraUrlField = (index) => {
    if (extraImageUrlFields.value.length === 1) {
        extraImageUrlFields.value[0] = "";
        return;
    }

    extraImageUrlFields.value.splice(index, 1);
};

const onImageFileChange = (event) => {
    const file = event.target.files[0] || null;
    form.image_file = file;

    if (!file) {
        mainPreview.value = form.image_url || "";
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        mainPreview.value = e.target?.result || "";
    };
    reader.readAsDataURL(file);
};

const onImageUrlInput = () => {
    if (!form.image_file) {
        mainPreview.value = form.image_url || "";
    }
};

const onExtraImageFilesChange = async (event) => {
    const files = Array.from(event.target.files || []);
    extraImageFiles.value = files;

    const previews = await Promise.all(
        files.map(
            (file) =>
                new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = (e) => resolve(e.target?.result || "");
                    reader.readAsDataURL(file);
                }),
        ),
    );

    extraImageFilePreviews.value = previews.filter(Boolean);
};

const submit = async () => {
    saving.value = true;
    errors.value = {};

    try {
        const payload = new FormData();
        payload.append("name", form.name);
        payload.append("about", form.about);
        payload.append("condition", form.condition);
        payload.append("price", form.price);
        payload.append("weight", form.weight);
        payload.append("stock", form.stock);
        payload.append("product_category_id", form.product_category_id);

        if (form.image_url) payload.append("image_url", form.image_url);
        if (form.image_file) payload.append("image_file", form.image_file);

        for (const url of urlExtraPreviews.value) {
            payload.append("extra_image_urls[]", url);
        }

        for (const file of extraImageFiles.value) {
            payload.append("extra_image_files[]", file);
        }

        if (isEdit.value) {
            payload.append("_method", "PUT");
            await window.axios.post(
                route("dashboard.products.update", props.product.uuid),
                payload,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
        } else {
            await window.axios.post(
                route("dashboard.products.store"),
                payload,
                {
                    headers: { "Content-Type": "multipart/form-data" },
                },
            );
        }

        router.visit(route("dashboard"));
    } catch (error) {
        if (error?.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        }
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="product-form-page">
        <div class="product-form-card">
            <div class="head">
                <div>
                    <p class="eyebrow">Dashboard Product</p>
                    <h1 class="title">
                        {{ isEdit ? "Edit Produk" : "Tambah Produk" }}
                    </h1>
                </div>
                <Link :href="route('dashboard')" class="back-link"
                    >Kembali</Link
                >
            </div>

            <form class="form-grid" @submit.prevent="submit">
                <label class="field field--full">
                    <span>Nama Produk</span>
                    <input v-model="form.name" type="text" required />
                    <small v-if="errors.name?.[0]" class="err">{{
                        errors.name[0]
                    }}</small>
                </label>

                <label class="field field--full">
                    <span>Deskripsi</span>
                    <textarea v-model="form.about" rows="4" required></textarea>
                    <small v-if="errors.about?.[0]" class="err">{{
                        errors.about[0]
                    }}</small>
                </label>

                <label class="field">
                    <span>Kategori</span>
                    <select v-model="form.product_category_id" required>
                        <option
                            v-for="category in categories"
                            :key="category.uuid"
                            :value="category.uuid"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <small v-if="errors.product_category_id?.[0]" class="err">{{
                        errors.product_category_id[0]
                    }}</small>
                </label>

                <label class="field">
                    <span>Kondisi</span>
                    <select v-model="form.condition" required>
                        <option value="new">Baru</option>
                        <option value="used">Bekas</option>
                    </select>
                    <small v-if="errors.condition?.[0]" class="err">{{
                        errors.condition[0]
                    }}</small>
                </label>

                <label class="field">
                    <span>Harga (Rp)</span>
                    <input
                        v-model="form.price"
                        type="number"
                        min="0"
                        required
                    />
                    <small v-if="errors.price?.[0]" class="err">{{
                        errors.price[0]
                    }}</small>
                </label>

                <label class="field">
                    <span>Berat (gram)</span>
                    <input
                        v-model="form.weight"
                        type="number"
                        min="1"
                        required
                    />
                    <small v-if="errors.weight?.[0]" class="err">{{
                        errors.weight[0]
                    }}</small>
                </label>

                <label class="field">
                    <span>Stok</span>
                    <input
                        v-model="form.stock"
                        type="number"
                        min="0"
                        required
                    />
                    <small v-if="errors.stock?.[0]" class="err">{{
                        errors.stock[0]
                    }}</small>
                </label>

                <div class="field field--full">
                    <span>Gambar Utama</span>
                    <div class="img-grid">
                        <label class="field">
                            <span>URL Gambar</span>
                            <input
                                v-model="form.image_url"
                                type="url"
                                placeholder="https://..."
                                @input="onImageUrlInput"
                            />
                        </label>
                        <label class="field">
                            <span>Upload File</span>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onImageFileChange"
                            />
                        </label>
                    </div>
                    <small v-if="errors.image_url?.[0]" class="err">{{
                        errors.image_url[0]
                    }}</small>
                    <small v-if="errors.image_file?.[0]" class="err">{{
                        errors.image_file[0]
                    }}</small>

                    <div v-if="mainPreview" class="preview">
                        <img :src="mainPreview" alt="Preview utama" />
                        <span>Preview Gambar Utama</span>
                    </div>
                </div>

                <div class="field field--full">
                    <span>Gambar Tambahan (Opsional)</span>

                    <div class="extra-url-list">
                        <div
                            v-for="(url, index) in extraImageUrlFields"
                            :key="`extra-${index}`"
                            class="extra-url-row"
                        >
                            <input
                                v-model="extraImageUrlFields[index]"
                                type="url"
                                placeholder="https://..."
                            />
                            <button
                                type="button"
                                class="url-remove"
                                @click="removeExtraUrlField(index)"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="extra-actions">
                        <button
                            type="button"
                            class="btn-inline"
                            @click="addExtraUrlField"
                        >
                            + Tambah URL Gambar
                        </button>
                    </div>

                    <label class="field">
                        <span>Upload Banyak File (maks 8)</span>
                        <input
                            type="file"
                            accept="image/*"
                            multiple
                            @change="onExtraImageFilesChange"
                        />
                    </label>

                    <small v-if="errors.extra_image_urls?.[0]" class="err">{{
                        errors.extra_image_urls[0]
                    }}</small>
                    <small v-if="errors['extra_image_urls.0']" class="err">{{
                        errors["extra_image_urls.0"]
                    }}</small>
                    <small v-if="errors.extra_image_files?.[0]" class="err">{{
                        errors.extra_image_files[0]
                    }}</small>
                    <small v-if="errors['extra_image_files.0']" class="err">{{
                        errors["extra_image_files.0"]
                    }}</small>

                    <div v-if="allExtraPreviews.length" class="preview-grid">
                        <div
                            v-for="(image, idx) in allExtraPreviews"
                            :key="`preview-${idx}`"
                            class="preview-tile"
                        >
                            <img :src="image" :alt="`Preview ${idx + 1}`" />
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <Link :href="route('dashboard')" class="btn btn--ghost"
                        >Batal</Link
                    >
                    <button
                        type="submit"
                        :disabled="saving"
                        class="btn btn--primary"
                    >
                        {{
                            saving
                                ? "Menyimpan..."
                                : isEdit
                                  ? "Update Produk"
                                  : "Simpan Produk"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.product-form-page {
    min-height: 100vh;
    padding: 2rem 1rem;
    background: #0b1220;
    color: #f8fafc;
}

.product-form-card {
    max-width: 980px;
    margin: 0 auto;
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.04);
    padding: 1.5rem;
}

.head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.4rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 1rem;
}

.eyebrow {
    font-size: 0.65rem;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: rgba(34, 211, 238, 0.9);
    font-weight: 700;
}

.title {
    font-size: 1.5rem;
    font-weight: 800;
}

.back-link {
    color: #22d3ee;
    text-decoration: none;
    font-weight: 700;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.9rem;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.field--full {
    grid-column: 1 / -1;
}

.field span {
    font-size: 0.75rem;
    color: rgba(248, 250, 252, 0.8);
    font-weight: 600;
}

.field input,
.field textarea,
.field select {
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.9);
    color: #f8fafc;
    padding: 0.65rem 0.8rem;
    font-size: 0.9rem;
}

.field textarea {
    resize: vertical;
}

.err {
    color: #fb7185;
    font-size: 0.76rem;
}

.img-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.8rem;
}

.preview {
    margin-top: 0.6rem;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    border: 1px dashed rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 0.6rem;
}

.preview img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 10px;
}

.preview span {
    font-size: 0.82rem;
    color: rgba(248, 250, 252, 0.7);
}

.extra-url-list {
    display: grid;
    gap: 0.5rem;
}

.extra-url-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.45rem;
}

.url-remove {
    border: 1px solid rgba(251, 113, 133, 0.32);
    background: rgba(251, 113, 133, 0.12);
    color: #fb7185;
    border-radius: 10px;
    padding: 0.45rem 0.8rem;
    font-weight: 700;
    cursor: pointer;
}

.extra-actions {
    margin-top: 0.2rem;
}

.btn-inline {
    border: 1px solid rgba(34, 211, 238, 0.35);
    background: rgba(34, 211, 238, 0.1);
    color: #67e8f9;
    border-radius: 10px;
    padding: 0.45rem 0.75rem;
    font-weight: 700;
    cursor: pointer;
}

.preview-grid {
    margin-top: 0.7rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.5rem;
}

.preview-tile {
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 10px;
    overflow: hidden;
    background: rgba(15, 23, 42, 0.65);
    aspect-ratio: 1;
}

.preview-tile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    margin-top: 0.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 1rem;
}

.btn {
    border-radius: 999px;
    padding: 0.58rem 1.1rem;
    font-weight: 700;
    text-decoration: none;
}

.btn--ghost {
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(248, 250, 252, 0.8);
    background: transparent;
}

.btn--primary {
    border: none;
    background: #22d3ee;
    color: #0f172a;
    cursor: pointer;
}

.btn--primary:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .form-grid,
    .img-grid,
    .extra-url-row {
        grid-template-columns: 1fr;
    }
}
</style>
