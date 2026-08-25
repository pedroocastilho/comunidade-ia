<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { t } = useI18n();

defineProps({
    posts: Array,
});

const corpo = ref('');
const comentarios = ref({}); // rascunho de comentario por post
const comentandoEm = ref(null);

function publicar() {
    if (!corpo.value.trim()) return;
    router.post(route('circulo.publicar'), { corpo: corpo.value }, {
        preserveScroll: true,
        onSuccess: () => { corpo.value = ''; },
    });
}

function reagir(post) {
    router.post(route('circulo.reagir', post.id), {}, { preserveScroll: true });
}

function comentar(post) {
    const texto = (comentarios.value[post.id] ?? '').trim();
    if (!texto) return;
    router.post(route('circulo.comentar', post.id), { texto }, {
        preserveScroll: true,
        onSuccess: () => { comentarios.value[post.id] = ''; comentandoEm.value = null; },
    });
}

function denunciar(tipo, id) {
    if (!confirm(t('circulo.denunciarConfirma'))) return;
    router.post(route('circulo.denunciar'), { tipo, id }, { preserveScroll: true });
}

const iniciais = (nome) => nome.split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]).join('').toUpperCase();
</script>

<template>
    <Head :title="t('circulo.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-2xl px-5 py-8 lg:py-12">
            <p class="text-sm uppercase tracking-widest text-aura-muted">{{ t('circulo.subtitulo') }}</p>
            <h1 class="mt-1 font-display text-4xl font-semibold text-aura-text">{{ t('circulo.titulo') }}</h1>

            <!-- Compor -->
            <div class="mt-8 rounded-2xl border border-aura-line bg-aura-surface p-4">
                <textarea
                    v-model="corpo"
                    rows="3"
                    :placeholder="t('circulo.placeholder')"
                    maxlength="2000"
                    class="w-full resize-none border-0 bg-transparent p-1 text-aura-text placeholder-aura-faint focus:ring-0"
                />
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-xs text-aura-faint">{{ corpo.length }}/2000</span>
                    <button
                        type="button"
                        class="rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-6 py-2 text-sm font-semibold text-aura-black transition hover:opacity-90 disabled:opacity-40"
                        :disabled="!corpo.trim()"
                        @click="publicar"
                    >
                        {{ t('circulo.publicar') }}
                    </button>
                </div>
            </div>

            <!-- Feed -->
            <div class="mt-8 space-y-5">
                <p v-if="!posts.length" class="text-center text-aura-muted">{{ t('circulo.vazio') }}</p>

                <article
                    v-for="post in posts"
                    :key="post.id"
                    class="rounded-2xl border bg-aura-surface p-5"
                    :class="post.fixado ? 'border-aura-gold/50' : 'border-aura-line'"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-aura-gold/40 bg-aura-raised text-sm font-bold text-aura-gold">
                            {{ iniciais(post.autor) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-aura-text">{{ post.autor }}</p>
                            <p class="text-xs text-aura-muted">{{ post.quando }}</p>
                        </div>
                        <span v-if="post.fixado" class="rounded-full border border-aura-gold/50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-aura-gold">
                            {{ t('circulo.fixado') }}
                        </span>
                    </div>

                    <p class="mt-4 whitespace-pre-wrap leading-relaxed text-aura-text">{{ post.corpo }}</p>

                    <div class="mt-4 flex items-center gap-5 text-sm">
                        <button
                            type="button"
                            class="flex items-center gap-1.5 font-semibold transition"
                            :class="post.reagi ? 'text-aura-gold' : 'text-aura-muted hover:text-aura-gold'"
                            @click="reagir(post)"
                        >
                            ✦ {{ post.reacoes }}
                        </button>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 text-aura-muted transition hover:text-aura-text"
                            @click="comentandoEm = comentandoEm === post.id ? null : post.id"
                        >
                            <AppIcon name="chat" class="h-4 w-4" /> {{ post.comentarios.length }}
                        </button>
                        <button
                            v-if="!post.meu"
                            type="button"
                            class="ml-auto text-xs text-aura-faint transition hover:text-aura-muted"
                            @click="denunciar('post', post.id)"
                        >
                            {{ t('circulo.denunciar') }}
                        </button>
                    </div>

                    <!-- Comentarios -->
                    <div v-if="post.comentarios.length || comentandoEm === post.id" class="mt-4 space-y-3 border-t border-aura-line pt-4">
                        <div v-for="comentario in post.comentarios" :key="comentario.id" class="flex items-start gap-2.5">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-aura-raised text-[10px] font-bold text-aura-gold">
                                {{ iniciais(comentario.autor) }}
                            </span>
                            <div class="min-w-0 flex-1 rounded-xl bg-aura-raised px-3.5 py-2.5">
                                <p class="text-xs font-semibold text-aura-text">
                                    {{ comentario.autor }}
                                    <span class="ml-1.5 font-normal text-aura-faint">{{ comentario.quando }}</span>
                                </p>
                                <p class="mt-0.5 text-sm text-aura-text">{{ comentario.texto }}</p>
                            </div>
                            <button
                                v-if="!comentario.meu"
                                type="button"
                                class="mt-1 text-[10px] text-aura-faint hover:text-aura-muted"
                                @click="denunciar('comentario', comentario.id)"
                            >
                                ⚑
                            </button>
                        </div>

                        <form class="flex items-center gap-2" @submit.prevent="comentar(post)">
                            <input
                                v-model="comentarios[post.id]"
                                type="text"
                                maxlength="1000"
                                :placeholder="t('circulo.comentarPlaceholder')"
                                class="flex-1 rounded-full border-aura-line bg-aura-raised px-4 py-2 text-sm text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
                            />
                            <button type="submit" class="rounded-full border border-aura-gold/50 px-4 py-2 text-sm font-semibold text-aura-gold transition hover:bg-aura-gold/10">
                                {{ t('circulo.comentar') }}
                            </button>
                        </form>
                    </div>
                </article>
            </div>
        </div>
    </PainelLayout>
</template>
