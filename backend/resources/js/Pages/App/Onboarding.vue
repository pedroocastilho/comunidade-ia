<script setup>
import { useI18n } from '@/useI18n';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    perguntas: Array,
});

const indice = ref(0);
const respostas = ref({});
const erro = ref(false);
const enviando = ref(false);

const atual = computed(() => props.perguntas[indice.value]);
const ultima = computed(() => indice.value === props.perguntas.length - 1);
const progresso = computed(() => ((indice.value + 1) / props.perguntas.length) * 100);

// Opcoes da pergunta 3 (segundo objetivo): esconde a escolhida na pergunta 2.
const opcoesVisiveis = computed(() => {
    if (!atual.value.opcoes) return [];
    if (atual.value.ordem !== 3) return atual.value.opcoes;
    const principal = props.perguntas.find((p) => p.ordem === 2);
    const escolhido = principal ? respostas.value[principal.id] : null;
    return atual.value.opcoes.filter((o) => o.valor !== escolhido);
});

function valorAtual() {
    const v = respostas.value[atual.value.id];
    return v === undefined || v === '' ? null : v;
}

function responder(valor) {
    respostas.value[atual.value.id] = valor;
    erro.value = false;
    // escala e escolha unica avancam sozinhas, com uma pausa curta para o feedback visual
    setTimeout(avancar, 220);
}

function avancar() {
    if (atual.value.obrigatoria && valorAtual() === null) {
        erro.value = true;
        return;
    }
    erro.value = false;
    if (ultima.value) {
        enviar();
    } else {
        indice.value++;
    }
}

function voltar() {
    if (indice.value > 0) {
        indice.value--;
        erro.value = false;
    }
}

function pular() {
    if (!atual.value.obrigatoria) {
        delete respostas.value[atual.value.id];
        ultima.value ? enviar() : indice.value++;
    }
}

function enviar() {
    enviando.value = true;
    router.post(route('onboarding.salvar'), { respostas: respostas.value }, {
        onError: () => {
            enviando.value = false;
        },
    });
}
</script>

<template>
    <Head :title="t('onboarding.titulo')" />

    <div class="flex min-h-screen flex-col bg-aura-black text-aura-text">
        <!-- Barra de progresso -->
        <div class="h-1 w-full bg-aura-raised">
            <div
                class="h-1 bg-gradient-to-r from-aura-gold to-aura-gold-light transition-all duration-500"
                :style="{ width: progresso + '%' }"
            />
        </div>

        <header class="flex items-center justify-between px-6 py-5">
            <span class="font-brand text-lg font-semibold tracking-[0.24em] text-aura-gold">
                {{ t('onboarding.titulo').toUpperCase() }}
            </span>
            <span class="text-sm text-aura-muted">
                {{ t('onboarding.passo') }} {{ indice + 1 }} {{ t('onboarding.de') }} {{ perguntas.length }}
            </span>
        </header>

        <main class="flex flex-1 items-center justify-center px-6 pb-16">
            <div v-if="enviando" class="text-center">
                <div class="mx-auto mb-6 h-16 w-16 animate-pulse rounded-full border border-aura-gold bg-aura-gold/10" />
                <p class="text-lg text-aura-muted">{{ t('onboarding.enviando') }}</p>
            </div>

            <Transition v-else name="pergunta" mode="out-in">
                <div :key="atual.id" class="w-full max-w-xl">
                    <p v-if="indice === 0" class="mb-3 text-sm uppercase tracking-widest text-aura-muted">
                        {{ t('onboarding.subtitulo') }}
                    </p>
                    <h1 class="font-display text-3xl font-semibold leading-tight lg:text-4xl">
                        {{ atual.texto }}
                    </h1>

                    <!-- texto -->
                    <div v-if="atual.tipo === 'texto'" class="mt-8">
                        <textarea
                            v-if="atual.ordem === 12"
                            v-model="respostas[atual.id]"
                            rows="4"
                            :placeholder="t('onboarding.placeholderTexto')"
                            class="w-full rounded-xl border border-aura-line bg-aura-surface p-4 text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                        />
                        <input
                            v-else
                            v-model="respostas[atual.id]"
                            type="text"
                            :placeholder="t('onboarding.placeholderTexto')"
                            class="w-full rounded-xl border border-aura-line bg-aura-surface p-4 text-lg text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                            @keyup.enter="avancar"
                        />
                    </div>

                    <!-- escala 0-10 -->
                    <div v-else-if="atual.tipo === 'escala'" class="mt-8 grid grid-cols-6 gap-2 sm:grid-cols-11">
                        <button
                            v-for="n in 11"
                            :key="n - 1"
                            type="button"
                            class="rounded-lg border py-3 text-lg font-semibold transition"
                            :class="respostas[atual.id] === n - 1
                                ? 'border-aura-gold bg-aura-gold text-aura-black'
                                : 'border-aura-line bg-aura-surface text-aura-text hover:border-aura-gold/60'"
                            @click="responder(n - 1)"
                        >
                            {{ n - 1 }}
                        </button>
                    </div>

                    <!-- escolha unica -->
                    <div v-else class="mt-8 space-y-3">
                        <button
                            v-for="opcao in opcoesVisiveis"
                            :key="opcao.valor"
                            type="button"
                            class="w-full rounded-xl border p-4 text-left text-lg transition"
                            :class="respostas[atual.id] === opcao.valor
                                ? 'border-aura-gold bg-aura-gold/10 text-aura-gold-light'
                                : 'border-aura-line bg-aura-surface hover:border-aura-gold/60'"
                            @click="responder(opcao.valor)"
                        >
                            {{ opcao.rotulo }}
                        </button>
                    </div>

                    <p v-if="erro" class="mt-4 text-sm text-red-400">{{ t('onboarding.obrigatoria') }}</p>

                    <div class="mt-10 flex items-center justify-between">
                        <button
                            type="button"
                            class="text-sm text-aura-muted transition hover:text-aura-text disabled:invisible"
                            :disabled="indice === 0"
                            @click="voltar"
                        >
                            ← {{ t('onboarding.voltar') }}
                        </button>

                        <div class="flex items-center gap-4">
                            <button
                                v-if="!atual.obrigatoria"
                                type="button"
                                class="text-sm text-aura-muted underline-offset-4 transition hover:text-aura-text hover:underline"
                                @click="pular"
                            >
                                {{ t('onboarding.pular') }}
                            </button>
                            <button
                                type="button"
                                class="rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-8 py-3 font-semibold text-aura-black transition hover:opacity-90"
                                @click="avancar"
                            >
                                {{ ultima ? t('onboarding.finalizar') : t('onboarding.avancar') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </main>
    </div>
</template>

<style scoped>
.pergunta-enter-active,
.pergunta-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.pergunta-enter-from {
    opacity: 0;
    transform: translateY(12px);
}
.pergunta-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}
</style>
