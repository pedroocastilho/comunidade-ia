<script setup>
// Marca do Circulo Aura: anel com aura externa e estrela de 4 pontas.
// variante "marca" = so o simbolo; "completa" = simbolo + wordmark.
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
            <!-- aura externa -->
            <circle cx="32" cy="32" r="29" stroke="#C9A24B" stroke-opacity="0.22" stroke-width="1.4" />
            <!-- anel principal, aberto no topo-direito (onde nasce a estrela) -->
            <path
                d="M 43.3 12.4 A 22.6 22.6 0 1 0 51 22.5"
                stroke="url(#ouro-aura)"
                stroke-width="2.6"
                stroke-linecap="round"
            />
            <!-- eco interno -->
            <circle cx="32" cy="32" r="14.5" stroke="#C9A24B" stroke-opacity="0.4" stroke-width="1.1" />
            <!-- estrela de 4 pontas na abertura do anel -->
            <path
                d="M 49.4 9.6 L 51 14.6 L 56 16.2 L 51 17.8 L 49.4 22.8 L 47.8 17.8 L 42.8 16.2 L 47.8 14.6 Z"
                fill="url(#ouro-aura)"
            />
        </svg>

        <span v-if="variante === 'completa'" class="font-brand text-xl font-semibold tracking-[0.24em] text-aura-gold">
            CÍRCULO AURA
        </span>
    </span>
</template>
