<script setup>
import { useI18n } from '@/useI18n';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    score: Object,
    dimensoes: Array,
    apelido: String,
});

// Animacao de contagem do score global.
const exibido = ref(0);
const revelado = ref(false);

onMounted(() => {
    const alvo = props.score.score_global;
    const duracao = 1400;
    const inicio = performance.now();

    function tick(agora) {
        const p = Math.min((agora - inicio) / duracao, 1);
        exibido.value = Math.round(alvo * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
    setTimeout(() => (revelado.value = true), 500);
});

const barras = computed(() =>
    props.dimensoes.map((d) => ({
        slug: d.slug,
        nome: t(`score.dimensoes.${d.slug}`),
        valor: props.score.scores_dimensoes[d.slug] ?? 0,
        prioritaria: d.slug === props.score.prioritaria,
        atencao: d.slug === props.score.ponto_atencao,
    }))
);
</script>

<template>
    <Head :title="t('score.titulo')" />

    <div class="min-h-screen bg-aura-black px-6 py-12 text-aura-text">
        <div class="mx-auto max-w-2xl">
            <p class="text-center text-sm uppercase tracking-widest text-aura-muted">
                {{ t('score.saudacao') }} {{ apelido }}
            </p>

            <!-- Score global -->
            <div class="mt-8 flex justify-center">
                <div class="relative flex h-52 w-52 items-center justify-center rounded-full border border-aura-gold/40 bg-gradient-to-b from-aura-surface to-aura-black shadow-[0_0_80px_-20px_rgba(201,162,75,0.45)]">
                    <div class="text-center">
                        <div class="font-display text-6xl font-semibold text-aura-gold-light">{{ exibido }}</div>
                        <div class="mt-1 text-xs uppercase tracking-widest text-aura-muted">{{ t('score.titulo') }}</div>
                    </div>
                </div>
            </div>

            <!-- Mapa de Manifestacao -->
            <h2 class="mt-14 font-display text-2xl font-semibold">{{ t('score.mapa') }}</h2>
            <div class="mt-6 space-y-5">
                <div v-for="barra in barras" :key="barra.slug">
                    <div class="mb-1.5 flex items-baseline justify-between text-sm">
                        <span :class="barra.prioritaria ? 'font-semibold text-aura-gold-light' : 'text-aura-text'">
                            {{ barra.nome }}
                            <span v-if="barra.prioritaria" class="ml-2 rounded-full border border-aura-gold/50 px-2 py-0.5 text-[11px] uppercase tracking-wider text-aura-gold">
                                {{ t('score.prioridade') }}
                            </span>
                            <span v-else-if="barra.atencao" class="ml-2 rounded-full border border-aura-faint px-2 py-0.5 text-[11px] uppercase tracking-wider text-aura-muted">
                                {{ t('score.atencao') }}
                            </span>
                        </span>
                        <span class="tabular-nums text-aura-muted">{{ barra.valor }}</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-aura-raised">
                        <div
                            class="h-full rounded-full transition-all duration-1000 ease-out"
                            :class="barra.prioritaria ? 'bg-gradient-to-r from-aura-gold to-aura-gold-light' : 'bg-aura-gold/45'"
                            :style="{ width: (revelado ? barra.valor : 0) + '%' }"
                        />
                    </div>
                </div>
            </div>

            <!-- Padroes -->
            <div class="mt-10 space-y-3">
                <div
                    v-for="padrao in score.padroes"
                    :key="padrao"
                    class="rounded-xl border border-aura-line bg-aura-surface p-4 text-aura-text"
                >
                    <span class="mr-2 text-aura-gold">✦</span>{{ t(`score.padroes.${padrao}`) }}
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-12 text-center">
                <Link
                    :href="route('home')"
                    class="inline-block rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-10 py-4 text-lg font-semibold text-aura-black transition hover:opacity-90"
                >
                    {{ t('score.comecar') }}
                </Link>
            </div>
        </div>
    </div>
</template>
