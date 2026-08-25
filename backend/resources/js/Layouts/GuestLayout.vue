<script setup>
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-aura-black pt-10 sm:justify-center sm:pt-0">
        <div class="absolute right-5 top-5">
            <LanguageSwitcher />
        </div>

        <Link href="/" class="flex flex-col items-center gap-3">
            <span class="flex h-14 w-14 items-center justify-center rounded-full border border-aura-gold/60 text-aura-gold">
                <span class="font-display text-3xl font-semibold">A</span>
            </span>
            <span class="font-display text-2xl font-semibold tracking-[0.25em] text-aura-gold">
                CÍRCULO AURA
            </span>
        </Link>

        <div class="mt-8 w-full overflow-hidden border border-aura-line bg-aura-surface px-6 py-8 sm:max-w-md sm:rounded-2xl">
            <slot />
        </div>
    </div>
</template>
