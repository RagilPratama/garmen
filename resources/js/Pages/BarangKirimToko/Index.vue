<template>
  <DataTable title="Barang Kirim Toko" :data="data" :columns="columns" base-path="/barang-kirim-toko"
    @open-create="openCreate" @open-edit="openEdit">
    <template #modal>
      <Modal v-model="showModal" :title="editItem ? 'Edit Barang Kirim Toko' : 'Tambah Barang Kirim Toko'" size="lg">
        <div v-if="!poOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Belum ada data dari <strong class="ml-1">Barang Masuk Kantor</strong>. Catat barang masuk terlebih dahulu.
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PO / Model <span class="text-red-500">*</span></label>
            <SearchableSelect
              v-model="selectedKey"
              :options="poOptions.map(o => ({ value: o.po+'||'+o.model, label: 'PO: ' + o.po + ' | Model: ' + o.model + ' (' + o.max_pcs + ' pcs di kantor)' }))"
              placeholder="-- Pilih PO dari Barang Masuk Kantor --"
              @change="onSelect"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan <span class="text-red-500">*</span></label>
              <div class="relative">
                <input v-model="form.no_surat_jalan" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white pr-16" placeholder="SJ-001"/>
                <span v-if="!editItem" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
              </div>
              <p v-if="form.errors.no_surat_jalan" class="mt-1 text-xs text-red-500">{{ form.errors.no_surat_jalan }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kirim <span class="text-red-500">*</span></label>
              <input v-model="form.tanggal_kirim" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="form.errors.tanggal_kirim" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_kirim }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">PO</label>
              <input v-model="form.po" type="text" class="w-full px-4 py-2.5 border border-gray-100 rounded-lg text-sm bg-gray-50 text-gray-500" readonly/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
              <input v-model="form.model" type="text" class="w-full px-4 py-2.5 border border-gray-100 rounded-lg text-sm bg-gray-50 text-gray-500" readonly/>
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Pcs Barang Jadi <span class="text-red-500">*</span></label>
              <input v-model="form.pcs_barang_jadi" type="number" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              <p v-if="form.errors.pcs_barang_jadi" class="mt-1 text-xs text-red-500">{{ form.errors.pcs_barang_jadi }}</p>
              <p v-if="selectedOpt" class="mt-1 text-xs text-gray-500">Stok di kantor: <span class="font-medium text-amber-600">{{ selectedOpt.max_pcs }} pcs</span></p>
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
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({ data: Object, poOptions: { type: Array, default: () => [] }, nextSuratJalan: { type: String, default: '' } })

const columns = [
  { key: 'no_surat_jalan', label: 'No. SJ' }, { key: 'tanggal_kirim', label: 'Tgl Kirim', type: 'date' },
  { key: 'po', label: 'PO' }, { key: 'model', label: 'Model' }, { key: 'pcs_barang_jadi', label: 'Pcs' },
]

const showModal = ref(false)
const editItem = ref(null)
const selectedKey = ref('')
const selectedOpt = ref(null)
const form = useForm({ no_surat_jalan: '', tanggal_kirim: '', po: '', model: '', pcs_barang_jadi: '' })

const onSelect = () => {
  const [po, model] = selectedKey.value.split('||')
  selectedOpt.value = props.poOptions.find(o => o.po === po && o.model === model) ?? null
  if (selectedOpt.value) { form.po = selectedOpt.value.po; form.model = selectedOpt.value.model; form.pcs_barang_jadi = selectedOpt.value.max_pcs }
}

const openCreate = () => { editItem.value = null; selectedKey.value = ''; selectedOpt.value = null; form.reset(); form.no_surat_jalan = props.nextSuratJalan; form.clearErrors(); showModal.value = true }
const openEdit = (item) => {
  editItem.value = item
  form.no_surat_jalan = item.no_surat_jalan ?? ''; form.tanggal_kirim = item.tanggal_kirim?.substring(0, 10) ?? ''
  form.po = item.po ?? ''; form.model = item.model ?? ''; form.pcs_barang_jadi = item.pcs_barang_jadi ?? ''
  selectedKey.value = (item.po ?? '') + '||' + (item.model ?? '')
  selectedOpt.value = props.poOptions.find(o => o.po === item.po && o.model === item.model) ?? null
  form.clearErrors(); showModal.value = true
}
const submit = () => {
  if (editItem.value) {
    form.put(`/barang-kirim-toko/${editItem.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/barang-kirim-toko', { onSuccess: () => { showModal.value = false; form.reset(); selectedKey.value = '' } })
  }
}
</script>
