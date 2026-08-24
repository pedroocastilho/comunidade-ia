<script setup>
defineProps({
    name: { type: String, required: true },
});
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <template v-if="name === 'home'">
            <path d="M4 11.5 12 4l8 7.5" />
            <path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9" />
        </template>
        <template v-else-if="name === 'grid'">
            <rect x="4" y="4" width="7" height="7" rx="1.5" />
            <rect x="13" y="4" width="7" height="7" rx="1.5" />
            <rect x="4" y="13" width="7" height="7" rx="1.5" />
            <rect x="13" y="13" width="7" height="7" rx="1.5" />
        </template>
        <template v-else-if="name === 'settings'">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
        </template>
        <template v-else-if="name === 'logout'">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="M16 17l5-5-5-5" />
            <path d="M21 12H9" />
        </template>
        <template v-else-if="name === 'search'">
            <circle cx="11" cy="11" r="7" />
            <path d="m21 21-4.3-4.3" />
        </template>
        <template v-else-if="name === 'play'">
            <path d="M8 5v14l11-7z" fill="currentColor" stroke="none" />
        </template>
        <template v-else-if="name === 'check'">
            <path d="M20 6 9 17l-5-5" />
        </template>
        <template v-else-if="name === 'star'">
            <path d="m12 2 3 6.5 7 .9-5 4.9 1.2 7L12 18l-6.4 3.3L6.8 14l-5-4.9 7-.9L12 2Z" />
        </template>
        <template v-else-if="name === 'arrow-left'">
            <path d="M19 12H5" />
            <path d="m12 19-7-7 7-7" />
        </template>
    </svg>
</template>
