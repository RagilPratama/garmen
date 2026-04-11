<template>
  <DataTable
    title="Bahan Proses Potong"
    :data="data"
    :columns="columns"
    base-path="/bahan-proses-potong"
    @open-create="openCreate"
    @open-edit="openEdit"
  >
    <template #modal>
      <Modal v-model="showModal" :title="editItem ? 'Edit Proses Potong' : 'Tambah Proses Potong'" size="lg">
        <div v-if="!bahanOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Belum ada bahan yang dikirim ke garment. Tambahkan data di <strong class="ml-1">Bahan Keluar</strong> terlebih dahulu.
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan <span class="text-red-500">*</span></label>
              <select v-model="form.kode_bahan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
                <option value="" disabled>-- Pilih Kode Bahan --</option>
                <option v-for="b in bahanOptions" :key="b.kode_bahan" :value="b.kode_bahan">
                  {{ b.kode_bahan }} (terkirim: {{ fmt(b.total_yard) }} yard)
                </option>
              </select>
              <p v-if="form.errors.kode_bahan" class="mt-1 text-xs text-red-500">{{ form.errors.kode_bahan }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Potong <span class="text-red-500">*</span></label>
              <input v-model="form.tanggal_potong" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="form.errors.tanggal_potong" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_potong }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Yard <span class="text-red-500">*</span></label>
              <input v-model="form.yard" type="number" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0.00"/>
              <p v-if="form.errors.yard" class="mt-1 text-xs text-red-500">{{ form.errors.yard }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">PO <span class="text-red-500">*</span></label>
              <input v-model="form.po" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="Nomor PO"/>
              <p v-if="form.errors.po" class="mt-1 text-xs text-red-500">{{ form.errors.po }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model <span class="text-red-500">*</span></label>
              <input v-model="form.model" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="Nama Model"/>
              <p v-if="form.errors.model" class="mt-1 text-xs text-red-500">{{ form.errors.model }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Potongan (pcs)</label>
              <input v-model="form.hasil_potongan" type="number" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              <p v-if="form.errors.hasil_potongan" class="mt-1 text-xs text-red-500">{{ form.errors.hasil_potongan }}</p>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ editItem ? 'Simpan Perubahan' : 'Tambah Data' }}
            </button>
          </div>
        </form>
      </Modal>
    </template>
  </DataTable>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DataTable from '@/Components/DataTable.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ data: Object, bahanOptions: { type: Array, default: () => [] } })

const columns = [
  { key: 'tanggal_potong', label: 'Tanggal Potong', type: 'date' },
  { key: 'po', label: 'PO' },
  { key: 'model', label: 'Model' },
  { key: 'kode_bahan', label: 'Kode Bahan' },
  { key: 'yard', label: 'Yard' },
  { key: 'hasil_potongan', label: 'Hasil Potong (pcs)' },
]

const showModal = ref(false)
const editItem = ref(null)
const form = useForm({ tanggal_potong: '', yard: '', po: '', model: '', kode_bahan: '', hasil_potongan: '' })

const fmt = (v) => Number(v ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const openCreate = () => { editItem.value = null; form.reset(); form.clearErrors(); showModal.value = true }
const openEdit = (item) => {
  editItem.value = item
  form.tanggal_potong = item.tanggal_potong?.substring(0, 10) ?? ''
  form.yard = item.yard ?? ''
  form.po = item.po ?? ''
  form.model = item.model ?? ''
  form.kode_bahan = item.kode_bahan ?? ''
  form.hasil_potongan = item.hasil_potongan ?? ''
  form.clearErrors(); showModal.value = true
}
const submit = () => {
  if (editItem.value) {
    form.put(`/bahan-proses-potong/${editItem.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/bahan-proses-potong', { onSuccess: () => { showModal.value = false; form.reset() } })
  }
}
</script>
