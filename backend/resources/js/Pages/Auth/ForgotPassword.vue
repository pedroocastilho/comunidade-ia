<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useI18n } from '@/useI18n';
import { Head, useForm } from '@inertiajs/vue3';

const { t } = useI18n();

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.esqueceu')" />

        <div class="mb-4 text-sm text-aura-muted">
            {{ t('auth.esqueceuTexto') }}
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-aura-gold">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="t('auth.email')" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.enviarLink') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
