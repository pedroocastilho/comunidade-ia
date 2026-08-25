import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Cormorant Garamond"', 'Sora', ...defaultTheme.fontFamily.serif],
            },
            // Identidade Circulo Aura: preto e dourado (PRD secao 1)
            colors: {
                aura: {
                    black: '#0A0A0A',
                    deep: '#111111',
                    surface: '#141414',
                    raised: '#1A1A1A',
                    line: '#2A2A2A',
                    gold: '#C9A24B',
                    'gold-light': '#E5C878',
                    text: '#F5F0E8',
                    muted: '#9C948A',
                    faint: '#5C564E',
                },
            },
        },
    },

    plugins: [forms],
};
