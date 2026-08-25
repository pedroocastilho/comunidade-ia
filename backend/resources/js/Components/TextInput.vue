<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-aura-line bg-aura-raised text-aura-text placeholder-aura-faint focus:border-aura-gold focus:ring-aura-gold"
        v-model="model"
        ref="input"
    />
</template>
