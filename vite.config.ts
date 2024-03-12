import { defineConfig } from 'vite'
import * as path from 'path'
import vue from '@vitejs/plugin-vue'
import legacy from '@vitejs/plugin-legacy'
import styleImport from 'vite-plugin-style-import'

// https://vitejs.dev/config/
export default defineConfig({

    base: '/',
    build: {
        emptyOutDir: true,
        chunkSizeWarningLimit: 1024,
        outDir: path.resolve() + '/h5/'//生成的后的文件存放目录
    },
    plugins: [
        vue(),
        legacy({
            targets: ['defaults', 'not IE 11']
        }),
        styleImport({
            libs: [
                {
                    libraryName: 'vant',
                    esModule: true,
                    // resolveStyle: (name) => `vant/es/${name}/style`,
                    resolveStyle: (name) => `../es/${name}/style/index`,
                },
            ],
        })
    ],
    server: {
        port: 3000,
        proxy: {
            '/api/': {
                target: 'http://38.55.214.59:739/',//'http://8.210.239.216/',//
                changeOrigin: true,
                //rewrite: (path) => path.replace(/^\/api/, '')
            }
        }
    }
})
