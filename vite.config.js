import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    
    build: {
        // Production optimizations
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.logs in production
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
        },
        
        // Code splitting for better caching
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor chunk for libraries
                    vendor: ['vue', '@inertiajs/vue3'],
                    // Separate chunk for large libraries
                    utils: ['axios', 'lodash'],
                },
            },
        },
        
        // Asset optimization
        assetsInlineLimit: 4096, // Inline assets smaller than 4kb
        chunkSizeWarningLimit: 1000,
        
        // CSS optimization
        cssCodeSplit: true,
        cssMinify: true,
    },
    
    // Performance optimizations
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'axios', 'lodash'],
    },
    
    // Server config for development
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});

