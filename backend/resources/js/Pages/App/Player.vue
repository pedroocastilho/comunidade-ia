<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    aula: Object,
    curso: Object,
});

const form = useForm({});

function concluir() {
    form.post(route('aula.concluir', props.aula.id), { preserveScroll: true });
}
</script>

<template>
    <Head :title="aula.titulo" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('curso', curso.slug)" class="text-sm text-indigo-600 hover:underline">
                ← {{ curso.titulo }}
            </Link>
        </template>

        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-black shadow-lg">
                <div class="aspect-video">
                    <iframe
                        v-if="aula.video_embed_url"
                        :src="aula.video_embed_url"
                        class="h-full w-full"
                        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                    <div v-else class="flex h-full items-center justify-center text-gray-400">
                        Vídeo indisponível
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ aula.titulo }}</h1>
                    <p class="mt-2 max-w-2xl text-gray-600">{{ aula.descricao }}</p>
                </div>
                <button
                    class="rounded-full px-6 py-2 font-semibold transition"
                    :class="aula.concluida
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-indigo-600 text-white hover:bg-indigo-700'"
                    :disabled="form.processing"
                    @click="concluir"
                >
                    {{ aula.concluida ? 'Concluída ✓' : 'Marcar como concluída' }}
                </button>
            </div>

            <a
                v-if="aula.material_url"
                :href="aula.material_url"
                target="_blank"
                class="mt-4 inline-flex text-indigo-600 hover:underline"
            >
                Baixar material da aula
            </a>
        </div>
    </AuthenticatedLayout>
</template>
