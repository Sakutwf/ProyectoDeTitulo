const { defineConfig } = require('@vue/cli-service')

const apiTarget = process.env.VUE_APP_DEV_API_PROXY || 'http://127.0.0.1:8000'
const devServerHost = process.env.VUE_APP_DEV_SERVER_HOST || '0.0.0.0'
const devServerPort = Number(process.env.VUE_APP_DEV_SERVER_PORT || 8081)

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    host: devServerHost,
    port: devServerPort,
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
