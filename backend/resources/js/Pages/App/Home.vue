<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    destaque: Object,
    trilhas: Array,
    avisos: Array,
});
</script>

<template>
    <Head title="Início" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Início</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Avisos -->
            <section v-if="avisos.length" class="space-y-3">
                <div
                    v-for="aviso in avisos"
                    :key="aviso.id"
                    class="rounded-xl border border-indigo-100 bg-indigo-50 p-4"
                >
                    <h3 class="font-semibold text-indigo-900">{{ aviso.titulo }}</h3>
                    <p class="text-sm text-indigo-800">{{ aviso.corpo }}</p>
                </div>
            </section>

            <!-- Destaque -->
            <section v-if="destaque" class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-8 text-white shadow-lg">
                <p class="text-sm uppercase tracking-wide text-indigo-200">Em destaque</p>
                <h1 class="mt-2 text-3xl font-bold">{{ destaque.titulo }}</h1>
                <p class="mt-3 max-w-2xl text-indigo-100">{{ destaque.descricao }}</p>
                <Link
                    :href="route('curso', destaque.slug)"
                    class="mt-6 inline-flex rounded-full bg-white px-6 py-2 font-semibold text-indigo-700 hover:bg-indigo-50"
                >
                    Começar
                </Link>
            </section>

            <!-- Trilhas por categoria -->
            <section v-for="trilha in trilhas" :key="trilha.slug" class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ trilha.nome }}</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <Link
                        v-for="curso in trilha.cursos"
                        :key="curso.id"
                        :href="route('curso', curso.slug)"
                        class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                    >
                        <div class="aspect-video bg-gradient-to-br from-indigo-100 to-violet-100">
                            <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover" />
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-gray-800 group-hover:text-indigo-700">{{ curso.titulo }}</h3>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
