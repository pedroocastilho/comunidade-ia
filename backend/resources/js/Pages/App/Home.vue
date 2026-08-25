<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    jornada: Object,
    atividades: Object,
    checkin_hoje: Object,
    progresso_semana: Array,
    apelido: String,
});

const saudacao = computed(() => {
    const hora = new Date().getHours();
    if (hora < 12) return t('dia.bomDia');
    if (hora < 18) return t('dia.boaTarde');
    return t('dia.boaNoite');
});

const feitas = computed(() => {
    if (!props.atividades) return 0;
    return [
        props.atividades.ritual?.concluido,
        props.atividades.aula?.concluida,
        props.atividades.acao?.concluida,
    ].filter(Boolean).length;
});

const total = computed(() => {
    if (!props.atividades) return 0;
    return [props.atividades.ritual, props.atividades.aula, props.atividades.acao].filter(Boolean).length;
});

const minutos = (segundos) => Math.max(1, Math.round((segundos ?? 0) / 60));

function concluir(tipo) {
    router.post(route('jornada.atividade'), { tipo }, { preserveScroll: true });
}

const humor = ref(props.checkin_hoje?.humor ?? null);
const nota = ref('');
const emojis = ['😞', '😐', '🙂', '😍', '🤩'];

function enviarCheckin() {
    if (!humor.value) return;
    router.post(route('checkin'), { humor: humor.value, texto: nota.value || null }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('nav.inicio')" />

    <PainelLayout>
        <div class="mx-auto max-w-3xl px-5 py-8 lg:py-12">
            <!-- Saudacao -->
            <p class="text-sm uppercase tracking-widest text-aura-muted">{{ saudacao }},</p>
            <div class="mt-1 flex flex-wrap items-baseline justify-between gap-3">
                <h1 class="font-display text-4xl font-semibold text-aura-text lg:text-5xl">{{ apelido }}</h1>
                <div v-if="jornada" class="flex items-center gap-2 rounded-full border border-aura-gold/40 bg-aura-surface px-4 py-1.5">
                    <AppIcon name="flame" class="h-4 w-4 text-aura-gold" />
                    <span class="text-sm font-semibold text-aura-gold">{{ t('dia.diaN') }} {{ jornada.dia }}</span>
                    <span class="text-xs text-aura-muted">· {{ jornada.etapa }}</span>
                </div>
            </div>

            <!-- Jornada concluida -->
            <div v-if="jornada && jornada.status === 'concluida'" class="mt-10 rounded-2xl border border-aura-gold/40 bg-aura-surface p-8 text-center">
                <p class="font-display text-2xl text-aura-gold">{{ t('dia.jornadaConcluida') }}</p>
            </div>

            <!-- Sem jornada -->
            <div v-else-if="!jornada" class="mt-10 rounded-2xl border border-aura-line bg-aura-surface p-8 text-center">
                <h2 class="font-display text-2xl text-aura-text">{{ t('dia.semJornadaTitulo') }}</h2>
                <p class="mt-2 text-aura-muted">{{ t('dia.semJornadaTexto') }}</p>
                <Link :href="route('cursos')" class="mt-6 inline-block rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-7 py-3 font-semibold text-aura-black">
                    {{ t('dia.irBiblioteca') }}
                </Link>
            </div>

            <!-- Plano do dia -->
            <template v-else>
                <section class="mt-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-widest text-aura-muted">{{ t('dia.suaJornada') }}</h2>
                        <span class="text-sm tabular-nums text-aura-muted">{{ feitas }}/{{ total }}</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-aura-raised">
                        <div class="h-full rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light transition-all duration-500" :style="{ width: total ? (feitas / total) * 100 + '%' : '0%' }" />
                    </div>

                    <div class="mt-5 space-y-3">
                        <!-- Ritual -->
                        <div v-if="atividades.ritual" class="flex items-center gap-4 rounded-2xl border border-aura-line bg-aura-surface p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" :class="atividades.ritual.concluido ? 'bg-aura-gold text-aura-black' : 'border border-aura-gold/50 text-aura-gold'">
                                <AppIcon :name="atividades.ritual.concluido ? 'check' : 'headphones'" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-aura-gold">{{ t('dia.ritual') }}</p>
                                <p class="truncate text-aura-text">{{ atividades.ritual.titulo }}</p>
                                <p class="text-xs text-aura-muted">{{ minutos(atividades.ritual.duracao) }} {{ t('dia.min') }}</p>
                            </div>
                            <Link v-if="!atividades.ritual.concluido" :href="route('audio', atividades.ritual.id)" class="shrink-0 rounded-full border border-aura-gold/60 px-4 py-2 text-sm font-semibold text-aura-gold transition hover:bg-aura-gold hover:text-aura-black">
                                {{ t('dia.ouvir') }}
                            </Link>
                        </div>

                        <!-- Aula -->
                        <div v-if="atividades.aula" class="flex items-center gap-4 rounded-2xl border border-aura-line bg-aura-surface p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" :class="atividades.aula.concluida ? 'bg-aura-gold text-aura-black' : 'border border-aura-gold/50 text-aura-gold'">
                                <AppIcon :name="atividades.aula.concluida ? 'check' : 'play'" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-aura-gold">{{ t('dia.aula') }}</p>
                                <p class="truncate text-aura-text">{{ atividades.aula.titulo }}</p>
                                <p class="text-xs text-aura-muted">{{ minutos(atividades.aula.duracao) }} {{ t('dia.min') }}</p>
                            </div>
                            <Link v-if="!atividades.aula.concluida" :href="route('aula', atividades.aula.id)" class="shrink-0 rounded-full border border-aura-gold/60 px-4 py-2 text-sm font-semibold text-aura-gold transition hover:bg-aura-gold hover:text-aura-black">
                                {{ t('dia.assistir') }}
                            </Link>
                        </div>

                        <!-- Acao -->
                        <div v-if="atividades.acao" class="flex items-center gap-4 rounded-2xl border border-aura-line bg-aura-surface p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" :class="atividades.acao.concluida ? 'bg-aura-gold text-aura-black' : 'border border-aura-gold/50 text-aura-gold'">
                                <AppIcon :name="atividades.acao.concluida ? 'check' : 'star'" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-aura-gold">{{ t('dia.acao') }}</p>
                                <p class="text-aura-text">{{ atividades.acao.texto }}</p>
                            </div>
                            <button v-if="!atividades.acao.concluida" type="button" class="shrink-0 rounded-full border border-aura-gold/60 px-4 py-2 text-sm font-semibold text-aura-gold transition hover:bg-aura-gold hover:text-aura-black" @click="concluir('acao')">
                                {{ t('dia.concluir') }}
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Falar com Aura (habilita na Fase 4) -->
                <section class="mt-8">
                    <div class="flex items-center gap-4 rounded-2xl border border-aura-gold/30 bg-gradient-to-r from-aura-surface to-aura-raised p-5" :title="t('nav.emBreve')">
                        <AppIcon name="sparkles" class="h-7 w-7 text-aura-gold" />
                        <div class="flex-1">
                            <p class="font-semibold text-aura-text">{{ t('dia.falarComAura') }}</p>
                            <p class="text-sm text-aura-muted">{{ t('dia.auraEmBreve') }}</p>
                        </div>
                    </div>
                </section>

                <!-- Progresso semanal + check-in -->
                <section class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-aura-line bg-aura-surface p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-aura-muted">{{ t('dia.progressoSemanal') }}</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span
                                v-for="(feito, i) in progresso_semana"
                                :key="i"
                                class="h-3.5 w-3.5 rounded-full"
                                :class="feito ? 'bg-aura-gold' : 'bg-aura-raised'"
                            />
                        </div>
                        <p class="mt-4 text-sm text-aura-muted">
                            {{ t('dia.proximoMarco') }}: <span class="text-aura-text">{{ t('dia.fimEtapa') }} {{ jornada.etapa }}</span>
                        </p>
                    </div>

                    <div class="rounded-2xl border border-aura-line bg-aura-surface p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-aura-muted">{{ t('dia.comoFoiSeuDia') }}</p>
                        <template v-if="!checkin_hoje">
                            <div class="mt-3 flex gap-2">
                                <button
                                    v-for="(emoji, i) in emojis"
                                    :key="i"
                                    type="button"
                                    class="flex h-11 w-11 items-center justify-center rounded-full text-xl transition"
                                    :class="humor === i + 1 ? 'bg-aura-gold/20 ring-1 ring-aura-gold' : 'bg-aura-raised hover:bg-aura-line'"
                                    @click="humor = i + 1"
                                >
                                    {{ emoji }}
                                </button>
                            </div>
                            <input
                                v-model="nota"
                                type="text"
                                :placeholder="t('dia.notaPlaceholder')"
                                class="mt-3 w-full rounded-xl border-aura-line bg-aura-raised text-sm text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                            />
                            <button
                                type="button"
                                class="mt-3 w-full rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light py-2.5 text-sm font-semibold text-aura-black transition hover:opacity-90 disabled:opacity-40"
                                :disabled="!humor"
                                @click="enviarCheckin"
                            >
                                {{ t('dia.enviarCheckin') }}
                            </button>
                        </template>
                        <p v-else class="mt-4 text-aura-gold">
                            {{ emojis[checkin_hoje.humor - 1] }} {{ t('dia.checkinFeito') }}
                        </p>
                    </div>
                </section>
            </template>
        </div>
    </PainelLayout>
</template>
