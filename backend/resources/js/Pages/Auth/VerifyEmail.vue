<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { t } = useI18n();

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.reenviar')" />

        <div class="mb-4 text-sm text-gray-600">
            {{ t('auth.verificarTexto') }}
        </div>

        <div class="mb-4 text-sm font-medium text-emerald-600" v-if="verificationLinkSent">
            {{ t('auth.verificacaoEnviada') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.reenviar') }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >{{ t('conta.sair') }}</Link>
            </div>
        </form>
    </GuestLayout>
</template>
