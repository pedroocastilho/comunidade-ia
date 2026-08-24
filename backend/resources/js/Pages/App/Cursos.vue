<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

function porCategoria(slug) {
    router.get(route('cursos'), { categoria: slug }, { preserveState: true });
}
</script>

<template>
    <Head title="Cursos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Cursos</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-3">
                <form class="flex-1" @submit.prevent="filtrar">
                    <input
                        v-model="busca"
                        type="search"
                        placeholder="Buscar cursos..."
                        class="w-full rounded-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </form>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    :href="route('cursos')"
                    class="rounded-full border border-gray-300 px-4 py-1 text-sm hover:bg-gray-100"
                >
                    Todos
                </Link>
                <button
                    v-for="cat in categorias"
                    :key="cat.slug"
                    class="rounded-full border border-gray-300 px-4 py-1 text-sm hover:bg-gray-100"
                    @click="porCategoria(cat.slug)"
                >
                    {{ cat.nome }}
                </button>
            </div>

            <div v-if="cursos.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <Link
                    v-for="curso in cursos"
                    :key="curso.id"
                    :href="route('curso', curso.slug)"
                    class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                >
                    <div class="aspect-video bg-gradient-to-br from-indigo-100 to-violet-100">
                        <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover" />
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-800 group-hover:text-indigo-700">{{ curso.titulo }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ curso.descricao }}</p>
                    </div>
                </Link>
            </div>
            <p v-else class="text-gray-500">Nenhum curso encontrado.</p>
        </div>
    </AuthenticatedLayout>
</template>
