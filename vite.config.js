import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/shop/app.css', 'resources/js/shop/app.js'],
            refresh: true,
        }),
    ],
});
