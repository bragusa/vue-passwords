<template>
  <div id="app" @dblclick="logout">
    <header v-if="route.name !== 'LoginView'">
      <div class="header">
        <div class="logo"><img src="/favicon.ico" width="22" /></div>
        <div>Secure Passwords</div>
      </div>
      <div class="spacer"><span v-if="showSeed">Seed: </span></div>
      <div class="logout">
        <button class="iconButton" @click="logout">
          <ArrowLeftStartOnRectangleIcon class="size-22 icon" />
        </button>
      </div>
    </header>
    <RouterView v-slot="{ Component }">
      <transition :name="routeTransition">
        <keep-alive include="ListView">
          <component :is="Component" />
        </keep-alive>
      </transition>
    </RouterView>
    <Wait v-if="appStore.loading" />
  </div>
</template>

<script>
import { computed, defineComponent, onUnmounted, ref } from 'vue'
import { ArrowLeftStartOnRectangleIcon } from '@heroicons/vue/24/solid'
import { useRouter, useRoute } from 'vue-router'
import { useAppStore } from './stores/accountData'
import DBAdapter from '@/rest/DatabaseAdapter'
import Wait from '@/components/Wait.vue'

export default defineComponent({
  name: 'App',
  components: {
    ArrowLeftStartOnRectangleIcon,
    Wait,
  },
  setup() {
    // Get the current route object
    const route = useRoute()
    const router = useRouter()
    const appStore = useAppStore()
    const timeoutCheck = ref(null)

    // Compute the transition dynamically based on route.meta.transition
    const routeTransition = computed(() => {
      console.log('Computed route transition:', route.meta.transition) // Debug log
      return route.meta.transition || 'fade' // Default to 'fade' if no transition is set
    })

    const logout = () => {
      appStore.setUserName(null)
      router.push({
        name: 'LoginView',
      })
    }

    timeoutCheck.value = setInterval(async () => {
      if (route.name === 'LoginView') return //no need to check if not logged in (yet
      const dbAdapter = DBAdapter()
      const connectionResult = await dbAdapter.validateConnection()

      //router would do this, but no need to create or focus if not connected.
      if (connectionResult.status === 'failed') {
        router.push({
          name: 'LoginView',
        })
        return
      }
    }, 10000) //every 30 seconds

    onUnmounted(() => {
      clearInterval(timeoutCheck.value)
    })

    return {
      route,
      routeTransition,
      logout,
      appStore,
    }
  },
})
</script>

<style>
/* Fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
  background-color: var(--color-background);
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  width: 100vw;
  background-color: var(--color-background);
}

/* Slide Left (Forward Navigation) */
.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.3s ease;
  position: absolute;
  top: 3rem;
  width: 100vw;
}

.slide-left-enter-from {
  transform: translateX(100%); /* Start off-screen to the right */
}

.slide-left-leave-to {
  transform: translateX(-100%); /* Exit off-screen to the right */
}

/* Slide Right (Backward Navigation) */
.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.3s ease;
  position: absolute;
  top: 3rem;
  width: 100vw;
}

.slide-right-enter-from {
  transform: translateX(-100%); /* Start off-screen to the left */
}

.slide-right-leave-to {
  transform: translateX(100%); /* Exit off-screen to the right */
}

.size-22 {
  height: 22px;
}

.size-32 {
  height: 32px;
}

.icon-blue {
  fill: blue;
}

.icon-green {
  fill: green;
}

.icon-red {
  fill: red;
}

.icon-yellow {
  fill: yellow;
}

.icon {
  fill: var(--color-text);
}
</style>

<style scoped>
header {
  position: sticky;
  top: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1;
  height: 3rem;
  padding: 0.25rem 1rem;
  background-color: var(--color-background-soft);
  font-size: 1.25rem;
  border-bottom: 1px solid var(--color-border);
  padding-inline-end: 0.5rem;

  > div {
    display: flex;
    align-items: center;
  }
}

.logo {
  margin-inline-end: 0.5rem;
  display: inline-flex;
  align-items: center;
  img {
    width: 22px;
    height: 22px;
  }
}

nav {
  width: 100%;
  font-size: 12px;
  text-align: center;
  margin-top: 3rem;
}

nav a.router-link-exact-active {
  color: var(--color-text);
}

nav a.router-link-exact-active:hover {
  background-color: transparent;
}

nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
}

nav a:first-of-type {
  border: 0;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }

  nav {
    text-align: left;
    margin-left: -1rem;
    font-size: 1rem;

    padding: 1rem 0;
    margin-top: 1rem;
  }
}
</style>
