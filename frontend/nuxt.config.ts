// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  // app: {
  //   baseURL: '/fe/',
  // },
  ssr: true,
  devtools: { enabled: true },
  modules: ['@t3headless/nuxt-typo3', 'nuxt-security'],
  vue: {
    compilerOptions: {
      isCustomElement: (tag) => {
        return tag.startsWith('ve-')
      }
    },
  },
  typo3: {
    api: {
      baseUrl: process.env.API_BASE || 'https://api.typo3.ddev.site/api',
      credentials: 'include',
      // headers: {},
      // proxyHeaders: true,
      // proxyReqHeaders: true,
      // endpoints: {
      //   initialData: "/initialData",
      //   initialDataFallback: "/initialData"
      // },
      //allowQuery: true
    }
  },
  devServer: {
      origin: ['https://api.typo3.ddev.site:3000', 'https://typo3.ddev.site', 'https://api.typo3.ddev.site'],
  },
  vite: {
    server: {
      allowedHosts: ['typo3.ddev.site', 'api.typo3.ddev.site', 'localhost'],
      hmr: {
        protocol: 'wss',
        clientPort: 443,
        path: 'hmr/'
      }
    },
  },
  security: {
    corsHandler: {
      origin: ['https://api.typo3.ddev.site:3000', 'https://typo3.ddev.site', 'https://api.typo3.ddev.site'],
      credentials: true
    },
    headers: {
      contentSecurityPolicy: {
        'frame-ancestors': ["'self' https://api.typo3.ddev.site https://typo3.ddev.site"],
      }
    }
  }
})
