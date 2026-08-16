/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    darkMode: 'class', // Enable dark mode with a class
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary Brand Colors (Stripe/Vercel inspired)
                primary: {
                    DEFAULT: '#6366F1', // Indigo 500
                    50: '#EEF2FF',
                    100: '#E0E7FF',
                    200: '#C7D2FE',
                    300: '#A5B4FC',
                    400: '#818CF8',
                    500: '#6366F1',
                    600: '#4F46E5',
                    700: '#4338CA',
                    800: '#3730A3',
                    900: '#312E81',
                    950: '#1e1b4b',
                },
                secondary: {
                    DEFAULT: '#1E293B', // Slate 800
                    50: '#F8FAFC',
                    100: '#F1F5F9',
                    200: '#E2E8F0',
                    300: '#CBD5E1',
                    400: '#94A3B8',
                    500: '#64748B',
                    600: '#475569',
                    700: '#334155',
                    800: '#1E293B',
                    900: '#0F172A',
                    950: '#020617',
                },
                accent: {
                    DEFAULT: '#06B6D4', // Cyan 500 (for highlights)
                    50: '#ECFEFF',
                    100: '#CFFAFE',
                    200: '#A5F3FC',
                    300: '#67E8F9',
                    400: '#22D3EE',
                    500: '#06B6D4',
                    600: '#0891B2',
                    700: '#0E7490',
                    800: '#155E75',
                    900: '#164E63',
                    950: '#083344',
                },
                success: {
                    DEFAULT: '#22C55E', // Green 500
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                    800: '#166534',
                    900: '#14532D',
                    950: '#052E16',
                },
                warning: {
                    DEFAULT: '#FBBF24', // Amber 400
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    200: '#FDE68A',
                    300: '#FCD34D',
                    400: '#FBBF24',
                    500: '#FB923C',
                    600: '#EA580C',
                    700: '#C2410C',
                    800: '#9A3412',
                    900: '#7C2D12',
                    950: '#431407',
                },
                danger: {
                    DEFAULT: '#EF4444', // Red 500
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    300: '#FCA5A5',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                    800: '#991B1B',
                    900: '#7F1D1D',
                    950: '#450A0A',
                },
                info: {
                    DEFAULT: '#3B82F6', // Blue 500
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                    950: '#172554',
                },

                // General UI Colors
                background: {
                    light: '#F8FAFC', // Slate 50
                    dark: '#0F172A',  // Slate 900
                },
                surface: {
                    light: '#FFFFFF',
                    dark: '#1E293B', // Slate 800
                },
                border: {
                    light: '#E2E8F0', // Slate 200
                    dark: '#334155',  // Slate 700
                },
                'text-primary': {
                    light: '#1E293B', // Slate 800
                    dark: '#F8FAFC',  // Slate 50
                },
                'text-secondary': {
                    light: '#64748B', // Slate 500
                    dark: '#94A3B8',  // Slate 400
                },
            },
            boxShadow: {
                // Soft, modern shadows
                'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'md': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)',
                'xl': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
                'glass-sm': '0 1px 3px rgba(0, 0, 0, 0.05)',
                'glass-md': '0 3px 8px rgba(0, 0, 0, 0.08)',
                'glass-lg': '0 8px 24px rgba(0, 0, 0, 0.12)',
                'card': 'rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.05) 0px 1px 2px 0px',
                'card-hover': 'rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.05) 0px 4px 8px 0px',
            },
            borderRadius: {
                'xs': '0.125rem', // 2px
                'sm': '0.25rem',  // 4px
                'md': '0.375rem', // 6px
                'lg': '0.5rem',   // 8px
                'xl': '0.75rem',  // 12px
                '2xl': '1rem',    // 16px
                '3xl': '1.5rem',  // 24px
                'full': '9999px',
            },
            spacing: {
                'px': '1px',
                '0': '0',
                '0.5': '0.125rem', // 2px
                '1': '0.25rem',    // 4px
                '1.5': '0.375rem', // 6px
                '2': '0.5rem',     // 8px
                '2.5': '0.625rem', // 10px
                '3': '0.75rem',    // 12px
                '3.5': '0.875rem', // 14px
                '4': '1rem',       // 16px
                '5': '1.25rem',    // 20px
                '6': '1.5rem',     // 24px
                '7': '1.75rem',    // 28px
                '8': '2rem',       // 32px
                '9': '2.25rem',    // 36px
                '10': '2.5rem',    // 40px
                '11': '2.75rem',   // 44px
                '12': '3rem',      // 48px
                '14': '3.5rem',    // 56px
                '16': '4rem',      // 64px
                '20': '5rem',      // 80px
                '24': '6rem',      // 96px
                '28': '7rem',      // 112px
                '32': '8rem',      // 128px
                '36': '9rem',      // 144px
                '40': '10rem',     // 160px
                '44': '11rem',     // 176px
                '48': '12rem',     // 192px
                '52': '13rem',     // 208px
                '56': '14rem',     // 224px
                '60': '15rem',     // 240px
                '64': '16rem',     // 256px
                '72': '18rem',     // 288px
                '80': '20rem',     // 320px
                '96': '24rem',     // 384px
            },
            backdropBlur: {
                xs: '4px',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
