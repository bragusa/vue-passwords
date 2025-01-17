import DBAdapter from '@/rest/DatabaseAdapter'
import { get } from 'cypress/types/lodash'
import { defineStore } from 'pinia'
import SimpleCrypto from 'simple-crypto-js'

interface AccountData {
  id: string
  sitename: string
  siteurl: string
  siteusername: string
  sitepassword: string
  new: boolean
}

export class Account implements AccountData {
  sitename: string
  siteurl: string
  siteusername: string
  sitepassword: string
  new: boolean

  constructor(account: AccountData) {
    this.id = account.id
    this.sitename = account.sitename
    this.sitepassword = account.sitepassword
    this.siteusername = account.siteusername
    this.siteurl = account.siteurl
    this.new = account.new
  }
  id: string
}

export const useAppStore = defineStore('applicationStore', {
  state: () => ({
    selectedAccountId: null,
    username: sessionStorage.getItem('username'),
    seed: localStorage.getItem(sessionStorage.getItem('username') + '-seed'),
    pageSize: 100,
    searchString: '',
    page: 1,
    data: [],
    loading: true,
  }),
  getters: {
    allAccounts: (state) => state.data,
    selectedAccount: (state) => {
      return state.data.find((account: Account) => account.id === state.selectedAccountId)
    },
  },
  actions: {
    async addAccount(account: AccountData) {
      this.data.push(new Account(account)) // Ensure you're pushing a new `Account` instance
    },
    setSelectedAccountId(accountId: string) {
      this.selectedAccountId = accountId
    },
    setLoading(loading: boolean) {
      this.loading = loading
    },
    setUserName(username: string) {
      if (!username) {
        sessionStorage.removeItem('username')
      } else {
        sessionStorage.setItem('username', username)
        this.seed = localStorage.getItem(sessionStorage.getItem('username') + '-seed')
      }
      this.username = username
    },
    setSeed(seed: string) {
      const appStore = useAppStore()
      if (!seed) {
        localStorage.removeItem(sessionStorage.getItem('username') + '-seed')
      } else {
        localStorage.setItem(
          sessionStorage.getItem('username') + '-seed',
          `${appStore.username}-${seed}`,
        )
      }
      this.seed = seed
    },
    clearAccounts() {
      this.data = []
    },
    async loadAccounts(page: number, searchString: string) {
      const dbAdapter = DBAdapter()
      //const accounts = await dbAdapter.fetchData('accounts')
      const accounts = await dbAdapter.fetchPagedData(
        'accounts',
        'sitename asc',
        this.pageSize,
        page * this.pageSize,
        searchString,
      )
      if (accounts) {
        const appStore = useAppStore()
        try {
          accounts.map((account: AccountData) => {
            const simpleCrypto = new SimpleCrypto(appStore.seed)
            try {
              account.sitepassword = simpleCrypto.decrypt(account.sitepassword || 'xxxx').toString()
              account.new = false
            } catch (error) {
              console.log(error)
            }
            this.addAccount(account)
          })
        } catch (error) {
          //nothing fetched
        }
      }
    },
  },
})
