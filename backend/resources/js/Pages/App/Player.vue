<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

// Carregado sob demanda: o pdf.js so entra no bundle de quem abre aula em PDF
const PdfViewer = defineAsyncComponent(() => import('@/Components/PdfViewer.vue'));

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
                <Link :href="route('curso', curso.slug)" class="-my-2 inline-flex min-h-11 items-center gap-2 py-2 text-sm font-semibold text-aura-muted transition hover:text-aura-gold">
                    <AppIcon name="arrow-left" class="h-4 w-4" /> {{ curso.titulo }}
                </Link>

                <div v-if="aula.video_embed_url || aula.video_file_url" class="mt-4 overflow-hidden rounded-2xl bg-black shadow-sm">
                    <div class="aspect-video">
                        <iframe
                            v-if="aula.video_embed_url"
                            :src="aula.video_embed_url"
                            class="h-full w-full"
                            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                        <video
                            v-else
                            :src="aula.video_file_url"
                            class="h-full w-full"
                            controls
                            controlslist="nodownload"
                            playsinline
                        ></video>
                    </div>
                </div>

                <!-- Aula em material de leitura: PDF renderizado dentro da plataforma -->
                <div v-else-if="aula.material_url" class="mt-4 rounded-2xl border border-aura-line bg-aura-surface p-3 sm:p-5">
                    <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-aura-gold">
                        <AppIcon name="book" class="h-4 w-4" />
                        {{ t('player.aulaLeitura') }}
                    </div>
                    <PdfViewer :url="aula.material_url" />
                </div>

                <div v-else class="mt-4 overflow-hidden rounded-2xl bg-black shadow-sm">
                    <div class="aspect-video flex items-center justify-center text-aura-muted">
                        {{ t('player.indisponivel') }}
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="font-display text-2xl font-bold tracking-tight text-aura-text">{{ aula.titulo }}</h1>
                        <p class="mt-2 max-w-2xl text-aura-muted">{{ aula.descricao }}</p>
                    </div>
                    <button
                        class="inline-flex shrink-0 items-center gap-2 rounded-full px-6 py-3 font-semibold transition"
                        :class="aula.concluida ? 'bg-aura-gold/15 text-aura-gold' : 'bg-aura-gold text-aura-black hover:bg-aura-gold'"
                        :disabled="form.processing"
                        @click="concluir"
                    >
                        <AppIcon name="check" class="h-4 w-4" />
                        {{ aula.concluida ? t('player.concluida') : t('player.concluir') }}
                    </button>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-aura-line pt-6">
                    <Link
                        v-if="aula.anterior_id"
                        :href="route('aula', aula.anterior_id)"
                        class="-mx-3 inline-flex min-h-11 items-center rounded-full px-3 py-2 text-sm font-semibold text-aura-muted transition hover:text-aura-gold"
                    >
                        ← {{ t('player.anterior') }}
                    </Link>
                    <span v-else></span>
                    <Link
                        v-if="aula.proxima_id"
                        :href="route('aula', aula.proxima_id)"
                        class="-mx-3 inline-flex min-h-11 items-center rounded-full px-3 py-2 text-sm font-semibold text-aura-muted transition hover:text-aura-gold"
                    >
                        {{ t('player.proxima') }} →
                    </Link>
                </div>

                <a
                    v-if="aula.material_url && (aula.video_embed_url || aula.video_file_url)"
                    :href="aula.material_url"
                    target="_blank"
                    class="mt-4 inline-flex font-medium text-aura-gold hover:underline"
                >
                    {{ t('player.material') }}
                </a>
            </div>

            <!-- Playlist -->
            <aside class="border-t border-aura-line bg-aura-surface lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:overflow-y-auto lg:border-l lg:border-t-0">
                <div class="border-b border-aura-line p-5">
                    <h2 class="font-display font-bold text-aura-text">{{ curso.titulo }}</h2>
                    <p v-if="curso.instrutor" class="mt-1 text-sm text-aura-muted">{{ t('common.com') }} {{ curso.instrutor }}</p>
                </div>

                <div v-for="(modulo, mi) in curso.modulos" :key="mi" class="border-b border-aura-line py-3">
                    <h3 class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-aura-faint">{{ modulo.titulo }}</h3>
                    <Link
                        v-for="a in modulo.aulas"
                        :key="a.id"
                        :href="route('aula', a.id)"
                        class="flex min-h-12 items-center gap-3 px-5 py-3 transition"
                        :class="a.id === aula.id ? 'bg-aura-gold/10' : 'hover:bg-aura-raised'"
                    >
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px]"
                            :class="a.concluida ? 'bg-aura-gold text-aura-black' : (a.id === aula.id ? 'bg-aura-gold text-aura-black' : 'border border-aura-line text-aura-faint')"
                        >
                            <AppIcon v-if="a.concluida" name="check" class="h-3 w-3" />
                            <AppIcon v-else-if="a.id === aula.id" name="play" class="h-3 w-3" />
                        </span>
                        <span
                            class="min-w-0 flex-1 break-words text-sm"
                            :class="a.id === aula.id ? 'font-semibold text-aura-gold' : 'text-aura-text'"
                        >{{ a.titulo }}</span>
                    </Link>
                </div>
            </aside>
        </div>
    </PainelLayout>
</template>
