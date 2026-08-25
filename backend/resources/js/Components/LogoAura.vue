<script setup>
// Marca do Circulo Aura: um unico anel dourado com abertura centrada no topo,
// onde assenta uma estrela de 4 pontas. Simetria total no eixo vertical.
defineProps({
    variante: { type: String, default: 'marca' }, // marca | completa
    tamanho: { type: String, default: 'h-10 w-10' },
});
</script>

<template>
    <span class="inline-flex items-center gap-3">
        <svg :class="tamanho" viewBox="0 0 64 64" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="ouro-aura" x1="12" y1="52" x2="52" y2="12" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#C9A24B" />
                    <stop offset="1" stop-color="#E8CE8F" />
                </linearGradient>
            </defs>
            <!-- anel unico com abertura simetrica no topo (pathLength 100: 88 de traco, 12 de vao) -->
            <circle
                cx="32" cy="34" r="21"
                stroke="url(#ouro-aura)"
                stroke-width="2.75"
                stroke-linecap="round"
                pathLength="100"
                stroke-dasharray="88 12"
                stroke-dashoffset="-6"
                transform="rotate(-90 32 34)"
            />
            <!-- estrela de 4 pontas assentada na abertura, no eixo central -->
            <path
                d="M 32 5.5 L 33.8 11.2 L 39.5 13 L 33.8 14.8 L 32 20.5 L 30.2 14.8 L 24.5 13 L 30.2 11.2 Z"
                fill="url(#ouro-aura)"
            />
        </svg>

        <span v-if="variante === 'completa'" class="font-brand text-xl font-semibold tracking-[0.24em] text-aura-gold">
            CÍRCULO AURA
        </span>
    </span>
</template>
