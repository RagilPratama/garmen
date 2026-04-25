<template>
  <AdminLayout title="Stok Barang">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-xl font-semibold text-gray-800">Stok Barang</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau stok di kantor dan di toko secara real-time</p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex flex-col justify-center">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Stok Kantor</p>
          <p class="text-lg md:text-xl font-bold text-blue-600 mt-1 truncate" :title="totalSisaKantor.toLocaleString('id-ID')">{{ totalSisaKantor.toLocaleString('id-ID') }}</p>
          <p class="text-xs text-gray-400 mt-0.5">pcs tersedia</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex flex-col justify-center">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Stok Toko</p>
          <p class="text-lg md:text-xl font-bold text-emerald-600 mt-1 truncate" :title="totalSisaToko.toLocaleString('id-ID')">{{ totalSisaToko.toLocaleString('id-ID') }}</p>
          <p class="text-xs text-gray-400 mt-0.5">pcs tersedia</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex flex-col justify-center">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Jenis Model</p>
          <p class="text-lg md:text-xl font-bold text-purple-600 mt-1 truncate" :title="uniqueModels.toLocaleString('id-ID')">{{ uniqueModels.toLocaleString('id-ID') }}</p>
          <p class="text-xs text-gray-400 mt-0.5">model aktif</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-100 shadow-sm px-5 py-4 flex flex-col justify-center">
          <p class="text-xs text-amber-600 uppercase tracking-wide font-semibold">Omset Toko</p>
          <p class="text-lg md:text-xl font-bold text-amber-700 mt-1 leading-tight truncate" :title="formatRupiah(omsetToko)">{{ formatRupiah(omsetToko) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">lunas + pending</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm px-5 py-4 flex flex-col justify-center">
          <p class="text-xs text-orange-600 uppercase tracking-wide font-semibold">Omset Gudang</p>
          <p class="text-lg md:text-xl font-bold text-orange-700 mt-1 leading-tight truncate" :title="formatRupiah(omsetGudang)">{{ formatRupiah(omsetGudang) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">lunas + pending</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm px-5 py-4 flex flex-col justify-center">
          <p class="text-xs text-green-600 uppercase tracking-wide font-semibold">Total Omset</p>
          <p class="text-lg md:text-xl font-bold text-green-700 mt-1 leading-tight truncate" :title="formatRupiah(omsetToko + omsetGudang)">{{ formatRupiah(omsetToko + omsetGudang) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">gabungan</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 flex">
          <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
            :class="['px-6 py-3.5 text-sm font-medium transition-colors border-b-2 -mb-px',
              activeTab === tab.key ? 'border-amber-500 text-amber-600 bg-amber-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50']">
            {{ tab.label }}
            <span :class="['ml-2 px-1.5 py-0.5 rounded-full text-xs font-semibold',
              activeTab === tab.key ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500']">
              {{ tab.key === 'kantor' ? stokKantor.length : stokToko.length }}
            </span>
          </button>
        </div>

        <!-- Search -->
        <div class="px-6 py-3 border-b border-gray-50 bg-gray-50/50">
          <input v-model="search" type="text" placeholder="Cari Model..."
            class="w-full max-w-xs px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent bg-white"/>
        </div>

        <!-- Stok Kantor Tab -->
        <div v-if="activeTab === 'kantor'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <th class="px-6 py-3 text-left font-medium">Model</th>
                <th class="px-6 py-3 text-right font-medium">Masuk Kantor</th>
                <th class="px-6 py-3 text-right font-medium">Kirim Toko</th>
                <th class="px-6 py-3 text-right font-medium">Jual Gudang</th>
                <th class="px-6 py-3 text-right font-medium">Sisa Kantor</th>
                <th class="px-6 py-3 text-center font-medium">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="filteredKantor.length === 0">
                <td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada data stok kantor.</td>
              </tr>
              <tr v-for="row in filteredKantor" :key="row.model" class="hover:bg-gray-50/60 transition-colors">
                <td class="px-6 py-3.5 text-gray-600">{{ row.model }}</td>
                <td class="px-6 py-3.5 text-right text-gray-700">{{ row.masuk_kantor }}</td>
                <td class="px-6 py-3.5 text-right text-gray-500">{{ row.kirim_toko }}</td>
                <td class="px-6 py-3.5 text-right text-orange-500">{{ row.jual_gudang }}</td>
                <td class="px-6 py-3.5 text-right font-semibold" :class="row.sisa_kantor > 0 ? 'text-blue-600' : 'text-gray-400'">
                  {{ row.sisa_kantor }}
                </td>
                <td class="px-6 py-3.5 text-center">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    row.sisa_kantor > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500']">
                    {{ row.sisa_kantor > 0 ? 'Tersedia' : 'Kosong' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Stok Toko Tab -->
        <div v-if="activeTab === 'toko'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <th class="px-6 py-3 text-left font-medium">Model</th>
                <th class="px-6 py-3 text-right font-medium">Dikirim ke Toko</th>
                <th class="px-6 py-3 text-right font-medium">Terjual</th>
                <th class="px-6 py-3 text-right font-medium">Sisa Toko</th>
                <th class="px-6 py-3 text-center font-medium">Status</th>
                <th class="px-6 py-3 text-center font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="filteredToko.length === 0">
                <td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada data stok toko.</td>
              </tr>
              <tr v-for="row in filteredToko" :key="row.model" class="hover:bg-gray-50/60 transition-colors">
                <td class="px-6 py-3.5 font-medium text-gray-800">{{ row.model }}</td>
                <td class="px-6 py-3.5 text-right text-gray-700">{{ row.dikirim_toko }}</td>
                <td class="px-6 py-3.5 text-right text-amber-600 font-medium">{{ row.terjual }}</td>
                <td class="px-6 py-3.5 text-right font-semibold" :class="row.sisa_toko > 0 ? 'text-emerald-600' : 'text-gray-400'">
                  {{ row.sisa_toko }}
                </td>
                <td class="px-6 py-3.5 text-center">
                  <button @click="openEditModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors">
                    Edit
                  </button>
                </td>
                <td class="px-6 py-3.5 text-center">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    row.sisa_toko > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500']">
                    {{ row.sisa_toko > 0 ? 'Tersedia' : 'Habis' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Edit Modal -->
      <Modal v-model="editModalOpen" title="Edit Stok Toko" size="lg">
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <p class="text-sm text-gray-600">Model: <span class="font-semibold">{{ editModel }}</span></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Sisa Toko</label>
            <input v-model.number="editForm.sisa_toko" type="number" min="0"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            <p v-if="editForm.errors.sisa_toko" class="mt-1 text-xs text-red-500">{{ editForm.errors.sisa_toko }}</p>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="closeEditModal"
              class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button type="submit" :disabled="editForm.processing"
              class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="editForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              Simpan
            </button>
          </div>
        </form>
      </Modal>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, defineAsyncComponent } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'

const Modal = defineAsyncComponent(() => import('@/Components/Modal.vue'))

const props = defineProps({
  stokKantor:  { type: Array, default: () => [] },
  stokToko:    { type: Array, default: () => [] },
  omsetToko:   { type: Number, default: 0 },
  omsetGudang: { type: Number, default: 0 },
})

const editModalOpen = ref(false)
const editModel = ref('')
const editSisaToko = ref(0)
const editForm = useForm({
  model: '',
  sisa_toko: 0,
})

const openEditModal = (row) => {
  editModel.value = row.model
  editSisaToko.value = row.sisa_toko
  editForm.model = row.model
  editForm.sisa_toko = row.sisa_toko
  editModalOpen.value = true
}

const closeEditModal = () => {
  editModalOpen.value = false
}

const submitEdit = () => {
  editForm.put(route('stok-barang.update'), {
    onSuccess: () => {
      closeEditModal()
    },
  })
}

const activeTab = ref('kantor')
const search = ref('')

const tabs = [
  { key: 'kantor', label: 'Stok Kantor' },
  { key: 'toko',   label: 'Stok Toko' },
]

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

const filteredKantor = computed(() => {
  const q = search.value.toLowerCase()
  return q ? props.stokKantor.filter(r => r.model?.toLowerCase().includes(q)) : props.stokKantor
})

const filteredToko = computed(() => {
  const q = search.value.toLowerCase()
  return q ? props.stokToko.filter(r => r.model?.toLowerCase().includes(q)) : props.stokToko
})

const totalSisaKantor = computed(() => props.stokKantor.reduce((s, r) => s + (r.sisa_kantor > 0 ? r.sisa_kantor : 0), 0))
const totalSisaToko   = computed(() => props.stokToko.reduce((s, r) => s + (r.sisa_toko > 0 ? r.sisa_toko : 0), 0))
const uniqueModels    = computed(() => new Set([
  ...props.stokKantor.map(r => r.model),
  ...props.stokToko.map(r => r.model),
]).size)
</script>
