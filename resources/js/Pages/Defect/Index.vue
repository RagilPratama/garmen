<template>
  <AdminLayout title="Laporan Defect">
    <div class="space-y-5">

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div v-for="s in summary" :key="s.sumber"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4 cursor-pointer transition-all hover:shadow-md"
          :class="activeSumber === s.sumber ? 'ring-2 ring-red-400' : ''"
          @click="toggleSumber(s.sumber)">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
            :class="sumberStyle(s.sumber).bg">
            <svg class="w-5 h-5" :class="sumberStyle(s.sumber).text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-400 capitalize">Proses {{ s.sumber }}</p>
            <p class="font-bold text-gray-800 text-lg">{{ s.total_defect }} <span class="text-sm font-normal text-gray-500">pcs</span></p>
            <p class="text-xs text-gray-400">{{ s.jumlah_kasus }} kasus</p>
          </div>
        </div>
        <div v-if="!summary.length" class="col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-8 text-center text-sm text-gray-400">
          Belum ada data defect
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-red-400 via-orange-400 to-amber-400"></div>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-base font-semibold text-gray-800">
                Rincian Defect
                <span v-if="activeSumber" class="ml-2 text-xs font-normal text-white px-2 py-0.5 rounded-full capitalize"
                  :class="sumberStyle(activeSumber).badge">Proses {{ activeSumber }}</span>
              </h2>
              <p class="text-xs text-gray-400 mt-0.5">{{ rows?.total ?? 0 }} kasus</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button v-if="activeSumber" @click="toggleSumber(null)"
              class="text-xs text-gray-500 hover:text-red-600 flex items-center gap-1 border border-gray-200 rounded-lg px-3 py-1.5 hover:border-red-300 transition">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
              Reset filter
            </button>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
              </svg>
              <input v-model="searchQuery" type="text" placeholder="Cari PO / model..."
                class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent w-52 transition-all"/>
              <button v-if="searchQuery" @click="searchQuery = ''"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
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
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Proses</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. PO</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Model</th>
                <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pcs Defect</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in rows?.data" :key="row.id"
                class="border-b border-gray-50 transition-colors"
                :class="[i % 2 === 0 ? 'bg-white' : 'bg-gray-50/30', 'hover:bg-red-50/30']">
                <td class="px-5 py-3.5">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500">
                    {{ (rows.current_page - 1) * rows.per_page + i + 1 }}
                  </span>
                </td>
                <td class="px-5 py-3.5">
                  <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full capitalize"
                    :class="sumberStyle(row.sumber).badge">
                    Proses {{ row.sumber }}
                  </span>
                </td>
                <td class="px-5 py-3.5 font-semibold text-gray-700">{{ row.po }}</td>
                <td class="px-5 py-3.5 text-gray-600">{{ row.model }}</td>
                <td class="px-5 py-3.5 text-center">
                  <span class="inline-flex items-center justify-center px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-full">
                    {{ row.pcs_defect }} pcs
                  </span>
                </td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ row.keterangan ?? '—' }}</td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ formatDate(row.created_at) }}</td>
              </tr>
              <tr v-if="!rows?.data?.length">
                <td colspan="7" class="px-4 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">{{ searchQuery ? 'Data tidak ditemukan' : 'Tidak ada defect' }}</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="rows?.last_page > 1" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-xs text-gray-500">
            Menampilkan <span class="font-semibold text-gray-700">{{ rows.from }}–{{ rows.to }}</span>
            dari <span class="font-semibold text-gray-700">{{ rows.total }}</span> kasus
          </p>
          <div class="flex items-center gap-1 flex-wrap justify-center">
            <Link v-for="link in rows.links" :key="link.label"
              :href="link.url ? appendQuery(link.url) : '#'"
              class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
              :class="link.active ? 'bg-red-500 text-white font-semibold shadow-sm'
                : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200'
                           : 'text-gray-300 cursor-default pointer-events-none'"
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
  sumber:  { type: String, default: null },
})

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

const sumberStyle = (s) => {
  const map = {
    jahit:     { bg: 'bg-blue-50',   text: 'text-blue-500',   badge: 'bg-blue-100 text-blue-700' },
    cuci:      { bg: 'bg-cyan-50',   text: 'text-cyan-500',   badge: 'bg-cyan-100 text-cyan-700' },
    finishing: { bg: 'bg-violet-50', text: 'text-violet-500', badge: 'bg-violet-100 text-violet-700' },
  }
  return map[s] ?? { bg: 'bg-gray-50', text: 'text-gray-500', badge: 'bg-gray-100 text-gray-700' }
}

const searchQuery  = ref(new URLSearchParams(window.location.search).get('search') ?? '')
const activeSumber = ref(props.sumber ?? null)

let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => navigate(), 350)
})

const navigate = () => {
  const params = {}
  if (searchQuery.value) params.search = searchQuery.value
  if (activeSumber.value) params.sumber = activeSumber.value
  router.get('/defect', params, { preserveState: true, replace: true })
}

const toggleSumber = (val) => {
  activeSumber.value = activeSumber.value === val ? null : val
  navigate()
}

const appendQuery = (url) => {
  const u = new URL(url, window.location.origin)
  if (searchQuery.value)  u.searchParams.set('search', searchQuery.value)
  if (activeSumber.value) u.searchParams.set('sumber', activeSumber.value)
  return u.pathname + u.search
}
</script>
