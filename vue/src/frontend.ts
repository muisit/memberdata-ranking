import './assets/main.scss'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './FrontendView.vue'

const app = createApp(App)

app.use(createPinia())

app.mount('#memberdataranking-frontend')
