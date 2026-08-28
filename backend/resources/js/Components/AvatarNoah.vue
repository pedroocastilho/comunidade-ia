<script setup>
import { ref } from 'vue';

// Avatar do Noah: foto em /public/noah.jpg; enquanto a imagem nao existir
// (ou falhar), mostra um "N" dourado no mesmo circulo.
defineProps({
    tamanho: { type: String, default: 'h-10 w-10' },
    pulsar: { type: Boolean, default: false },
});

const semFoto = ref(false);
</script>

<template>
    <span
        class="avatar-noah relative flex shrink-0 items-center justify-center overflow-hidden rounded-full border border-aura-gold/50 bg-aura-gold/10"
        :class="[tamanho, { 'avatar-noah--pulsar': pulsar }]"
    >
        <img
            v-if="!semFoto"
            src="/noah.jpg"
            alt="Noah"
            class="h-full w-full object-cover"
            @error="semFoto = true"
        />
        <span v-else class="font-brand text-[0.95em] font-semibold text-aura-gold">N</span>
    </span>
</template>

<style scoped>
/* Pulso dourado enquanto o Noah escreve */
.avatar-noah--pulsar {
    animation: avatar-noah-pulso 1.6s ease-in-out infinite;
}
@keyframes avatar-noah-pulso {
    0%, 100% { box-shadow: 0 0 0 0 rgba(201, 162, 75, 0.45); }
    50% { box-shadow: 0 0 0 7px rgba(201, 162, 75, 0); }
}
@media (prefers-reduced-motion: reduce) {
    .avatar-noah--pulsar { animation: none; }
}
</style>
