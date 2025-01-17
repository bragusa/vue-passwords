<template>
  <div class="view">
    <div @keydown.esc="closeDetails" class="header" @click="closeDetails">
      <button class="iconButton">
        <ChevronLeftIcon class="size-22 icon" />
      </button>
      Return to list
    </div>
    <form @submit.prevent="handleSubmit" @keydown.esc="closeDetails" autocomplete="off">
      <button @click="deleteAccount($event)" type="button">Delete Account</button>
      <label for="accountName">Account Name:</label>
      <input
        ref="firstField"
        id="accountName"
        type="text"
        v-model="account.sitename"
        placeholder="Account name"
      />
      <label for="accountURL">Account URL:</label>
      <input id="accountURL" type="text" v-model="account.siteurl" placeholder="Account url" />
      <label for="accountUsername">Account username:</label>
      <input
        id="accountUsername"
        type="text"
        v-model="account.siteusername"
        placeholder="Account username"
      />
      <label for="accountPassword">Account password:</label>
      <input
        id="accountPassword"
        type="text"
        v-model="account.sitepassword"
        placeholder="Account password"
      />
      <div class="buttonGroup">
        <button @click="closeDetails" type="button">Cancel</button>
        <button type="submit" :disabled="blockSave">Save</button>
      </div>
    </form>
  </div>
</template>

<script>
import { ChevronLeftIcon } from '@heroicons/vue/24/solid'
import { useAppStore } from '@/stores/accountData'
import { defineComponent, onMounted, ref, nextTick, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import DBAdapter from '@/rest/DatabaseAdapter'
import SimpleCrypto from 'simple-crypto-js'

export default defineComponent({
  name: 'DetailView',
  components: {
    ChevronLeftIcon,
  },
  props: ['accountId'],

  beforeMount() {
    this.loadAccountDetails()
  },
  methods: {
    loadAccountDetails() {
      // Fetch or process account details using accountId
      this.account.sitename = this.appStore.selectedAccount.sitename
      this.account.siteurl = this.appStore.selectedAccount.siteurl
      this.account.siteusername = this.appStore.selectedAccount.siteusername
      this.account.sitepassword = this.appStore.selectedAccount.sitepassword
      if (this.account.new) {
        this.blockSave = true
      } else {
        this.blockSave = false
      }
      if (this.account.new) {
        this.blockSave = true
      }
      console.log('Account details:', this.account)
    },
    async handleSubmit() {
      // Handle form submission
      console.log('Form submitted:', this.account)
      const appStore = useAppStore()
      appStore.setLoading(true)
      const dbAdapter = DBAdapter()
      const connectionResult = await dbAdapter.validateConnection()

      //router would do this, but no need to create or focus if not connected.
      if (connectionResult.status === 'failed') {
        this.$router.push({
          name: 'LoginView',
        })
        return
      }

      const selectedAccount = this.appStore.selectedAccount
      if (selectedAccount) {
        selectedAccount.sitename = this.account.sitename
        selectedAccount.siteurl = this.account.siteurl
        selectedAccount.siteusername = this.account.siteusername
        const appStore = useAppStore()
        const simpleCrypto = new SimpleCrypto(appStore.seed)

        try {
          selectedAccount.sitepassword = simpleCrypto
            .encrypt(this.account.sitepassword || 'xxxx')
            .toString()
        } catch {
          //error
        }
      }

      if (this.account.new) {
        await dbAdapter.insertAccount(selectedAccount)
      } else {
        await dbAdapter.updateAccount(selectedAccount)
      }
      this.closeDetails()
    },
    async deleteAccount(event) {
      event.stopPropagation()
      const dbAdapter = DBAdapter()
      await dbAdapter.deleteAccount(this.account)
      this.closeDetails()
    },
  },
  setup() {
    const appStore = useAppStore() // Access the store instance
    const router = useRouter() // Access the Vue Router instance
    const firstField = ref(null)
    const blockSave = ref(false)
    const account = reactive({
      sitename: '',
      siteurl: '',
      siteusername: '',
      sitepassword: '',
    })

    const closeDetails = () => {
      router.back()
    }

    onMounted(async () => {
      if (firstField.value) {
        await nextTick()
        setTimeout(() => {
          firstField.value.focus()
        }, 300)
      }
    })

    watch(
      () => account,
      (newAccount, oldAccount) => {
        console.log('Account changed:', { oldAccount, newAccount })
        if (newAccount.sitepassword === '' || newAccount.sitename === '') {
          blockSave.value = true
        } else {
          blockSave.value = false
        }
      },
      { deep: true },
    )

    return { account, closeDetails, router, appStore, firstField, blockSave }
  },
})
</script>

<style scoped>
.header {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding: 0.5rem;
  background-color: var(--color-background-soft);
  border-bottom: 1px solid var(--color-border);
}

form {
  padding-block: 1rem;
}
</style>
