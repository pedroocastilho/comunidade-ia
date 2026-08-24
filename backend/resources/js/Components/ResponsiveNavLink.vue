<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full rounded-lg px-3 py-2 text-start text-base font-semibold text-emerald-700 bg-emerald-50 focus:outline-none focus:bg-emerald-100 transition duration-150 ease-in-out'
        : 'block w-full rounded-lg px-3 py-2 text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:bg-gray-50 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
