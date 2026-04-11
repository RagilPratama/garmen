<template>
  <AdminLayout :title="title">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

      <!-- Top accent bar -->
      <div class="h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-orange-400"></div>

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-800">{{ title }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ data?.total ?? 0 }} total data</p>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <!-- Search -->
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari data..."
              class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent w-52 transition-all"
            />
            <button v-if="searchQuery" @click="clearSearch"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
              <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <button @click="$emit('open-create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-orange-500 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Data
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-50/80 border-b border-gray-100">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
              <th v-for="col in columns" :key="col.key"
                class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">
                {{ col.label }}
              </th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data?.data" :key="item.id"
              class="border-b border-gray-50 hover:bg-amber-50/40 transition-colors group"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-amber-100 group-hover:text-amber-700 transition-colors">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td v-for="col in columns" :key="col.key" class="px-5 py-3.5">
                <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]">
                  <template v-if="col.type === 'status'">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                      :class="statusClass(item[col.key])">
                      <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(item[col.key])"></span>
                      {{ item[col.key] }}
                    </span>
                  </template>
                  <template v-else-if="col.type === 'currency'">
                    <span class="font-medium text-gray-800">{{ formatRupiah(item[col.key]) }}</span>
                  </template>
                  <template v-else-if="col.type === 'date'">
                    <span class="inline-flex items-center gap-1.5 text-gray-600">
                      <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                      {{ formatDate(item[col.key]) }}
                    </span>
                  </template>
                  <template v-else>
                    <span class="text-gray-700">{{ item[col.key] ?? '—' }}</span>
                  </template>
                </slot>
              </td>
              <td class="px-5 py-3.5">
                <div class="flex items-center justify-center gap-1.5">
                  <slot name="actions" :item="item" />
                  <button @click="$emit('open-edit', item)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                    title="Edit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                  </button>
                  <button @click="confirmDelete(item.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                    title="Hapus">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td :colspan="columns.length + 2" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">
                      {{ searchQuery ? 'Data tidak ditemukan' : 'Belum ada data' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      {{ searchQuery ? `Tidak ada hasil untuk "${searchQuery}"` : 'Klik "Tambah Data" untuk memulai.' }}
                    </p>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.last_page > 1" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-500">
          Menampilkan <span class="font-semibold text-gray-700">{{ data.from }}–{{ data.to }}</span> dari <span class="font-semibold text-gray-700">{{ data.total }}</span> data
        </p>
        <div class="flex items-center gap-1 flex-wrap justify-center">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url ? appendSearch(link.url) : '#'"
            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
            :class="link.active
              ? 'bg-amber-500 text-white font-semibold shadow-sm'
              : link.url
                ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200'
                : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="deleteId" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="deleteId = null"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full">
        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-800 text-center mb-1">Hapus Data?</h3>
        <p class="text-sm text-gray-500 text-center mb-6">Tindakan ini tidak dapat dibatalkan. Data akan dihapus permanen.</p>
        <div class="flex gap-3">
          <button @click="deleteId = null"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </button>
          <button @click="doDelete"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>

    <!-- Form Modal Slot -->
    <slot name="modal" />
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  title: String,
  data:  Object,
  columns: Array,
  basePath: String,
})

defineEmits(['open-create', 'open-edit'])

const page = usePage()
const searchQuery = ref(page.props.ziggy?.query?.search ?? new URLSearchParams(window.location.search).get('search') ?? '')
const deleteId = ref(null)

let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get(props.basePath, { search: val || undefined }, { preserveState: true, replace: true })
  }, 350)
})

const clearSearch = () => { searchQuery.value = '' }

const appendSearch = (url) => {
  if (!searchQuery.value) return url
  const u = new URL(url, window.location.origin)
  u.searchParams.set('search', searchQuery.value)
  return u.pathname + u.search
}

const confirmDelete = (id) => { deleteId.value = id }
const doDelete = () => {
  router.delete(`${props.basePath}/${deleteId.value}`, {
    onSuccess: () => { deleteId.value = null }
  })
}

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
const statusClass = (s) => ({
  'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': s === 'diterima' || s === 'lunas',
  'bg-amber-50 text-amber-700 ring-1 ring-amber-200':       s === 'pending',
  'bg-red-50 text-red-700 ring-1 ring-red-200':             s === 'ditolak' || s === 'batal',
})
const statusDotClass = (s) => ({
  'bg-emerald-500': s === 'diterima' || s === 'lunas',
  'bg-amber-500':   s === 'pending',
  'bg-red-500':     s === 'ditolak' || s === 'batal',
})
</script>
