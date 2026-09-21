<script setup>
import LogoAura from '@/Components/LogoAura.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Termos de Uso" />

    <div class="min-h-screen bg-aura-black px-5 py-12 text-aura-text">
        <div class="mx-auto max-w-3xl">
            <Link href="/" class="flex items-center gap-3">
                <LogoAura tamanho="h-11 w-11" />
                <span class="font-brand text-lg font-semibold tracking-[0.24em] text-aura-gold">CÍRCULO AURA</span>
            </Link>

            <h1 class="mt-10 font-display text-4xl font-semibold">Termos de Uso</h1>
            <p class="mt-2 text-sm text-aura-faint">Última atualização: 21 de setembro de 2026</p>

            <div class="prose-aura mt-8 space-y-6 leading-relaxed text-aura-muted">
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">1. Sobre a plataforma</h2>
                    <p class="mt-2">O Círculo Aura é uma plataforma digital de desenvolvimento pessoal por assinatura, operada pela Diamond Global ("nós"). Ela oferece jornadas diárias, aulas, áudios, comunidade e o Noah, um assistente de inteligência artificial. Ao criar conta ou usar a plataforma, você concorda com estes Termos.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">2. Conta e acesso</h2>
                    <p class="mt-2">A conta é criada automaticamente após a confirmação do pagamento, usando o e-mail informado na compra. Ela é pessoal e intransferível: compartilhar login é violação destes Termos e pode levar à suspensão. Você é responsável por manter sua senha segura — ela é sua e deve ser trocada no primeiro acesso.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">3. Assinatura, pagamento e cancelamento</h2>
                    <p class="mt-2">A cobrança é recorrente e processada por plataformas de pagamento parceiras (como Kiwify e Hotmart), sujeitas aos termos delas. O acesso permanece ativo enquanto a assinatura estiver em dia. Você pode cancelar a qualquer momento pela plataforma onde comprou; o cancelamento interrompe as próximas cobranças e o acesso é encerrado. Reembolsos seguem o prazo legal de 7 dias (art. 49 do CDC) e a política da plataforma de pagamento. Em caso de reembolso ou estorno, o acesso é revogado.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">4. O Noah (inteligência artificial)</h2>
                    <p class="mt-2">O Noah é um assistente de IA de apoio motivacional e organização pessoal. Ele <strong class="text-aura-text">não substitui</strong> acompanhamento médico, psicológico, psiquiátrico, jurídico ou financeiro, e não faz diagnósticos nem promete resultados. Em situações de sofrimento emocional intenso, procure ajuda profissional — no Brasil, o CVV atende 24h pelo telefone 188.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">5. Comunidade (O Círculo)</h2>
                    <p class="mt-2">Ao publicar no Círculo, você se compromete a não postar conteúdo ilegal, ofensivo, discriminatório, comercial ou que exponha dados de terceiros. Podemos moderar, ocultar ou remover conteúdo e suspender contas que violem estas regras. Você mantém a autoria do que publica e nos autoriza a exibi-lo dentro da plataforma.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">6. Propriedade intelectual</h2>
                    <p class="mt-2">Todo o conteúdo da plataforma (aulas, áudios, textos, marca, design) pertence à Diamond Global ou a seus licenciantes. O acesso é para uso pessoal; é proibido copiar, gravar, distribuir ou revender qualquer conteúdo.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">7. Limitação de responsabilidade</h2>
                    <p class="mt-2">A plataforma é fornecida "como está". Trabalhamos para mantê-la disponível e segura, mas não garantimos operação ininterrupta. Resultados de desenvolvimento pessoal dependem de fatores individuais — não garantimos resultados específicos.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">8. Alterações e contato</h2>
                    <p class="mt-2">Podemos atualizar estes Termos; mudanças relevantes serão comunicadas na plataforma ou por e-mail. Dúvidas: <a href="mailto:noah@circuloaura.com" class="text-aura-gold underline">noah@circuloaura.com</a>.</p>
                </section>
            </div>

            <div class="mt-10 flex gap-4 text-sm">
                <Link :href="route('privacidade')" class="text-aura-gold underline">Política de Privacidade</Link>
                <Link href="/" class="text-aura-muted underline">Voltar ao início</Link>
            </div>
        </div>
    </div>
</template>
