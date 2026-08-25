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
        <template v-else-if="name === 'map'">
            <path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2Z" />
            <path d="M9 4v14" />
            <path d="M15 6v14" />
        </template>
        <template v-else-if="name === 'sparkles'">
            <path d="M12 3v3M12 18v3M3 12h3M18 12h3" />
            <path d="m12 8 1.2 2.8L16 12l-2.8 1.2L12 16l-1.2-2.8L8 12l2.8-1.2L12 8Z" fill="currentColor" stroke="none" />
        </template>
        <template v-else-if="name === 'headphones'">
            <path d="M4 14v-3a8 8 0 0 1 16 0v3" />
            <rect x="3" y="14" width="4" height="6" rx="1.5" />
            <rect x="17" y="14" width="4" height="6" rx="1.5" />
        </template>
        <template v-else-if="name === 'flame'">
            <path d="M12 3s5 4.5 5 9a5 5 0 0 1-10 0c0-1.8.8-3.5 2-5 0 0 .3 2 1.5 2.5C10.5 8 12 3 12 3Z" />
        </template>
        <template v-else-if="name === 'chat'">
            <path d="M21 12a8 8 0 0 1-8 8H4l2-3a8 8 0 1 1 15-5Z" />
        </template>
    </svg>
</template>
