<template>
  <AdminLayout title="Stok Barang">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-xl font-semibold text-gray-800">Stok Barang</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau stok di kantor dan di toko secara real-time</p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Stok Kantor</p>
          <p class="text-2xl font-bold text-blue-600 mt-1">{{ totalSisaKantor }}</p>
          <p class="text-xs text-gray-400 mt-0.5">pcs tersedia</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Stok Toko</p>
          <p class="text-2xl font-bold text-emerald-600 mt-1">{{ totalSisaToko }}</p>
          <p class="text-xs text-gray-400 mt-0.5">pcs tersedia</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Jenis Model</p>
          <p class="text-2xl font-bold text-purple-600 mt-1">{{ uniqueModels }}</p>
          <p class="text-xs text-gray-400 mt-0.5">model aktif</p>
        </div>
        <div class="col-span-2 sm:col-span-1 bg-white rounded-xl border border-amber-100 shadow-sm px-5 py-4">
          <p class="text-xs text-amber-600 uppercase tracking-wide font-semibold">Omset Toko</p>
          <p class="text-lg font-bold text-amber-700 mt-1 leading-tight">{{ formatRupiah(omsetToko) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">lunas + pending</p>
        </div>
        <div class="col-span-2 sm:col-span-1 bg-white rounded-xl border border-orange-100 shadow-sm px-5 py-4">
          <p class="text-xs text-orange-600 uppercase tracking-wide font-semibold">Omset Gudang</p>
          <p class="text-lg font-bold text-orange-700 mt-1 leading-tight">{{ formatRupiah(omsetGudang) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">lunas + pending</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm px-5 py-4">
          <p class="text-xs text-green-600 uppercase tracking-wide font-semibold">Total Omset</p>
          <p class="text-lg font-bold text-green-700 mt-1 leading-tight">{{ formatRupiah(omsetToko + omsetGudang) }}</p>
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
          <input v-model="search" type="text" placeholder="Cari PO / Model..."
            class="w-full max-w-xs px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent bg-white"/>
        </div>

        <!-- Stok Kantor Tab -->
        <div v-if="activeTab === 'kantor'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <th class="px-6 py-3 text-left font-medium">PO</th>
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
                <td colspan="7" class="px-6 py-10 text-center text-gray-400">Belum ada data stok kantor.</td>
              </tr>
              <tr v-for="row in filteredKantor" :key="row.po+'||'+row.model" class="hover:bg-gray-50/60 transition-colors">
                <td class="px-6 py-3.5 font-medium text-gray-800">{{ row.po }}</td>
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
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="filteredToko.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada data stok toko.</td>
              </tr>
              <tr v-for="row in filteredToko" :key="row.model" class="hover:bg-gray-50/60 transition-colors">
                <td class="px-6 py-3.5 font-medium text-gray-800">{{ row.model }}</td>
                <td class="px-6 py-3.5 text-right text-gray-700">{{ row.dikirim_toko }}</td>
                <td class="px-6 py-3.5 text-right text-amber-600 font-medium">{{ row.terjual }}</td>
                <td class="px-6 py-3.5 text-right font-semibold" :class="row.sisa_toko > 0 ? 'text-emerald-600' : 'text-gray-400'">
                  {{ row.sisa_toko }}
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
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  stokKantor:  { type: Array, default: () => [] },
  stokToko:    { type: Array, default: () => [] },
  omsetToko:   { type: Number, default: 0 },
  omsetGudang: { type: Number, default: 0 },
})

const activeTab = ref('kantor')
const search = ref('')

const tabs = [
  { key: 'kantor', label: 'Stok Kantor' },
  { key: 'toko',   label: 'Stok Toko' },
]

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

const filteredKantor = computed(() => {
  const q = search.value.toLowerCase()
  return q ? props.stokKantor.filter(r => r.po?.toLowerCase().includes(q) || r.model?.toLowerCase().includes(q)) : props.stokKantor
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
