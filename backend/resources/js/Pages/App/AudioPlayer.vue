<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    audio: Object,
    premium_bloqueado: Boolean,
    checkout_url: String,
});

const player = ref(null);
let ultimoEnvio = 0;

function salvarProgresso(concluido = false) {
    const posicao = Math.floor(player.value?.currentTime ?? 0);
    router.post(
        route('audio.progresso', props.audio.id),
        { posicao_segundos: posicao, concluido },
        { preserveScroll: true, preserveState: !concluido },
    );
}

// Salva a posicao a cada ~15s de reproducao, no pause e ao concluir.
function aoAtualizar() {
    const agora = player.value?.currentTime ?? 0;
    if (agora - ultimoEnvio >= 15) {
        ultimoEnvio = agora;
        salvarProgresso(false);
    }
}

function aoMontarPlayer(el) {
    if (el && props.audio.posicao_segundos > 0 && !props.audio.concluido) {
        el.currentTime = props.audio.posicao_segundos;
    }
}

onBeforeUnmount(() => {
    if (player.value && !player.value.paused) {
        salvarProgresso(false);
    }
});
</script>

<template>
    <Head :title="audio.titulo" />

    <PainelLayout>
        <div class="mx-auto max-w-2xl px-5 py-8 lg:py-12">
            <Link :href="route('audios')" class="inline-flex items-center gap-2 text-sm text-aura-muted transition hover:text-aura-text">
                <AppIcon name="arrow-left" class="h-4 w-4" /> {{ t('audios.titulo') }}
            </Link>

            <div class="mt-8 overflow-hidden rounded-3xl border border-aura-line bg-aura-surface">
                <div class="relative flex aspect-square max-h-80 w-full items-center justify-center bg-gradient-to-br from-aura-raised to-aura-deep">
                    <img v-if="audio.capa_url" :src="audio.capa_url" :alt="audio.titulo" class="absolute inset-0 h-full w-full object-cover" />
                    <div v-else class="flex h-32 w-32 items-center justify-center rounded-full border border-aura-gold/40 bg-aura-gold/5">
                        <AppIcon name="headphones" class="h-12 w-12 text-aura-gold" />
                    </div>
                </div>

                <div class="p-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-aura-gold">{{ t(`audios.${audio.tipo}`) }}</p>
                    <h1 class="mt-1 font-display text-3xl font-semibold text-aura-text">{{ audio.titulo }}</h1>
                    <p v-if="audio.descricao" class="mt-2 text-sm text-aura-muted">{{ audio.descricao }}</p>

                    <!-- Premium sem compra: oferta no lugar do player -->
                    <div v-if="premium_bloqueado" class="mt-6 rounded-2xl border border-aura-gold/40 bg-aura-gold/5 p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-aura-gold">🔒 {{ t('premium.selo') }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-aura-muted">{{ t('premium.bloqueadoTexto') }}</p>
                        <a
                            v-if="checkout_url"
                            :href="checkout_url"
                            target="_blank"
                            rel="noopener"
                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-6 py-2.5 font-semibold text-aura-black transition hover:opacity-90"
                        >
                            {{ t('premium.cta') }}
                        </a>
                        <p v-else class="mt-4 text-sm font-semibold text-aura-gold">{{ t('premium.indisponivel') }}</p>
                    </div>

                    <audio
                        v-else-if="audio.arquivo_url"
                        :ref="(el) => { player = el; aoMontarPlayer(el); }"
                        :src="audio.arquivo_url"
                        controls
                        class="mt-6 w-full"
                        @timeupdate="aoAtualizar"
                        @pause="salvarProgresso(false)"
                        @ended="salvarProgresso(true)"
                    />
                    <iframe
                        v-else-if="audio.embed_url"
                        :src="audio.embed_url"
                        class="mt-6 aspect-video w-full rounded-xl border-0"
                        allow="autoplay; encrypted-media"
                        allowfullscreen
                    />
                    <p v-else class="mt-6 text-aura-muted">{{ t('audios.indisponivel') }}</p>

                    <div v-if="!premium_bloqueado" class="mt-5 flex items-center justify-between">
                        <span v-if="audio.concluido" class="inline-flex items-center gap-2 text-sm text-aura-gold">
                            <AppIcon name="check" class="h-4 w-4" /> {{ t('audios.concluido') }}
                        </span>
                        <span v-else />
                        <button
                            v-if="!audio.concluido"
                            type="button"
                            class="rounded-full border border-aura-gold/60 px-5 py-3 text-sm font-semibold text-aura-gold transition hover:bg-aura-gold hover:text-aura-black"
                            @click="salvarProgresso(true)"
                        >
                            {{ t('dia.concluir') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PainelLayout>
</template>
