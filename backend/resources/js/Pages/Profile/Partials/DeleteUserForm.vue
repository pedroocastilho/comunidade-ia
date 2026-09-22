<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useI18n } from '@/useI18n';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const { t } = useI18n();

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-aura-text">
                {{ t('perfil.excluirTitulo') }}
            </h2>

            <p class="mt-1 text-sm text-aura-muted">
                {{ t('perfil.excluirDesc') }}
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">{{ t('perfil.excluir') }}</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-aura-text">
                    {{ t('perfil.excluirConfirmaTitulo') }}
                </h2>

                <p class="mt-1 text-sm text-aura-muted">
                    {{ t('perfil.excluirConfirmaDesc') }}
                </p>

                <div class="mt-6">
                    <InputLabel for="password" :value="t('auth.senha')" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        :placeholder="t('auth.senha')"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <SecondaryButton @click="closeModal">
                        {{ t('common.cancelar') }}
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ t('perfil.excluir') }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
