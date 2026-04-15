<template>
  <AdminLayout title="Stok Bahan Garmen">

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Kode Bahan</p>
        <p class="text-2xl font-bold text-gray-800">{{ stats.total_kode }}</p>
        <p class="text-xs text-gray-400 mt-0.5">jenis bahan keluar</p>
      </div>
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm px-5 py-4">
        <p class="text-xs text-blue-500 font-medium uppercase tracking-wide mb-1">Total Transaksi</p>
        <p class="text-2xl font-bold text-blue-700">{{ stats.total_transaksi }}</p>
        <p class="text-xs text-gray-400 mt-0.5">pengiriman bahan</p>
      </div>
      <div class="bg-white rounded-2xl border border-orange-100 shadow-sm px-5 py-4">
        <p class="text-xs text-orange-500 font-medium uppercase tracking-wide mb-1">Total Yard Keluar</p>
        <p class="text-2xl font-bold text-orange-700">{{ formatYard(stats.total_yard) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">yard ke garmen</p>
      </div>
      <div class="bg-white rounded-2xl border border-violet-100 shadow-sm px-5 py-4">
        <p class="text-xs text-violet-500 font-medium uppercase tracking-wide mb-1">Total Nilai</p>
        <p class="text-xl font-bold text-violet-700">{{ formatRupiah(stats.total_nilai) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">nilai bahan keluar</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-orange-400 via-amber-400 to-yellow-300"></div>

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 10h4m0 0l-4-4m4 4l-4 4M3 12a9 9 0 0118 0"/></svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-800">Stok Bahan Garmen</h2>
            <p class="text-xs text-gray-400 mt-0.5">Bahan yang telah dikirim ke produksi garmen</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="searchQuery" type="text" placeholder="Cari kode / nama bahan..."
              class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent w-56 transition-all"/>
            <button v-if="searchQuery" @click="searchQuery = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
              <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode Bahan</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Bahan</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Transaksi</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Yard Keluar</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sudah Dipotong</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Nilai</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sisa di Garmen</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl Terakhir</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data?.data" :key="item.kode_bahan"
              class="border-b border-gray-50 hover:bg-orange-50/30 transition-colors cursor-pointer"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
              @click="openDetail(item)">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-mono font-semibold text-orange-600">{{ item.kode_bahan }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-600">{{ item.nama_bahan ?? '—' }}</td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">{{ item.jumlah_transaksi }}x</span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <span class="font-semibold text-orange-700">{{ formatYard(item.total_yard) }}</span>
                <span class="text-xs text-gray-400 ml-1">yd</span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <span class="text-red-500 font-medium">{{ formatYard(item.sudah_dipotong) }}</span>
                <span class="text-xs text-gray-400 ml-1">yd</span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <span class="text-violet-700 font-medium text-xs">{{ formatRupiah(item.total_nilai) }}</span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <span class="font-semibold" :class="item.sisa_stok > 0 ? 'text-emerald-700' : 'text-red-500'">
                  {{ formatYard(item.sisa_stok) }}
                </span>
                <span class="text-xs text-gray-400 ml-1">yd</span>
              </td>
              <td class="px-5 py-3.5 text-center text-xs text-gray-500">
                <span class="inline-flex items-center gap-1">
                  <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(item.last_keluar) }}
                </span>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="9" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 10h4m0 0l-4-4m4 4l-4 4M3 12a9 9 0 0118 0"/></svg>
                  </div>
                  <p class="text-sm font-medium text-gray-500">{{ searchQuery ? 'Bahan tidak ditemukan' : 'Belum ada bahan keluar' }}</p>
                  <p v-if="!searchQuery" class="text-xs text-gray-400">Tambahkan data Bahan Keluar untuk mulai memantau</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.last_page > 1" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-700">{{ data.from }}–{{ data.to }}</span> dari <span class="font-semibold text-gray-700">{{ data.total }}</span> bahan</p>
        <div class="flex items-center gap-1 flex-wrap justify-center">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url ? appendSearch(link.url) : '#'"
            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
            :class="link.active ? 'bg-orange-500 text-white font-semibold shadow-sm' : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200' : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true" v-html="link.label"/>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal v-model="showDetail" :title="'Detail Keluar: ' + (detailItem?.kode_bahan ?? '')" size="xl">
      <div v-if="detailItem" class="space-y-4">
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
          <span><span class="font-medium text-gray-700">Nama Bahan:</span> {{ detailItem.nama_bahan || '—' }}</span>
          <span><span class="font-medium text-gray-700">Total Keluar:</span> <span class="text-orange-700 font-semibold">{{ formatYard(detailItem.total_yard) }} yd</span></span>
          <span><span class="font-medium text-gray-700">Sudah Dipotong:</span> <span class="text-red-600 font-semibold">{{ formatYard(detailItem.sudah_dipotong) }} yd</span></span>
          <span><span class="font-medium text-gray-700">Sisa di Garmen:</span>
            <span :class="detailItem.sisa_stok > 0 ? 'text-emerald-700 font-semibold' : 'text-red-600 font-semibold'"> {{ formatYard(detailItem.sisa_stok) }} yd</span>
          </span>
          <span><span class="font-medium text-gray-700">Total Nilai:</span> <span class="text-violet-700">{{ formatRupiah(detailItem.total_nilai) }}</span></span>
        </div>
        <div v-if="loadingDetail" class="py-8 text-center text-gray-400 text-sm">Memuat data...</div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 rounded-tl-lg">Tanggal</th>
              <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">No. Surat Jalan</th>
              <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Yard</th>
              <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Rp/yd</th>
              <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 rounded-tr-lg">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in detailRows" :key="row.id" class="hover:bg-gray-50">
              <td class="px-4 py-2.5 text-gray-600">{{ formatDate(row.tanggal) }}</td>
              <td class="px-4 py-2.5">
                <span v-if="row.no_surat_jalan" class="font-mono text-xs bg-orange-50 text-orange-700 border border-orange-100 px-2 py-0.5 rounded">{{ row.no_surat_jalan }}</span>
                <span v-else class="text-gray-400 text-xs">—</span>
              </td>
              <td class="px-4 py-2.5 text-right font-semibold text-orange-700">{{ formatYard(row.yard) }} yd</td>
              <td class="px-4 py-2.5 text-right text-gray-500 text-xs">{{ row.rp_per_yard ? formatRupiah(row.rp_per_yard) : '—' }}</td>
              <td class="px-4 py-2.5 text-right text-violet-700 font-semibold text-xs">{{ row.total ? formatRupiah(row.total) : '—' }}</td>
            </tr>
            <tr v-if="!detailRows.length">
              <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-xs">Tidak ada data transaksi</td>
            </tr>
          </tbody>
          <tfoot v-if="detailRows.length" class="bg-orange-50 border-t border-orange-100">
            <tr>
              <td colspan="2" class="px-4 py-2 text-xs font-semibold text-gray-600">Total</td>
              <td class="px-4 py-2 text-right text-xs font-bold text-orange-700">{{ formatYard(detailItem.total_yard) }} yd</td>
              <td class="px-4 py-2"></td>
              <td class="px-4 py-2 text-right text-xs font-bold text-violet-700">{{ formatRupiah(detailItem.total_nilai) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  data:   Object,
  stats:  Object,
  search: { type: String, default: '' },
})

const formatYard   = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatDate   = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatRupiah = (v) => v != null && v > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v) : '—'

// Search
const searchQuery = ref(props.search)
let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/stok-bahan-garmen', { search: val || undefined }, { preserveState: true, replace: true })
  }, 350)
})
const appendSearch = (url) => {
  if (!searchQuery.value) return url
  const u = new URL(url, window.location.origin)
  u.searchParams.set('search', searchQuery.value)
  return u.pathname + u.search
}

// Detail modal — load transaction rows via Inertia-safe approach
const showDetail   = ref(false)
const detailItem   = ref(null)
const detailRows   = ref([])
const loadingDetail = ref(false)

const openDetail = async (item) => {
  detailItem.value = item
  detailRows.value = []
  showDetail.value = true
  loadingDetail.value = true
  try {
    const res = await axios.get('/stok-bahan-garmen/detail', { params: { kode_bahan: item.kode_bahan } })
    detailRows.value = res.data
  } finally {
    loadingDetail.value = false
  }
}
</script>
