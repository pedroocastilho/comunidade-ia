<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { t, locale } = useI18n();

const props = defineProps({
    metas: Array,
    hoje: String,
});

// ---- Calendario ---------------------------------------------------------
const hojeData = new Date(props.hoje + 'T00:00:00');
const mesAtual = ref(new Date(hojeData.getFullYear(), hojeData.getMonth(), 1));

const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

const nomeMes = computed(() =>
    mesAtual.value.toLocaleDateString(locale.value === 'es' ? 'es-ES' : 'pt-BR', { month: 'long', year: 'numeric' }),
);

const diasSemana = computed(() => {
    const base = new Date(2024, 0, 1); // segunda-feira
    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(base);
        d.setDate(base.getDate() + i);
        return d.toLocaleDateString(locale.value === 'es' ? 'es-ES' : 'pt-BR', { weekday: 'short' }).replace('.', '');
    });
});

// Metas agrupadas por dia de prazo (para os pontos no calendario)
const porDia = computed(() => {
    const mapa = {};
    for (const m of props.metas) (mapa[m.prazo] ??= []).push(m);
    return mapa;
});

// Celulas do mes: comeca na segunda, preenche com vazios antes do dia 1
const celulas = computed(() => {
    const ano = mesAtual.value.getFullYear();
    const mes = mesAtual.value.getMonth();
    const primeiro = new Date(ano, mes, 1);
    const inicioVazio = (primeiro.getDay() + 6) % 7;
    const totalDias = new Date(ano, mes + 1, 0).getDate();
    const lista = Array.from({ length: inicioVazio }, () => null);
    for (let d = 1; d <= totalDias; d++) {
        const data = new Date(ano, mes, d);
        const chave = iso(data);
        lista.push({
            dia: d,
            chave,
            passado: chave < props.hoje,
            hoje: chave === props.hoje,
            metas: porDia.value[chave] ?? [],
        });
    }
    return lista;
});

function mudarMes(delta) {
    mesAtual.value = new Date(mesAtual.value.getFullYear(), mesAtual.value.getMonth() + delta, 1);
}

// ---- Nova meta -----------------------------------------------------------
const diaSelecionado = ref(null);
const titulo = ref('');
const salvando = ref(false);
const campoTitulo = ref(null);

function selecionarDia(celula) {
    if (!celula || celula.passado) return;
    diaSelecionado.value = celula.chave;
    setTimeout(() => campoTitulo.value?.focus(), 30);
}

function salvar() {
    if (!titulo.value.trim() || !diaSelecionado.value) return;
    salvando.value = true;
    router.post(route('metas.store'), { titulo: titulo.value.trim(), prazo: diaSelecionado.value }, {
        preserveScroll: true,
        onSuccess: () => { titulo.value = ''; diaSelecionado.value = null; },
        onFinish: () => { salvando.value = false; },
    });
}

function marcar(meta, concluida) {
    router.patch(route('metas.update', meta.id), { concluida }, { preserveScroll: true });
}

function excluir(meta) {
    router.delete(route('metas.destroy', meta.id), { preserveScroll: true });
}

// ---- Listas ---------------------------------------------------------------
const emAndamento = computed(() => props.metas.filter((m) => m.situacao === 'andamento'));
const vencidas = computed(() => props.metas.filter((m) => m.situacao === 'vencida'));
const concluidas = computed(() => [...props.metas.filter((m) => m.situacao === 'concluida')].reverse());

const dataCurta = (chave) =>
    new Date(chave + 'T00:00:00').toLocaleDateString(locale.value === 'es' ? 'es-ES' : 'pt-BR', { day: '2-digit', month: 'short' }).replace('.', '');

function prazoTexto(meta) {
    if (meta.dias_restantes === 0) return t('metas.hoje');
    if (meta.dias_restantes === 1) return t('metas.amanha');
    if (meta.dias_restantes > 1) return `${meta.dias_restantes} ${t('metas.dias')}`;
    return dataCurta(meta.prazo);
}
</script>

