<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head } from '@inertiajs/vue3';
import { nextTick, onMounted, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    conversas: Array,
    conversa_ativa: Number,
    mensagens: Array,
    apelido: String,
});

const conversaId = ref(props.conversa_ativa);
const historico = ref([...props.mensagens]);
const texto = ref('');
const enviando = ref(false);
const erro = ref(null);
const areaMensagens = ref(null);

function rolarParaFim() {
    nextTick(() => {
        areaMensagens.value?.scrollTo({ top: areaMensagens.value.scrollHeight, behavior: 'smooth' });
    });
}

onMounted(rolarParaFim);

function novaConversa() {
    conversaId.value = null;
    historico.value = [];
    erro.value = null;
}

// Exibicao progressiva (typewriter) da resposta da Aura.
function digitar(mensagem) {
    const completo = mensagem.conteudo;
    const alvo = { ...mensagem, conteudo: '' };
    historico.value.push(alvo);
    let i = 0;
    const intervalo = setInterval(() => {
        i = Math.min(completo.length, i + 3);
        alvo.conteudo = completo.slice(0, i);
        rolarParaFim();
        if (i >= completo.length) clearInterval(intervalo);
    }, 16);
}

async function enviar() {
    const conteudo = texto.value.trim();
    if (!conteudo || enviando.value) return;

    erro.value = null;
    enviando.value = true;
    historico.value.push({ id: 'tmp-' + Date.now(), papel: 'user', conteudo });
    texto.value = '';
    rolarParaFim();

    try {
        const resposta = await fetch(route('aura.mensagem'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
            },
            body: JSON.stringify({ texto: conteudo, conversa_id: conversaId.value }),
        });

        const dados = await resposta.json();

        if (!resposta.ok) {
            erro.value = dados.erro ?? t('aura.erroGenerico');
            return;
        }

        conversaId.value = dados.conversa_id;
        digitar(dados.mensagem);
    } catch {
        erro.value = t('aura.erroGenerico');
    } finally {
        enviando.value = false;
    }
}
</script>

<template>
    <Head :title="t('aura.titulo')" />

    <PainelLayout>
        <div class="mx-auto flex h-[calc(100vh-4rem)] max-w-4xl flex-col px-5">
            <!-- Cabecalho -->
            <div class="flex items-center justify-between border-b border-aura-line py-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-aura-gold/50 bg-aura-gold/10">
                        <AppIcon name="sparkles" class="h-5 w-5 text-aura-gold" />
                    </span>
                    <div>
                        <h1 class="font-display text-2xl font-semibold text-aura-text">{{ t('aura.titulo') }}</h1>
                        <p class="text-xs text-aura-muted">{{ t('aura.subtitulo') }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-full border border-aura-line px-4 py-2 text-sm font-semibold text-aura-muted transition hover:border-aura-gold/50 hover:text-aura-gold"
                    @click="novaConversa"
                >
                    {{ t('aura.novaConversa') }}
                </button>
            </div>

            <!-- Mensagens -->
            <div ref="areaMensagens" class="flex-1 space-y-5 overflow-y-auto py-6">
                <div v-if="!historico.length" class="flex h-full flex-col items-center justify-center text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full border border-aura-gold/40 bg-aura-gold/5">
                        <AppIcon name="sparkles" class="h-7 w-7 text-aura-gold" />
                    </span>
                    <p class="mt-5 font-display text-2xl text-aura-text">{{ t('aura.boasVindas') }} {{ apelido }}.</p>
                    <p class="mt-1 max-w-sm text-sm text-aura-muted">{{ t('aura.boasVindasSub') }}</p>
                </div>

                <div
                    v-for="mensagem in historico"
                    :key="mensagem.id"
                    class="flex"
                    :class="mensagem.papel === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[80%] whitespace-pre-wrap rounded-2xl px-4 py-3 text-[15px] leading-relaxed"
                        :class="mensagem.papel === 'user'
                            ? 'rounded-br-md bg-aura-raised text-aura-text'
                            : 'rounded-bl-md border border-aura-gold/25 bg-aura-surface text-aura-text'"
                    >
                        <span v-if="mensagem.papel === 'assistant'" class="mr-1.5 text-aura-gold">✦</span>{{ mensagem.conteudo }}
                    </div>
                </div>

                <div v-if="enviando" class="flex justify-start">
                    <div class="rounded-2xl rounded-bl-md border border-aura-gold/25 bg-aura-surface px-4 py-3 text-sm text-aura-muted">
                        <span class="mr-1.5 text-aura-gold">✦</span>{{ t('aura.digitando') }}
                        <span class="inline-flex w-6 animate-pulse">...</span>
                    </div>
                </div>

                <p v-if="erro" class="text-center text-sm text-red-400">{{ erro }}</p>
            </div>

            <!-- Entrada -->
            <div class="border-t border-aura-line py-4">
                <form class="flex items-end gap-3" @submit.prevent="enviar">
                    <textarea
                        v-model="texto"
                        rows="1"
                        :placeholder="t('aura.placeholder')"
                        class="max-h-32 flex-1 resize-none rounded-2xl border-aura-line bg-aura-surface px-4 py-3 text-[15px] text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                        @keydown.enter.exact.prevent="enviar"
                    />
                    <button
                        type="submit"
                        class="rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-6 py-3 font-semibold text-aura-black transition hover:opacity-90 disabled:opacity-40"
                        :disabled="enviando || !texto.trim()"
                    >
                        {{ t('aura.enviar') }}
                    </button>
                </form>
                <p class="mt-2 text-center text-[11px] text-aura-faint">{{ t('aura.aviso') }}</p>
            </div>
        </div>
    </PainelLayout>
</template>
