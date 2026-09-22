<script setup>
// Remedicao do Aura Score ao fim de uma jornada: as 6 escalas do questionario,
// uma por tela, na mesma estetica do onboarding.
import { useI18n } from '@/useI18n';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    perguntas: Array, // [{id, ordem, texto}] — ordens 4..9
});

const indice = ref(0);
const escalas = ref({});
const enviando = ref(false);

const atual = computed(() => props.perguntas[indice.value]);
const ultima = computed(() => indice.value === props.perguntas.length - 1);
const progresso = computed(() => ((indice.value + 1) / props.perguntas.length) * 100);

function responder(valor) {
    escalas.value['p' + atual.value.ordem] = valor;
    setTimeout(() => {
        if (ultima.value) {
            enviar();
        } else {
            indice.value++;
        }
    }, 220);
}

function voltar() {
    if (indice.value > 0) indice.value--;
}

function enviar() {
    enviando.value = true;
    router.post(route('reavaliacao.salvar'), { escalas: escalas.value }, {
        onError: () => { enviando.value = false; },
    });
}
</script>

<template>
    <Head :title="t('reavaliacao.titulo')" />

    <div class="flex min-h-dvh flex-col bg-aura-black text-aura-text">
        <div class="h-1 w-full bg-aura-raised">
            <div class="h-1 bg-gradient-to-r from-aura-gold to-aura-gold-light transition-all duration-500" :style="{ width: progresso + '%' }" />
        </div>

        <header class="flex items-center justify-between gap-3 px-6 py-5">
            <span class="min-w-0 truncate font-brand text-base font-semibold tracking-[0.18em] text-aura-gold sm:text-lg sm:tracking-[0.24em]">CÍRCULO AURA</span>
            <span class="shrink-0 text-sm text-aura-muted">{{ indice + 1 }} / {{ perguntas.length }}</span>
        </header>

        <main class="flex flex-1 items-center justify-center px-6 pb-16">
            <div v-if="enviando" class="text-center">
                <div class="mx-auto mb-6 h-16 w-16 animate-pulse rounded-full border border-aura-gold bg-aura-gold/10" />
                <p class="text-lg text-aura-muted">{{ t('reavaliacao.calculando') }}</p>
            </div>

            <Transition v-else name="pergunta" mode="out-in">
                <div :key="atual.id" class="w-full max-w-xl">
                    <p v-if="indice === 0" class="mb-3 text-sm uppercase tracking-widest text-aura-muted">
                        {{ t('reavaliacao.subtitulo') }}
                    </p>
                    <h1 class="font-display text-3xl font-semibold leading-tight lg:text-4xl">
                        {{ atual.texto }}
                    </h1>

                    <div class="mt-8 grid grid-cols-6 gap-2 sm:grid-cols-11">
                        <button
                            v-for="n in 11"
                            :key="n - 1"
                            type="button"
                            class="rounded-lg border py-3 text-lg font-semibold transition"
                            :class="escalas['p' + atual.ordem] === n - 1
                                ? 'border-aura-gold bg-aura-gold text-aura-black'
                                : 'border-aura-line bg-aura-surface text-aura-text hover:border-aura-gold/60'"
                            @click="responder(n - 1)"
                        >
                            {{ n - 1 }}
                        </button>
                    </div>

                    <div class="mt-10">
                        <button
                            type="button"
                            class="text-sm text-aura-muted transition hover:text-aura-text disabled:invisible"
                            :disabled="indice === 0"
                            @click="voltar"
                        >
                            ← {{ t('onboarding.voltar') }}
                        </button>
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
