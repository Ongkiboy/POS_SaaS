import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { setupApi } from '@/lib/setupApi'

const app = createApp(App)

app.use(createPinia())
app.use(router)

setupApi(router)

app.mount('#app')
