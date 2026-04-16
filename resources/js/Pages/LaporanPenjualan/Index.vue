<template>
  <AdminLayout title="Laporan Penjualan">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Header & Search -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Laporan Penjualan Gabungan</h2>
            <p class="text-xs text-gray-500">Histori penjualan Toko dan Gudang</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative group">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-violet-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nota atau buyer..."
              class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl w-64 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition-all"
            />
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Nota</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl Nota</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Buyer</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Tagihan</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide text-orange-600">Sisa Piutang</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in data?.data" :key="row.no_nota + '-' + row.tipe"
              @click="openDetail(row)"
              class="border-b border-gray-50 hover:bg-violet-50/40 transition-colors group cursor-pointer"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-violet-100 group-hover:text-violet-700 transition-colors">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm"
                  :class="row.tipe === 'toko' ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200' : 'bg-amber-100 text-amber-700 ring-1 ring-amber-200'">
                  {{ row.tipe }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-mono text-xs bg-gray-100 text-gray-700 border border-gray-200 px-2 py-0.5 rounded shadow-sm">{{ row.no_nota || '—' }}</span>
              </td>
              <td class="px-5 py-3.5 font-medium text-gray-700">{{ formatDate(row.tanggal_nota) }}</td>
              <td class="px-5 py-3.5 text-gray-700">{{ row.buyer }}</td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full"
                  :class="row.sisa_piutang > 0 ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700'">
                  {{ row.sisa_piutang > 0 ? 'Piutang' : 'Lunas' }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right font-semibold text-gray-800">{{ formatRupiah(row.total_harga) }}</td>
              <td class="px-5 py-3.5 text-right font-bold" :class="row.sisa_piutang > 0 ? 'text-orange-600' : 'text-emerald-600'">
                {{ row.sisa_piutang > 0 ? formatRupiah(row.sisa_piutang) : '✓ LUNAS' }}
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="8" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-500">{{ searchQuery ? 'Data tidak ditemukan' : 'Belum ada data' }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.last_page > 1" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-700">{{ data.from }}–{{ data.to }}</span> dari <span class="font-semibold text-gray-700">{{ data.total }}</span> Nota</p>
        <div class="flex items-center gap-1 flex-wrap justify-center">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url ? appendSearch(link.url) : '#'"
            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
            :class="link.active ? 'bg-violet-500 text-white font-semibold shadow-sm' : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200' : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true" v-html="link.label"/>
        </div>
      </div>
    </div>

    <!-- DETAIL MODAL -->
    <Modal v-model="showDetail" :title="'Detail Nota: ' + (selectedNota?.no_nota || '')" size="lg">
      <div v-if="selectedNota" class="space-y-6">
        <!-- Info Nota -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
          <div>
            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Buyer</p>
            <p class="text-sm font-semibold text-gray-700">{{ selectedNota.buyer }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Tanggal</p>
            <p class="text-sm font-semibold text-gray-700">{{ formatDate(selectedNota.tanggal_nota) }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Tipe Penjualan</p>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
              :class="selectedNota.tipe === 'toko' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'">
              {{ selectedNota.tipe }}
            </span>
          </div>
        </div>

        <!-- Table Models -->
        <div class="border border-gray-100 rounded-xl overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50/80">
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Model</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Harga</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Disc</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="m in selectedNota.models" :key="m.model" class="hover:bg-gray-50/50">
                <td class="px-4 py-3 font-medium text-gray-700">{{ m.model }}</td>
                <td class="px-4 py-3 text-center text-gray-600">{{ m.pcs }} pcs</td>
                <td class="px-4 py-3 text-right text-gray-600">{{ formatRupiah(m.harga_satuan) }}</td>
                <td class="px-4 py-3 text-center">
                  <span v-if="m.diskon" class="text-xs text-red-600 font-medium">-{{ m.diskon }}%</span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ formatRupiah(m.total_harga) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-gray-50/50">
                <td colspan="4" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Nilai Nota</td>
                <td class="px-4 py-3 text-right font-bold text-gray-900 border-t border-gray-200">{{ formatRupiah(selectedNota.total_harga) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Summary Payment -->
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
            <p class="text-[10px] uppercase font-bold text-emerald-600 mb-1">Total Terbayar</p>
            <p class="text-xl font-black text-emerald-700">{{ formatRupiah(selectedNota.total_bayar) }}</p>
          </div>
          <div v-if="selectedNota.sisa_piutang > 0" class="flex-1 p-4 bg-orange-50 rounded-xl border border-orange-100">
            <p class="text-[10px] uppercase font-bold text-orange-600 mb-1">Sisa Piutang</p>
            <p class="text-xl font-black text-orange-700">{{ formatRupiah(selectedNota.sisa_piutang) }}</p>
          </div>
          <div v-else class="flex-1 p-4 bg-emerald-100 rounded-xl border border-emerald-200 flex items-center justify-center">
            <span class="text-emerald-700 font-bold uppercase tracking-widest text-lg italic">✓ LUNAS</span>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button @click="showDetail = false" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">Tutup</button>
        </div>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  data: Object
})

const searchQuery = ref(new URLSearchParams(window.location.search).get('search') || '')
const showDetail  = ref(false)
const selectedNota = ref(null)

const openDetail = (row) => {
  selectedNota.value = row
  showDetail.value = true
}

let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/laporan-penjualan', { search: val || undefined }, { preserveState: true, replace: true })
  }, 400)
})

const appendSearch = (url) => {
  if (!searchQuery.value) return url
  const u = new URL(url)
  u.searchParams.set('search', searchQuery.value)
  return u.toString()
}

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val || 0)
}
</script>
