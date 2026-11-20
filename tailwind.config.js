import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/robsontenorio/mary/src/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Calibri', 'Candara', 'Segoe UI', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#00236f',
                    navy: '#00236f',
                    light: '#eff3ff',
                    container: '#1e3a8a',
                    orange: '#fd761a',
                    amber: '#d97706',
                    emerald: '#10b981',
                },
                primary: {
                    DEFAULT: '#00236f',
                    deep: '#00236f',
                    container: '#1e3a8a',
                    hover: '#1e3a8a',
                    light: '#eff3ff',
                },
                surface: {
                    DEFAULT: '#f9f9ff',
                    low: '#eff3ff',
                    container: '#e6eeff',
                    high: '#dee9fd',
                },
            },
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: [
            {
                corporate: {
                    primary: '#00236f',
                    'primary-content': '#ffffff',
                    secondary: '#fd761a',
                    'secondary-content': '#ffffff',
                    accent: '#1e3a8a',
                    'accent-content': '#ffffff',
                    neutral: '#121c2a',
                    'neutral-content': '#ffffff',
                    'base-100': '#ffffff',
                    'base-200': '#f8fafc',
                    'base-300': '#eff3ff',
                    'base-content': '#1e293b',
                    info: '#1e3a8a',
                    success: '#10b981',
                    warning: '#f59e0b',
                    error: '#ef4444',
                },
            },
            'light',
        ],
        darkTheme: false,
    },
};

