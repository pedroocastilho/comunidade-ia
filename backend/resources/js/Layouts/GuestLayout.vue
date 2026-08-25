<script setup>
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import LogoAura from '@/Components/LogoAura.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-aura-black pt-10 sm:justify-center sm:pt-0">
        <div class="absolute right-5 top-5">
            <LanguageSwitcher />
        </div>

        <Link href="/" class="flex flex-col items-center gap-4">
            <LogoAura tamanho="h-16 w-16" />
            <span class="font-brand text-2xl font-semibold tracking-[0.24em] text-aura-gold">
                CÍRCULO AURA
            </span>
        </Link>

        <div class="mt-8 w-full overflow-hidden border border-aura-line bg-aura-surface px-6 py-8 sm:max-w-md sm:rounded-2xl">
            <slot />
        </div>
    </div>
</template>
