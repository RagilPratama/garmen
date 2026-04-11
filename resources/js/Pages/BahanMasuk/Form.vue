<template>
  <AdminLayout :title="isEdit ? 'Edit Bahan Masuk' : 'Tambah Bahan Masuk'">
    <div class="max-w-2xl">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
          <Link href="/bahan-masuk" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          </Link>
          <h2 class="text-lg font-semibold text-gray-800">{{ isEdit ? 'Edit Bahan Masuk' : 'Tambah Bahan Masuk' }}</h2>
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Tanggal" required>
              <input v-model="form.tanggal" type="date" v-bind="inputProps" :class="errorClass('tanggal')"/>
              <FieldError :error="form.errors.tanggal"/>
            </FormField>
            <FormField label="Status" required>
              <select v-model="form.status" v-bind="inputProps" :class="errorClass('status')">
                <option value="pending">Pending</option>
                <option value="diterima">Diterima</option>
                <option value="ditolak">Ditolak</option>
              </select>
              <FieldError :error="form.errors.status"/>
            </FormField>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <FormField label="No. Surat Jalan">
              <input v-model="form.no_surat_jalan" type="text" placeholder="SJ-001" v-bind="inputProps"/>
            </FormField>
            <FormField label="No. Nota">
              <input v-model="form.no_nota" type="text" placeholder="NT-001" v-bind="inputProps"/>
            </FormField>
          </div>
          <FormField label="Supplier" required>
            <input v-model="form.supplier" type="text" placeholder="Nama supplier" v-bind="inputProps" :class="errorClass('supplier')"/>
            <FieldError :error="form.errors.supplier"/>
          </FormField>
          <FormField label="Kode Bahan" required>
            <input v-model="form.kode_bahan" type="text" placeholder="KB-001" v-bind="inputProps" :class="errorClass('kode_bahan')"/>
            <FieldError :error="form.errors.kode_bahan"/>
          </FormField>
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Yard" required>
              <input v-model="form.yard" type="number" step="0.01" min="0" placeholder="0.00" v-bind="inputProps" :class="errorClass('yard')" @input="calcTotal"/>
              <FieldError :error="form.errors.yard"/>
            </FormField>
            <FormField label="Rp/Yard" required>
              <input v-model="form.rp_per_yard" type="number" step="0.01" min="0" placeholder="0.00" v-bind="inputProps" :class="errorClass('rp_per_yard')" @input="calcTotal"/>
              <FieldError :error="form.errors.rp_per_yard"/>
            </FormField>
          </div>
          <FormField label="Total (otomatis)">
            <input :value="formatRupiah(total)" type="text" readonly class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed"/>
          </FormField>
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
              class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-70">
              {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <Link href="/bahan-masuk" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</Link>
          </div>
        </form>
      </div>
    </div>
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

const form = useForm({
  tanggal: props.item?.tanggal?.split('T')[0] ?? '',
  no_surat_jalan: props.item?.no_surat_jalan ?? '',
  no_nota: props.item?.no_nota ?? '',
  supplier: props.item?.supplier ?? '',
  kode_bahan: props.item?.kode_bahan ?? '',
  yard: props.item?.yard ?? '',
  rp_per_yard: props.item?.rp_per_yard ?? '',
  status: props.item?.status ?? 'pending',
})

const total = computed(() => (parseFloat(form.yard) || 0) * (parseFloat(form.rp_per_yard) || 0))
const calcTotal = () => {}
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const inputProps = { class: 'w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white' }
const errorClass = (field) => form.errors[field] ? 'border-red-300 bg-red-50' : ''

const submit = () => {
  if (isEdit.value) form.put(`/bahan-masuk/${props.item.id}`)
  else form.post('/bahan-masuk')
}
</script>
