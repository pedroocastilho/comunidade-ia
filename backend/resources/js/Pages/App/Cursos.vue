<script setup>
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    cursos: Array,
    categorias: Array,
    filtros: Object,
});

const busca = ref(props.filtros.busca ?? '');

function filtrar() {
    router.get(route('cursos'), { busca: busca.value }, { preserveState: true });
}
</script>

<template>
    <Head :title="t('cursos.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-7xl px-5 py-8 lg:px-10 lg:py-12">
            <!-- Abas Cursos / Audios -->
            <div class="flex items-center gap-6 border-b border-aura-line">
                <span class="border-b-2 border-aura-gold pb-3 text-sm font-semibold text-aura-gold">
                    {{ t('audios.cursosAba') }}
                </span>
                <Link :href="route('audios')" class="pb-3 text-sm font-semibold text-aura-muted transition hover:text-aura-text">
                    {{ t('audios.audiosAba') }}
                </Link>
            </div>

            <div class="mt-8 flex flex-wrap items-end justify-between gap-4">
                <h1 class="font-display text-4xl font-semibold tracking-tight text-aura-text">{{ t('cursos.titulo') }}</h1>
                <form class="w-full sm:w-72" @submit.prevent="filtrar">
                    <input
                        v-model="busca"
                        type="search"
                        :placeholder="t('cursos.buscar')"
                        class="w-full rounded-full border-aura-line bg-aura-surface text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                    />
                </form>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <Link
                    :href="route('cursos')"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                    :class="!filtros.categoria ? 'border border-aura-gold bg-aura-gold/10 text-aura-gold' : 'border border-aura-line text-aura-muted hover:text-aura-text'"
                >
                    {{ t('cursos.todos') }}
                </Link>
                <Link
                    v-for="cat in categorias"
                    :key="cat.slug"
                    :href="route('cursos', { categoria: cat.slug })"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                    :class="filtros.categoria === cat.slug ? 'border border-aura-gold bg-aura-gold/10 text-aura-gold' : 'border border-aura-line text-aura-muted hover:text-aura-text'"
                >
                    {{ cat.nome }}
                </Link>
            </div>

            <div v-if="cursos.length" class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <Link
                    v-for="curso in cursos"
                    :key="curso.id"
                    :href="route('curso', curso.slug)"
                    class="group block"
                >
                    <div class="relative aspect-[3/4] overflow-hidden rounded-2xl border border-aura-line bg-aura-surface transition group-hover:border-aura-gold/60">
                        <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <h3 class="absolute inset-x-0 bottom-0 p-3 font-display text-lg font-semibold leading-tight text-aura-text">{{ curso.titulo }}</h3>
                    </div>
                </Link>
            </div>
            <p v-else class="mt-10 text-aura-muted">{{ t('cursos.vazio') }}</p>
        </div>
    </PainelLayout>
</template>
