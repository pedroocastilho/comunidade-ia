<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, router } from '@inertiajs/vue3';

const { t } = useI18n();

const props = defineProps({
    audios: Array,
    filtros: Object,
});

const tipos = ['frequencia', 'meditacao', 'ritual'];

function filtrar(tipo) {
    router.get(route('audios'), tipo ? { tipo } : {}, { preserveState: true });
}

const minutos = (segundos) => Math.max(1, Math.round((segundos ?? 0) / 60));
</script>

<template>
    <Head :title="t('audios.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-5xl px-5 py-8 lg:py-12">
            <!-- Abas Cursos / Audios -->
            <div class="flex items-center gap-6 border-b border-aura-line">
                <Link :href="route('cursos')" class="pb-3 text-sm font-semibold text-aura-muted transition hover:text-aura-text">
                    {{ t('audios.cursosAba') }}
                </Link>
                <span class="border-b-2 border-aura-gold pb-3 text-sm font-semibold text-aura-gold">
                    {{ t('audios.audiosAba') }}
                </span>
            </div>

            <h1 class="mt-8 font-display text-4xl font-semibold text-aura-text">{{ t('audios.titulo') }}</h1>

            <!-- Filtro por tipo -->
            <div class="mt-5 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                    :class="!filtros.tipo ? 'border-aura-gold bg-aura-gold/10 text-aura-gold' : 'border-aura-line text-aura-muted hover:text-aura-text'"
                    @click="filtrar(null)"
                >
                    {{ t('audios.todos') }}
                </button>
                <button
                    v-for="tipo in tipos"
                    :key="tipo"
                    type="button"
                    class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                    :class="filtros.tipo === tipo ? 'border-aura-gold bg-aura-gold/10 text-aura-gold' : 'border-aura-line text-aura-muted hover:text-aura-text'"
                    @click="filtrar(tipo)"
                >
                    {{ t(`audios.${tipo}`) }}
                </button>
            </div>

            <p v-if="!audios.length" class="mt-10 text-aura-muted">{{ t('audios.vazio') }}</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="audio in audios"
                    :key="audio.id"
                    :href="route('audio', audio.id)"
                    class="group overflow-hidden rounded-2xl border border-aura-line bg-aura-surface transition hover:border-aura-gold/60"
                >
                    <div class="relative flex aspect-video items-center justify-center bg-gradient-to-br from-aura-raised to-aura-deep">
                        <img v-if="audio.capa_url" :src="audio.capa_url" :alt="audio.titulo" class="absolute inset-0 h-full w-full object-cover" />
                        <AppIcon v-else name="headphones" class="h-10 w-10 text-aura-gold/60 transition group-hover:text-aura-gold" />
                        <span v-if="audio.concluido" class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-aura-gold text-aura-black">
                            <AppIcon name="check" class="h-4 w-4" />
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-aura-gold">{{ t(`audios.${audio.tipo}`) }}</p>
                        <p class="mt-1 font-semibold text-aura-text">{{ audio.titulo }}</p>
                        <p class="mt-1 text-xs text-aura-muted">{{ minutos(audio.duracao) }} {{ t('dia.min') }}</p>
                    </div>
                </Link>
            </div>
        </div>
    </PainelLayout>
</template>
