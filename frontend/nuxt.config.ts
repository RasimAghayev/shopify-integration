export default defineNuxtConfig({
  devtools: { enabled: true },

  srcDir: 'src',

  modules: [
    '@pinia/nuxt'
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost/api'
    }
  },

  typescript: {
    strict: true,
    typeCheck: true
  },

  components: {
    dirs: [
      '~/components/ui',
      '~/components/products',
      '~/components/sync',
      '~/components'
    ]
  },

  imports: {
    dirs: [
      'composables',
      'stores',
      'utils'
    ]
  },

  app: {
    head: {
      title: 'Shopify Integration',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ]
    }
  },

  css: ['~/assets/css/main.css'],

  compatibilityDate: '2024-01-01'
})
