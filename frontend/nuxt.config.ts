// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  modules: ['@pinia/nuxt', '@adminlte/nuxt'],
  adminlte: {
    defaults: {
      sidebarTheme: 'dark',
      fixedHeader: true,
      fixedSidebar: true,
      initialColorMode: 'auto',
    },
  },
  css: [
    'bootstrap-icons/font/bootstrap-icons.css',
    'overlayscrollbars/overlayscrollbars.css',
    '~/assets/css/main.css',
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
    },
  },
  devServer: {
    port: 5173,
    host: '0.0.0.0',
  },
  typescript: {
    strict: true,
  },
})
  