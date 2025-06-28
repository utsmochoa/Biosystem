import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-out',
              },
              keyframes: {
                fadeIn: {
                  '0%': { opacity: '0', transform: 'scale(0.95)' },
                  '100%': { opacity: '1', transform: 'scale(1)' },
                },
              },

        },
    },
    plugins: [],
};
