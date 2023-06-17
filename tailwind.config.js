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

    safelist: [
        'carat'
    ],

    theme: {
        extend: {
            fontFamily: {
                'mono': ['Source Code Pro', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                'ui-orange-500': '#fc8437',
                'ui-grey-900': '#262626',
                'ui-yellow': '#ffcd4b',
                'ui-salmon': '#ff7b55',
            },
        },
    },

    plugins: [forms],
};
