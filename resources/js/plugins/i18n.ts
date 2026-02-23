import { createI18n } from 'vue-i18n'
import esAR from '@/locales/es-AR.json'
import enUS from '@/locales/en-US.json'

export type MessageSchema = typeof esAR

// Detectar locale del navegador o usar guardado
function getDefaultLocale(): string {
  const saved = localStorage.getItem('locale')
  if (saved && ['es-AR', 'en-US'].includes(saved)) {
    return saved
  }

  const browserLang = navigator.language
  if (browserLang.startsWith('es')) {
    return 'es-AR'
  }
  if (browserLang.startsWith('en')) {
    return 'en-US'
  }

  return 'es-AR' // Default
}

const i18n = createI18n<[MessageSchema], 'es-AR' | 'en-US'>({
  legacy: false, // Composition API
  locale: getDefaultLocale(),
  fallbackLocale: 'en-US',
  messages: {
    'es-AR': esAR,
    'en-US': enUS,
  },
  numberFormats: {
    'es-AR': {
      currency: {
        style: 'currency',
        currency: 'ARS',
        notation: 'standard',
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      },
      percent: {
        style: 'percent',
        useGrouping: false,
      },
    },
    'en-US': {
      currency: {
        style: 'currency',
        currency: 'USD',
        notation: 'standard',
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      },
      percent: {
        style: 'percent',
        useGrouping: false,
      },
    },
  },
  datetimeFormats: {
    'es-AR': {
      short: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
      },
      datetime: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      },
    },
    'en-US': {
      short: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
      },
      datetime: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      },
    },
  },
})

// Helper para cambiar idioma
export function setLocale(locale: 'es-AR' | 'en-US') {
  i18n.global.locale.value = locale
  localStorage.setItem('locale', locale)
  document.documentElement.setAttribute('lang', locale)
}

export default i18n
