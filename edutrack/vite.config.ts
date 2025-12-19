import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
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
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    css: {
        postcss: {
            plugins: [tailwindcss, autoprefixer],
        },
    },
});

// NGROK working but needs Paid version

// import { loadEnv, defineConfig } from 'vite'
// import vue from '@vitejs/plugin-vue';
// import laravel from 'laravel-vite-plugin';
// import path from 'path';
// import tailwindcss from 'tailwindcss';
// import autoprefixer from 'autoprefixer';
// import fs from 'fs';
// import { ngrok } from 'vite-plugin-ngrok'
// const { NGROK_AUTH_TOKEN } = loadEnv('', process.cwd(), 'NGROK')


// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/js/app.ts'],
//             refresh: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//         ngrok({
//             // domain: 'my-domain.ngrok.app',
//             // compression: true,
//             authtoken: NGROK_AUTH_TOKEN,
//         }),
//     ],
//     resolve: {
//         alias: {
//             '@': path.resolve(__dirname, './resources/js'),
//         },
//     },
//     css: {
//         postcss: {
//             plugins: [tailwindcss, autoprefixer],
//         },
//     },
//     server: {
//         host: 'localhost', // Allow external access (required for Ngrok)
//         https: {
//             key: fs.readFileSync('./certs/localhost-key.pem'),
//             cert: fs.readFileSync('./certs/localhost.pem'),
//         },
//         proxy: {
//             '/api': {
//                 target: 'https://2255-136-158-117-248.ngrok-free.app', // Your Ngrok URL
//                 changeOrigin: true,
//                 secure: false,
//             },
//         },
//         allowedHosts: ['2255-136-158-117-248.ngrok-free.app'], // Allow Ngrok requests
//     },
// });


// Works only on this machine

// import { defineConfig } from 'vite';
// import vue from '@vitejs/plugin-vue';
// import laravel from 'laravel-vite-plugin';
// import path from 'path';
// import tailwindcss from 'tailwindcss';
// import autoprefixer from 'autoprefixer';
// import fs from 'fs';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/js/app.ts'],
//             refresh: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//     ],
//     resolve: {
//         alias: {
//             '@': path.resolve(__dirname, './resources/js'),
//         },
//     },
//     css: {
//         postcss: {
//             plugins: [tailwindcss, autoprefixer],
//         },
//     },
//     server: {
//         host: 'localhost', // Allow external access (required for Ngrok)
//         https: {
//             key: fs.readFileSync('./certs/localhost-key.pem'),
//             cert: fs.readFileSync('./certs/localhost.pem'),
//         },
//         proxy: {
//             '/api': {
//                 target: 'https://2255-136-158-117-248.ngrok-free.app', // Your Ngrok URL
//                 changeOrigin: true,
//                 secure: false,
//             },
//         },
//         allowedHosts: ['2255-136-158-117-248.ngrok-free.app'], // Allow Ngrok requests
//     },
// });



// import vue from '@vitejs/plugin-vue';
// import autoprefixer from 'autoprefixer';
// import laravel from 'laravel-vite-plugin';
// import path from 'path';
// import tailwindcss from 'tailwindcss';
// import { defineConfig } from 'vite';
// import fs from 'fs';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/js/app.ts'],
//             refresh: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//     ],
//     resolve: {
//         alias: {
//             '@': path.resolve(__dirname, './resources/js'),
//         },
//     },
//     css: {
//         postcss: {
//             plugins: [tailwindcss, autoprefixer],
//         },
//     },
//     server: {
//         host: '0.0.0.0', // Allows access from external networks
//         port: 5173,
//         strictPort: true,
//         https: {
//             key: fs.readFileSync('./certs/localhost-key.pem'),
//             cert: fs.readFileSync('./certs/localhost.pem'),
//         }, // Forces Vite to serve assets over HTTPS
//         allowedHosts: ['.ngrok-free.app'], // Allows all Ngrok subdomains
//         proxy: {
//             '/api': {
//                 target: 'https://2255-136-158-117-248.ngrok-free.app', // Update with your latest Ngrok URL
//                 changeOrigin: true,
//                 secure: true,
//             },
//         },
//     },
// });


//Working

// import vue from '@vitejs/plugin-vue';
// import autoprefixer from 'autoprefixer';
// import laravel from 'laravel-vite-plugin';
// import path from 'path';
// import tailwindcss from 'tailwindcss';
// import { defineConfig } from 'vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/js/app.ts'],
//             refresh: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//     ],
//     resolve: {
//         alias: {
//             '@': path.resolve(__dirname, './resources/js'),
//         },
//     },
//     css: {
//         postcss: {
//             plugins: [tailwindcss, autoprefixer],
//         },
//     },
//     server: {
//         host: '0.0.0.0', // Allows access from external networks
//         port: 5173,
//         strictPort: true,
//         allowedHosts: ['.ngrok-free.app'], // Allows all Ngrok subdomains
//         proxy: {
//             '/api': {
//                 target: 'https://2255-136-158-117-248.ngrok-free.app', // Update with your Ngrok URL
//                 changeOrigin: true,
//                 secure: true,
//             },
//         },
//     },
//     // server: {
//     //     proxy: {
//     //         '/api': {
//     //             target: 'https://03bc-136-158-117-248.ngrok-free.app', // Your Ngrok URL
//     //             changeOrigin: true,
//     //             secure: false, // Only set this to false if using self-signed certificates
//     //         },
//     //     },
//     // },
// });


// import vue from '@vitejs/plugin-vue';
// import autoprefixer from 'autoprefixer';
// import laravel from 'laravel-vite-plugin';
// import path from 'path';
// import tailwindcss from 'tailwindcss';
// import { defineConfig } from 'vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/js/app.ts'],
//             refresh: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//     ],
//     resolve: {
//         alias: {
//             '@': path.resolve(__dirname, './resources/js'),
//         },
//     },
//     css: {
//         postcss: {
//             plugins: [tailwindcss, autoprefixer],
//         },
//     },
// });

