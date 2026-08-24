<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { t } = useI18n();

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

    <PainelLayout>
        <div class="grid lg:grid-cols-[minmax(0,1fr)_360px]">
            <!-- Player -->
            <div class="px-5 py-6 lg:px-10 lg:py-8">
                <Link :href="route('curso', curso.slug)" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-emerald-700">
                    <AppIcon name="arrow-left" class="h-4 w-4" /> {{ curso.titulo }}
                </Link>

                <div class="mt-4 overflow-hidden rounded-2xl bg-gray-900 shadow-sm">
                    <div class="aspect-video">
                        <iframe
                            v-if="aula.video_embed_url"
                            :src="aula.video_embed_url"
                            class="h-full w-full"
                            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                        <div v-else class="flex h-full items-center justify-center text-gray-500">
                            {{ t('player.indisponivel') }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="font-display text-2xl font-bold tracking-tight text-gray-900">{{ aula.titulo }}</h1>
                        <p class="mt-2 max-w-2xl text-gray-600">{{ aula.descricao }}</p>
                    </div>
                    <button
                        class="inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3 font-semibold transition"
                        :class="aula.concluida ? 'bg-emerald-100 text-emerald-800' : 'bg-emerald-600 text-white hover:bg-emerald-700'"
                        :disabled="form.processing"
                        @click="concluir"
                    >
                        <AppIcon name="check" class="h-4 w-4" />
                        {{ aula.concluida ? t('player.concluida') : t('player.concluir') }}
                    </button>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6">
                    <Link
                        v-if="aula.anterior_id"
                        :href="route('aula', aula.anterior_id)"
                        class="text-sm font-semibold text-gray-600 transition hover:text-emerald-700"
                    >
                        ← {{ t('player.anterior') }}
                    </Link>
                    <span v-else></span>
                    <Link
                        v-if="aula.proxima_id"
                        :href="route('aula', aula.proxima_id)"
                        class="text-sm font-semibold text-gray-600 transition hover:text-emerald-700"
                    >
                        {{ t('player.proxima') }} →
                    </Link>
                </div>

                <a
                    v-if="aula.material_url"
                    :href="aula.material_url"
                    target="_blank"
                    class="mt-4 inline-flex font-medium text-emerald-700 hover:underline"
                >
                    {{ t('player.material') }}
                </a>
            </div>

            <!-- Playlist -->
            <aside class="border-t border-gray-200 bg-white lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:overflow-y-auto lg:border-l lg:border-t-0">
                <div class="border-b border-gray-100 p-5">
                    <h2 class="font-display font-bold text-gray-900">{{ curso.titulo }}</h2>
                    <p v-if="curso.instrutor" class="mt-1 text-sm text-gray-500">{{ t('common.com') }} {{ curso.instrutor }}</p>
                </div>

                <div v-for="(modulo, mi) in curso.modulos" :key="mi" class="border-b border-gray-100 py-3">
                    <h3 class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-gray-400">{{ modulo.titulo }}</h3>
                    <Link
                        v-for="a in modulo.aulas"
                        :key="a.id"
                        :href="route('aula', a.id)"
                        class="flex items-center gap-3 px-5 py-2.5 transition"
                        :class="a.id === aula.id ? 'bg-emerald-50' : 'hover:bg-gray-50'"
                    >
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px]"
                            :class="a.concluida ? 'bg-emerald-600 text-white' : (a.id === aula.id ? 'bg-emerald-600 text-white' : 'border border-gray-300 text-gray-400')"
                        >
                            <AppIcon v-if="a.concluida" name="check" class="h-3 w-3" />
                            <AppIcon v-else-if="a.id === aula.id" name="play" class="h-3 w-3" />
                        </span>
                        <span
                            class="flex-1 text-sm"
                            :class="a.id === aula.id ? 'font-semibold text-emerald-800' : 'text-gray-700'"
                        >{{ a.titulo }}</span>
                    </Link>
                </div>
            </aside>
        </div>
    </PainelLayout>
</template>
