<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    curso: Object,
});

function formatarDuracao(segundos) {
    const m = Math.floor(segundos / 60);
    const s = segundos % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}
</script>

<template>
    <Head :title="curso.titulo" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('cursos')" class="text-sm text-indigo-600 hover:underline">← Voltar aos cursos</Link>
        </template>

        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-8 text-white">
                <h1 class="text-3xl font-bold">{{ curso.titulo }}</h1>
                <p class="mt-3 max-w-2xl text-indigo-100">{{ curso.descricao }}</p>
                <p v-if="curso.instrutor" class="mt-4 text-sm text-indigo-100">Com {{ curso.instrutor.nome }}</p>
            </div>

            <div class="mt-8 space-y-6">
                <div v-for="(modulo, mi) in curso.modulos" :key="mi" class="rounded-xl border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-800">{{ modulo.titulo }}</h2>
                    <ul class="divide-y divide-gray-100">
                        <li v-for="aula in modulo.aulas" :key="aula.id">
                            <Link
                                :href="route('aula', aula.id)"
                                class="flex items-center justify-between px-5 py-3 hover:bg-gray-50"
                            >
                                <span class="flex items-center gap-3">
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-xs"
                                        :class="aula.concluida ? 'bg-emerald-500 text-white' : 'border border-gray-300 text-gray-400'"
                                    >
                                        <span v-if="aula.concluida">✓</span>
                                    </span>
                                    <span class="text-gray-800">{{ aula.titulo }}</span>
                                </span>
                                <span class="text-sm text-gray-400">{{ formatarDuracao(aula.duracao) }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
