import 'element-plus/dist/index.css'
import './assets/frontend.scss'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './FrontendView.vue'

console.log("searching for memberdata_ranking-frontend");
const el = document.getElementById('memberdata_ranking-frontend');
let props = {};
if (el) {
    const data = el.getAttribute('data-memberdata_ranking');
    if (data) {
        props = JSON.parse(data);
    }
}

const app = createApp(App, props);
app.use(createPinia());
app.mount('#memberdata_ranking-frontend');
