<template>
  <AdminLayout :title="isEdit ? 'Edit Proses Cuci' : 'Tambah Proses Cuci'">
    <div class="max-w-2xl"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/proses-cuci" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></Link>
        <h2 class="text-lg font-semibold">{{ isEdit ? 'Edit' : 'Tambah' }} Proses Cuci</h2>
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Tgl Kirim Cuci" required><input v-model="form.tanggal_kirim_cuci" type="date" v-bind="ip"/></FormField>
          <FormField label="No. Surat Jalan"><input v-model="form.no_surat_jalan" type="text" v-bind="ip"/></FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="PO" required><input v-model="form.po" type="text" v-bind="ip"/></FormField>
          <FormField label="Model" required><input v-model="form.model" type="text" v-bind="ip"/></FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Pcs Kirim" required><input v-model="form.pcs_kirim" type="number" min="0" v-bind="ip"/></FormField>
          <FormField label="Pcs Kembali"><input v-model="form.pcs_kembali" type="number" min="0" v-bind="ip"/></FormField>
        </div>
        <FormField label="Tanggal Kembali dari Cuci"><input v-model="form.tanggal_kembali_dari_cuci" type="date" v-bind="ip"/></FormField>
        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-70">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
          <Link href="/proses-cuci" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</Link>
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
const form = useForm({ tanggal_kirim_cuci: props.item?.tanggal_kirim_cuci?.split('T')[0] ?? '', no_surat_jalan: props.item?.no_surat_jalan ?? '', po: props.item?.po ?? '', model: props.item?.model ?? '', pcs_kirim: props.item?.pcs_kirim ?? 0, tanggal_kembali_dari_cuci: props.item?.tanggal_kembali_dari_cuci?.split('T')[0] ?? '', pcs_kembali: props.item?.pcs_kembali ?? 0 })
const ip = { class: 'w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white' }
const submit = () => { isEdit.value ? form.put(`/proses-cuci/${props.item.id}`) : form.post('/proses-cuci') }
</script>
