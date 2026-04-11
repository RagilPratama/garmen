<template>
  <DataTable title="Proses Jahit" :data="data" :columns="columns" base-path="/proses-jahit"
    @open-create="openCreate" @open-edit="openEdit">
    <template #modal>
      <Modal v-model="showModal" :title="editItem ? 'Edit Proses Jahit' : 'Tambah Proses Jahit'" size="lg">
        <div v-if="!poOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Belum ada data dari <strong class="ml-1">Proses Potong</strong>. Lengkapi kolom Hasil Potongan terlebih dahulu.
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PO / Model <span class="text-red-500">*</span></label>
            <select v-model="selectedKey" @change="onSelect" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
              <option value="" disabled>-- Pilih PO dari Proses Potong --</option>
              <option v-for="o in poOptions" :key="o.po+'||'+o.model" :value="o.po+'||'+o.model">
                PO: {{ o.po }} | Model: {{ o.model }} ({{ o.max_pcs }} pcs siap jahit)
              </option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Jahit <span class="text-red-500">*</span></label>
              <input v-model="form.tanggal_jahit" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="form.errors.tanggal_jahit" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_jahit }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pcs Potongan <span class="text-red-500">*</span></label>
              <input v-model="form.pcs_potongan" type="number" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              <p v-if="form.errors.pcs_potongan" class="mt-1 text-xs text-red-500">{{ form.errors.pcs_potongan }}</p>
              <p v-if="selectedOpt" class="mt-1 text-xs text-gray-500">Maks: <span class="font-medium text-amber-600">{{ selectedOpt.max_pcs }} pcs</span></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai Jahit</label>
              <input v-model="form.tanggal_selesai_jahit" type="date" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pcs Hasil Jahit</label>
              <input v-model="form.pcs_hasil_jahit" type="number" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
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

const props = defineProps({ data: Object, poOptions: { type: Array, default: () => [] } })

const columns = [
  { key: 'tanggal_jahit', label: 'Tanggal Jahit', type: 'date' },
  { key: 'po', label: 'PO' }, { key: 'model', label: 'Model' },
  { key: 'pcs_potongan', label: 'Pcs Potongan' },
  { key: 'tanggal_selesai_jahit', label: 'Tgl Selesai', type: 'date' },
  { key: 'pcs_hasil_jahit', label: 'Pcs Hasil' },
]

const showModal = ref(false)
const editItem = ref(null)
const selectedKey = ref('')
const selectedOpt = ref(null)
const form = useForm({ tanggal_jahit: '', po: '', model: '', pcs_potongan: '', tanggal_selesai_jahit: '', pcs_hasil_jahit: '' })

const onSelect = () => {
  const [po, model] = selectedKey.value.split('||')
  selectedOpt.value = props.poOptions.find(o => o.po === po && o.model === model) ?? null
  if (selectedOpt.value) { form.po = selectedOpt.value.po; form.model = selectedOpt.value.model; form.pcs_potongan = selectedOpt.value.max_pcs }
}

const openCreate = () => { editItem.value = null; selectedKey.value = ''; selectedOpt.value = null; form.reset(); form.clearErrors(); showModal.value = true }
const openEdit = (item) => {
  editItem.value = item
  form.tanggal_jahit = item.tanggal_jahit?.substring(0, 10) ?? ''
  form.po = item.po ?? ''; form.model = item.model ?? ''
  form.pcs_potongan = item.pcs_potongan ?? ''
  form.tanggal_selesai_jahit = item.tanggal_selesai_jahit?.substring(0, 10) ?? ''
  form.pcs_hasil_jahit = item.pcs_hasil_jahit ?? ''
  selectedKey.value = (item.po ?? '') + '||' + (item.model ?? '')
  selectedOpt.value = props.poOptions.find(o => o.po === item.po && o.model === item.model) ?? null
  form.clearErrors(); showModal.value = true
}
const submit = () => {
  if (editItem.value) {
    form.put(`/proses-jahit/${editItem.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/proses-jahit', { onSuccess: () => { showModal.value = false; form.reset(); selectedKey.value = '' } })
  }
}
</script>
