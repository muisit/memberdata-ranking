import 'element-plus/dist/index.css'
import './assets/main.scss'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './AdminView.vue'

var el = document.getElementById('memberdata_ranking-admin');
var props = {};
if (el) {
    var data = el.getAttribute('data-memberdata_ranking');
    if (data) {
        props = JSON.parse(data);
    }
}

const app = createApp(App, props);
app.use(createPinia())
app.mount('#memberdata_ranking-admin');
