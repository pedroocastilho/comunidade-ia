import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // O worker do pdf.js e um asset .mjs; o nginx do CloudPanel serve
                // .mjs como application/octet-stream e o module worker recusa.
                // Emitindo como .js, o MIME sai application/javascript.
                assetFileNames: (asset) =>
                    (asset.names?.[0] ?? '').endsWith('.mjs')
                        ? 'assets/[name]-[hash].js'
                        : 'assets/[name]-[hash][extname]',
            },
        },
    },
});
