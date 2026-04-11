<template>
  <AdminLayout :title="isEdit ? 'Edit Proses Jual' : 'Tambah Proses Jual'">
    <div class="max-w-2xl"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/proses-jual" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></Link>
        <h2 class="text-lg font-semibold">{{ isEdit ? 'Edit' : 'Tambah' }} Proses Jual</h2>
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <FormField label="No. Nota"><input v-model="form.no_nota" type="text" v-bind="ip"/></FormField>
          <FormField label="Tanggal Nota" required><input v-model="form.tanggal_nota" type="date" v-bind="ip"/></FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Buyer" required><input v-model="form.buyer" type="text" v-bind="ip"/></FormField>
          <FormField label="Model" required><input v-model="form.model" type="text" v-bind="ip"/></FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Pcs" required><input v-model="form.pcs" type="number" min="0" v-bind="ip" @input="calcTotal"/></FormField>
          <FormField label="Harga Satuan" required><input v-model="form.harga_satuan" type="number" min="0" v-bind="ip" @input="calcTotal"/></FormField>
        </div>
        <FormField label="Total Harga (otomatis)">
          <input :value="formatRupiah(total)" type="text" readonly class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed"/>
        </FormField>
        <FormField label="Status" required>
          <select v-model="form.status" v-bind="ip">
            <option value="pending">Pending</option>
            <option value="lunas">Lunas</option>
            <option value="batal">Batal</option>
          </select>
        </FormField>
        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-70">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
          <Link href="/proses-jual" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</Link>
        </div>
      </form>
    </div></div>
  </AdminLayout>
</template>
<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormField from '@/Components/FormField.vue'
import FieldError from '@/Components/FieldError.vue'
const props = defineProps({ item: { type: Object, default: null } })
const isEdit = computed(() => !!props.item)
const form = useForm({ no_nota: props.item?.no_nota ?? '', tanggal_nota: props.item?.tanggal_nota?.split('T')[0] ?? '', buyer: props.item?.buyer ?? '', model: props.item?.model ?? '', pcs: props.item?.pcs ?? 0, harga_satuan: props.item?.harga_satuan ?? 0, status: props.item?.status ?? 'pending' })
const total = computed(() => (parseFloat(form.pcs) || 0) * (parseFloat(form.harga_satuan) || 0))
const calcTotal = () => {}
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const ip = { class: 'w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white' }
const submit = () => { isEdit.value ? form.put(`/proses-jual/${props.item.id}`) : form.post('/proses-jual') }
</script>
