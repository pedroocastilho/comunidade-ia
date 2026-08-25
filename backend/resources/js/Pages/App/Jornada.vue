<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    titulo: String,
    dias: Array,
});

// Agrupa os dias por etapa, preservando a ordem.
const etapas = computed(() => {
    const grupos = [];
    for (const dia of props.dias) {
        const ultimo = grupos[grupos.length - 1];
        if (ultimo && ultimo.etapa === dia.etapa) {
            ultimo.dias.push(dia);
        } else {
            grupos.push({ etapa: dia.etapa, dias: [dia] });
        }
    }
    return grupos;
});
</script>

<template>
    <Head :title="t('jornadaPage.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-3xl px-5 py-8 lg:py-12">
            <p class="text-sm uppercase tracking-widest text-aura-muted">{{ t('jornadaPage.titulo') }}</p>
            <h1 class="mt-1 font-display text-4xl font-semibold text-aura-text">{{ titulo ?? '—' }}</h1>

            <p v-if="!dias.length" class="mt-10 text-aura-muted">{{ t('jornadaPage.vazio') }}</p>

            <div v-for="grupo in etapas" :key="grupo.etapa" class="mt-10">
                <h2 class="flex items-center gap-3 text-sm font-semibold uppercase tracking-widest text-aura-gold">
                    <span class="h-px w-8 bg-aura-gold/50" /> {{ grupo.etapa }}
                </h2>
                <ol class="mt-4 space-y-2">
                    <li
                        v-for="dia in grupo.dias"
                        :key="dia.dia"
                        class="flex items-center gap-4 rounded-2xl border p-4"
                        :class="dia.atual ? 'border-aura-gold/60 bg-aura-surface' : dia.concluido ? 'border-aura-line bg-aura-surface/60' : 'border-aura-line/60 bg-transparent opacity-60'"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                            :class="dia.concluido ? 'bg-aura-gold text-aura-black' : dia.atual ? 'border border-aura-gold text-aura-gold' : 'border border-aura-line text-aura-muted'"
                        >
                            <AppIcon v-if="dia.concluido" name="check" class="h-4 w-4" />
                            <template v-else>{{ dia.dia }}</template>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-aura-text">
                                {{ t('jornadaPage.dia') }} {{ dia.dia }}
                                <span v-if="dia.atual" class="ml-2 rounded-full border border-aura-gold/50 px-2 py-0.5 text-[11px] uppercase tracking-wider text-aura-gold">{{ t('jornadaPage.hoje') }}</span>
                            </p>
                            <p v-if="dia.atual || dia.concluido" class="mt-0.5 truncate text-sm text-aura-muted">
                                {{ [dia.ritual, dia.aula, dia.acao].filter(Boolean).join(' · ') }}
                            </p>
                            <p v-else class="mt-0.5 text-sm text-aura-faint">{{ t('jornadaPage.bloqueado') }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </PainelLayout>
</template>
