<template>
  <div class="view list">
    <div class="heading" :data-count="accounts.length">
      <input class="search" type="search" v-model="searchQuery" placeholder="Search..." />
      <div>
        <button @click="openDetails('new', $event)" class="iconButton">
          <PlusIcon class="size-22 icon" />
        </button>
      </div>
    </div>
    <ul>
      <li class="listHeadings">
        <div><span>Account Name</span></div>
        <div>&nbsp;</div>
      </li>
      <li class="noData" v-if="accounts.length === 0 && !loading">No Accounts</li>
      <li
        :class="{ selected: appStore.selectedAccountId === account.id }"
        v-for="(account, index) in accounts"
        :key="account.id"
        :data-account-id="account.id"
        :ref="(el) => setItemRef(el, index)"
        @click="openDetails(account.id, $event)"
        @keydown.enter="openDetails(account.id, $event)"
        @keydown.up="focusPreviousItem(index, $event)"
        @keydown.down="focusNextItem(index, $event)"
        :tabindex="index === 0 ? 0 : -1"
        @focus="focusAccount"
        role="button"
      >
        <AccountValue>{{ account.sitename }}</AccountValue>
        <div>
          <!-- <button @click="openDetails(account.id)" class="iconButton small"> -->
          <ChevronRightIcon class="size-22 icon" />
          <!-- </button> -->
        </div>
      </li>
    </ul>
    <!-- <Wait v-if="loading" /> -->
  </div>
</template>

