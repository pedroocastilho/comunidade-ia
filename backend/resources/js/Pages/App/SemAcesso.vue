<script setup>
import PainelLayout from '@/Layouts/PainelLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Acesso inativo" />

    <PainelLayout>
        <div class="mx-auto flex max-w-2xl flex-col items-center px-5 py-24 text-center lg:px-10">
            <h1 class="font-display text-4xl font-extrabold tracking-tight text-gray-900">
                Sua assinatura ainda não está ativa
            </h1>
            <p class="mt-5 max-w-md text-gray-600">
                Assim que sua assinatura for confirmada, todo o conteúdo é liberado
                automaticamente aqui. Já assinou e continua vendo esta tela? Fale com o suporte.
            </p>
        </div>
    </PainelLayout>
</template>
