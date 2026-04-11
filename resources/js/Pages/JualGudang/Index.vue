<template>
  <DataTable title="Jual dari Gudang" :data="data" :columns="columns" base-path="/jual-gudang"
    @open-create="openCreate" @open-edit="openEdit">
    <template #modal>
      <Modal v-model="showModal" :title="editItem ? 'Edit Jual Gudang' : 'Tambah Jual Gudang'" size="lg">
        <div v-if="!stokOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Stok gudang kosong. Pastikan barang sudah masuk kantor terlebih dahulu.
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Stok Model <span class="text-red-500">*</span></label>
            <select v-model="form.model" @change="onSelect" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
              <option value="" disabled>-- Pilih model dari stok gudang --</option>
              <option v-for="o in stokOptions" :key="o.model" :value="o.model">
                {{ o.model }} — stok: {{ o.sisa_stok }} pcs
              </option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Nota</label>
              <input v-model="form.no_nota" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="NT-001"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Nota <span class="text-red-500">*</span></label>
              <input v-model="form.tanggal_nota" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="form.errors.tanggal_nota" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_nota }}</p>
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Buyer <span class="text-red-500">*</span></label>
              <input v-model="form.buyer" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="Nama pembeli"/>
              <p v-if="form.errors.buyer" class="mt-1 text-xs text-red-500">{{ form.errors.buyer }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pcs <span class="text-red-500">*</span></label>
              <input v-model="form.pcs" type="number" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              <p v-if="form.errors.pcs" class="mt-1 text-xs text-red-500">{{ form.errors.pcs }}</p>
              <p v-if="selectedStok" class="mt-1 text-xs text-gray-500">Stok tersedia: <span class="font-medium text-amber-600">{{ selectedStok.sisa_stok }} pcs</span></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan <span class="text-red-500">*</span></label>
              <input v-model="form.harga_satuan" type="number" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              <p v-if="form.errors.harga_satuan" class="mt-1 text-xs text-red-500">{{ form.errors.harga_satuan }}</p>
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
              <select v-model="form.status" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
                <option value="pending">Pending</option>
                <option value="lunas">Lunas</option>
                <option value="batal">Batal</option>
              </select>
            </div>
            <div v-if="totalNota > 0" class="col-span-2 flex items-center justify-between px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg">
              <span class="text-sm font-medium text-amber-700">Total Nota:</span>
              <span class="text-base font-semibold text-amber-800">{{ formatRupiah(totalNota) }}</span>
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
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DataTable from '@/Components/DataTable.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  data: Object,
  stokOptions: { type: Array, default: () => [] },
})

const columns = [
  { key: 'no_nota', label: 'No. Nota' },
  { key: 'tanggal_nota', label: 'Tgl Nota', type: 'date' },
  { key: 'buyer', label: 'Buyer' },
  { key: 'model', label: 'Model' },
  { key: 'pcs', label: 'Pcs' },
  { key: 'harga_satuan', label: 'Harga Satuan', type: 'currency' },
  { key: 'status', label: 'Status' },
]

const showModal = ref(false)
const editItem = ref(null)
const selectedStok = ref(null)
const form = useForm({ no_nota: '', tanggal_nota: '', buyer: '', model: '', pcs: '', harga_satuan: '', status: 'pending' })

const totalNota = computed(() => (Number(form.pcs) || 0) * (Number(form.harga_satuan) || 0))
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

const onSelect = () => {
  selectedStok.value = props.stokOptions.find(o => o.model === form.model) ?? null
  if (selectedStok.value) form.pcs = selectedStok.value.sisa_stok
}

const openCreate = () => {
  editItem.value = null; selectedStok.value = null
  form.reset(); form.status = 'pending'; form.clearErrors(); showModal.value = true
}
const openEdit = (item) => {
  editItem.value = item
  form.no_nota = item.no_nota ?? ''; form.tanggal_nota = item.tanggal_nota?.substring(0, 10) ?? ''
  form.buyer = item.buyer ?? ''; form.model = item.model ?? ''
  form.pcs = item.pcs ?? ''; form.harga_satuan = item.harga_satuan != null ? parseFloat(item.harga_satuan) : ''; form.status = item.status ?? 'pending'
  selectedStok.value = props.stokOptions.find(o => o.model === item.model) ?? null
  form.clearErrors(); showModal.value = true
}
const submit = () => {
  if (editItem.value) {
    form.put(`/jual-gudang/${editItem.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/jual-gudang', { onSuccess: () => { showModal.value = false; form.reset(); form.status = 'pending' } })
  }
}
</script>
