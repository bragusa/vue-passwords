<template>
  <div class="view seed">
    <form @submit.prevent="handleSubmit" autocomplete="off">
      <p>
        You must set an encryption seed to proceed. It must be at least {{ min }} characters long
        and only contain numbers.
      </p>
      <p>
        This allows us to encrypt your passwords when we store them in the database for added
        security.
      </p>
      <p>
        Make note of the seed value you use. If it is ever lost, you will have to enter the same one
        again to be able to retrieve your passwords.
      </p>
      <label for="username">Seed:</label>
      <input id="seed" @change="this.error = ''" type="text" v-model="seed" placeholder="seed" />
      <label class="error">{{ error }}</label>
      <div class="buttonGroup">
        <button :disabled="blockSave" type="submit">Save</button>
      </div>
    </form>
  </div>
</template>

<script>
import { useAppStore } from '@/stores/accountData'
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'SeedView',
  data() {
    return {
      seed: '',
      min: 6,
    }
  },
  methods: {
    handleSubmit() {
      if (this.seed.length < this.min || !this.seed.match(/^[0-9]+$/)) {
        this.error =
          'Seed must be at least ' + this.min + ' characters long and only contain numbers'
        return
      }
      const appStore = useAppStore()
      appStore.setSeed(this.seed)
      this.$router.push('/list')
    },
  },
  computed: {
    blockSave() {
      return this.seed.length < this.min || !this.seed.match(/^[0-9]+$/)
    },
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
    background-color: var(--color-background-soft);
    background-size: contain;
    padding: 2.5rem;

    label {
      display: inline-block;
      font-size: 1.25rem;
    }

    h2 {
      white-space: nowrap;
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
