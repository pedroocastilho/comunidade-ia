<script setup>
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();

defineProps({
    checkout_url: String,
});
</script>

<template>
    <Head :title="t('semAcesso.titulo')" />

    <PainelLayout>
        <div class="mx-auto flex max-w-2xl flex-col items-center px-5 py-24 text-center lg:px-10">
            <h1 class="font-display text-4xl font-extrabold tracking-tight text-aura-text">
                {{ t('semAcesso.titulo') }}
            </h1>
            <p class="mt-5 max-w-md text-aura-muted">
                {{ t('semAcesso.texto') }}
            </p>
            <a
                v-if="checkout_url"
                :href="checkout_url"
                class="mt-8 rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-8 py-3 font-semibold text-aura-black transition hover:opacity-90"
            >
                {{ t('semAcesso.cta') }}
            </a>
            <a
                href="mailto:noah@circuloaura.com"
                class="mt-4 text-sm font-semibold text-aura-gold underline hover:text-aura-gold-light"
            >
                {{ t('semAcesso.suporte') }}
            </a>
        </div>
    </PainelLayout>
</template>
