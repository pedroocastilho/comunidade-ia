<script setup>
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import LogoAura from '@/Components/LogoAura.vue';
import { useI18n } from '@/useI18n';
import { Head, useForm } from '@inertiajs/vue3';

const { t } = useI18n();

defineProps({
    apelido: String,
});

const form = useForm({
    password: '',
    password_confirmation: '',
});

function enviar() {
    form.post(route('senha.salvar'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head :title="t('primeiroAcesso.titulo')" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-aura-black px-5 text-aura-text">
        <div class="absolute right-5 top-5">
            <LanguageSwitcher />
        </div>

        <LogoAura tamanho="h-14 w-14" />
        <p class="mt-4 font-brand text-lg font-semibold tracking-[0.25em] text-aura-gold">CÍRCULO AURA</p>

        <div class="mt-8 w-full max-w-md rounded-2xl border border-aura-line bg-aura-surface p-8">
            <h1 class="font-display text-2xl font-semibold">{{ t('primeiroAcesso.bemVindo') }} {{ apelido }} ✦</h1>
            <p class="mt-2 text-sm leading-relaxed text-aura-muted">{{ t('primeiroAcesso.texto') }}</p>

            <form class="mt-6 space-y-4" @submit.prevent="enviar">
                <div>
                    <label class="text-sm font-semibold text-aura-muted" for="password">{{ t('primeiroAcesso.novaSenha') }}</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autofocus
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border-aura-line bg-aura-raised px-4 py-3 text-aura-text focus:border-aura-gold focus:ring-aura-gold"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-400">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-aura-muted" for="password_confirmation">{{ t('primeiroAcesso.repitaSenha') }}</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border-aura-line bg-aura-raised px-4 py-3 text-aura-text focus:border-aura-gold focus:ring-aura-gold"
                    />
                </div>
                <button
                    type="submit"
                    class="w-full rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light py-3 font-semibold text-aura-black transition hover:opacity-90 disabled:opacity-40"
                    :disabled="form.processing"
                >
                    {{ t('primeiroAcesso.salvar') }}
                </button>
            </form>
        </div>
    </div>
</template>
