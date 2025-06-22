// nuxt.config.ts
import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
    modules: [
        '@nuxtjs/tailwindcss'
    ],

    components: true,

    build: {
        transpile: [
            'naive-ui',
            'vueuc',
            'vooks',
            '@css-render/vue3-ssr'
        ]
    },

    vite: {
        ssr: {
            noExternal: [
                'naive-ui',
                'vueuc',
                'vooks',
                '@css-render/vue3-ssr'
            ]
        },
        server: {
            fs: {
                allow: ['..']
            }
        }
    }
})
