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

    <div class="min-h-screen bg-[#0A0A0A] px-6 py-12 text-[#F5F0E8]">
        <div class="mx-auto max-w-2xl">
            <p class="text-center text-sm uppercase tracking-widest text-[#9C948A]">
                {{ t('score.saudacao') }} {{ apelido }}
            </p>

            <!-- Score global -->
            <div class="mt-8 flex justify-center">
                <div class="relative flex h-52 w-52 items-center justify-center rounded-full border border-[#C9A24B]/40 bg-gradient-to-b from-[#141414] to-[#0A0A0A] shadow-[0_0_80px_-20px_rgba(201,162,75,0.55)]">
                    <div class="text-center">
                        <div class="font-display text-6xl font-semibold text-[#E5C878]">{{ exibido }}</div>
                        <div class="mt-1 text-xs uppercase tracking-widest text-[#9C948A]">{{ t('score.titulo') }}</div>
                    </div>
                </div>
            </div>

            <!-- Mapa de Manifestacao -->
            <h2 class="mt-14 font-display text-2xl font-semibold">{{ t('score.mapa') }}</h2>
            <div class="mt-6 space-y-5">
                <div v-for="barra in barras" :key="barra.slug">
                    <div class="mb-1.5 flex items-baseline justify-between text-sm">
                        <span :class="barra.prioritaria ? 'font-semibold text-[#E5C878]' : 'text-[#F5F0E8]'">
                            {{ barra.nome }}
                            <span v-if="barra.prioritaria" class="ml-2 rounded-full border border-[#C9A24B]/50 px-2 py-0.5 text-[11px] uppercase tracking-wider text-[#C9A24B]">
                                {{ t('score.prioridade') }}
                            </span>
                            <span v-else-if="barra.atencao" class="ml-2 rounded-full border border-[#5C564E] px-2 py-0.5 text-[11px] uppercase tracking-wider text-[#9C948A]">
                                {{ t('score.atencao') }}
                            </span>
                        </span>
                        <span class="tabular-nums text-[#9C948A]">{{ barra.valor }}</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-[#1A1A1A]">
                        <div
                            class="h-full rounded-full transition-all duration-1000 ease-out"
                            :class="barra.prioritaria ? 'bg-gradient-to-r from-[#C9A24B] to-[#E5C878]' : 'bg-[#C9A24B]/45'"
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
                    class="rounded-xl border border-[#2A2A2A] bg-[#141414] p-4 text-[#D9D2C7]"
                >
                    <span class="mr-2 text-[#C9A24B]">✦</span>{{ t(`score.padroes.${padrao}`) }}
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-12 text-center">
                <Link
                    :href="route('home')"
                    class="inline-block rounded-full bg-gradient-to-r from-[#C9A24B] to-[#E5C878] px-10 py-4 text-lg font-semibold text-[#0A0A0A] transition hover:opacity-90"
                >
                    {{ t('score.comecar') }}
                </Link>
            </div>
        </div>
    </div>
</template>
