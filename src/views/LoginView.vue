<template>
  <div class="view login">
    <form @submit.prevent="handleSubmit" autocomplete="off">
      <h2>
        <div class="logo"><img src="/favicon.ico" width="22" /></div>
        <div>Secure Passwords</div>
      </h2>
      <label for="username">Username:</label>
      <input
        id="username"
        @change="this.error = ''"
        type="text"
        v-model="username"
        placeholder="username"
      />
      <label for="password">Password:</label>
      <input
        id="password"
        @change="this.error = ''"
        type="password"
        v-model="password"
        placeholder="password"
      />
      <label class="error">{{ error }}</label>
      <div class="buttonGroup">
        <button type="submit">Login</button>
      </div>
    </form>
  </div>
</template>

<script>
import { useAppStore } from '@/stores/accountData'
import { defineComponent} from 'vue'
import { useRouter } from 'vue-router'
import DBAdapter from '@/rest/DatabaseAdapter'

export default defineComponent({
  name: 'LoginView',
  props: ['accountId'],
  data() {
    return {
      error: '',
      username: '',
      password: '',
    }
  },
  mounted() {
    this.loadAccountDetails()
  },
  methods: {
    loadAccountDetails() {
      // Fetch or process account details using accountId
      //this.account = this.appStore.selectedAccount
      this.account = { ...this.appStore.selectedAccount } // Clone the account data
      console.log('Account details:', this.account)
    },
    async handleSubmit() {
      // Handle form submission
      if (this.username == '' || this.password == '') return

      const dbAdapter = DBAdapter()
      const auth = await dbAdapter.authenticateUser(this.username, this.password)
      if (auth.status === 'success') {
        this.appStore.setUserName(this.username)
        //await this.appStore.loadAccounts()
        this.$router.push({
          name: 'ListView',
        })
        return
      } else {
        this.error = auth.reason
      }
    },
  },
  setup() {
    const appStore = useAppStore() // Access the store instance
    const router = useRouter() // Access the Vue Router instance

    const closeDetails = () => {
      router.back()
    }

    return { closeDetails, router, appStore }
  },
})
</script>

<style scoped>
.login {
  max-width: 400px;
  height: 100vh;
  margin: auto auto;
  display: flex;
  align-items: center;
  justify-content: center;

  form {
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    background-color: #202021;
    background: url('/favicon.ico') no-repeat center center;
    background-size: contain;
    padding: 2.5rem;

    label {
      color: var(--color-background);
      display: inline-block;
      font-size: 1.25rem;
    }

    h2 {
      white-space: nowrap;
      color: var(--color-background);
      display: flex;
      flex-direction: row;
      align-items: center;
      margin-block-end: 1rem;
      font-weight: bold;
      font-size: 2rem;

      .logo {
        margin-inline-end: 1rem;
        margin-block-start: 0.25rem;
      }
    }

    .error {
      height: 1rem;
      color: orange;
    }
  }
}

form {
  padding-block: 1rem;
}
</style>
