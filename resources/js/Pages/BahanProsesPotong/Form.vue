<template>
  <AdminLayout :title="isEdit ? 'Edit Proses Potong' : 'Tambah Proses Potong'">
    <div class="max-w-2xl">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
          <Link href="/bahan-proses-potong" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></Link>
          <h2 class="text-lg font-semibold text-gray-800">{{ isEdit ? 'Edit' : 'Tambah' }} Proses Potong</h2>
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Tanggal Potong" required><input v-model="form.tanggal_potong" type="date" v-bind="ip"/><FieldError :error="form.errors.tanggal_potong"/></FormField>
            <FormField label="Yard" required><input v-model="form.yard" type="number" min="0" v-bind="ip"/></FormField>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <FormField label="PO" required><input v-model="form.po" type="text" v-bind="ip"/></FormField>
            <FormField label="Model" required><input v-model="form.model" type="text" v-bind="ip"/></FormField>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Kode Bahan" required><input v-model="form.kode_bahan" type="text" v-bind="ip"/></FormField>
            <FormField label="Hasil Potongan" required><input v-model="form.hasil_potongan" type="number" min="0" v-bind="ip"/></FormField>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-70">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
            <Link href="/bahan-proses-potong" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</Link>
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
const form = useForm({ tanggal_potong: props.item?.tanggal_potong?.split('T')[0] ?? '', yard: props.item?.yard ?? '', po: props.item?.po ?? '', model: props.item?.model ?? '', kode_bahan: props.item?.kode_bahan ?? '', hasil_potongan: props.item?.hasil_potongan ?? 0 })
const ip = { class: 'w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white' }
const submit = () => { isEdit.value ? form.put(`/bahan-proses-potong/${props.item.id}`) : form.post('/bahan-proses-potong') }
</script>
