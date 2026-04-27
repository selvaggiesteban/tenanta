import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import router from './router'
import vuetify from './plugins/vuetify'
import i18n from './plugins/i18n'
import VueApexCharts from "vue3-apexcharts";

// Styles
import '@mdi/font/css/materialdesignicons.css'
import '@styles/main.scss'

const app = createApp(App)

// ApexCharts
app.use(VueApexCharts)

// Pinia
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)

// i18n (debe ir antes del router para que las rutas puedan usar traducciones)
app.use(i18n)

// Router
app.use(router)

// Vuetify
app.use(vuetify)

// Mount
app.mount('#app')
