<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Acesso inativo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Acesso inativo</h2>
        </template>

        <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-10 shadow-sm">
                <h1 class="text-2xl font-bold text-gray-800">Sua assinatura ainda não está ativa</h1>
                <p class="mt-4 text-gray-600">
                    Assim que sua assinatura for confirmada, todo o conteúdo é liberado
                    automaticamente aqui. Já assinou e continua vendo esta tela? Fale com o suporte.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
