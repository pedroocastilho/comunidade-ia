<script setup>
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    <Head title="Cursos" />

    <PainelLayout>
        <div class="mx-auto max-w-7xl px-5 py-8 lg:px-10 lg:py-12">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h1 class="font-display text-4xl font-extrabold tracking-tight text-gray-900">Cursos</h1>
                <form class="w-full sm:w-72" @submit.prevent="filtrar">
                    <input
                        v-model="busca"
                        type="search"
                        placeholder="Buscar cursos..."
                        class="w-full rounded-full border-gray-200 bg-white focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </form>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <Link
                    :href="route('cursos')"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                    :class="!filtros.categoria ? 'bg-gray-900 text-white' : 'border border-gray-300 text-gray-600 hover:bg-white'"
                >
                    Todos
                </Link>
                <Link
                    v-for="cat in categorias"
                    :key="cat.slug"
                    :href="route('cursos', { categoria: cat.slug })"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                    :class="filtros.categoria === cat.slug ? 'bg-gray-900 text-white' : 'border border-gray-300 text-gray-600 hover:bg-white'"
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
                    <div class="relative aspect-[3/4] overflow-hidden rounded-2xl bg-emerald-700 shadow-sm transition group-hover:shadow-lg">
                        <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                        <h3 class="absolute inset-x-0 bottom-0 p-3 font-display text-base font-bold leading-tight text-white">{{ curso.titulo }}</h3>
                    </div>
                </Link>
            </div>
            <p v-else class="mt-10 text-gray-500">Nenhum curso encontrado.</p>
        </div>
    </PainelLayout>
</template>
