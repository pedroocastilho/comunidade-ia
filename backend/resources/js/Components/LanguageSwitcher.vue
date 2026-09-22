<script setup>
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/useI18n';

const { locale } = useI18n();

function trocar(novo) {
    if (novo !== locale.value) {
        router.post(route('idioma'), { locale: novo }, { preserveScroll: true });
    }
}

const idiomas = [
    { code: 'pt', label: 'PT' },
    { code: 'es', label: 'ES' },
];
</script>

<template>
    <div class="inline-flex items-center rounded-full border border-aura-line bg-aura-surface p-0.5 text-xs font-bold">
        <button
            v-for="idioma in idiomas"
            :key="idioma.code"
            type="button"
            class="rounded-full px-3 py-2 transition"
            :class="locale === idioma.code ? 'bg-aura-gold text-aura-black' : 'text-aura-muted hover:text-aura-text'"
            @click="trocar(idioma.code)"
        >
            {{ idioma.label }}
        </button>
    </div>
</template>
