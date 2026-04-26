<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { onBeforeUnmount, onMounted, reactive, ref, watch } from "vue";

const props = defineProps({
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: "" },
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
});

const user = usePage().props.auth?.user;

const accountForm = useForm({
    name: user?.name || "",
    email: user?.email || "",
});

const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const deleteForm = useForm({
    password: "",
});

const storeForm = reactive({
    name: "",
    about: "",
    phone: "",
    city: "",
    address: "",
    postal_code: "",
    logo_url: "",
    logo_file: null,
});

const storeErrors = ref({});
const storeSaving = ref(false);
const storeDirty = ref(false);
const logoPreview = ref("");
const showDeleteConfirm = ref(false);
let profileTimer = null;

const syncStoreFormFromProps = (source) => {
    storeForm.name = source?.name || "";
    storeForm.about = source?.about || "";
    storeForm.phone = source?.phone || "";
    storeForm.city = source?.city || "";
    storeForm.address = source?.address || "";
    storeForm.postal_code = source?.postal_code || "";
    storeForm.logo_url = source?.logo || "";
    storeForm.logo_file = null;
    logoPreview.value = source?.logo || "";
};

watch(
    () => props.storeProfile,
    (value) => {
        if (!storeDirty.value) {
            syncStoreFormFromProps(value);
        }
    },
    { immediate: true, deep: true },
);

const markStoreDirty = () => {
    storeDirty.value = true;
};

const onLogoFileChange = (event) => {
    storeDirty.value = true;
    const file = event.target.files[0] || null;
    storeForm.logo_file = file;

    if (!file) {
        logoPreview.value = storeForm.logo_url || "";
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target?.result || "";
    };
    reader.readAsDataURL(file);
};

const onLogoUrlInput = () => {
    storeDirty.value = true;
    if (!storeForm.logo_file) {
        logoPreview.value = storeForm.logo_url || "";
    }
};

const submitStoreProfile = async () => {
    storeSaving.value = true;
    storeErrors.value = {};

    try {
        const payload = new FormData();
        payload.append("name", storeForm.name || "");
        payload.append("about", storeForm.about || "");
        payload.append("phone", storeForm.phone || "");
        payload.append("city", storeForm.city || "");
        payload.append("address", storeForm.address || "");
        payload.append("postal_code", storeForm.postal_code || "");

        if (storeForm.logo_url) payload.append("logo_url", storeForm.logo_url);
        if (storeForm.logo_file)
            payload.append("logo_file", storeForm.logo_file);

        await window.axios.post(
            route("dashboard.store-profile.update"),
            payload,
            {
                headers: { "Content-Type": "multipart/form-data" },
            },
        );

        storeDirty.value = false;
        router.reload({
            only: ["storeProfile"],
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        if (error?.response?.status === 422) {
            storeErrors.value = error.response.data.errors || {};
        }
    } finally {
        storeSaving.value = false;
    }
};

const submitAccount = () => {
    accountForm.patch(route("profile.update"), {
        preserveScroll: true,
    });
};

const submitPassword = () => {
    passwordForm.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const submitDeleteAccount = () => {
    deleteForm.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteForm.reset();
        },
    });
};

onMounted(() => {
    profileTimer = window.setInterval(() => {
        if (document.visibilityState === "visible") {
            router.reload({
                only: ["storeProfile"],
                preserveState: true,
                preserveScroll: true,
            });
        }
    }, 8000);
});

onBeforeUnmount(() => {
    if (profileTimer) window.clearInterval(profileTimer);
});
</script>

