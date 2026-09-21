<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import LogoAura from '@/Components/LogoAura.vue';
import { useI18n } from '@/useI18n';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { t } = useI18n();
const page = usePage();

const usuario = computed(() => page.props.auth?.user);
const categorias = computed(() => page.props.categorias ?? []);
const avisoTopo = computed(() => page.props.aviso_topo);

const iniciais = computed(() => {
    const nome = usuario.value?.name ?? '';
    return nome
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((p) => p[0])
        .join('')
        .toUpperCase();
});

const nav = [
    { key: 'nav.inicio', icon: 'home', rota: 'home', ativos: ['home'] },
    { key: 'nav.jornada', icon: 'map', rota: 'jornada', ativos: ['jornada'] },
    { key: 'nav.biblioteca', icon: 'grid', rota: 'cursos', ativos: ['cursos', 'curso', 'aula', 'audios', 'audio'] },
    { key: 'nav.aura', icon: 'sparkles', rota: 'aura', ativos: ['aura'] },
    { key: 'nav.circulo', icon: 'users', rota: 'circulo', ativos: ['circulo'] },
    { key: 'nav.metas', icon: 'target', rota: 'metas', ativos: ['metas'] },
    { key: 'nav.config', icon: 'settings', rota: 'profile.edit', ativos: ['profile.edit'] },
];

function ativo(item) {
    return item.ativos.some((r) => route().current(r));
}

const busca = ref('');
function buscar() {
    if (busca.value.trim()) {
        router.get(route('cursos'), { busca: busca.value.trim() });
    }
}
</script>

<template>
    <div class="flex min-h-screen bg-aura-black text-aura-text">
        <!-- Rail lateral (desktop) -->
        <aside class="sticky top-0 hidden h-screen w-20 shrink-0 flex-col items-center justify-between border-r border-aura-line bg-aura-deep py-6 md:flex">
            <div class="flex flex-col items-center gap-8">
                <Link :href="route('home')" aria-label="Círculo Aura">
                    <LogoAura tamanho="h-11 w-11" />
                </Link>
                <nav class="flex flex-col items-center gap-2">
                    <template v-for="item in nav" :key="item.key">
                        <Link
                            v-if="item.rota"
                            :href="route(item.rota)"
                            class="flex w-16 flex-col items-center gap-1 rounded-2xl py-2.5 transition"
                            :class="ativo(item) ? 'bg-aura-gold/10 text-aura-gold' : 'text-aura-muted hover:bg-aura-raised hover:text-aura-text'"
                        >
                            <AppIcon :name="item.icon" class="h-6 w-6" />
                            <span class="text-[10px] font-semibold uppercase tracking-wide">{{ t(item.key) }}</span>
                        </Link>
                        <span
                            v-else
                            class="flex w-16 cursor-default flex-col items-center gap-1 rounded-2xl py-2.5 text-aura-faint"
                            :title="t('nav.emBreve')"
                        >
                            <AppIcon :name="item.icon" class="h-6 w-6" />
                            <span class="text-[10px] font-semibold uppercase tracking-wide">{{ t(item.key) }}</span>
                        </span>
                    </template>
                </nav>
            </div>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex w-16 flex-col items-center gap-1 rounded-2xl py-2.5 text-aura-muted transition hover:bg-aura-raised hover:text-aura-text"
            >
                <AppIcon name="logout" class="h-6 w-6" />
                <span class="text-[10px] font-semibold uppercase tracking-wide">{{ t('nav.sair') }}</span>
            </Link>
        </aside>

        <!-- Coluna principal -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Barrinha de aviso no topo (unico elemento fixo, estilo MeuFluxo) -->
            <div v-if="avisoTopo" class="flex items-center justify-center gap-2 bg-aura-gold px-4 py-1.5 text-center text-sm font-semibold text-aura-black">
                {{ avisoTopo }}
            </div>

            <div class="relative flex-1">
                <!-- Barra transparente flutuando por cima do conteudo/hero -->
                <header class="absolute inset-x-0 top-0 z-20 flex h-16 items-center justify-between gap-4 px-5 lg:px-10">
                    <div class="flex items-center gap-8">
                        <span class="font-brand text-lg font-semibold tracking-[0.25em] text-aura-gold drop-shadow">CÍRCULO AURA</span>
                        <nav class="hidden items-center gap-6 lg:flex">
                            <Link
                                v-for="cat in categorias"
                                :key="cat.slug"
                                :href="route('cursos', { categoria: cat.slug })"
                                class="text-sm font-semibold text-aura-text/80 drop-shadow transition hover:text-aura-gold"
                            >
                                {{ cat.nome }}
                            </Link>
                        </nav>
                    </div>

                    <div class="flex flex-1 items-center justify-end gap-3">
                        <form class="relative hidden sm:block" @submit.prevent="buscar">
                            <AppIcon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-aura-muted" />
                            <input
                                v-model="busca"
                                type="search"
                                :placeholder="t('nav.buscar')"
                                class="w-40 rounded-full border-aura-line/60 bg-aura-black/40 py-2 pl-10 pr-4 text-sm text-aura-text placeholder-aura-faint backdrop-blur focus:border-aura-gold focus:ring-aura-gold lg:w-56"
                            />
                        </form>
                        <LanguageSwitcher />
                        <Link
                            :href="route('profile.edit')"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-aura-gold/50 bg-aura-black/40 text-sm font-bold text-aura-gold backdrop-blur"
                        >
                            {{ iniciais }}
                        </Link>
                    </div>
                </header>

                <main class="min-h-full pb-24 pt-16 md:pb-0">
                    <slot />
                </main>
            </div>
        </div>

        <!-- Nav inferior (mobile) -->
        <nav class="fixed inset-x-0 bottom-0 z-20 flex items-center justify-around border-t border-aura-line bg-aura-deep py-2 md:hidden">
            <template v-for="item in nav" :key="item.key">
                <Link
                    v-if="item.rota"
                    :href="route(item.rota)"
                    class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-0.5 py-1"
                    :class="ativo(item) ? 'text-aura-gold' : 'text-aura-muted'"
                >
                    <AppIcon :name="item.icon" class="h-6 w-6" />
                    <span class="w-full truncate text-center text-[9px] font-semibold">{{ t(item.key) }}</span>
                </Link>
                <span v-else class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-0.5 py-1 text-aura-faint">
                    <AppIcon :name="item.icon" class="h-6 w-6" />
                    <span class="w-full truncate text-center text-[9px] font-semibold">{{ t(item.key) }}</span>
                </span>
            </template>
        </nav>
    </div>
</template>
