<script setup>
import AvatarNoah from '@/Components/AvatarNoah.vue';
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
// Estado "vivo" do Noah: null | 'pensando' (esperando a API) | 'escrevendo' (typewriter)
const fase = ref(null);
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

function abrirConversa(id) {
    if (id && id !== conversaId.value) {
        window.location.href = route('aura') + '?conversa=' + id;
    }
}

// Ritmo de digitacao natural: cada caractere tem um atraso proprio (com jitter),
// pontuacao forte pausa mais, virgula pausa um pouco. Pre-calcula o tempo acumulado
// de cada posicao e avanca por tempo real (imune ao throttle de timers em aba de fundo).
function agendaDigitacao(completo) {
    const tempos = new Array(completo.length);
    let acumulado = 0;
    for (let i = 0; i < completo.length; i++) {
        const c = completo[i];
        let atraso = 14 + Math.random() * 22; // 14-36ms por caractere
        if ('.!?'.includes(c)) atraso += 220;
        else if (',;:'.includes(c)) atraso += 90;
        else if (c === '\n') atraso += 260;
        acumulado += atraso;
        tempos[i] = acumulado;
    }
    return tempos;
}

function digitar(mensagem) {
    const completo = mensagem.conteudo;
    historico.value.push({ ...mensagem, conteudo: '' });
    // referencia REATIVA (o objeto cru fora do array nao dispara re-render)
    const alvo = historico.value[historico.value.length - 1];
    const tempos = agendaDigitacao(completo);
    const inicio = Date.now();
    fase.value = 'escrevendo';

    const intervalo = setInterval(() => {
        const decorrido = Date.now() - inicio;
        let n = 0;
        while (n < completo.length && tempos[n] <= decorrido) n++;
        alvo.conteudo = completo.slice(0, n);
        rolarParaFim();
        if (n >= completo.length) {
            clearInterval(intervalo);
            fase.value = null;
        }
    }, 30);
}

async function enviar() {
    const conteudo = texto.value.trim();
    if (!conteudo || enviando.value) return;

    erro.value = null;
    enviando.value = true;
    fase.value = 'pensando';
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
            fase.value = null;
            return;
        }

        conversaId.value = dados.conversa_id;
        digitar(dados.mensagem);
    } catch {
        erro.value = t('aura.erroGenerico');
        fase.value = null;
    } finally {
        enviando.value = false;
    }
}
</script>

<template>
    <Head :title="t('aura.titulo')" />

    <PainelLayout>
        <div class="mx-auto flex h-[calc(100dvh-8.5rem)] max-w-4xl flex-col px-5 md:h-[calc(100vh-4rem)]">
            <!-- Cabecalho -->
            <div class="flex items-center justify-between border-b border-aura-line py-5">
                <div class="flex items-center gap-3">
                    <AvatarNoah tamanho="h-11 w-11" :pulsar="fase !== null" />
                    <div>
                        <h1 class="font-display text-2xl font-semibold leading-tight text-aura-text">{{ t('aura.titulo') }}</h1>
                        <p class="flex items-center gap-1.5 text-xs text-aura-muted">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400/90"></span>
                            {{ fase === 'pensando' ? t('aura.pensando') : fase === 'escrevendo' ? t('aura.digitando') : t('aura.subtitulo') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select
                        v-if="conversas.length > 1"
                        class="max-w-44 rounded-full border-aura-line bg-aura-surface py-2 pl-4 pr-8 text-sm text-aura-muted focus:border-aura-gold focus:ring-aura-gold"
                        :value="conversaId ?? ''"
                        @change="abrirConversa(Number($event.target.value))"
                    >
                        <option v-for="conversa in conversas" :key="conversa.id" :value="conversa.id">
                            {{ conversa.titulo ?? t('aura.titulo') }}
                        </option>
                    </select>
                    <button
                        type="button"
                        class="rounded-full border border-aura-line px-4 py-2 text-sm font-semibold text-aura-muted transition hover:border-aura-gold/50 hover:text-aura-gold"
                        @click="novaConversa"
                    >
                        {{ t('aura.novaConversa') }}
                    </button>
                </div>
            </div>

            <!-- Mensagens -->
            <div ref="areaMensagens" class="flex-1 space-y-5 overflow-y-auto py-6">
                <div v-if="!historico.length" class="flex h-full flex-col items-center justify-center text-center">
                    <AvatarNoah tamanho="h-20 w-20 text-3xl" />
                    <p class="mt-5 font-display text-2xl text-aura-text">{{ t('aura.boasVindas') }} {{ apelido }}.</p>
                    <p class="mt-1 max-w-sm text-sm text-aura-muted">{{ t('aura.boasVindasSub') }}</p>
                </div>

                <div
                    v-for="mensagem in historico"
                    :key="mensagem.id"
                    class="msg flex items-end gap-2.5"
                    :class="mensagem.papel === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <AvatarNoah v-if="mensagem.papel === 'assistant'" tamanho="h-8 w-8 text-sm" />
                    <div
                        class="max-w-[80%] whitespace-pre-wrap rounded-2xl px-4 py-3 text-[15px] leading-relaxed"
                        :class="mensagem.papel === 'user'
                            ? 'rounded-br-md bg-aura-raised text-aura-text'
                            : 'rounded-bl-md border border-aura-gold/25 bg-aura-surface text-aura-text'"
                    >{{ mensagem.conteudo }}<span v-if="fase === 'escrevendo' && mensagem === historico[historico.length - 1] && mensagem.papel === 'assistant'" class="cursor ml-0.5 inline-block h-[1em] w-[2px] translate-y-[2px] bg-aura-gold"></span></div>
                </div>

                <!-- Noah pensando (aguardando a resposta) -->
                <div v-if="fase === 'pensando'" class="msg flex items-end justify-start gap-2.5">
                    <AvatarNoah tamanho="h-8 w-8 text-sm" pulsar />
                    <div class="flex items-center gap-3 rounded-2xl rounded-bl-md border border-aura-gold/25 bg-aura-surface px-4 py-3 text-sm text-aura-muted">
                        <span class="pontos" aria-hidden="true"><i></i><i></i><i></i></span>
                        {{ t('aura.pensando') }}
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

<style scoped>
/* Entrada suave de cada balao */
.msg {
    animation: msg-entrar 0.28s ease-out both;
}
@keyframes msg-entrar {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Cursor piscando no fim do texto enquanto o Noah escreve */
.cursor {
    animation: cursor-piscar 0.9s steps(2, start) infinite;
}
@keyframes cursor-piscar {
    to { visibility: hidden; }
}

/* Tres pontos "pensando" */
.pontos {
    display: inline-flex;
    gap: 4px;
}
.pontos i {
    display: block;
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #C9A24B;
    animation: pontos-subir 1.2s ease-in-out infinite;
}
.pontos i:nth-child(2) { animation-delay: 0.15s; }
.pontos i:nth-child(3) { animation-delay: 0.3s; }
@keyframes pontos-subir {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.45; }
    30% { transform: translateY(-4px); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .msg, .cursor, .pontos i { animation: none; }
}
</style>
