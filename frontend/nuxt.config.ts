// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: [
    '@nuxtjs/tailwindcss',
     '@pinia/nuxt', // required
    'pinia-plugin-persistedstate/nuxt',
    '@vueuse/nuxt'
  ],
  css: [
    '~/assets/css/main.css'
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE || 'http://localhost:8000/api'
    }
  },
  pinia: {
    autoImports: ['defineStore', 'acceptHMRUpdate']
  },
  app: {
    head: {
      title: 'Product Management System',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'A comprehensive product management system' }
      ]
    },
    pageTransition: { name: 'page', mode: 'out-in' }
  }
})
