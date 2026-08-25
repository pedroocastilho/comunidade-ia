<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useI18n } from '@/useI18n';
import { Head, useForm } from '@inertiajs/vue3';

const { t } = useI18n();

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.confirmar')" />

        <div class="mb-4 text-sm text-aura-muted">
            {{ t('auth.confirmarTexto') }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="t('auth.senha')" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" autofocus />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-end">
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.confirmar') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
