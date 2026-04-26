<template>
  <div class="cash-account-form">
    <h3>Tambah Kas (Gudang / Garmen)</h3>
    <form @submit.prevent="submit">
      <label for="type">Tipe</label>
      <select v-model="form.type" required>
        <option value="gudang">Gudang</option>
        <option value="garmen">Garmen</option>
      </select>

      <label for="balance">Saldo</label>
      <input type="number" step="0.01" v-model="form.balance" required />

      <button type="submit">Simpan</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'CashAccountForm',
  data() {
    return {
      form: {
        type: 'gudang',
        balance: 0,
      },
    };
  },
  methods: {
    async submit() {
      try {
        await axios.post('/cash-accounts', this.form);
        this.$emit('added');
        this.form.balance = 0;
      } catch (e) {
        console.error('Error adding cash account', e);
      }
    },
  },
};
</script>

<style scoped>
.cash-account-form {
  max-width: 400px;
  margin: 1rem 0;
}
label {
  display: block;
  margin-top: 0.5rem;
}
button {
  margin-top: 1rem;
}
</style>
