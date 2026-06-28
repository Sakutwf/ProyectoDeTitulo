const { defineConfig } = require('@vue/cli-service')

const apiTarget = process.env.VUE_APP_DEV_API_PROXY || 'http://localhost:8000'

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    host: '0.0.0.0',
    port: 8080,
    allowedHosts: 'all',
    proxy: {
      '^/api': {
        target: apiTarget,
        changeOrigin: true
      },
      '^/sanctum': {
        target: apiTarget,
        changeOrigin: true
      },
      '^/storage': {
        target: apiTarget,
        changeOrigin: true
      },
      '^/up': {
        target: apiTarget,
        changeOrigin: true
      }
    }
  }
})