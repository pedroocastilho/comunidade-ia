<script setup>
import PainelLayout from '@/Layouts/PainelLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { useI18n } from '@/useI18n';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    evolucao: Object,
    conquistas: Array,
});
</script>

<template>
    <Head :title="t('perfil.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-3xl space-y-6 px-5 py-8 lg:px-10 lg:py-12">
            <h1 class="font-display text-4xl font-extrabold tracking-tight text-aura-text">
                {{ t('perfil.titulo') }}
            </h1>

            <!-- Sua evolucao: nivel, XP e conquistas -->
            <div v-if="evolucao" class="rounded-2xl border border-aura-gold/30 bg-aura-surface p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-2xl font-semibold text-aura-text">{{ t('perfil.evolucao') }}</h2>
                    <div class="flex items-center gap-3">
                        <span v-if="evolucao.streak > 0" class="rounded-full border border-aura-gold/40 px-3 py-1 text-sm font-semibold text-aura-gold">
                            🔥 {{ evolucao.streak }}
                        </span>
                        <span class="rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light px-4 py-1 text-sm font-bold text-aura-black">
                            {{ t('perfil.nivel') }} {{ evolucao.nivel }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-baseline justify-between text-xs text-aura-muted">
                        <span>{{ evolucao.xp }} XP</span>
                        <span>{{ t('perfil.proximoNivel') }}: {{ evolucao.proximo_em }} XP</span>
                    </div>
                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-aura-raised">
                        <div class="h-full rounded-full bg-gradient-to-r from-aura-gold to-aura-gold-light transition-all duration-700" :style="{ width: evolucao.percentual + '%' }" />
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div
                        v-for="conquista in conquistas"
                        :key="conquista.slug"
                        class="rounded-xl border p-4 text-center transition"
                        :class="conquista.conquistado_em ? 'border-aura-gold/50 bg-aura-gold/5' : 'border-aura-line opacity-45'"
                        :title="conquista.descricao"
                    >
                        <div class="text-2xl">{{ conquista.conquistado_em ? conquista.icone : '🔒' }}</div>
                        <p class="mt-2 text-sm font-semibold" :class="conquista.conquistado_em ? 'text-aura-gold' : 'text-aura-muted'">
                            {{ conquista.nome }}
                        </p>
                        <p class="mt-0.5 text-[11px] leading-snug text-aura-muted">{{ conquista.descricao }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-aura-line bg-aura-surface p-6 sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="rounded-2xl border border-aura-line bg-aura-surface p-6 sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="rounded-2xl border border-aura-line bg-aura-surface p-6 sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </PainelLayout>
</template>
