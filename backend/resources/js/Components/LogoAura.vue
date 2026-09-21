<script setup>
// Marca do Circulo Aura: anel de galaxia dourado com estrela no topo
// (imagem oficial em public/logo.png; recorte circular para assentar no dark).
defineProps({
    variante: { type: String, default: 'marca' }, // marca | completa
    tamanho: { type: String, default: 'h-10 w-10' },
});
</script>

<template>
    <span class="inline-flex items-center gap-3">
        <img
            src="/logo.png"
            alt="Círculo Aura"
            :class="tamanho"
            class="rounded-full object-cover"
        />

        <span v-if="variante === 'completa'" class="font-brand text-xl font-semibold tracking-[0.24em] text-aura-gold">
            CÍRCULO AURA
        </span>
    </span>
</template>
