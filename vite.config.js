import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/css/demo.css',
                'resources/assets/vendor/fonts/iconify/iconify.css',
                'resources/scss/app.scss',
                'resources/css/shop/app.css',
                'resources/js/shop/app.js',
            ],
            refresh: true,
        }),
    ],
});
