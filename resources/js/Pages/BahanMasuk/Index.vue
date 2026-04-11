<template>
  <DataTable
    title="Bahan Masuk"
    :data="data"
    :columns="columns"
    base-path="/bahan-masuk"
    @open-create="openCreate"
    @open-edit="openEdit"
  >
    <template #modal>
      <Modal v-model="showModal" :title="editItem ? 'Edit Bahan Masuk' : 'Tambah Bahan Masuk'" size="lg">
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
              <input v-model="form.tanggal" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
              <input v-model="form.no_surat_jalan" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="No. Surat Jalan"/>
              <p v-if="form.errors.no_surat_jalan" class="mt-1 text-xs text-red-500">{{ form.errors.no_surat_jalan }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Nota</label>
              <input v-model="form.no_nota" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="No. Nota"/>
              <p v-if="form.errors.no_nota" class="mt-1 text-xs text-red-500">{{ form.errors.no_nota }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
              <select v-model="form.supplier" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
                <option value="" disabled>-- Pilih supplier --</option>
                <option v-for="s in supplierOptions" :key="s" :value="s">{{ s }}</option>
              </select>
              <p v-if="form.errors.supplier" class="mt-1 text-xs text-red-500">{{ form.errors.supplier }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan <span class="text-red-500">*</span></label>
              <input v-model="form.kode_bahan" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="Kode Bahan"/>
              <p v-if="form.errors.kode_bahan" class="mt-1 text-xs text-red-500">{{ form.errors.kode_bahan }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Yard <span class="text-red-500">*</span></label>
              <input v-model="form.yard" type="number" step="0.01" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0.00"/>
              <p v-if="form.errors.yard" class="mt-1 text-xs text-red-500">{{ form.errors.yard }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rp / Yard <span class="text-red-500">*</span></label>
              <input v-model="form.rp_per_yard" type="number" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              <p v-if="form.errors.rp_per_yard" class="mt-1 text-xs text-red-500">{{ form.errors.rp_per_yard }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
                <option value="pending">Pending</option>
                <option value="diterima">Diterima</option>
                <option value="ditolak">Ditolak</option>
              </select>
              <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status }}</p>
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

defineProps({
  data: Object,
  supplierOptions: { type: Array, default: () => [] },
})

const columns = [
  { key: 'tanggal', label: 'Tanggal', type: 'date' },
  { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
  { key: 'no_nota', label: 'No. Nota' },
  { key: 'supplier', label: 'Supplier' },
  { key: 'kode_bahan', label: 'Kode Bahan' },
  { key: 'yard', label: 'Yard' },
  { key: 'rp_per_yard', label: 'Rp/Yard', type: 'currency' },
  { key: 'total', label: 'Total', type: 'currency' },
  { key: 'status', label: 'Status', type: 'status' },
]

const showModal = ref(false)
const editItem = ref(null)

const form = useForm({ tanggal: '', no_surat_jalan: '', no_nota: '', supplier: '', kode_bahan: '', yard: '', rp_per_yard: '', status: 'pending' })

const openCreate = () => {
  editItem.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

const openEdit = (item) => {
  editItem.value = item
  form.tanggal = item.tanggal?.substring(0, 10) ?? ''
  form.no_surat_jalan = item.no_surat_jalan ?? ''
  form.no_nota = item.no_nota ?? ''
  form.supplier = item.supplier ?? ''
  form.kode_bahan = item.kode_bahan ?? ''
  form.yard = item.yard ?? ''
  form.rp_per_yard = item.rp_per_yard ?? ''
  form.status = item.status ?? 'pending'
  form.clearErrors()
  showModal.value = true
}

const submit = () => {
  if (editItem.value) {
    form.put(`/bahan-masuk/${editItem.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/bahan-masuk', { onSuccess: () => { showModal.value = false; form.reset() } })
  }
}
</script>
