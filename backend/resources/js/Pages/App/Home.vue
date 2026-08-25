<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import HeroAura from '@/Components/HeroAura.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    jornada: Object,
    jornada_concluida: Boolean,
    objetivos: Array,
    atividades: Object,
    checkin_hoje: Object,
    progresso_semana: Array,
    apelido: String,
    continuar: Array,
    em_alta: Array,
    audios_destaque: Array,
    destaque: Object,
    hero_video: String,
    aura_score: Number,
});

function iniciarJornada(objetivo) {
    router.post(route('jornada.nova'), { objetivo }, { preserveScroll: true });
}

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
        <!-- Hero em tela cheia (layout MeuFluxo): video/cena cosmica de fundo -->
        <HeroAura :video="hero_video" class="-mt-16 h-[72vh] min-h-[520px] max-h-[820px] w-full">
            <div class="flex h-full flex-col justify-between">
                <div class="mx-auto flex w-full max-w-6xl items-start justify-between gap-4 px-5 pt-24 lg:px-8">
                    <p class="text-sm uppercase tracking-widest text-aura-text/90">
                        {{ saudacao }}, <span class="font-semibold text-aura-text">{{ apelido }}</span>
                    </p>
                    <div class="flex shrink-0 items-center gap-2">
                        <Link
                            v-if="aura_score !== null"
                            :href="route('aura-score')"
                            class="flex items-center gap-2 rounded-full border border-aura-gold/40 bg-aura-black/50 px-4 py-1.5 backdrop-blur transition hover:border-aura-gold"
                            :title="t('score.mapa')"
                        >
                            <AppIcon name="sparkles" class="h-4 w-4 text-aura-gold" />
                            <span class="text-sm font-semibold text-aura-gold">{{ aura_score }}</span>
                        </Link>
                        <div v-if="jornada" class="flex items-center gap-2 rounded-full border border-aura-gold/40 bg-aura-black/50 px-4 py-1.5 backdrop-blur">
                            <AppIcon name="flame" class="h-4 w-4 text-aura-gold" />
                            <span class="text-sm font-semibold text-aura-gold">{{ t('dia.diaN') }} {{ jornada.dia }}</span>
                            <span class="text-xs text-aura-muted">· {{ jornada.etapa }}</span>
                        </div>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-6xl px-5 pb-14 lg:px-8">
                    <template v-if="destaque">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-aura-gold">{{ t('home.destaque') }}</p>
                        <h1 class="mt-2 max-w-2xl font-display text-4xl font-semibold leading-[1.05] text-aura-text lg:text-6xl">{{ destaque.titulo }}</h1>
                        <p v-if="destaque.descricao" class="mt-4 hidden max-w-xl leading-relaxed text-aura-text/80 sm:block">{{ destaque.descricao }}</p>
                        <p v-if="destaque.instrutor" class="mt-3 text-sm font-semibold text-aura-muted">{{ t('common.com') }} {{ destaque.instrutor }}</p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <Link
                                :href="route('curso', destaque.slug)"
                                class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-8 py-3 font-semibold text-aura-black transition hover:opacity-90"
                            >
                                <AppIcon name="play" class="h-4 w-4" /> {{ t('common.iniciar') }}
                            </Link>
                            <Link
                                :href="route('curso', destaque.slug)"
                                class="rounded-full border border-aura-gold/50 px-8 py-3 font-semibold text-aura-gold backdrop-blur transition hover:bg-aura-gold/10"
                            >
                                {{ t('common.detalhes') }}
                            </Link>
                        </div>
                    </template>
                    <template v-else>
                        <h1 class="max-w-2xl font-display text-4xl font-semibold leading-[1.05] text-aura-text lg:text-6xl">{{ apelido }}</h1>
                        <p class="mt-4 max-w-md text-aura-text/80">{{ t('home.heroFrase') }}</p>
                    </template>
                </div>
            </div>
        </HeroAura>

        <div class="mx-auto max-w-6xl px-5 py-8 lg:py-10">
            <div class="mx-auto max-w-3xl">

            <!-- Sem jornada ativa: celebrar (se concluiu) e escolher a proxima -->
            <div v-if="!jornada" class="mt-10 rounded-2xl border border-aura-line bg-aura-surface p-8">
                <template v-if="jornada_concluida">
                    <p class="text-center font-display text-3xl text-aura-gold">{{ t('dia.jornadaConcluida') }}</p>
                    <p class="mt-2 text-center text-aura-muted">{{ t('dia.proximaJornadaTexto') }}</p>
                </template>
                <template v-else>
                    <h2 class="text-center font-display text-2xl text-aura-text">{{ t('dia.semJornadaTitulo') }}</h2>
                    <p class="mt-2 text-center text-aura-muted">{{ t('dia.escolhaObjetivoTexto') }}</p>
                </template>

                <div class="mt-6 space-y-3">
                    <button
                        v-for="objetivo in objetivos"
                        :key="objetivo.slug"
                        type="button"
                        class="w-full rounded-xl border border-aura-line bg-aura-raised p-4 text-left text-lg text-aura-text transition hover:border-aura-gold/60 hover:text-aura-gold-light"
                        @click="iniciarJornada(objetivo.slug)"
                    >
                        {{ objetivo.nome }}
                    </button>
                </div>

                <p class="mt-6 text-center text-sm text-aura-muted">
                    {{ t('dia.semJornadaTexto') }}
                    <Link :href="route('cursos')" class="text-aura-gold underline-offset-4 hover:underline">{{ t('dia.irBiblioteca') }}</Link>
                </p>
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

                <!-- Falar com Aura -->
                <section class="mt-8">
                    <Link
                        :href="route('aura')"
                        class="flex items-center gap-4 rounded-2xl border border-aura-gold/30 bg-gradient-to-r from-aura-surface to-aura-raised p-5 transition hover:border-aura-gold/60"
                    >
                        <AppIcon name="sparkles" class="h-7 w-7 text-aura-gold" />
                        <div class="flex-1">
                            <p class="font-semibold text-aura-text">{{ t('dia.falarComAura') }}</p>
                            <p class="text-sm text-aura-muted">{{ t('dia.auraSubtitulo') }}</p>
                        </div>
                        <span class="text-aura-gold">→</span>
                    </Link>
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

            <!-- Vitrine de conteudo (estrutura MeuFluxo) -->
            <section v-if="continuar?.length" class="mt-14">
                <h2 class="font-display text-2xl font-semibold text-aura-text">{{ t('home.continue') }}</h2>
                <div class="mt-4 flex gap-4 overflow-x-auto pb-3">
                    <Link
                        v-for="curso in continuar"
                        :key="curso.id"
                        :href="route('curso', curso.slug)"
                        class="group w-56 shrink-0"
                    >
                        <div class="relative aspect-video overflow-hidden rounded-xl border border-aura-line bg-aura-surface transition group-hover:border-aura-gold/60">
                            <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                            <span class="absolute bottom-2 left-3 right-3 truncate font-display text-sm font-semibold text-aura-text">{{ curso.titulo }}</span>
                            <span class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-aura-gold">
                                <AppIcon name="play" class="h-4 w-4" />
                            </span>
                        </div>
                    </Link>
                </div>
            </section>

            <section v-if="em_alta?.length" class="mt-12">
                <h2 class="font-display text-2xl font-semibold text-aura-text">{{ t('home.emAlta') }}</h2>
                <div class="mt-4 flex gap-5 overflow-x-auto pb-3">
                    <Link
                        v-for="curso in em_alta"
                        :key="curso.id"
                        :href="route('curso', curso.slug)"
                        class="group w-72 shrink-0 overflow-hidden rounded-2xl border border-aura-line bg-aura-surface transition hover:border-aura-gold/60"
                    >
                        <div class="relative aspect-video bg-gradient-to-br from-aura-raised to-aura-deep">
                            <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-transparent"></div>
                            <div class="absolute bottom-3 left-4 right-4">
                                <p class="font-display text-lg font-semibold leading-tight text-aura-text">{{ curso.titulo }}</p>
                                <p v-if="curso.instrutor" class="mt-0.5 text-xs text-aura-muted">{{ t('common.com') }} {{ curso.instrutor }}</p>
                            </div>
                        </div>
                        <p v-if="curso.descricao" class="p-4 text-sm leading-relaxed text-aura-muted line-clamp-3">{{ curso.descricao }}</p>
                    </Link>
                </div>
            </section>

            <section v-if="audios_destaque?.length" class="mt-12">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display text-2xl font-semibold text-aura-text">{{ t('home.audiosDestaque') }}</h2>
                    <Link :href="route('audios')" class="text-sm font-semibold text-aura-gold hover:underline">{{ t('home.verTodos') }}</Link>
                </div>
                <div class="mt-4 flex gap-4 overflow-x-auto pb-3">
                    <Link
                        v-for="audio in audios_destaque"
                        :key="audio.id"
                        :href="route('audio', audio.id)"
                        class="group w-44 shrink-0"
                    >
                        <div class="relative flex aspect-square items-center justify-center overflow-hidden rounded-xl border border-aura-line bg-gradient-to-br from-aura-raised to-aura-deep transition group-hover:border-aura-gold/60">
                            <img v-if="audio.capa_url" :src="audio.capa_url" :alt="audio.titulo" class="absolute inset-0 h-full w-full object-cover" />
                            <AppIcon v-else name="headphones" class="h-9 w-9 text-aura-gold/60 transition group-hover:text-aura-gold" />
                        </div>
                        <p class="mt-2 truncate text-sm font-semibold text-aura-text">{{ audio.titulo }}</p>
                        <p class="text-xs text-aura-muted">{{ t(`audios.${audio.tipo}`) }} · {{ minutos(audio.duracao) }} {{ t('dia.min') }}</p>
                    </Link>
                </div>
            </section>
        </div>
    </PainelLayout>
</template>
