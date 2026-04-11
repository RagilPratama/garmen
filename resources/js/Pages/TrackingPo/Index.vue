<template>
  <AdminLayout title="Tracking PO">
    <div class="space-y-6">
      <div>
        <h1 class="text-xl font-semibold text-gray-800">Tracking PO Produksi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau posisi setiap PO di alur produksi secara real-time.</p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-9">
        <div
          v-for="(stageKey, i) in [...stageOrder, 'selesai', 'pending']"
          :key="stageKey"
          class="bg-white rounded-xl border px-3 py-3 text-center shadow-sm"
          :class="stageMeta[stageKey]?.borderColor || 'border-gray-100'"
        >
          <p class="text-2xl font-bold" :class="stageMeta[stageKey]?.textColor || 'text-gray-400'">
            {{ summary[stageKey] ?? 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-0.5 leading-tight">{{ stageMeta[stageKey]?.label || stageKey }}</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="flex gap-3 items-center">
        <input
          v-model="search"
          type="text"
          placeholder="Cari PO atau model..."
          class="w-64 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
        />
        <select
          v-model="filterStage"
          class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
        >
          <option value="">Semua Stage</option>
          <option v-for="s in stageOrder" :key="s" :value="s">{{ stageMeta[s]?.label }}</option>
          <option value="selesai">Selesai</option>
          <option value="pending">Belum Mulai</option>
        </select>
        <span class="text-xs text-gray-400">{{ filtered.length }} PO ditemukan</span>
      </div>

      <!-- PO Cards -->
      <div v-if="filtered.length === 0" class="text-center py-16 text-gray-300 text-sm bg-white rounded-xl border border-gray-100">
        Belum ada data PO.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="row in filtered"
          :key="row.po + row.model"
          class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
        >
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
            <div class="flex items-center gap-3">
              <div class="bg-amber-50 text-amber-700 font-bold text-sm px-3 py-1 rounded-lg">{{ row.po }}</div>
              <span class="text-gray-700 font-medium">{{ row.model }}</span>
            </div>
            <span
              class="text-xs font-semibold px-3 py-1 rounded-full"
              :class="currentStageBadge(row.current_stage)"
            >
              {{ row.current_stage === 'selesai' ? '✓ Selesai' : row.current_stage === 'pending' ? 'Belum Mulai' : '▶ ' + row.current_label }}
            </span>
          </div>

          <!-- Progress Steps -->
          <div class="px-5 py-4 overflow-x-auto">
            <div class="flex items-center min-w-max gap-0">
              <template v-for="(stageKey, i) in stageOrder" :key="stageKey">
                <!-- Step Node -->
                <div class="flex flex-col items-center w-20">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all"
                    :class="stepNodeClass(row.stages[stageKey], row.current_stage === stageKey)"
                  >
                    <svg v-if="row.stages[stageKey] === 'done'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span v-else-if="row.stages[stageKey] === 'active'" class="animate-pulse">●</span>
                    <span v-else class="text-xs">{{ i + 1 }}</span>
                  </div>
                  <p class="text-xs text-center mt-1 leading-tight w-full" :class="stepLabelClass(row.stages[stageKey], row.current_stage === stageKey)">
                    {{ stageMeta[stageKey]?.shortLabel }}
                  </p>
                  <span
                    class="mt-1 text-xs px-1.5 py-0.5 rounded-full font-medium"
                    :class="stepBadgeClass(row.stages[stageKey])"
                  >
                    {{ row.stages[stageKey] === 'done' ? 'selesai' : row.stages[stageKey] === 'active' ? 'proses' : '—' }}
                  </span>
                </div>
                <!-- Connector -->
                <div v-if="i < stageOrder.length - 1" class="h-0.5 w-6 self-start mt-4 shrink-0" :class="connectorClass(row.stages[stageKey])"></div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  trackingData: { type: Array, default: () => [] },
  summary:      { type: Object, default: () => ({}) },
  stageLabels:  { type: Object, default: () => ({}) },
})

const search      = ref('')
const filterStage = ref('')

const stageOrder = ['potong', 'jahit', 'cuci', 'finishing', 'kantor', 'toko', 'jual']

const stageMeta = {
  potong:    { label: 'Proses Potong',    shortLabel: 'Potong',    textColor: 'text-amber-600',   borderColor: 'border-amber-100' },
  jahit:     { label: 'Proses Jahit',     shortLabel: 'Jahit',     textColor: 'text-orange-600',  borderColor: 'border-orange-100' },
  cuci:      { label: 'Proses Cuci',      shortLabel: 'Cuci',      textColor: 'text-cyan-600',    borderColor: 'border-cyan-100' },
  finishing: { label: 'Finishing',        shortLabel: 'Finishing', textColor: 'text-purple-600',  borderColor: 'border-purple-100' },
  kantor:    { label: 'Masuk Kantor',     shortLabel: 'Kantor',    textColor: 'text-indigo-600',  borderColor: 'border-indigo-100' },
  toko:      { label: 'Kirim Toko',       shortLabel: 'Ke Toko',   textColor: 'text-blue-600',    borderColor: 'border-blue-100' },
  jual:      { label: 'Jual Gudang',      shortLabel: 'Jual',      textColor: 'text-green-600',   borderColor: 'border-green-100' },
  selesai:   { label: 'Selesai',          shortLabel: 'Selesai',   textColor: 'text-emerald-600', borderColor: 'border-emerald-100' },
  pending:   { label: 'Belum Mulai',      shortLabel: '—',         textColor: 'text-gray-400',    borderColor: 'border-gray-100' },
}

const filtered = computed(() => {
  let list = props.trackingData
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(r => r.po.toLowerCase().includes(q) || r.model.toLowerCase().includes(q))
  }
  if (filterStage.value) {
    list = list.filter(r => r.current_stage === filterStage.value)
  }
  return list
})

const stepNodeClass = (status, isCurrent) => ({
  'bg-green-500 border-green-500 text-white':                       status === 'done',
  'bg-amber-400 border-amber-400 text-white ring-4 ring-amber-100': status === 'active' || isCurrent && status !== 'done',
  'bg-white border-gray-200 text-gray-300':                         status === 'pending' && !isCurrent,
})

const stepLabelClass = (status, isCurrent) => ({
  'text-green-600 font-medium': status === 'done',
  'text-amber-600 font-semibold': status === 'active' || (isCurrent && status !== 'done'),
  'text-gray-400': status === 'pending' && !isCurrent,
})

const stepBadgeClass = (status) => ({
  'bg-green-100 text-green-700':  status === 'done',
  'bg-amber-100 text-amber-700':  status === 'active',
  'text-gray-200':                status === 'pending',
})

const connectorClass = (stageStatus) => ({
  'bg-green-400': stageStatus === 'done',
  'bg-amber-300': stageStatus === 'active',
  'bg-gray-200':  stageStatus === 'pending',
})

const currentStageBadge = (stage) => ({
  'bg-emerald-100 text-emerald-700': stage === 'selesai',
  'bg-amber-100 text-amber-700':     stage !== 'selesai' && stage !== 'pending',
  'bg-gray-100 text-gray-400':       stage === 'pending',
})
</script>