<template>
    <Head title="Profile Dashboard" />

    <div class="profile-root">
        <div class="profile-glow" aria-hidden="true" />

        <div class="profile-wrap">
            <header class="profile-header">
                <div>
                    <p class="header-eyebrow">Store Settings</p>
                    <h1 class="header-title">Profile Mengikuti Dashboard</h1>
                </div>
                <Link :href="route('dashboard')" class="back-btn"
                    >Kembali ke Dashboard</Link
                >
            </header>

            <section class="card card--store">
                <div class="card-head">
                    <h2>Store Profile</h2>
                    <span class="pill">Realtime Sync</span>
                </div>

                <form class="grid" @submit.prevent="submitStoreProfile">
                    <label class="field field--full">
                        <span>Nama Store</span>
                        <input
                            v-model="storeForm.name"
                            type="text"
                            @input="markStoreDirty"
                            required
                        />
                        <small v-if="storeErrors.name?.[0]" class="err">{{
                            storeErrors.name[0]
                        }}</small>
                    </label>

                    <label class="field field--full">
                        <span>About</span>
                        <textarea
                            v-model="storeForm.about"
                            rows="3"
                            @input="markStoreDirty"
                        />
                    </label>

                    <label class="field">
                        <span>Telepon</span>
                        <input
                            v-model="storeForm.phone"
                            type="text"
                            @input="markStoreDirty"
                        />
                    </label>

                    <label class="field">
                        <span>Kota</span>
                        <input
                            v-model="storeForm.city"
                            type="text"
                            @input="markStoreDirty"
                        />
                    </label>

                    <label class="field field--full">
                        <span>Alamat</span>
                        <textarea
                            v-model="storeForm.address"
                            rows="2"
                            @input="markStoreDirty"
                        />
                    </label>

                    <label class="field">
                        <span>Kode Pos</span>
                        <input
                            v-model="storeForm.postal_code"
                            type="text"
                            @input="markStoreDirty"
                        />
                    </label>

                    <div class="field field--full">
                        <span>Logo Store</span>
                        <div class="logo-grid">
                            <label class="field">
                                <span>Logo URL</span>
                                <input
                                    v-model="storeForm.logo_url"
                                    type="url"
                                    placeholder="https://..."
                                    @input="onLogoUrlInput"
                                />
                            </label>
                            <label class="field">
                                <span>Upload File</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onLogoFileChange"
                                />
                            </label>
                        </div>

                        <small v-if="storeErrors.logo_url?.[0]" class="err">{{
                            storeErrors.logo_url[0]
                        }}</small>
                        <small v-if="storeErrors.logo_file?.[0]" class="err">{{
                            storeErrors.logo_file[0]
                        }}</small>

                        <div v-if="logoPreview" class="logo-preview">
                            <img :src="logoPreview" alt="Logo preview" />
                            <span>Preview realtime logo store</span>
                        </div>
                    </div>

                    <div class="actions">
                        <button
                            type="submit"
                            class="btn btn--primary"
                            :disabled="storeSaving"
                        >
                            {{
                                storeSaving
                                    ? "Menyimpan..."
                                    : "Simpan Profile Store"
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2>Akun Login</h2>
                </div>

                <form class="grid" @submit.prevent="submitAccount">
                    <label class="field">
                        <span>Nama User</span>
                        <input
                            v-model="accountForm.name"
                            type="text"
                            required
                        />
                        <small v-if="accountForm.errors.name" class="err">{{
                            accountForm.errors.name
                        }}</small>
                    </label>

                    <label class="field">
                        <span>Email</span>
                        <input
                            v-model="accountForm.email"
                            type="email"
                            required
                        />
                        <small v-if="accountForm.errors.email" class="err">{{
                            accountForm.errors.email
                        }}</small>
                    </label>

                    <p v-if="mustVerifyEmail" class="muted field--full">
                        Verifikasi email diperlukan untuk keamanan akun.
                    </p>
                    <p
                        v-if="status === 'verification-link-sent'"
                        class="ok field--full"
                    >
                        Link verifikasi baru sudah dikirim ke email kamu.
                    </p>

                    <div class="actions">
                        <button
                            type="submit"
                            class="btn btn--primary"
                            :disabled="accountForm.processing"
                        >
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2>Ganti Password</h2>
                </div>

                <form class="grid" @submit.prevent="submitPassword">
                    <label class="field">
                        <span>Password Saat Ini</span>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                        />
                        <small
                            v-if="passwordForm.errors.current_password"
                            class="err"
                            >{{ passwordForm.errors.current_password }}</small
                        >
                    </label>

                    <label class="field">
                        <span>Password Baru</span>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                        />
                        <small
                            v-if="passwordForm.errors.password"
                            class="err"
                            >{{ passwordForm.errors.password }}</small
                        >
                    </label>

                    <label class="field field--full">
                        <span>Konfirmasi Password Baru</span>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                        />
                        <small
                            v-if="passwordForm.errors.password_confirmation"
                            class="err"
                            >{{
                                passwordForm.errors.password_confirmation
                            }}</small
                        >
                    </label>

                    <div class="actions">
                        <button
                            type="submit"
                            class="btn btn--primary"
                            :disabled="passwordForm.processing"
                        >
                            Update Password
                        </button>
                    </div>
                </form>
            </section>

            <section class="card card--danger">
                <div class="card-head">
                    <h2>Danger Zone</h2>
                </div>

                <p class="muted">
                    Hapus akun akan menghapus data akun secara permanen.
                </p>

                <div v-if="!showDeleteConfirm" class="actions actions--left">
                    <button
                        type="button"
                        class="btn btn--danger"
                        @click="showDeleteConfirm = true"
                    >
                        Hapus Akun
                    </button>
                </div>

                <form v-else class="grid" @submit.prevent="submitDeleteAccount">
                    <label class="field field--full">
                        <span>Konfirmasi Password</span>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            required
                        />
                        <small v-if="deleteForm.errors.password" class="err">{{
                            deleteForm.errors.password
                        }}</small>
                    </label>

                    <div class="actions actions--left">
                        <button
                            type="button"
                            class="btn btn--ghost"
                            @click="showDeleteConfirm = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn btn--danger"
                            :disabled="deleteForm.processing"
                        >
                            Ya, Hapus Akun
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</template>

<style scoped>
.profile-root {
    --bg: #080d18;
    --bg-2: #0c1528;
    --line: rgba(255, 255, 255, 0.12);
    --txt: #e8f1ff;
    --muted: rgba(232, 241, 255, 0.65);
    --cyan: #22d3ee;
    --lime: #bef264;
    --rose: #fb7185;

    min-height: 100vh;
    background: var(--bg);
    color: var(--txt);
    position: relative;
    padding: 1.3rem 1rem 2rem;
}

.profile-glow {
    position: fixed;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(
            circle at 0% 0%,
            rgba(34, 211, 238, 0.12),
            transparent 40%
        ),
        radial-gradient(
            circle at 100% 100%,
            rgba(190, 242, 100, 0.1),
            transparent 45%
        );
}

.profile-wrap {
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    gap: 1rem;
}

.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.2rem;
    border: 1px solid var(--line);
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.04);
}

