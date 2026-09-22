<script setup>
// Leitor de PDF embutido (pdf.js renderizando em canvas).
// Sem barra nativa do navegador: nao ha botao de baixar/imprimir e o
// texto nao e selecionavel — dificulta (nao impossibilita) copiar o material.
// Clicar numa pagina abre em tela cheia (setas navegam, Esc fecha).
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from '@/useI18n';

const props = defineProps({
    url: { type: String, required: true },
});

const { t } = useI18n();
const container = ref(null);
const carregando = ref(true);
const erro = ref(false);
const zoomSrc = ref(null);
const zoomIndex = ref(0);
let cancelado = false;
const canvases = [];

function abrirZoom(indice) {
    zoomIndex.value = indice;
    zoomSrc.value = canvases[indice].toDataURL('image/png');
}

function fecharZoom() {
    zoomSrc.value = null;
}

function navegarZoom(delta) {
    const novo = zoomIndex.value + delta;
    if (novo >= 0 && novo < canvases.length) {
        abrirZoom(novo);
    }
}

function aoTeclar(e) {
    if (! zoomSrc.value) return;
    if (e.key === 'Escape') fecharZoom();
    if (e.key === 'ArrowRight') navegarZoom(1);
    if (e.key === 'ArrowLeft') navegarZoom(-1);
}

onMounted(async () => {
    window.addEventListener('keydown', aoTeclar);

    try {
        const pdfjs = await import('pdfjs-dist');
        const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
        pdfjs.GlobalWorkerOptions.workerSrc = worker.default;

        // pdf.js 6 exige URL absoluta no formato objeto ({url}); string relativa falha
        const pdf = await pdfjs.getDocument({ url: new URL(props.url, window.location.origin).href }).promise;
        carregando.value = false;

        const larguraBase = container.value?.clientWidth || 700;
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
            canvas.className = 'cursor-zoom-in rounded-lg bg-white shadow-md transition hover:ring-2 hover:ring-aura-gold/60';
            canvas.title = t('player.ampliar');
            const indice = canvases.length;
            canvas.addEventListener('click', () => abrirZoom(indice));
            canvases.push(canvas);
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
    window.removeEventListener('keydown', aoTeclar);
});
</script>

<template>
    <div class="select-none" @contextmenu.prevent>
        <div v-if="carregando" class="flex items-center justify-center gap-3 py-16 text-aura-muted">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-aura-gold border-t-transparent"></span>
            {{ t('player.carregandoMaterial') }}
        </div>
        <p v-if="erro" class="py-16 text-center text-aura-muted">{{ t('player.erroMaterial') }}</p>
        <div ref="container" class="mx-auto max-w-2xl space-y-6"></div>

        <!-- Pagina em tela cheia -->
        <Teleport to="body">
            <div
                v-if="zoomSrc"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                @contextmenu.prevent
                @click.self="fecharZoom"
            >
                <img :src="zoomSrc" class="max-h-[92vh] max-w-[96vw] rounded-lg object-contain" :alt="t('player.aulaLeitura')" />

                <button
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-aura-black/80 text-xl text-aura-text transition hover:bg-aura-gold hover:text-aura-black"
                    :aria-label="t('player.fechar')"
                    @click="fecharZoom"
                >
                    ✕
                </button>

                <button
                    v-if="zoomIndex > 0"
                    class="absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-aura-black/80 text-xl text-aura-text transition hover:bg-aura-gold hover:text-aura-black"
                    aria-label="←"
                    @click.stop="navegarZoom(-1)"
                >
                    ←
                </button>
                <button
                    v-if="zoomIndex < canvases.length - 1"
                    class="absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-aura-black/80 text-xl text-aura-text transition hover:bg-aura-gold hover:text-aura-black"
                    aria-label="→"
                    @click.stop="navegarZoom(1)"
                >
                    →
                </button>

                <span class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-aura-black/80 px-3 py-1 text-sm text-aura-muted">
                    {{ zoomIndex + 1 }} / {{ canvases.length }}
                </span>
            </div>
        </Teleport>
    </div>
</template>
