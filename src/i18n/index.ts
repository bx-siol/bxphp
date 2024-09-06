// index.ts
import { createI18n } from 'vue-i18n'
import zh from './lang/zh'
import en from './lang/en'
import ind from './lang/ind'
const messages = {
    en,
    zh,
    ind,
}
const language = (navigator.language || 'en').toLocaleLowerCase() // 这是获取浏览器的语言
const i18n = createI18n({
    locale: localStorage.getItem('lang') || 'en', // 首先从缓存里拿，没有的话就用浏览器语言，
    fallbackLocale: 'en', // 设置备用语言
    messages,
    legacy: false,
    globalInjection: true,
})

export default i18n