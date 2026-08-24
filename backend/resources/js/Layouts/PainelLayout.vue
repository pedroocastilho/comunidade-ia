<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();

const categorias = computed(() => page.props.categorias ?? []);
const usuario = computed(() => page.props.auth?.user);

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
    { label: 'Início', icon: 'home', rota: 'home', ativos: ['home'] },
    { label: 'Cursos', icon: 'grid', rota: 'cursos', ativos: ['cursos', 'curso', 'aula'] },
    { label: 'Config', icon: 'settings', rota: 'profile.edit', ativos: ['profile.edit'] },
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
    <div class="flex min-h-screen bg-[#FAFAF9] text-gray-900">
        <!-- Rail lateral (desktop) -->
        <aside class="sticky top-0 hidden h-screen w-20 shrink-0 flex-col items-center justify-between border-r border-gray-200 bg-white py-6 md:flex">
            <div class="flex flex-col items-center gap-8">
                <Link :href="route('home')" aria-label="Comunidade IA">
                    <ApplicationLogo class="h-10 w-10" />
                </Link>
                <nav class="flex flex-col items-center gap-2">
                    <Link
                        v-for="item in nav"
                        :key="item.label"
                        :href="route(item.rota)"
                        class="flex w-16 flex-col items-center gap-1 rounded-2xl py-2.5 transition"
                        :class="ativo(item) ? 'bg-emerald-50 text-emerald-700' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-700'"
                    >
                        <AppIcon :name="item.icon" class="h-6 w-6" />
                        <span class="text-[10px] font-semibold uppercase tracking-wide">{{ item.label }}</span>
                    </Link>
                </nav>
            </div>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex w-16 flex-col items-center gap-1 rounded-2xl py-2.5 text-gray-400 transition hover:bg-gray-50 hover:text-gray-700"
            >
                <AppIcon name="logout" class="h-6 w-6" />
                <span class="text-[10px] font-semibold uppercase tracking-wide">Sair</span>
            </Link>
        </aside>

        <!-- Coluna principal -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Top bar -->
            <header class="sticky top-0 z-10 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-[#FAFAF9]/90 px-5 backdrop-blur lg:px-10">
                <nav class="hidden items-center gap-6 lg:flex">
                    <Link
                        v-for="cat in categorias"
                        :key="cat.slug"
                        :href="route('cursos', { categoria: cat.slug })"
                        class="text-sm font-semibold text-gray-500 transition hover:text-emerald-700"
                    >
                        {{ cat.nome }}
                    </Link>
                </nav>

                <div class="flex flex-1 items-center justify-end gap-4">
                    <form class="relative hidden sm:block" @submit.prevent="buscar">
                        <AppIcon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="busca"
                            type="search"
                            placeholder="Buscar"
                            class="w-44 rounded-full border-gray-200 bg-white py-2 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500 lg:w-60"
                        />
                    </form>
                    <Link
                        :href="route('profile.edit')"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white"
                    >
                        {{ iniciais }}
                    </Link>
                </div>
            </header>

            <main class="flex-1 pb-24 md:pb-0">
                <slot />
            </main>
        </div>

        <!-- Nav inferior (mobile) -->
        <nav class="fixed inset-x-0 bottom-0 z-20 flex items-center justify-around border-t border-gray-200 bg-white py-2 md:hidden">
            <Link
                v-for="item in nav"
                :key="item.label"
                :href="route(item.rota)"
                class="flex flex-col items-center gap-0.5 px-4 py-1"
                :class="ativo(item) ? 'text-emerald-700' : 'text-gray-400'"
            >
                <AppIcon :name="item.icon" class="h-6 w-6" />
                <span class="text-[10px] font-semibold">{{ item.label }}</span>
            </Link>
        </nav>
    </div>
</template>
