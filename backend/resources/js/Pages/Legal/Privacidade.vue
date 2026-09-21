<script setup>
import LogoAura from '@/Components/LogoAura.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Política de Privacidade" />

    <div class="min-h-screen bg-aura-black px-5 py-12 text-aura-text">
        <div class="mx-auto max-w-3xl">
            <Link href="/" class="flex items-center gap-3">
                <LogoAura tamanho="h-11 w-11" />
                <span class="font-brand text-lg font-semibold tracking-[0.24em] text-aura-gold">CÍRCULO AURA</span>
            </Link>

            <h1 class="mt-10 font-display text-4xl font-semibold">Política de Privacidade</h1>
            <p class="mt-2 text-sm text-aura-faint">Última atualização: 21 de setembro de 2026 · Conforme a LGPD (Lei 13.709/2018)</p>

            <div class="mt-8 space-y-6 leading-relaxed text-aura-muted">
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">1. Quais dados coletamos</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li><strong class="text-aura-text">Cadastro:</strong> nome, e-mail e telefone (recebidos da plataforma de pagamento na compra).</li>
                        <li><strong class="text-aura-text">Uso da plataforma:</strong> respostas do questionário inicial, Aura Score, progresso na jornada, check-ins diários, metas, aulas e áudios consumidos.</li>
                        <li><strong class="text-aura-text">Conversas com o Noah:</strong> mensagens trocadas com o assistente de IA e memórias derivadas delas, usadas para personalizar seu acompanhamento.</li>
                        <li><strong class="text-aura-text">Comunidade:</strong> posts, comentários e reações que você publica no Círculo.</li>
                        <li><strong class="text-aura-text">Técnicos:</strong> cookies de sessão essenciais ao funcionamento (login, idioma, segurança). Não usamos cookies de publicidade.</li>
                    </ul>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">2. Para que usamos</h2>
                    <p class="mt-2">Para entregar o serviço contratado (execução de contrato): personalizar sua jornada, operar o Noah, exibir seu progresso, enviar e-mails transacionais (acesso, senha, avisos da conta) e manter a segurança da plataforma. Não vendemos seus dados.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">3. Com quem compartilhamos</h2>
                    <p class="mt-2">Apenas com operadores necessários ao serviço: <strong class="text-aura-text">Anthropic</strong> (processamento das conversas com o Noah), <strong class="text-aura-text">Bunny Stream</strong> (entrega de vídeos e áudios), <strong class="text-aura-text">Resend</strong> (envio de e-mails), plataformas de pagamento (<strong class="text-aura-text">Kiwify/Hotmart</strong>, que nos informam o status da sua assinatura) e nossa infraestrutura de hospedagem. Alguns operadores processam dados fora do Brasil, com salvaguardas contratuais adequadas.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">4. Por quanto tempo guardamos</h2>
                    <p class="mt-2">Enquanto sua conta existir. Se você excluir a conta (em Configurações), seus dados pessoais, conversas, progresso e publicações são apagados definitivamente; registros mínimos de pagamento podem ser mantidos pelo prazo legal fiscal.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">5. Seus direitos (LGPD)</h2>
                    <p class="mt-2">Você pode solicitar confirmação de tratamento, acesso, correção, portabilidade, anonimização ou exclusão dos seus dados, além de revogar consentimentos. A exclusão de conta está disponível diretamente em Configurações; os demais pedidos podem ser feitos pelo e-mail <a href="mailto:noah@circuloaura.com" class="text-aura-gold underline">noah@circuloaura.com</a> — respondemos em até 15 dias.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">6. Segurança</h2>
                    <p class="mt-2">Usamos criptografia em trânsito (HTTPS), senhas com hash, controle de acesso e monitoramento. Nenhum sistema é 100% infalível; em caso de incidente relevante, comunicaremos você e as autoridades conforme a LGPD.</p>
                </section>
                <section>
                    <h2 class="font-display text-xl font-semibold text-aura-text">7. Contato do encarregado (DPO)</h2>
                    <p class="mt-2">Diamond Global — <a href="mailto:noah@circuloaura.com" class="text-aura-gold underline">noah@circuloaura.com</a>.</p>
                </section>
            </div>

            <div class="mt-10 flex gap-4 text-sm">
                <Link :href="route('termos')" class="text-aura-gold underline">Termos de Uso</Link>
                <Link href="/" class="text-aura-muted underline">Voltar ao início</Link>
            </div>
        </div>
    </div>
</template>
