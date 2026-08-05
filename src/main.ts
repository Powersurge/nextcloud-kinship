import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const app = createApp(App)
const pinia = createPinia()

app.use(router)
app.use(pinia)

usePermissionsStore(pinia).load().finally(() => {
	app.mount('#kinship')
})
