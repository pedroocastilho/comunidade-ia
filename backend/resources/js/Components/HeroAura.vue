<script setup>
// Cena cosmica do hero (referencia NOYA/dribbble, em dourado):
// nucleo de energia com o anel da marca, planetas orbitando em pseudo-3D,
// nebulosa, poeira estelar e malha de ondas na base. Canvas 2D puro.
// Pausa em aba oculta; prefers-reduced-motion => 1 frame estatico.
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    // Caminho de um video de fundo (mp4). Sem video, roda a cena em canvas.
    video: { type: String, default: null },
});

const canvas = ref(null);
const vid = ref(null);
const videoFalhou = ref(false);
let animacao = null;

onMounted(() => {
    // Modo video: loop continuo com guardas (nunca fica parado)
    if (props.video) {
        const v = vid.value;
        if (!v) return;
        v.muted = true; // garante autoplay em todos os browsers
        const tocar = () => v.play().catch(() => {});
        const retomar = () => { if (!document.hidden) tocar(); };
        v.addEventListener('pause', retomar);
        v.addEventListener('ended', () => { v.currentTime = 0; tocar(); }); // reforco do loop
        v.addEventListener('stalled', tocar);
        v.addEventListener('error', () => { videoFalhou.value = true; });
        document.addEventListener('visibilitychange', retomar);
        tocar();

        onBeforeUnmount(() => document.removeEventListener('visibilitychange', retomar));
        return;
    }

    const el = canvas.value;
    if (!el) return;
    const ctx = el.getContext('2d');
    const reduzido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let L, A, cx, cy, R;

    function redimensionar() {
        L = el.clientWidth;
        A = el.clientHeight;
        el.width = L * devicePixelRatio;
        el.height = A * devicePixelRatio;
        ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
        // centro do sistema: direita do hero (texto fica a esquerda)
        cx = L * (L < 640 ? 0.5 : 0.7);
        cy = A * 0.44;
        R = Math.min(A * 0.19, 96);
    }
    redimensionar();
    const observador = new ResizeObserver(() => { redimensionar(); if (reduzido) quadro(0, true); });
    observador.observe(el);

    const aleatorio = (a, b) => a + Math.random() * (b - a);

    // estrelas fixas de fundo
    const estrelas = Array.from({ length: 70 }, () => ({
        x: Math.random(), y: Math.random(), raio: aleatorio(0.4, 1.1),
        fase: aleatorio(0, Math.PI * 2), freq: aleatorio(0.2, 0.9),
    }));

    // poeira estelar em disco ao redor do nucleo
    const poeira = Array.from({ length: 130 }, () => ({
        orbita: aleatorio(0.5, 3.4), ang: aleatorio(0, Math.PI * 2),
        vel: aleatorio(0.04, 0.16), raio: aleatorio(0.5, 1.4),
        achata: aleatorio(0.42, 0.62), fase: aleatorio(0, Math.PI * 2),
    }));

    // planetas: orbitas elipticas, z = profundidade (escala/alpha/ordem)
    const tons = [
        ['#F2E3B8', '#C9A24B', '#5C4620'],
        ['#E8CE8F', '#A8813A', '#4A3819'],
        ['#D9B563', '#8A6A2C', '#3A2C13'],
        ['#CDB07A', '#7A6234', '#332916'],
    ];
    const planetas = Array.from({ length: 13 }, (_, i) => ({
        a: aleatorio(1.5, 3.6), b: aleatorio(0.5, 1.1),
        ang: aleatorio(0, Math.PI * 2), vel: aleatorio(0.05, 0.16) * (Math.random() < 0.5 ? 1 : -1),
        raio: aleatorio(3, i % 4 === 0 ? 15 : 9), tom: tons[i % tons.length],
        inclina: aleatorio(-0.35, 0.35),
    }));

    // nebulosa: blobs de luz orbitando devagar
    const nebulosa = Array.from({ length: 7 }, () => ({
        orbita: aleatorio(0.4, 1.9), ang: aleatorio(0, Math.PI * 2),
        vel: aleatorio(0.02, 0.07), raio: aleatorio(0.8, 1.9),
        alfa: aleatorio(0.05, 0.12), fase: aleatorio(0, Math.PI * 2),
    }));

    function esfera(x, y, r, [claro, meio, escuro], alfa) {
        const g = ctx.createRadialGradient(x - r * 0.35, y - r * 0.35, r * 0.1, x, y, r);
        g.addColorStop(0, claro);
        g.addColorStop(0.55, meio);
        g.addColorStop(1, escuro);
        ctx.globalAlpha = alfa;
        ctx.beginPath();
        ctx.arc(x, y, r, 0, Math.PI * 2);
        ctx.fillStyle = g;
        ctx.fill();
        ctx.globalAlpha = 1;
    }

    function brilho(x, y, r, cor, alfa) {
        const g = ctx.createRadialGradient(x, y, 0, x, y, r);
        g.addColorStop(0, cor.replace('ALFA', String(alfa)));
        g.addColorStop(1, cor.replace('ALFA', '0'));
        ctx.fillStyle = g;
        ctx.fillRect(x - r, y - r, r * 2, r * 2);
    }

    const OURO = 'rgba(201, 162, 75, ALFA)';
    const OURO_CLARO = 'rgba(232, 206, 143, ALFA)';
    const BRANCO_QUENTE = 'rgba(255, 246, 224, ALFA)';

    function posPlaneta(p) {
        const x0 = Math.cos(p.ang) * p.a * R;
        const y0 = Math.sin(p.ang) * p.b * R;
        return {
            x: cx + x0 * Math.cos(p.inclina) - y0 * Math.sin(p.inclina),
            y: cy + x0 * Math.sin(p.inclina) + y0 * Math.cos(p.inclina),
            z: Math.sin(p.ang), // -1 (atras) .. 1 (na frente)
        };
    }

    let ultimo = 0;
    function quadro(agora, forcar = false) {
        const t = agora / 1000;
        const dt = Math.min(0.05, (agora - ultimo) / 1000 || 0.016);
        ultimo = agora;
        ctx.clearRect(0, 0, L, A);

        // estrelas de fundo
        for (const e of estrelas) {
            const alfa = 0.14 + 0.2 * Math.abs(Math.sin(t * e.freq + e.fase));
            ctx.globalAlpha = alfa;
            ctx.fillStyle = '#E8CE8F';
            ctx.beginPath();
            ctx.arc(e.x * L, e.y * A, e.raio, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;

        // malha de ondas na base (linhas senoidais finas)
        ctx.lineWidth = 1;
        for (let i = 0; i < 11; i++) {
            ctx.strokeStyle = `rgba(201, 162, 75, ${0.028 + i * 0.006})`;
            ctx.beginPath();
            for (let x = 0; x <= L; x += 10) {
                const y = A * 0.82 + i * 6.5
                    + Math.sin(x * 0.0075 + t * 0.5 + i * 0.4) * 13
                    + Math.sin(x * 0.017 - t * 0.32 + i * 0.15) * 5;
                x === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.stroke();
        }

        // nebulosa (aditiva)
        ctx.globalCompositeOperation = 'lighter';
        for (const n of nebulosa) {
            n.ang += n.vel * dt;
            const x = cx + Math.cos(n.ang) * n.orbita * R * 1.6;
            const y = cy + Math.sin(n.ang) * n.orbita * R * 0.75;
            const pulso = n.alfa * (0.75 + 0.25 * Math.sin(t * 0.4 + n.fase));
            brilho(x, y, n.raio * R, OURO, pulso.toFixed(3));
        }
        ctx.globalCompositeOperation = 'source-over';

        // planetas atras do nucleo
        const ordenados = planetas
            .map((p) => { p.ang += p.vel * dt; return { p, ...posPlaneta(p) }; })
            .sort((a, b) => a.z - b.z);
        for (const { p, x, y, z } of ordenados.filter((o) => o.z < 0)) {
            esfera(x, y, p.raio * (1 + 0.3 * z), p.tom, 0.5 + 0.3 * z);
        }

        // poeira estelar
        for (const g of poeira) {
            g.ang += g.vel * dt;
            const x = cx + Math.cos(g.ang) * g.orbita * R;
            const y = cy + Math.sin(g.ang) * g.orbita * R * g.achata;
            const alfa = 0.12 + 0.3 * Math.abs(Math.sin(t * 0.8 + g.fase));
            ctx.globalAlpha = alfa;
            ctx.fillStyle = '#DEBA6E';
            ctx.beginPath();
            ctx.arc(x, y, g.raio, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;

        // nucleo de energia
        const pulso = 1 + 0.035 * Math.sin(t * 1.1);
        ctx.globalCompositeOperation = 'lighter';
        brilho(cx, cy, R * 3.1 * pulso, OURO, '0.34');
        brilho(cx, cy, R * 1.7 * pulso, OURO_CLARO, '0.4');
        brilho(cx, cy, R * 0.85 * pulso, BRANCO_QUENTE, '0.9');
        ctx.globalCompositeOperation = 'source-over';

        // anel da marca ao redor do nucleo, com a estrela no topo
        ctx.strokeStyle = 'rgba(232, 206, 143, 0.85)';
        ctx.lineWidth = 1.8;
        ctx.beginPath();
        ctx.arc(cx, cy, R * 1.12, 0, Math.PI * 2);
        ctx.stroke();

        const sx = cx, sy = cy - R * 1.12;
        const sv = R * 0.34 * (1 + 0.18 * Math.sin(t * 1.6));
        const sh = sv * 0.78;
        ctx.fillStyle = '#F4E7C2';
        ctx.beginPath();
        ctx.moveTo(sx, sy - sv);
        ctx.quadraticCurveTo(sx + sh * 0.2, sy - sv * 0.2, sx + sh, sy);
        ctx.quadraticCurveTo(sx + sh * 0.2, sy + sv * 0.2, sx, sy + sv);
        ctx.quadraticCurveTo(sx - sh * 0.2, sy + sv * 0.2, sx - sh, sy);
        ctx.quadraticCurveTo(sx - sh * 0.2, sy - sv * 0.2, sx, sy - sv);
        ctx.fill();

        // planetas na frente do nucleo
        for (const { p, x, y, z } of ordenados.filter((o) => o.z >= 0)) {
            esfera(x, y, p.raio * (1 + 0.3 * z), p.tom, 0.6 + 0.35 * z);
        }

        if (!forcar && !reduzido) {
            animacao = requestAnimationFrame(quadro);
        }
    }

    function visibilidade() {
        if (document.hidden) {
            cancelAnimationFrame(animacao);
            animacao = null;
        } else if (!animacao && !reduzido) {
            animacao = requestAnimationFrame(quadro);
        }
    }
    document.addEventListener('visibilitychange', visibilidade);

    if (reduzido) {
        quadro(0, true); // um unico frame estatico
    } else {
        animacao = requestAnimationFrame(quadro);
    }

    onBeforeUnmount(() => {
        cancelAnimationFrame(animacao);
        observador.disconnect();
        document.removeEventListener('visibilitychange', visibilidade);
    });
});
</script>

<template>
    <div class="relative overflow-hidden bg-aura-deep">
        <!-- video de fundo em loop continuo -->
        <video
            v-if="video && !videoFalhou"
            ref="vid"
            :src="video"
            autoplay
            muted
            loop
            playsinline
            preload="auto"
            class="absolute inset-0 h-full w-full object-cover"
        ></video>

        <!-- cena cosmica em canvas (padrao / fallback) -->
        <canvas v-else ref="canvas" class="absolute inset-0 h-full w-full"></canvas>

        <!-- escurecimento para leitura do texto e fusao com a pagina -->
        <div class="absolute inset-0 bg-gradient-to-r from-aura-black/80 via-aura-black/25 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-aura-black via-aura-black/20 to-transparent"></div>

        <div class="relative h-full">
            <slot />
        </div>
    </div>
</template>