<template>
    <Head :title="t('metas.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-5xl px-5 py-8 lg:py-12">
            <p class="text-sm uppercase tracking-widest text-aura-muted">{{ t('metas.subtitulo') }}</p>
            <h1 class="mt-1 font-display text-4xl font-semibold text-aura-text">{{ t('metas.titulo') }}</h1>
            <p class="mt-2 max-w-xl text-aura-muted">{{ t('metas.intro') }}</p>

            <div class="mt-8 grid gap-6 lg:grid-cols-[1.15fr_1fr]">
                <!-- Calendario -->
                <section class="rounded-2xl border border-aura-line bg-aura-surface p-5">
                    <div class="flex items-center justify-between">
                        <button type="button" class="rounded-full p-2 text-aura-muted transition hover:bg-aura-raised hover:text-aura-gold" :aria-label="t('metas.mesAnterior')" @click="mudarMes(-1)">
                            <AppIcon name="arrow-left" class="h-5 w-5" />
                        </button>
                        <h2 class="font-display text-xl font-semibold capitalize text-aura-text">{{ nomeMes }}</h2>
                        <button type="button" class="rounded-full p-2 text-aura-muted transition hover:bg-aura-raised hover:text-aura-gold" :aria-label="t('metas.mesSeguinte')" @click="mudarMes(1)">
                            <AppIcon name="arrow-left" class="h-5 w-5 rotate-180" />
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wider text-aura-faint">
                        <span v-for="d in diasSemana" :key="d">{{ d }}</span>
                    </div>

                    <div class="mt-1 grid grid-cols-7 gap-1">
                        <template v-for="(celula, i) in celulas" :key="i">
                            <span v-if="!celula" class="aspect-square"></span>
                            <button
                                v-else
                                type="button"
                                class="group relative flex aspect-square flex-col items-center justify-center rounded-xl text-sm transition"
                                :class="[
                                    celula.passado ? 'cursor-default text-aura-faint' : 'text-aura-text hover:bg-aura-raised',
                                    celula.hoje ? 'font-bold text-aura-gold' : '',
                                    diaSelecionado === celula.chave ? 'bg-aura-gold/15 ring-1 ring-aura-gold' : '',
                                ]"
                                :disabled="celula.passado"
                                :title="celula.metas.map((m) => m.titulo).join(', ') || null"
                                @click="selecionarDia(celula)"
                            >
                                {{ celula.dia }}
                                <span v-if="celula.metas.length" class="mt-0.5 flex gap-0.5">
                                    <i
                                        v-for="m in celula.metas.slice(0, 3)"
                                        :key="m.id"
                                        class="block h-1.5 w-1.5 rounded-full"
                                        :class="m.situacao === 'concluida' ? 'bg-emerald-400' : m.situacao === 'vencida' ? 'bg-aura-faint' : 'bg-aura-gold'"
                                    ></i>
                                </span>
                            </button>
                        </template>
                    </div>

                    <!-- Nova meta para o dia escolhido -->
                    <form v-if="diaSelecionado" class="mt-5 rounded-xl border border-aura-gold/40 bg-aura-raised p-4" @submit.prevent="salvar">
                        <p class="text-xs font-semibold uppercase tracking-widest text-aura-gold">
                            {{ t('metas.ateODia') }} {{ dataCurta(diaSelecionado) }}
                        </p>
                        <input
                            ref="campoTitulo"
                            v-model="titulo"
                            type="text"
                            maxlength="120"
                            :placeholder="t('metas.placeholder')"
                            class="mt-2 w-full rounded-full border-aura-line bg-aura-surface px-4 py-2.5 text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                        />
                        <div class="mt-3 flex items-center justify-end gap-2">
                            <button type="button" class="rounded-full px-4 py-2 text-sm text-aura-muted transition hover:text-aura-text" @click="diaSelecionado = null">
                                {{ t('common.cancelar') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-5 py-2 text-sm font-semibold text-aura-black transition hover:opacity-90 disabled:opacity-40"
                                :disabled="salvando || !titulo.trim()"
                            >
                                {{ t('metas.criar') }}
                            </button>
                        </div>
                    </form>
                    <p v-else class="mt-5 text-center text-xs text-aura-faint">{{ t('metas.dicaCalendario') }}</p>
                </section>

                <!-- Listas -->
                <div class="space-y-6">
                    <section>
                        <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-aura-text">
                            <span class="h-2 w-2 rounded-full bg-aura-gold"></span>{{ t('metas.emAndamento') }}
                            <span class="text-sm font-normal text-aura-faint">{{ emAndamento.length }}</span>
                        </h2>
                        <p v-if="!emAndamento.length" class="mt-3 text-sm text-aura-muted">{{ t('metas.vazioAndamento') }}</p>
                        <ul v-else class="mt-3 space-y-2">
                            <li v-for="meta in emAndamento" :key="meta.id" class="flex items-center gap-3 rounded-xl border border-aura-line bg-aura-surface px-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-aura-text">{{ meta.titulo }}</p>
                                    <p class="text-xs text-aura-muted">{{ t('metas.prazo') }}: {{ prazoTexto(meta) }}</p>
                                </div>
                                <button type="button" class="rounded-full border border-aura-gold/50 px-3.5 py-1.5 text-xs font-semibold text-aura-gold transition hover:bg-aura-gold/10" @click="marcar(meta, true)">
                                    {{ t('metas.fiz') }}
                                </button>
                                <button type="button" class="text-aura-faint transition hover:text-red-400" :aria-label="t('metas.excluir')" @click="excluir(meta)">✕</button>
                            </li>
                        </ul>
                    </section>

                    <section v-if="vencidas.length">
                        <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-aura-text">
                            <span class="h-2 w-2 rounded-full bg-aura-faint"></span>{{ t('metas.vencidas') }}
                            <span class="text-sm font-normal text-aura-faint">{{ vencidas.length }}</span>
                        </h2>
                        <ul class="mt-3 space-y-2">
                            <li v-for="meta in vencidas" :key="meta.id" class="flex items-center gap-3 rounded-xl border border-aura-line bg-aura-surface/60 px-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-aura-muted">{{ meta.titulo }}</p>
                                    <p class="text-xs text-aura-faint">{{ t('metas.venceuEm') }} {{ dataCurta(meta.prazo) }}</p>
                                </div>
                                <button type="button" class="rounded-full border border-aura-line px-3.5 py-1.5 text-xs font-semibold text-aura-muted transition hover:border-aura-gold/50 hover:text-aura-gold" @click="marcar(meta, true)">
                                    {{ t('metas.fizAtrasado') }}
                                </button>
                                <button type="button" class="text-aura-faint transition hover:text-red-400" :aria-label="t('metas.excluir')" @click="excluir(meta)">✕</button>
                            </li>
                        </ul>
                    </section>

                    <section v-if="concluidas.length">
                        <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-aura-text">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>{{ t('metas.concluidas') }}
                            <span class="text-sm font-normal text-aura-faint">{{ concluidas.length }}</span>
                        </h2>
                        <ul class="mt-3 space-y-2">
                            <li v-for="meta in concluidas" :key="meta.id" class="flex items-center gap-3 rounded-xl border border-emerald-400/20 bg-aura-surface px-4 py-3">
                                <AppIcon name="check" class="h-4 w-4 shrink-0 text-emerald-400" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-aura-text line-through decoration-aura-faint">{{ meta.titulo }}</p>
                                    <p class="text-xs text-aura-faint">{{ dataCurta(meta.prazo) }}</p>
                                </div>
                                <button type="button" class="text-xs text-aura-faint transition hover:text-aura-muted" @click="marcar(meta, false)">
                                    {{ t('metas.naoFiz') }}
                                </button>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </PainelLayout>
</template>
