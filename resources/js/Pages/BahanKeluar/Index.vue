<template>
  <DataTable
    title="Bahan Keluar ke Garment"
    :data="data"
    :columns="columns"
    base-path="/bahan-keluar"
    @open-create="openCreate"
    @open-edit="openEdit"
  >
    <template #modal>
      <Modal v-model="showModal" :title="editItem ? 'Edit Bahan Keluar' : 'Tambah Bahan Keluar'">
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan <span class="text-red-500">*</span></label>
              <select v-model="form.kode_bahan" required @change="selectedStok = stok.find(s => s.kode_bahan === form.kode_bahan) ?? null"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
                <option value="" disabled>-- Pilih Kode Bahan --</option>
                <option v-for="s in stok" :key="s.kode_bahan" :value="s.kode_bahan">
                  {{ s.kode_bahan }} (sisa: {{ formatYard(s.sisa_stok) }} yard)
                </option>
              </select>
              <p v-if="form.errors.kode_bahan" class="mt-1 text-xs text-red-500">{{ form.errors.kode_bahan }}</p>
              <p v-if="!stok.length" class="mt-1 text-xs text-amber-600">Belum ada stok bahan yang tersedia.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Yard <span class="text-red-500">*</span></label>
              <input v-model="form.yard" type="number" step="0.01" required
                :max="selectedStok?.sisa_stok ?? undefined"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                :class="yardError ? 'border-red-400' : 'border-gray-200'"
                placeholder="0.00"/>
              <p v-if="form.errors.yard" class="mt-1 text-xs text-red-500">{{ form.errors.yard }}</p>
              <p v-else-if="yardError" class="mt-1 text-xs text-red-500">{{ yardError }}</p>
              <p v-else-if="selectedStok" class="mt-1 text-xs text-gray-500">
                Stok tersedia: <span class="font-medium text-amber-600">{{ formatYard(selectedStok.sisa_stok) }} yard</span>
              </p>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="form.processing || !stok.length || !!yardError" class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
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

const props = defineProps({ data: Object, stok: Array })

const columns = [
  { key: 'tanggal', label: 'Tanggal', type: 'date' },
  { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
  { key: 'kode_bahan', label: 'Kode Bahan' },
  { key: 'yard', label: 'Yard' },
]

const showModal = ref(false)
const editItem = ref(null)
const selectedStok = ref(null)

const form = useForm({ tanggal: '', no_surat_jalan: '', kode_bahan: '', yard: '' })

const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const yardError = computed(() => {
  if (!selectedStok.value || !form.yard) return null
  const sisa = selectedStok.value.sisa_stok
  if (Number(form.yard) > sisa) {
    return `Melebihi stok. Maksimal ${formatYard(sisa)} yard.`
  }
  return null
})

const openCreate = () => {
  editItem.value = null
  selectedStok.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

const openEdit = (item) => {
  editItem.value = item
  form.tanggal = item.tanggal?.substring(0, 10) ?? ''
  form.no_surat_jalan = item.no_surat_jalan ?? ''
  form.kode_bahan = item.kode_bahan ?? ''
  form.yard = item.yard ?? ''
  selectedStok.value = props.stok.find(s => s.kode_bahan === item.kode_bahan) ?? null
  form.clearErrors()
  showModal.value = true
}

const submit = () => {
  if (editItem.value) {
    form.put(`/bahan-keluar/${editItem.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/bahan-keluar', { onSuccess: () => { showModal.value = false; form.reset(); selectedStok.value = null } })
  }
}
</script>
