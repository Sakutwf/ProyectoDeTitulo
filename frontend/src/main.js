import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import 'bootstrap/dist/css/bootstrap.css'
import '@fortawesome/fontawesome-free/css/all.css'

import './assets/styles/cruz-roja-theme.css';
import './assets/styles/document-print.css';
import './assets/styles/admin-layout.css';

store.dispatch('restoreSession')

createApp(App).use(store).use(router).mount('#app')
