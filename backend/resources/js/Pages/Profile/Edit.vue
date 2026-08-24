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
});
</script>

<template>
    <Head :title="t('perfil.titulo')" />

    <PainelLayout>
        <div class="mx-auto max-w-3xl space-y-6 px-5 py-8 lg:px-10 lg:py-12">
            <h1 class="font-display text-4xl font-extrabold tracking-tight text-gray-900">
                {{ t('perfil.titulo') }}
            </h1>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </PainelLayout>
</template>
