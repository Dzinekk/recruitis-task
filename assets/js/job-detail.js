import { createApp } from 'vue';
import App from './pages/JobDetail.vue';
import { plugin, defaultConfig } from '@formkit/vue'
import config from './formkit.config.js'

const appElement = document.getElementById('app');
const jobId = appElement.dataset.jobId;

createApp(App, {jobId: parseInt(jobId)})
  .use(plugin, defaultConfig(config))
  .mount('#app');
