<script setup>
// Hero vivo da home: banner com Ken Burns (se houver destaque) + aura
// dourada respirando + particulas de luz em canvas. Respeita
// prefers-reduced-motion e pausa quando a aba fica oculta.
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
    imagem: { type: String, default: null },
});

const canvas = ref(null);
let animacao = null;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const el = canvas.value;
    const ctx = el.getContext('2d');
    let largura, altura;

    function redimensionar() {
        largura = el.clientWidth;
        altura = el.clientHeight;
        el.width = largura * devicePixelRatio;
        el.height = altura * devicePixelRatio;
        ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
    }
    redimensionar();

    const observador = new ResizeObserver(redimensionar);
    observador.observe(el);

    // particulas de luz: sobem devagar, cintilando
    const particulas = Array.from({ length: 34 }, () => ({
        x: Math.random(),
        y: Math.random(),
        raio: 0.6 + Math.random() * 1.6,
        vel: 0.006 + Math.random() * 0.014,
        deriva: (Math.random() - 0.5) * 0.02,
        fase: Math.random() * Math.PI * 2,
        freq: 0.4 + Math.random() * 1.2,
    }));

    let t = 0;
    function quadro() {
        t += 1 / 60;
        ctx.clearRect(0, 0, largura, altura);
        for (const p of particulas) {
            p.y -= p.vel / 60;
            if (p.y < -0.05) { p.y = 1.05; p.x = Math.random(); }
            const x = (p.x + Math.sin(t * 0.3 + p.fase) * p.deriva) * largura;
            const y = p.y * altura;
            const alfa = 0.28 + 0.32 * Math.sin(t * p.freq + p.fase);
            ctx.beginPath();
            ctx.arc(x, y, p.raio, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(222, 186, 110, ${Math.max(0, alfa)})`;
            ctx.fill();
        }
        animacao = requestAnimationFrame(quadro);
    }

    function visibilidade() {
        if (document.hidden) {
            cancelAnimationFrame(animacao);
            animacao = null;
        } else if (!animacao) {
            animacao = requestAnimationFrame(quadro);
        }
    }
    document.addEventListener('visibilitychange', visibilidade);
    animacao = requestAnimationFrame(quadro);

    onBeforeUnmount(() => {
        cancelAnimationFrame(animacao);
        observador.disconnect();
        document.removeEventListener('visibilitychange', visibilidade);
    });
});
</script>

<template>
    <div class="relative overflow-hidden rounded-3xl border border-aura-line bg-aura-deep">
        <!-- banner do destaque com Ken Burns -->
        <img
            v-if="imagem"
            :src="imagem"
            alt=""
            class="hero-kenburns absolute inset-0 h-full w-full object-cover opacity-45"
        />

        <!-- aura respirando (gradientes animados) -->
        <div class="hero-aura hero-aura-1"></div>
        <div class="hero-aura hero-aura-2"></div>
        <div class="hero-aura hero-aura-3"></div>

        <!-- particulas de luz -->
        <canvas ref="canvas" class="absolute inset-0 h-full w-full"></canvas>

        <!-- escurecimento para leitura -->
        <div class="absolute inset-0 bg-gradient-to-r from-aura-black/85 via-aura-black/45 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-aura-black/70 via-transparent to-transparent"></div>

        <div class="relative">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.hero-kenburns {
    animation: kenburns 32s ease-in-out infinite alternate;
    will-change: transform;
}

@keyframes kenburns {
    from { transform: scale(1) translate(0, 0); }
    to { transform: scale(1.14) translate(-2.5%, -2%); }
}

/* blobs de aura dourada, desfocados, respirando devagar */
.hero-aura {
    position: absolute;
    border-radius: 9999px;
    filter: blur(70px);
    opacity: 0.16;
    will-change: transform, opacity;
}

.hero-aura-1 {
    width: 34rem;
    height: 34rem;
    right: -8rem;
    top: -14rem;
    background: radial-gradient(circle, #c9a24b 0%, transparent 70%);
    animation: respirar 11s ease-in-out infinite;
}

.hero-aura-2 {
    width: 26rem;
    height: 26rem;
    right: 16rem;
    bottom: -16rem;
    background: radial-gradient(circle, #8a6a2c 0%, transparent 70%);
    animation: respirar 14s ease-in-out 2s infinite;
}

.hero-aura-3 {
    width: 18rem;
    height: 18rem;
    left: 30%;
    top: -8rem;
    background: radial-gradient(circle, #e8ce8f 0%, transparent 70%);
    animation: respirar 17s ease-in-out 5s infinite;
}

@keyframes respirar {
    0%, 100% { transform: scale(1) translateY(0); opacity: 0.12; }
    50% { transform: scale(1.25) translateY(1.5rem); opacity: 0.26; }
}

@media (prefers-reduced-motion: reduce) {
    .hero-kenburns,
    .hero-aura {
        animation: none;
    }
}
</style>
