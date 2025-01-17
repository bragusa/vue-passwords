import { createRouter, createWebHistory } from 'vue-router'
import ListView from '@/views/ListView.vue'
import DetailView from '@/views/DetailView.vue'
import LoginView from '@/views/LoginView.vue'
import SeedView from '@/views/SeedView.vue'
import { useAppStore } from '@/stores/accountData'
import DBAdapter from '@/rest/DatabaseAdapter'

const routes = [
  {
    path: '/',
    name: 'LoginView',
    component: LoginView,
    meta: { index: 0 },
    //meta: { transition: useRoute.name && useRoute.name !== 'LoginView' ? 'slide-right' : 'fade' }, // Add meta for transitions
  },
  {
    path: '/seed',
    name: 'SeedView',
    component: SeedView,
    meta: { transition: 'fade' }, // Add meta for transitions
  },
  {
    path: '/list',
    name: 'ListView',
    component: ListView,
    meta: { index: 1 },
    // meta: { transition: 'slide-left' }, // Add meta for transitions
  },
  {
    path: '/details',
    name: 'DetailView',
    component: DetailView,
    meta: { index: 2 },
    // meta: { transition: 'slide-right' }, // Add meta for transitions
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.afterEach((to, from) => {
  console.log('Navigated from', from.name, 'to', to.name)
  const appStore = useAppStore() // Access the store instance
  setTimeout(() => {
    appStore.setLoading(false)
  }, 100)
})

router.beforeEach(async (to, from, next) => {
  console.log('Navigating from', from.name, 'to', to.name)
  if (!from.name) {
    to.meta.transition = 'fade'
  } else if (from.name === 'ListView' && to.name === 'DetailView') {
    to.meta.transition = 'slide-left'
  } else if (from.name === 'DetailView' && to.name === 'ListView') {
    to.meta.transition = 'slide-right'
  }

  const isAuthenticated = false
  const appStore = useAppStore() // Access the store instance

  const dbAdapter = DBAdapter()
  const connectionCheck = await dbAdapter.validateConnection()
  if (connectionCheck.status === 'failed') {
    if (to.name === 'LoginView') {
      next()
      return
    }
    next({ name: 'LoginView' })
    return
  }

  if (appStore.username !== null && to.name === 'LoginView') {
    next({ name: 'ListView' })
    return
  }

  if (appStore.username === null && to.name !== 'LoginView' && !isAuthenticated) {
    // Redirect to login page
    next({ name: 'LoginView' })
  } else {
    if (!appStore.seed && appStore.username && to.name !== 'SeedView') {
      next({ name: 'SeedView' })
      return
    }
    // Allow navigation
    next()
  }
})

export default router
