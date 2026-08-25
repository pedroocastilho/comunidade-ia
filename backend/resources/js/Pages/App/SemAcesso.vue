<script setup>
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();
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
        </div>
    </PainelLayout>
</template>