<script>
import { PlusIcon } from '@heroicons/vue/24/solid'
import { ChevronRightIcon } from '@heroicons/vue/24/solid'
import { useAppStore } from '@/stores/accountData'
import AccountValue from '@/components/AccountValue.vue'
import { Account } from '@/stores/accountData'
import { defineComponent, onMounted, ref, watch, onBeforeUnmount, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import DBAdapter from '@/rest/DatabaseAdapter'

export default defineComponent({
  name: 'ListView',
  components: {
    AccountValue,
    ChevronRightIcon,
    PlusIcon,
  },
  methods: {
    selectAccount(accountId) {
      this.appStore.setSelectedAccountId(accountId)
      const li = document.querySelector('li[data-account-id="' + accountId + '"]')
      if (li) {
        li.focus()
      }
      navigator.clipboard.writeText(this.appStore.selectedAccount.password)
    },
    async openDetails(accountId, evt) {
      evt.stopPropagation()
      evt.preventDefault()
      const dbAdapter = DBAdapter()
      const connectionResult = await dbAdapter.validateConnection()

      //router would do this, but no need to create or focus if not connected.
      if (connectionResult.status === 'failed') {
        this.$router.push({
          name: 'LoginView',
        })
        return
      }
      if (accountId === 'new') {
        const account = new Account({ id: new Date().getTime() })
        account.new = true
        this.appStore.addAccount(account)
        accountId = account.id
      }
      this.selectAccount(accountId)
      this.$router.push({
        name: 'DetailView',
      })
    },
    focusPreviousItem(index, evt) {
      const account = this.accountsRef[index - 1]
      if (account) {
        evt.target.setAttribute('tabindex', '-1')
        account.focus()
      }
    },
    focusNextItem(index, evt) {
      const account = this.accountsRef[index + 1]
      if (account) {
        evt.target.setAttribute('tabindex', '-1')
        account.focus()
      }
    },
    focusAccount(evt) {
      evt.target.setAttribute('tabindex', '0')
    },
  },
  mounted() {
    this.disablePage()
  },
  activated() {
    this.disablePage()
    if (this.appStore.selectedAccountId) {
      this.selectAccount(this.appStore.selectedAccountId)
    }
    this.restoreScroll()
  },
  deactivated() {
    //console.log('ListView deactivated')
    this.disablePage()
  },
  unmounted() {
    //console.log('ListView unmounted. Keep-alive is not working!!!')
  },

  setup() {
    const appStore = useAppStore() // Access the store instance
    const searchQuery = ref('') // Define a reactive ref for searchQuery
    const accounts = ref([]) // Reactive ref for filtered accounts
    const loading = ref(true) // Loading state
    const page = ref(0) // Current page
    const accountsRef = ref([]) // Array to hold all item refs
    const observer = ref(null) // Initialize observer as a ref
    const scrollTop = ref({}) // Initialize scrollTop as a ref
    const router = useRouter() // Access the router instance
    const lastAccount = ref(null) // Initialize lastAccount as a ref

    loading.value = true
    // Function to restore scroll position
    const restoreScroll = async () => {
      await nextTick() // Wait for the DOM to be updated
      const top = scrollTop.value[router.currentRoute.value.name]
      enablePage()
      await nextTick() // Wait for the DOM to be updated
      setTimeout(() => {
        document.scrollingElement.scrollTop = top
      }, 100)
    }

    const debounce = (func, wait) => {
      let timeout
      return function (...args) {
        const context = this
        clearTimeout(timeout)
        timeout = setTimeout(() => func.apply(context, args), wait)
      }
    }

    const updateScrollTop = debounce(() => {
      //console.log('Scrolling...', new Date().getTime())
      scrollTop.value[router.currentRoute.value.name] = document.scrollingElement.scrollTop
    }, 200) // Adjust the debounce delay (200ms) as needed

    window.addEventListener('scroll', updateScrollTop)

    // Function to update the ref array reactively
    const setItemRef = (el, index) => {
      if (el) {
        // Add or replace the reference reactively
        accountsRef.value.splice(index, 1, el)
      } else {
        // Remove the reference if the element is null
        accountsRef.value.splice(index, 1)
      }
    }

    const enablePage = (timeOut = 50) => {
      setTimeout(() => {
        appStore.setLoading(false)
        //   appStore.loading.value = false // Set loading to false after a short delay
      }, timeOut)
    }

    const disablePage = () => {
      appStore.setLoading(true)
      //appStore.loading.value = true // Set loading to true after a short delay
    }

    const loadAndFilterAccounts = async () => {
      page.value = 0
      disablePage()
      appStore.clearAccounts()
      document.scrollingElement.scrollTop = 0
      await appStore.loadAccounts(page.value, searchQuery.value.toLowerCase()) // Load accounts if not already loaded

      accounts.value = [...[], ...appStore.allAccounts]
      enablePage(200)
    }

    const loadMoreData = async () => {
      if (loading.value) return // Prevent multiple simultaneous calls
      disablePage()
      page.value++
      await appStore.loadAccounts(page.value, searchQuery.value.toLowerCase()) // Load accounts if not already loaded
      accounts.value = [...[], ...appStore.allAccounts]
      enablePage()
    }

    const observeLastItem = async () => {
      await nextTick() // Wait for the DOM to be updated
      const last = accountsRef.value[accountsRef.value.length - 1] // Get the last ref
      if (observer.value) {
        observer.value.disconnect() // Disconnect any previous observer
        //console.log('Disconnecting previous observer.')
      }

      if (last) {
        observer.value = new IntersectionObserver(
          (entries) => {
            const [entry] = entries
            if (entry.isIntersecting) {
              if (lastAccount.value !== last) {
                lastAccount.value = last
                loadMoreData()
              }
            }
          },
          { root: null, threshold: 0.1 }, // Trigger when the entire element is visible
        )
        observer.value.observe(last)
        //console.log('Observer is observing the last item.')
      } else {
        //console.error('Last item is not set! Cannot observe.')
      }
    }

    // Watch searchQuery and reload accounts when it changes
    watch(searchQuery, loadAndFilterAccounts)

    // watch(appStore.selectedAccountId, () => {

    // })

    // Observe accounts changes to trigger last-item observation
    watch(accounts, () => {
      setTimeout(() => {
        observeLastItem()
      }, 100)
    })

    // Load accounts on component mount
    onMounted(loadAndFilterAccounts)

    onBeforeUnmount(() => {
      if (observer.value) {
        observer.value.disconnect()
        window.removeEventListener('scroll', updateScrollTop)
      }
    })

    return {
      appStore,
      accounts,
      searchQuery,
      loading,
      accountsRef,
      disablePage,
      restoreScroll,
      setItemRef, // Expose the function to set refs
    }
  },
})
</script>

<style>
.list {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: stretch;

  .heading {
    display: flex;
    z-index: 1;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0.5rem;
    border-block-end: 1px solid var(--color-border);
    position: sticky;
    top: 3rem;
    background: var(--color-background-soft);
    height: 3.5rem;

    &:after {
      content: attr(data-count);
      color: #fff;
      position: absolute;
      top: -1px;
      right: 3.5rem;
      background: #1c4e8b;
      border-radius: 50%;
      width: 3ch;
      height: 3ch;
      display: flex;
      justify-content: center;
      font-size: 10px;

      align-items: center;
      padding: 0.7rem;
      opacity: 1;
    }

    input,
    div {
      margin-inline-start: 0.5rem;
      display: flex;
      justify-content: flex-end;
    }
    input {
      height: 2.5rem;
      flex: 100;
    }
  }

  ul {
    list-style: none;
    padding: 0;
    width: 100%;

    li {
      padding: 0.5rem 0;
      padding-inline-start: 0;
      border-block-end: 1px solid var(--color-border);
      display: flex;
      align-items: center;
      border-inline-start: 0.5rem solid transparent;
      justify-content: space-between;
      transition: border 0.2s;
      border-radius: 5px;

      &:focus {
        outline: 1px solid var(--color-text);
        outline-offset: -2px;
      }

      &:hover {
        background-color: var(--color-background-mute);
      }

      > *:first-of-type {
        padding-inline-start: 0.75rem;
      }

      > *:last-of-type {
        padding-inline-end: 0.75rem;
      }

      div {
        display: flex;
        flex: 1;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        span {
          text-overflow: ellipsis;
          white-space: nowrap;
          overflow: hidden;
        }
      }
      div:last-of-type {
        display: flex;
        justify-content: flex-end;
      }
    }

    .listHeadings {
      position: sticky;
      top: 6.5rem;
      background-color: var(--color-background-soft);
      z-index: 1;
      flex: 1;

      > div {
        flex: 1;

        span {
          font-weight: 600;
          color: #ffffff;
        }
      }

      > div:first-of-type {
        flex: 1.5;
      }
    }

    .selected {
      border-inline-start-color: var(--color-border-selected);
      outline: 1px solid var(--color-border-selected);
      outline-offset: -1px;
    }

    .noData {
      justify-content: center;
      padding: 1rem;
      border-bottom: 0;
      font-size: 1.5rem;
    }
  }
}
</style>
