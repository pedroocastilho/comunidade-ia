<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    destaque: Object,
    continuar: Array,
    trilhas: Array,
    avisos: Array,
});
</script>

<template>
    <Head title="Início" />

    <PainelLayout>
        <!-- Aviso -->
        <div v-if="avisos.length" class="border-b border-amber-200 bg-amber-50 px-5 py-2.5 text-sm text-amber-900 lg:px-10">
            <span class="font-semibold">{{ avisos[0].titulo }}</span>
            <span class="text-amber-800"> — {{ avisos[0].corpo }}</span>
        </div>

        <div class="mx-auto max-w-7xl px-5 py-8 lg:px-10 lg:py-12">
            <!-- Hero editorial -->
            <section v-if="destaque" class="grid items-center gap-8 lg:grid-cols-[1.4fr_1fr]">
                <div>
                    <h1 class="font-display text-5xl font-extrabold leading-[0.95] tracking-tight text-gray-900 lg:text-7xl">
                        {{ destaque.titulo }}
                    </h1>
                    <p v-if="destaque.instrutor" class="mt-5 text-sm font-semibold text-gray-500">
                        Com {{ destaque.instrutor.nome }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            :href="route('curso', destaque.slug)"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-7 py-3 font-semibold text-white transition hover:bg-emerald-700"
                        >
                            <AppIcon name="play" class="h-4 w-4" /> Iniciar
                        </Link>
                        <Link
                            :href="route('curso', destaque.slug)"
                            class="inline-flex items-center rounded-full border border-gray-300 px-7 py-3 font-semibold text-gray-800 transition hover:border-gray-400 hover:bg-white"
                        >
                            Detalhes
                        </Link>
                    </div>
                </div>

                <div class="hidden aspect-[4/5] overflow-hidden rounded-3xl bg-emerald-700 lg:block">
                    <img v-if="destaque.capa_url" :src="destaque.capa_url" :alt="destaque.titulo" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-end p-7">
                        <span class="font-display text-3xl font-bold leading-tight text-white/95">{{ destaque.titulo }}</span>
                    </div>
                </div>
            </section>

            <!-- Continue de onde parou -->
            <section v-if="continuar.length" class="mt-14">
                <h2 class="font-display text-xl font-bold tracking-tight text-gray-900">Continue de onde parou</h2>
                <div class="mt-4 flex gap-4 overflow-x-auto pb-2">
                    <Link
                        v-for="curso in continuar"
                        :key="curso.id"
                        :href="route('curso', curso.slug)"
                        class="group relative aspect-video w-80 shrink-0 overflow-hidden rounded-2xl bg-emerald-800"
                    >
                        <img v-if="curso.banner_url || curso.capa_url" :src="curso.banner_url || curso.capa_url" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-black/30"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 text-emerald-700">
                                <AppIcon name="play" class="h-6 w-6" />
                            </span>
                        </div>
                        <h3 class="absolute inset-x-0 bottom-0 p-4 font-display text-lg font-bold text-white">{{ curso.titulo }}</h3>
                    </Link>
                </div>
            </section>

            <!-- Trilhas por categoria -->
            <section v-for="trilha in trilhas" :key="trilha.slug" class="mt-14">
                <h2 class="font-display text-xl font-bold tracking-tight text-gray-900">{{ trilha.nome }}</h2>
                <div class="mt-4 flex gap-4 overflow-x-auto pb-2">
                    <Link
                        v-for="curso in trilha.cursos"
                        :key="curso.id"
                        :href="route('curso', curso.slug)"
                        class="group block w-44 shrink-0"
                    >
                        <div class="relative aspect-[3/4] overflow-hidden rounded-2xl bg-emerald-700 shadow-sm transition group-hover:shadow-lg">
                            <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                            <h3 class="absolute inset-x-0 bottom-0 p-3 font-display text-base font-bold leading-tight text-white">{{ curso.titulo }}</h3>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </PainelLayout>
</template>
