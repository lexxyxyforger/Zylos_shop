<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-5">
            <p
                class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500"
            >
                Welcome Back
            </p>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                Sign in to your account
            </h1>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel
                    for="email"
                    value="Email / Username"
                    class="text-slate-700"
                />

                <TextInput
                    id="email"
                    type="text"
                    class="mt-1 block w-full rounded-xl border-slate-200 bg-white/95 px-3 py-2.5 text-slate-800 shadow-sm focus:border-slate-800 focus:ring-slate-800"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="contoh: AdaWong atau email@domain.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password"
                    value="Password"
                    class="text-slate-700"
                />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-200 bg-white/95 px-3 py-2.5 text-slate-800 shadow-sm focus:border-slate-800 focus:ring-slate-800"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-slate-600">Remember me</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-slate-600 underline decoration-slate-300 underline-offset-2 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4 rounded-full bg-slate-900 px-5 py-2.5 text-xs font-bold tracking-[0.16em] text-white hover:bg-slate-800 focus:bg-slate-800 focus:ring-slate-700 active:bg-slate-950"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
