import 'element-plus/dist/index.css'
import './assets/main.scss'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './AdminView.vue'

const el = document.getElementById('memberdata_ranking-admin');
let props = {};
if (el) {
    const data = el.getAttribute('data-memberdata_ranking');
    if (data) {
        props = JSON.parse(data);
    }
}

const app = createApp(App, props);
app.use(createPinia())
app.mount('#memberdata_ranking-admin');
