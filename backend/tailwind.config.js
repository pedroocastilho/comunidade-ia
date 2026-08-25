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
                sans: ['"Instrument Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Cormorant Garamond"', 'Sora', ...defaultTheme.fontFamily.serif],
            },
            // Identidade Circulo Aura: dark quente + dourado (PRD secao 1).
            // Nao e preto absoluto: base espresso com subtom ambar, para nao pesar.
            colors: {
                aura: {
                    black: '#16120C',
                    deep: '#1B1610',
                    surface: '#211B13',
                    raised: '#2B241A',
                    line: '#3B3224',
                    gold: '#C9A24B',
                    'gold-light': '#E8CE8F',
                    text: '#F2EBDD',
                    muted: '#A89D8C',
                    faint: '#6E6456',
                },
            },
        },
    },

    plugins: [forms],
};
