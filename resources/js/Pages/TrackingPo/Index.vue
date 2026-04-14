<template>
  <AdminLayout title="Tracking PO">
    <div class="space-y-5">

      <!-- Stat cards (clickable = filter toggle) -->
      <div class="grid grid-cols-3 sm:grid-cols-5 xl:grid-cols-9 gap-2.5">
        <div
          v-for="k in [...stageOrder, 'selesai', 'pending']"
          :key="k"
          @click="filterStage = filterStage === k ? '' : k"
          class="bg-white rounded-xl border shadow-sm px-3 py-3 text-center cursor-pointer select-none transition-all hover:shadow-md"
          :class="[stageMeta[k].borderColor, filterStage === k ? stageMeta[k].activeBg : '']"
        >
          <p class="text-xl font-bold tabular-nums" :class="stageMeta[k].textColor">{{ summary[k] ?? 0 }}</p>
          <p class="text-[11px] text-gray-500 mt-0.5 leading-tight">{{ stageMeta[k].shortLabel }}</p>
        </div>
      </div>

      <!-- Main card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-orange-400"></div>

        <!-- Header + filter bar -->
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
              </svg>
            </div>
            <div>
              <h2 class="text-sm font-semibold text-gray-800 leading-none">Tracking PO Produksi</h2>
              <p class="text-xs text-gray-400 mt-0.5">{{ filteredPOs.length }} PO · {{ filtered.length }} model</p>
            </div>
          </div>
          <div class="ml-auto flex items-center gap-2 flex-wrap">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
              </svg>
              <input v-model="search" type="text" placeholder="Cari PO atau model..."
                class="pl-8 pr-7 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 w-52 transition-all"/>
              <button v-if="search" @click="search = ''"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition">
                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <select v-model="filterStage"
              class="py-2 px-3 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
              <option value="">Semua Tahap</option>
              <option v-for="s in stageOrder" :key="s" :value="s">{{ stageMeta[s].label }}</option>
              <option value="selesai">Selesai</option>
              <option value="pending">Belum Mulai</option>
            </select>
          </div>
        </div>

        <!-- Empty -->
        <div v-if="filteredPOs.length === 0" class="py-16 text-center">
          <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <p class="text-sm text-gray-400 font-medium">{{ search || filterStage ? 'Data tidak ditemukan' : 'Belum ada data tracking PO' }}</p>
        </div>

        <!-- PO Groups -->
        <div v-else class="divide-y divide-gray-100">
          <div v-for="group in filteredPOs" :key="group.po">

            <!-- PO row header (collapsible) -->
            <button type="button"
              class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50/80 transition-colors text-left group"
              @click="togglePo(group.po)">
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 transition-transform duration-200 shrink-0"
                  :class="isExpanded(group.po) ? '' : '-rotate-90'"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                <span class="bg-amber-100 text-amber-800 font-bold text-sm px-3 py-1 rounded-lg">{{ group.po }}</span>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                  {{ group.rows.length }} model
                </span>
              </div>
              <div class="flex gap-1.5 flex-wrap justify-end max-w-xs">
                <span v-for="stg in group.uniqueStages" :key="stg"
                  class="text-xs px-2 py-0.5 rounded-full font-medium"
                  :class="stageMeta[stg]?.badgeClass ?? 'bg-gray-100 text-gray-500'">
                  {{ stageMeta[stg]?.label ?? stg }}
                </span>
              </div>
            </button>

            <!-- Model rows -->
            <div v-show="isExpanded(group.po)" class="bg-gradient-to-b from-gray-50/40 to-white">
              <div
                v-for="(row, rIdx) in group.rows"
                :key="row.model"
                class="px-6 pt-3 pb-4"
                :class="rIdx < group.rows.length - 1 ? 'border-b border-gray-100' : ''"
              >
                <!-- Model + current stage badge -->
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <div class="w-1.5 h-4 rounded-full bg-amber-300 shrink-0"></div>
                    <span class="text-sm font-semibold text-gray-700">{{ row.model }}</span>
                  </div>
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-full" :class="currentStageBadge(row.current_stage)">
                    {{ row.current_stage === 'selesai' ? '✓ Selesai' : row.current_stage === 'pending' ? '— Belum Mulai' : '▶ ' + row.current_label }}
                  </span>
                </div>

                <!-- Pipeline -->
                <div class="overflow-x-auto -mx-1 px-1 pb-1">
                  <div class="flex items-start min-w-max">
                    <template v-for="(s, i) in stageOrder" :key="s">
                      <!-- Node + label -->
                      <div class="flex flex-col items-center w-[68px]">
                        <div
                          class="w-8 h-8 rounded-full flex items-center justify-center font-bold border-2 transition-all"
                          :class="nodeClass(row.stages[s])"
                        >
                          <svg v-if="row.stages[s] === 'done'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                          </svg>
                          <span v-else-if="row.stages[s] === 'active'" class="w-2.5 h-2.5 rounded-full bg-current animate-pulse"></span>
                          <span v-else class="text-[11px] text-gray-300">{{ i + 1 }}</span>
                        </div>
                        <p class="text-[10px] text-center mt-1 leading-tight px-0.5 w-full" :class="labelClass(row.stages[s])">
                          {{ stageMeta[s].shortLabel }}
                        </p>
                        <span class="mt-0.5 text-[9px] font-semibold leading-none"
                          :class="row.stages[s] === 'done' ? 'text-green-400' : row.stages[s] === 'active' ? 'text-amber-400' : 'text-gray-200'">
                          {{ row.stages[s] === 'done' ? 'selesai' : row.stages[s] === 'active' ? 'proses' : '—' }}
                        </span>
                      </div>
                      <!-- Connector -->
                      <div v-if="i < stageOrder.length - 1"
                        class="h-0.5 w-5 self-start mt-4 shrink-0 rounded-full transition-all"
                        :class="connectorClass(row.stages[s])">
                      </div>
                    </template>
                  </div>
                </div>
              </div>
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
  potong:    { label: 'Proses Potong',  shortLabel: 'Potong',    textColor: 'text-amber-600',   borderColor: 'border-amber-200',   activeBg: 'bg-amber-50',   badgeClass: 'bg-amber-100 text-amber-700' },
  jahit:     { label: 'Proses Jahit',   shortLabel: 'Jahit',     textColor: 'text-orange-600',  borderColor: 'border-orange-200',  activeBg: 'bg-orange-50',  badgeClass: 'bg-orange-100 text-orange-700' },
  cuci:      { label: 'Proses Cuci',    shortLabel: 'Cuci',      textColor: 'text-cyan-600',    borderColor: 'border-cyan-200',    activeBg: 'bg-cyan-50',    badgeClass: 'bg-cyan-100 text-cyan-700' },
  finishing: { label: 'Finishing',      shortLabel: 'Finishing', textColor: 'text-purple-600',  borderColor: 'border-purple-200',  activeBg: 'bg-purple-50',  badgeClass: 'bg-purple-100 text-purple-700' },
  kantor:    { label: 'Masuk Kantor',   shortLabel: 'Kantor',    textColor: 'text-indigo-600',  borderColor: 'border-indigo-200',  activeBg: 'bg-indigo-50',  badgeClass: 'bg-indigo-100 text-indigo-700' },
  toko:      { label: 'Kirim Toko',     shortLabel: 'Ke Toko',   textColor: 'text-blue-600',    borderColor: 'border-blue-200',    activeBg: 'bg-blue-50',    badgeClass: 'bg-blue-100 text-blue-700' },
  jual:      { label: 'Jual Gudang',    shortLabel: 'Jual',      textColor: 'text-green-600',   borderColor: 'border-green-200',   activeBg: 'bg-green-50',   badgeClass: 'bg-green-100 text-green-700' },
  selesai:   { label: 'Selesai',        shortLabel: 'Selesai',   textColor: 'text-emerald-600', borderColor: 'border-emerald-200', activeBg: 'bg-emerald-50', badgeClass: 'bg-emerald-100 text-emerald-700' },
  pending:   { label: 'Belum Mulai',    shortLabel: 'Blm Mulai', textColor: 'text-gray-400',    borderColor: 'border-gray-200',    activeBg: 'bg-gray-50',    badgeClass: 'bg-gray-100 text-gray-500' },
}

