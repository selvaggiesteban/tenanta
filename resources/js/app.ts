import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import router from './router'
import vuetify from './plugins/vuetify'

// Styles
import '@mdi/font/css/materialdesignicons.css'
import '@styles/main.scss'

const app = createApp(App)

// Pinia
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)

// Router
app.use(router)

// Vuetify
app.use(vuetify)

// Mount
app.mount('#app')