.header-eyebrow {
    font-size: 0.62rem;
    letter-spacing: 0.3em;
    color: var(--cyan);
    text-transform: uppercase;
    font-weight: 700;
}

.header-title {
    font-size: 1.35rem;
    font-weight: 800;
}

.back-btn {
    text-decoration: none;
    padding: 0.5rem 0.95rem;
    border-radius: 999px;
    border: 1px solid var(--line);
    color: var(--txt);
    font-weight: 700;
    background: rgba(255, 255, 255, 0.06);
}

.card {
    border: 1px solid var(--line);
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.04);
    padding: 1rem;
}

.card--store {
    border-top: 2px solid var(--cyan);
}

.card--danger {
    border-top: 2px solid var(--rose);
}

.card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.9rem;
}

.card-head h2 {
    font-size: 1.05rem;
    font-weight: 800;
}

.pill {
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    font-size: 0.68rem;
    border: 1px solid rgba(34, 211, 238, 0.35);
    background: rgba(34, 211, 238, 0.12);
    color: var(--cyan);
    font-weight: 700;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field--full {
    grid-column: 1 / -1;
}

.field span {
    font-size: 0.72rem;
    color: var(--muted);
    font-weight: 700;
    letter-spacing: 0.05em;
}

.field input,
.field textarea {
    width: 100%;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: rgba(9, 15, 29, 0.85);
    color: var(--txt);
    padding: 0.62rem 0.78rem;
}

.field textarea {
    resize: vertical;
}

.logo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.7rem;
}

.logo-preview {
    margin-top: 0.6rem;
    padding: 0.65rem;
    border: 1px dashed rgba(255, 255, 255, 0.22);
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
}

.logo-preview img {
    width: 68px;
    height: 68px;
    object-fit: cover;
    border-radius: 10px;
}

.logo-preview span {
    color: var(--muted);
    font-size: 0.82rem;
}

.muted {
    color: var(--muted);
    font-size: 0.85rem;
}

.ok {
    color: #d9f99d;
    font-size: 0.84rem;
}

.err {
    color: var(--rose);
    font-size: 0.78rem;
}

.actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 0.55rem;
    margin-top: 0.2rem;
}

.actions--left {
    justify-content: flex-start;
}

.btn {
    border: none;
    border-radius: 999px;
    padding: 0.55rem 1rem;
    font-weight: 700;
    cursor: pointer;
}

.btn--primary {
    background: var(--cyan);
    color: #0b1220;
}

.btn--ghost {
    background: transparent;
    color: var(--txt);
    border: 1px solid var(--line);
}

.btn--danger {
    background: var(--rose);
    color: #fff;
}

@media (max-width: 820px) {
    .grid,
    .logo-grid {
        grid-template-columns: 1fr;
    }

    .profile-header {
        flex-direction: column;
    }
}
</style>