// Filtered flat rows
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

// Group by PO
const filteredPOs = computed(() => {
  const map = new Map()
  for (const row of filtered.value) {
    if (!map.has(row.po)) map.set(row.po, { po: row.po, rows: [], stageSet: new Set() })
    const g = map.get(row.po)
    g.rows.push(row)
    g.stageSet.add(row.current_stage)
  }
  return Array.from(map.values()).map(g => ({ ...g, uniqueStages: Array.from(g.stageSet) }))
})

// Expand state — default all collapsed
const expandedPos = ref(new Set())
const togglePo = (po) => {
  const next = new Set(expandedPos.value)
  if (next.has(po)) next.delete(po)
  else next.add(po)
  expandedPos.value = next
}
const isExpanded = (po) => expandedPos.value.has(po)

// Style helpers
const nodeClass = (status) => ({
  'bg-green-500 border-green-500 text-white':                       status === 'done',
  'bg-amber-400 border-amber-400 text-white ring-4 ring-amber-100': status === 'active',
  'bg-white border-gray-200':                                       status === 'pending',
})

const labelClass = (status) => ({
  'text-green-600 font-medium':   status === 'done',
  'text-amber-600 font-semibold': status === 'active',
  'text-gray-400':                status === 'pending',
})

const connectorClass = (status) => ({
  'bg-green-400': status === 'done',
  'bg-amber-300': status === 'active',
  'bg-gray-200':  status === 'pending',
})

const currentStageBadge = (stage) => ({
  'bg-emerald-100 text-emerald-700': stage === 'selesai',
  'bg-amber-100 text-amber-700':     stage !== 'selesai' && stage !== 'pending',
  'bg-gray-100 text-gray-400':       stage === 'pending',
})
</script>
