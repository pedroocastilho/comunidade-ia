<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    curso: Object,
    premium_bloqueado: Boolean,
    checkout_url: String,
});

const aulas = computed(() => props.curso.modulos.flatMap((m) => m.aulas));
const total = computed(() => aulas.value.length);
const concluidas = computed(() => aulas.value.filter((a) => a.concluida).length);
const percentual = computed(() => (total.value ? Math.round((concluidas.value / total.value) * 100) : 0));
const primeira = computed(() => aulas.value[0]);

function formatarDuracao(segundos) {
    const m = Math.floor(segundos / 60);
    const s = segundos % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}
</script>

<template>
    <Head :title="curso.titulo" />

    <PainelLayout>
        <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
            <!-- Hero fixo -->
            <aside class="bg-aura-deep px-6 py-10 text-aura-text lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:px-10 lg:py-14">
                <Link :href="route('cursos')" class="inline-flex items-center gap-2 text-sm font-semibold text-aura-muted transition hover:text-aura-text">
                    <AppIcon name="arrow-left" class="h-4 w-4" /> {{ t('common.voltar') }}
                </Link>

                <h1 class="mt-8 break-words font-display text-3xl font-extrabold leading-[0.95] tracking-tight sm:text-4xl lg:text-6xl">
                    {{ curso.titulo }}
                </h1>
                <p class="mt-5 max-w-md text-gray-300">{{ curso.descricao }}</p>

                <div class="mt-6 flex items-center gap-4 text-sm text-aura-faint">
                    <span v-if="curso.instrutor">{{ t('common.com') }} {{ curso.instrutor.nome }}</span>
                    <span class="h-1 w-1 rounded-full bg-gray-600"></span>
                    <span>{{ total }} {{ t('common.aulas') }}</span>
                </div>

                <!-- Premium sem compra: oferta no lugar do CTA -->
                <div v-if="premium_bloqueado" class="mt-8 max-w-md rounded-2xl border border-aura-gold/40 bg-aura-gold/5 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-aura-gold">🔒 {{ t('premium.selo') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-aura-muted">{{ t('premium.bloqueadoTexto') }}</p>
                    <a
                        v-if="checkout_url"
                        :href="checkout_url"
                        target="_blank"
                        rel="noopener"
                        class="mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-7 py-3 font-semibold text-aura-black transition hover:opacity-90"
                    >
                        {{ t('premium.cta') }}
                    </a>
                    <p v-else class="mt-4 text-sm font-semibold text-aura-gold">{{ t('premium.indisponivel') }}</p>
                </div>

                <Link
                    v-else-if="primeira"
                    :href="route('aula', primeira.id)"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-7 py-3 font-semibold text-aura-black transition hover:opacity-90"
                >
                    <AppIcon name="play" class="h-4 w-4" />
                    {{ concluidas > 0 ? t('common.continuar') : t('common.iniciar') }}
                </Link>
            </aside>

            <!-- Aulas -->
            <div class="px-6 py-10 lg:px-10 lg:py-14">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-aura-muted">{{ t('curso.progresso') }}</span>
                    <span class="text-sm font-bold text-aura-gold">{{ percentual }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-aura-gold transition-all" :style="{ width: percentual + '%' }"></div>
                </div>

                <div class="mt-10 space-y-8">
                    <div v-for="(modulo, mi) in curso.modulos" :key="mi">
                        <h2 class="font-display text-lg font-bold tracking-tight text-aura-text">{{ modulo.titulo }}</h2>
                        <ul class="mt-3 divide-y divide-aura-line overflow-hidden rounded-2xl border border-aura-line bg-aura-surface">
                            <li v-for="aula in modulo.aulas" :key="aula.id">
                                <component
                                    :is="premium_bloqueado ? 'div' : Link"
                                    :href="premium_bloqueado ? undefined : route('aula', aula.id)"
                                    class="flex items-center gap-3 px-4 py-4 transition sm:gap-4 sm:px-5"
                                    :class="premium_bloqueado ? 'cursor-default opacity-60' : 'hover:bg-aura-raised'"
                                >
                                    <span
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                        :class="aula.concluida ? 'bg-aura-gold text-aura-black' : 'bg-aura-gold/10 text-aura-gold'"
                                    >
                                        <span v-if="premium_bloqueado" class="text-xs">🔒</span>
                                        <AppIcon v-else :name="aula.concluida ? 'check' : (aula.leitura ? 'book' : 'play')" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 flex-1 break-words font-medium text-aura-text">{{ aula.titulo }}</span>
                                    <span
                                        v-if="aula.leitura"
                                        class="shrink-0 whitespace-nowrap rounded-full border border-aura-gold/40 bg-aura-gold/10 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-aura-gold"
                                    >
                                        {{ t('curso.leitura') }}
                                    </span>
                                    <span v-else class="shrink-0 whitespace-nowrap text-sm text-aura-faint">{{ formatarDuracao(aula.duracao) }}</span>
                                </component>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </PainelLayout>
</template>
