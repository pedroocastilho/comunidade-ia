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
    <div class="inline-flex items-center rounded-full border border-gray-200 bg-white p-0.5 text-xs font-bold">
        <button
            v-for="idioma in idiomas"
            :key="idioma.code"
            type="button"
            class="rounded-full px-2.5 py-1 transition"
            :class="locale === idioma.code ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-gray-800'"
            @click="trocar(idioma.code)"
        >
            {{ idioma.label }}
        </button>
    </div>
</template>
