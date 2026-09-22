<script setup>
// Leitor de PDF embutido (pdf.js renderizando em canvas).
// Sem barra nativa do navegador: nao ha botao de baixar/imprimir e o
// texto nao e selecionavel — dificulta (nao impossibilita) copiar o material.
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from '@/useI18n';

const props = defineProps({
    url: { type: String, required: true },
});

const { t } = useI18n();
const container = ref(null);
const carregando = ref(true);
const erro = ref(false);
let cancelado = false;

onMounted(async () => {
    try {
        const pdfjs = await import('pdfjs-dist');
        const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
        pdfjs.GlobalWorkerOptions.workerSrc = worker.default;

        const pdf = await pdfjs.getDocument(props.url).promise;
        carregando.value = false;

        const larguraBase = container.value?.clientWidth || 800;
        const dpr = Math.min(window.devicePixelRatio || 1, 2);

        for (let n = 1; n <= pdf.numPages; n++) {
            if (cancelado || ! container.value) return;

            const pagina = await pdf.getPage(n);
            const escala = larguraBase / pagina.getViewport({ scale: 1 }).width;
            const viewport = pagina.getViewport({ scale: escala * dpr });

            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.style.width = '100%';
            canvas.style.height = 'auto';
            canvas.className = 'rounded-lg bg-white';
            container.value.appendChild(canvas);

            await pagina.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
        }
    } catch {
        carregando.value = false;
        erro.value = true;
    }
});

onBeforeUnmount(() => {
    cancelado = true;
});
</script>

<template>
    <div class="select-none" @contextmenu.prevent>
        <div v-if="carregando" class="flex items-center justify-center gap-3 py-16 text-aura-muted">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-aura-gold border-t-transparent"></span>
            {{ t('player.carregandoMaterial') }}
        </div>
        <p v-if="erro" class="py-16 text-center text-aura-muted">{{ t('player.erroMaterial') }}</p>
        <div ref="container" class="space-y-3"></div>
    </div>
</template>
