<template>
  <AdminLayout title="Rincian Bahan Masuk & Keluar">
    <div class="space-y-5">

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="s in summary" :key="s.kode_bahan"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
            :class="tab === 'masuk' ? 'bg-emerald-50' : 'bg-rose-50'">
            <svg class="w-5 h-5" :class="tab === 'masuk' ? 'text-emerald-500' : 'text-rose-500'"
              fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-400 truncate">{{ s.kode_bahan }}</p>
            <p class="font-bold text-gray-800 text-sm">{{ Number(s.total_yard).toLocaleString('id-ID') }} yard</p>
            <p class="text-xs font-medium" :class="tab === 'masuk' ? 'text-emerald-600' : 'text-rose-600'">
              {{ formatRupiah(s.total_nilai) }}
            </p>
          </div>
        </div>
        <div v-if="!summary.length"
          class="col-span-full bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-8 text-center text-sm text-gray-400">
          Belum ada data
        </div>
      </div>

      <!-- Main Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-1" :class="tab === 'masuk' ? 'bg-gradient-to-r from-emerald-400 to-teal-400' : 'bg-gradient-to-r from-rose-400 to-orange-400'"></div>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
              :class="tab === 'masuk' ? 'bg-emerald-50' : 'bg-rose-50'">
              <svg class="w-5 h-5" :class="tab === 'masuk' ? 'text-emerald-500' : 'text-rose-500'"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            <div>
              <h2 class="text-base font-semibold text-gray-800">Rincian Bahan {{ tab === 'masuk' ? 'Masuk' : 'Keluar' }}</h2>
              <p class="text-xs text-gray-400 mt-0.5">{{ rows?.total ?? 0 }} baris</p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Tab switcher -->
            <div class="flex rounded-xl overflow-hidden border border-gray-200 text-sm">
              <button @click="switchTab('masuk')"
                class="px-4 py-2 font-medium transition-colors"
                :class="tab === 'masuk' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">
                Bahan Masuk
              </button>
              <button @click="switchTab('keluar')"
                class="px-4 py-2 font-medium transition-colors"
                :class="tab === 'keluar' ? 'bg-rose-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">
                Bahan Keluar
              </button>
            </div>

            <!-- Search -->
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
              </svg>
              <input v-model="searchQuery" type="text" placeholder="Cari kode bahan, nota..."
                class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-current focus:border-transparent w-52 transition-all"
                :class="tab === 'masuk' ? 'focus:ring-emerald-400' : 'focus:ring-rose-400'"/>
              <button v-if="searchQuery" @click="searchQuery = ''"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Table Masuk -->
        <div v-if="tab === 'masuk'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Nota</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Supplier</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode Bahan</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Bahan</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Yard</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rp/Yard</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in rows?.data" :key="row.id"
                class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">
                <td class="px-5 py-3">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500">
                    {{ (rows.current_page - 1) * rows.per_page + i + 1 }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-600 text-xs">{{ formatDate(row.tanggal) }}</td>
                <td class="px-5 py-3 font-medium text-gray-700">{{ row.no_nota ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-600">{{ row.supplier ?? '—' }}</td>
                <td class="px-5 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full">
                    {{ row.kode_bahan }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ row.nama_bahan ?? '—' }}</td>
                <td class="px-5 py-3 text-right font-medium text-gray-700">{{ Number(row.yard).toLocaleString('id-ID') }}</td>
                <td class="px-5 py-3 text-right text-gray-600 text-xs">{{ formatRupiah(row.rp_per_yard) }}</td>
                <td class="px-5 py-3 text-right font-semibold text-emerald-700">{{ formatRupiah(row.total) }}</td>
              </tr>
              <tr v-if="!rows?.data?.length">
                <td colspan="9" class="px-4 py-16 text-center text-sm text-gray-400">Belum ada data</td>
              </tr>
            </tbody>
            <!-- Footer total -->
            <tfoot v-if="rows?.data?.length">
              <tr class="bg-emerald-50 border-t border-emerald-100">
                <td colspan="6" class="px-5 py-3 text-xs font-semibold text-emerald-700">Subtotal halaman ini</td>
                <td class="px-5 py-3 text-right text-xs font-bold text-emerald-700">
                  {{ Number(rows.data.reduce((s, r) => s + Number(r.yard), 0)).toLocaleString('id-ID') }} yard
                </td>
                <td class="px-5 py-3"></td>
                <td class="px-5 py-3 text-right text-xs font-bold text-emerald-700">
                  {{ formatRupiah(rows.data.reduce((s, r) => s + Number(r.total), 0)) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Table Keluar -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Surat Jalan</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode Bahan</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Yard</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rp/Yard</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in rows?.data" :key="row.id"
                class="border-b border-gray-50 hover:bg-rose-50/30 transition-colors"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">
                <td class="px-5 py-3">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500">
                    {{ (rows.current_page - 1) * rows.per_page + i + 1 }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-600 text-xs">{{ formatDate(row.tanggal) }}</td>
                <td class="px-5 py-3 font-medium text-gray-700">{{ row.no_surat_jalan ?? '—' }}</td>
                <td class="px-5 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 bg-rose-50 text-rose-700 text-xs font-semibold rounded-full">
                    {{ row.kode_bahan }}
                  </span>
                </td>
                <td class="px-5 py-3 text-right font-medium text-gray-700">{{ Number(row.yard).toLocaleString('id-ID') }}</td>
                <td class="px-5 py-3 text-right text-gray-600 text-xs">{{ formatRupiah(row.rp_per_yard) }}</td>
                <td class="px-5 py-3 text-right font-semibold text-rose-700">{{ formatRupiah(row.total) }}</td>
              </tr>
              <tr v-if="!rows?.data?.length">
                <td colspan="7" class="px-4 py-16 text-center text-sm text-gray-400">Belum ada data</td>
              </tr>
            </tbody>
            <tfoot v-if="rows?.data?.length">
              <tr class="bg-rose-50 border-t border-rose-100">
                <td colspan="4" class="px-5 py-3 text-xs font-semibold text-rose-700">Subtotal halaman ini</td>
                <td class="px-5 py-3 text-right text-xs font-bold text-rose-700">
                  {{ Number(rows.data.reduce((s, r) => s + Number(r.yard), 0)).toLocaleString('id-ID') }} yard
                </td>
                <td class="px-5 py-3"></td>
                <td class="px-5 py-3 text-right text-xs font-bold text-rose-700">
                  {{ formatRupiah(rows.data.reduce((s, r) => s + Number(r.total), 0)) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="rows?.last_page > 1" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-xs text-gray-500">
            Menampilkan <span class="font-semibold text-gray-700">{{ rows.from }}–{{ rows.to }}</span>
            dari <span class="font-semibold text-gray-700">{{ rows.total }}</span> baris
          </p>
          <div class="flex items-center gap-1 flex-wrap justify-center">
            <Link v-for="link in rows.links" :key="link.label"
              :href="link.url ? appendSearch(link.url) : '#'"
              class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
              :class="[
                link.active
                  ? (tab === 'masuk' ? 'bg-emerald-500 text-white font-semibold' : 'bg-rose-500 text-white font-semibold')
                  : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200'
                             : 'text-gray-300 cursor-default pointer-events-none'
              ]"
              :preserve-scroll="true" v-html="link.label"/>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  rows:    Object,
  summary: { type: Array, default: () => [] },
  tab:     { type: String, default: 'masuk' },
})

const formatDate   = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatRupiah = (v) => v != null ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v) : '—'

const searchQuery = ref(new URLSearchParams(window.location.search).get('search') ?? '')

let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/rincian-bahan', { tab: props.tab, search: val || undefined }, { preserveState: true, replace: true })
  }, 350)
})

const switchTab = (newTab) => {
  router.get('/rincian-bahan', { tab: newTab, search: searchQuery.value || undefined }, { preserveState: true })
}

const appendSearch = (url) => {
  const u = new URL(url, window.location.origin)
  u.searchParams.set('tab', props.tab)
  if (searchQuery.value) u.searchParams.set('search', searchQuery.value)
  return u.pathname + u.search
}
</script>
