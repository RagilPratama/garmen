<template>
  <DataTable
    title="Bahan Keluar ke Garment"
    :data="data"
    :columns="columns"
    base-path="/bahan-keluar"
    @open-create="openCreate"
    @open-edit="openEdit"
  >
    <!-- No. Surat Jalan clickable -->
    <template #cell-no_surat_jalan="{ item }">
      <button type="button" @click="openDetail(item)"
        class="font-semibold text-amber-600 hover:text-amber-800 hover:underline transition-colors">
        {{ item.no_surat_jalan ?? '—' }}
      </button>
    </template>

    <!-- Items count -->
    <template #cell-items_count="{ item }">
      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
        {{ item.items_count }} item
      </span>
    </template>

    <!-- Grand total -->
    <template #cell-grand_total="{ item }">
      <span class="font-medium text-gray-800">{{ formatRupiah(item.grand_total) }}</span>
    </template>

    <template #modal>
      <!-- ── Create Modal ── -->
      <Modal v-model="showCreateModal" title="Tambah Bahan Keluar" size="xl">
        <form @submit.prevent="submitCreate" class="space-y-5">
          <!-- Header -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
              <input v-model="createForm.tanggal" type="date" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="createForm.errors.tanggal" class="mt-1 text-xs text-red-500">{{ createForm.errors.tanggal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
              <div class="relative">
                <input v-model="createForm.no_surat_jalan" type="text"
                  class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white pr-16"
                  placeholder="No. Surat Jalan"/>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
              </div>
            </div>
          </div>

          <!-- Items table -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Daftar Bahan <span class="text-red-500">*</span></label>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium w-6">#</th>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium">Kode Bahan</th>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium w-28">Yard</th>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium w-36">Rp/Yard</th>
                    <th class="text-right px-3 py-2 text-gray-500 font-medium w-32">Total</th>
                    <th class="w-10"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(item, idx) in createForm.items" :key="idx">
                    <td class="px-3 py-2 text-gray-400 text-xs">{{ idx + 1 }}</td>
                    <td class="px-3 py-2">
                      <SearchableSelect
                        v-model="item.kode_bahan"
                        :options="stok.map(s => ({ value: s.kode_bahan, label: s.kode_bahan + ' (sisa: ' + formatYard(s.sisa_stok) + ')' }))"
                        placeholder="-- Pilih --"
                      />
                      <p v-if="createForm.errors[`items.${idx}.kode_bahan`]" class="mt-0.5 text-xs text-red-500">{{ createForm.errors[`items.${idx}.kode_bahan`] }}</p>
                    </td>
                    <td class="px-3 py-2">
                      <input v-model="item.yard" type="number" min="0.01" step="0.01" required placeholder="0.00"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"/>
                      <p v-if="createForm.errors[`items.${idx}.yard`]" class="mt-0.5 text-xs text-red-500">{{ createForm.errors[`items.${idx}.yard`] }}</p>
                    </td>
                    <td class="px-3 py-2">
                      <input v-model="item.rp_per_yard" type="number" min="0" step="1" required placeholder="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"/>
                      <p v-if="createForm.errors[`items.${idx}.rp_per_yard`]" class="mt-0.5 text-xs text-red-500">{{ createForm.errors[`items.${idx}.rp_per_yard`] }}</p>
                    </td>
                    <td class="px-3 py-2 text-right text-gray-700 font-medium text-xs">
                      {{ formatRupiah(itemTotal(item)) }}
                    </td>
                    <td class="px-2 py-2 text-center">
                      <button type="button" @click="createForm.items.splice(idx, 1)" :disabled="createForm.items.length === 1"
                        class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:text-red-500 hover:bg-red-50 disabled:opacity-30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="4" class="px-3 py-2 text-right text-sm font-medium text-gray-600">Grand Total</td>
                    <td class="px-3 py-2 text-right font-semibold text-gray-800">{{ formatRupiah(createGrandTotal) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <p v-if="createForm.errors.items" class="mt-1 text-xs text-red-500">{{ createForm.errors.items }}</p>
            <button type="button" @click="createForm.items.push({ kode_bahan: '', yard: '', rp_per_yard: '' })"
              class="mt-2 text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              Tambah Bahan
            </button>
          </div>

          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showCreateModal = false"
              class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="createForm.processing"
              class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="createForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              Tambah Data
            </button>
          </div>
        </form>
      </Modal>

      <!-- ── Edit Modal ── -->
      <Modal v-model="showEditModal" title="Edit Bahan Keluar" size="xl">
        <form @submit.prevent="submitEdit" class="space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
              <input v-model="editForm.tanggal" type="date" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="editForm.errors.tanggal" class="mt-1 text-xs text-red-500">{{ editForm.errors.tanggal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
              <input v-model="editForm.no_surat_jalan" type="text"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Daftar Bahan <span class="text-red-500">*</span></label>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium w-6">#</th>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium">Kode Bahan</th>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium w-28">Yard</th>
                    <th class="text-left px-3 py-2 text-gray-500 font-medium w-36">Rp/Yard</th>
                    <th class="text-right px-3 py-2 text-gray-500 font-medium w-32">Total</th>
                    <th class="w-10"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(item, idx) in editForm.items" :key="idx">
                    <td class="px-3 py-2 text-gray-400 text-xs">{{ idx + 1 }}</td>
                    <td class="px-3 py-2">
                      <SearchableSelect
                        v-model="item.kode_bahan"
                        :options="stok.map(s => ({ value: s.kode_bahan, label: s.kode_bahan + ' (sisa: ' + formatYard(s.sisa_stok) + ')' }))"
                        placeholder="-- Pilih --"
                      />
                      <p v-if="editForm.errors[`items.${idx}.kode_bahan`]" class="mt-0.5 text-xs text-red-500">{{ editForm.errors[`items.${idx}.kode_bahan`] }}</p>
                    </td>
                    <td class="px-3 py-2">
                      <input v-model="item.yard" type="number" min="0.01" step="0.01" required placeholder="0.00"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"/>
                      <p v-if="editForm.errors[`items.${idx}.yard`]" class="mt-0.5 text-xs text-red-500">{{ editForm.errors[`items.${idx}.yard`] }}</p>
                    </td>
                    <td class="px-3 py-2">
                      <input v-model="item.rp_per_yard" type="number" min="0" step="1" required placeholder="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"/>
                      <p v-if="editForm.errors[`items.${idx}.rp_per_yard`]" class="mt-0.5 text-xs text-red-500">{{ editForm.errors[`items.${idx}.rp_per_yard`] }}</p>
                    </td>
                    <td class="px-3 py-2 text-right text-gray-700 font-medium text-xs">
                      {{ formatRupiah(itemTotal(item)) }}
                    </td>
                    <td class="px-2 py-2 text-center">
                      <button type="button" @click="editForm.items.splice(idx, 1)" :disabled="editForm.items.length === 1"
                        class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:text-red-500 hover:bg-red-50 disabled:opacity-30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="4" class="px-3 py-2 text-right text-sm font-medium text-gray-600">Grand Total</td>
                    <td class="px-3 py-2 text-right font-semibold text-gray-800">{{ formatRupiah(editGrandTotal) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <p v-if="editForm.errors.items" class="mt-1 text-xs text-red-500">{{ editForm.errors.items }}</p>
            <button type="button" @click="editForm.items.push({ kode_bahan: '', yard: '', rp_per_yard: '' })"
              class="mt-2 text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              Tambah Bahan
            </button>
          </div>

          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showEditModal = false"
              class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="editForm.processing"
              class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="editForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </Modal>

      <!-- ── Detail Modal ── -->
      <Modal v-model="showDetailModal" title="Detail Surat Jalan" size="lg">
        <div v-if="detailNota" class="space-y-4">
          <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm bg-gray-50 rounded-lg p-4">
            <div class="text-gray-500">No. Surat Jalan</div>
            <div class="font-semibold text-gray-800">{{ detailNota.no_surat_jalan }}</div>
            <div class="text-gray-500">Tanggal</div>
            <div class="font-medium text-gray-700">{{ formatDate(detailNota.tanggal) }}</div>
            <div class="text-gray-500">Jumlah Item</div>
            <div class="font-medium text-gray-700">{{ detailNota.items_count }} item</div>
            <div class="text-gray-500">Grand Total</div>
            <div class="font-semibold text-amber-700">{{ formatRupiah(detailNota.grand_total) }}</div>
          </div>

          <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-3 py-2 text-gray-500 font-medium">#</th>
                <th class="text-left px-3 py-2 text-gray-500 font-medium">Kode Bahan</th>
                <th class="text-right px-3 py-2 text-gray-500 font-medium">Yard</th>
                <th class="text-right px-3 py-2 text-gray-500 font-medium">Rp/Yard</th>
                <th class="text-right px-3 py-2 text-gray-500 font-medium">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(item, idx) in detailNota.items" :key="item.id" class="hover:bg-gray-50">
                <td class="px-3 py-2 text-gray-400">{{ idx + 1 }}</td>
                <td class="px-3 py-2 font-medium text-gray-800">{{ item.kode_bahan }}</td>
                <td class="px-3 py-2 text-right text-gray-700">{{ formatYard(item.yard) }}</td>
                <td class="px-3 py-2 text-right text-gray-700">{{ formatRupiah(item.rp_per_yard) }}</td>
                <td class="px-3 py-2 text-right font-medium text-gray-800">{{ formatRupiah(item.total) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button type="button" @click="openEditFromDetail"
              class="px-4 py-2 text-sm text-amber-700 border border-amber-300 rounded-lg hover:bg-amber-50 transition-colors">Edit</button>
            <button type="button" @click="showDetailModal = false"
              class="px-4 py-2 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Tutup</button>
          </div>
        </div>
      </Modal>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DataTable from '@/Components/DataTable.vue'
import Modal from '@/Components/Modal.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
  data: Object,
  stok: Array,
  nextSuratJalan: { type: String, default: '' },
})

const columns = [
  { key: 'tanggal',        label: 'Tanggal',        type: 'date' },
  { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
  { key: 'items_count',    label: 'Jumlah Bahan' },
  { key: 'grand_total',    label: 'Total Nilai' },
]

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const formatYard   = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatDate   = (val) => { if (!val) return '—'; return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }
const itemTotal    = (item) => (parseFloat(item.yard) || 0) * (parseFloat(item.rp_per_yard) || 0)

// ── Detail Modal ──
const showDetailModal = ref(false)
const detailNota      = ref(null)

const openDetail = (nota) => {
  detailNota.value  = nota
  showDetailModal.value = true
}

// ── Create Modal ──
const showCreateModal = ref(false)

const createForm = useForm({
  tanggal: '',
  no_surat_jalan: '',
  items: [{ kode_bahan: '', yard: '', rp_per_yard: '' }],
})

const createGrandTotal = computed(() => createForm.items.reduce((sum, item) => sum + itemTotal(item), 0))

const openCreate = () => {
  createForm.reset()
  createForm.no_surat_jalan = props.nextSuratJalan
  createForm.items = [{ kode_bahan: '', yard: '', rp_per_yard: '' }]
  createForm.clearErrors()
  showCreateModal.value = true
}

const submitCreate = () => {
  createForm.post('/bahan-keluar', {
    onSuccess: () => {
      showCreateModal.value = false
      createForm.reset()
      createForm.items = [{ kode_bahan: '', yard: '', rp_per_yard: '' }]
    },
  })
}

// ── Edit Modal ──
const showEditModal = ref(false)
const editNota      = ref(null)

const editForm = useForm({
  tanggal: '', no_surat_jalan: '',
  items: [{ kode_bahan: '', yard: '', rp_per_yard: '' }],
})

const editGrandTotal = computed(() => editForm.items.reduce((sum, item) => sum + itemTotal(item), 0))

const openEdit = (nota) => {
  editNota.value          = nota
  editForm.tanggal        = nota.tanggal?.substring(0, 10) ?? ''
  editForm.no_surat_jalan = nota.no_surat_jalan ?? ''
  editForm.items = nota.items.map(i => ({
    kode_bahan:  i.kode_bahan  ?? '',
    yard:        i.yard        ?? '',
    rp_per_yard: i.rp_per_yard != null ? parseFloat(i.rp_per_yard) : '',
  }))
  editForm.clearErrors()
  showEditModal.value = true
}

const openEditFromDetail = () => {
  showDetailModal.value = false
  openEdit(detailNota.value)
}

const submitEdit = () => {
  editForm.put(`/bahan-keluar/${editNota.value.id}`, {
    onSuccess: () => { showEditModal.value = false },
  })
}
</script>

