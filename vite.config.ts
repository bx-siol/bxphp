import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import * as path from "path";
import Components from 'unplugin-vue-components/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

// https://vitejs.dev/config/
export default defineConfig({
    base: '/',
    build: {
        chunkSizeWarningLimit: 1024,
        emptyOutDir: true,
        outDir: path.resolve() + '/ht8888/'
    },
    plugins: [
        vue(),
        // Components({
        //     resolvers: [ElementPlusResolver()],
        // })
    ],
    server: {
        port: 3001,
        proxy: {
            '/api/': {
                target: 'http://38.55.214.59:740/',//'http://8.210.239.216/',//
                changeOrigin: true,
                //rewrite: (path) => path.replace(/^\/api/, '')
            }
        }
    }
})
