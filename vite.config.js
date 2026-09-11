import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// No Tailwind and no font plugin on purpose: the stylesheet is hand-written
// against the brand tokens in resources/css/app.css, and the two Google Fonts
// are requested from the layout so text paints before they arrive.
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/dashboard.css',
            ],
            refresh: true,
        }),
    ],
});
