<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-[#FAFAF9] pt-10 sm:justify-center sm:pt-0">
        <div class="absolute right-5 top-5">
            <LanguageSwitcher />
        </div>

        <Link href="/" class="flex flex-col items-center gap-3">
            <ApplicationLogo class="h-14 w-14" />
            <span class="font-display text-2xl font-extrabold tracking-tight text-gray-900">
                Comunidade IA
            </span>
        </Link>

        <div class="mt-8 w-full overflow-hidden border border-gray-100 bg-white px-6 py-8 shadow-sm sm:max-w-md sm:rounded-2xl">
            <slot />
        </div>
    </div>
</template>
