<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { useI18n } from '@/useI18n';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    curso: Object,
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
            <aside class="bg-gray-900 px-6 py-10 text-white lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:px-10 lg:py-14">
                <Link :href="route('cursos')" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-400 transition hover:text-white">
                    <AppIcon name="arrow-left" class="h-4 w-4" /> {{ t('common.voltar') }}
                </Link>

                <h1 class="mt-8 font-display text-4xl font-extrabold leading-[0.95] tracking-tight lg:text-6xl">
                    {{ curso.titulo }}
                </h1>
                <p class="mt-5 max-w-md text-gray-300">{{ curso.descricao }}</p>

                <div class="mt-6 flex items-center gap-4 text-sm text-gray-400">
                    <span v-if="curso.instrutor">{{ t('common.com') }} {{ curso.instrutor.nome }}</span>
                    <span class="h-1 w-1 rounded-full bg-gray-600"></span>
                    <span>{{ total }} {{ t('common.aulas') }}</span>
                </div>

                <Link
                    v-if="primeira"
                    :href="route('aula', primeira.id)"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-emerald-500 px-7 py-3 font-semibold text-gray-900 transition hover:bg-emerald-400"
                >
                    <AppIcon name="play" class="h-4 w-4" />
                    {{ concluidas > 0 ? t('common.continuar') : t('common.iniciar') }}
                </Link>
            </aside>

            <!-- Aulas -->
            <div class="px-6 py-10 lg:px-10 lg:py-14">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-500">{{ t('curso.progresso') }}</span>
                    <span class="text-sm font-bold text-emerald-700">{{ percentual }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-emerald-600 transition-all" :style="{ width: percentual + '%' }"></div>
                </div>

                <div class="mt-10 space-y-8">
                    <div v-for="(modulo, mi) in curso.modulos" :key="mi">
                        <h2 class="font-display text-lg font-bold tracking-tight text-gray-900">{{ modulo.titulo }}</h2>
                        <ul class="mt-3 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                            <li v-for="aula in modulo.aulas" :key="aula.id">
                                <Link
                                    :href="route('aula', aula.id)"
                                    class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50"
                                >
                                    <span
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                        :class="aula.concluida ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700'"
                                    >
                                        <AppIcon :name="aula.concluida ? 'check' : 'play'" class="h-4 w-4" />
                                    </span>
                                    <span class="flex-1 font-medium text-gray-800">{{ aula.titulo }}</span>
                                    <span class="text-sm text-gray-400">{{ formatarDuracao(aula.duracao) }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </PainelLayout>
</template>
