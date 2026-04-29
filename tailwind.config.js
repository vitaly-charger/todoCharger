import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'ui-monospace', '"SF Mono"', 'Menlo', 'monospace'],
            },
            // CSS-variable-backed colors so accent + dark mode flips affect utilities.
            colors: {
                bg:           'var(--bg)',
                'bg-elev':    'var(--bg-elev)',
                'bg-sunken':  'var(--bg-sunken)',
                'bg-hover':   'var(--bg-hover)',
                'bg-active':  'var(--bg-active)',

                fg:           'var(--fg)',
                'fg-muted':   'var(--fg-muted)',
                'fg-subtle':  'var(--fg-subtle)',
                'fg-faint':   'var(--fg-faint)',

                border:          'var(--border)',
                'border-strong': 'var(--border-strong)',
                'border-focus':  'var(--border-focus)',

                accent:          'var(--accent)',
                'accent-hover':  'var(--accent-hover)',
                'accent-soft':   'var(--accent-soft)',
                'accent-soft-2': 'var(--accent-soft-2)',
                'accent-fg':     'var(--accent-fg)',
                'accent-on':     'var(--accent-on)',

                urgent:        'var(--urgent)',
                'urgent-soft': 'var(--urgent-soft)',
                'urgent-fg':   'var(--urgent-fg)',
                success:       'var(--success)',
                'success-soft':'var(--success-soft)',
                'success-fg':  'var(--success-fg)',
                info:          'var(--info)',
                'info-soft':   'var(--info-soft)',
                'info-fg':     'var(--info-fg)',
                warn:          'var(--warn)',
                'warn-soft':   'var(--warn-soft)',
                'warn-fg':     'var(--warn-fg)',
                review:        'var(--review)',
                'review-soft': 'var(--review-soft)',
                'review-fg':   'var(--review-fg)',
            },
            borderRadius: {
                xs: '4px',
                sm: '6px',
                DEFAULT: '7px',
                md: '8px',
                lg: '12px',
                xl: '16px',
            },
            boxShadow: {
                e1: '0 1px 0 rgba(20,18,14,.04), 0 1px 2px rgba(20,18,14,.04)',
                e2: '0 1px 0 rgba(20,18,14,.04), 0 4px 12px rgba(20,18,14,.05)',
                e3: '0 1px 0 rgba(20,18,14,.05), 0 12px 32px rgba(20,18,14,.09)',
                focus: '0 0 0 3px rgba(217,119,6,.18)',
            },
            fontSize: {
                '2xs': ['10.5px', { lineHeight: '1.2' }],
            },
            letterSpacing: {
                tight2: '-0.012em',
                tight3: '-0.018em',
                tight4: '-0.025em',
            },
        },
    },

    plugins: [forms],
};
