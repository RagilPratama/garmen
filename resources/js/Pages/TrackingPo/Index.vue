<template>
  <AdminLayout title="Tracking Produksi">
    <div class="max-w-[1600px] mx-auto space-y-8 animate-in fade-in duration-700">
      
      <!-- Glassmorphism Summary Section -->
      <section class="relative">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-widest px-1">Ringkasan Status</h3>
          <span class="text-[10px] text-slate-400 font-normal bg-white px-2 py-0.5 rounded-full border shadow-sm">Updated just now</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div
            v-for="k in [...stageOrder, 'selesai', 'pending']"
            :key="k"
            @click="filterStage = filterStage === k ? '' : k"
            class="group relative overflow-hidden bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 p-4 cursor-pointer transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1"
            :class="[
              filterStage === k ? 'ring-2 ring-offset-2 ' + stageMeta[k].ringColor : 'hover:border-slate-300'
            ]"
          >
            <!-- Decorative background element -->
            <div 
              class="absolute -right-2 -bottom-2 w-12 h-12 rounded-full opacity-[0.03] transition-transform duration-500 group-hover:scale-150"
              :class="stageMeta[k].bgColor"
            ></div>

            <div class="flex flex-col">
              <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1 group-hover:text-slate-500 transition-colors">
                {{ stageMeta[k].shortLabel }}
              </span>
              <div class="flex items-baseline gap-1">
                <span class="text-3xl font-medium tabular-nums tracking-tight" :class="stageMeta[k].textColor">
                  {{ summary[k] ?? 0 }}
                </span>
                <span class="text-xs font-normal text-slate-400">lot</span>
              </div>
            </div>
            
            <!-- Progress indicator at bottom -->
            <div class="absolute bottom-0 left-0 h-1 bg-slate-100 w-full overflow-hidden">
               <div 
                 class="h-full transition-all duration-1000 ease-out" 
                 :class="stageMeta[k].bgColor"
                 :style="{ width: filterStage === k ? '100%' : '0%' }"
               ></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Main Content Area -->
      <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden min-h-[500px]">
        <!-- Modern Header Table -->
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/30 flex flex-wrap items-center justify-between gap-6">
          <div class="space-y-1">
            <h2 class="text-xl font-medium text-slate-900 tracking-tight">Daftar Pantauan Produksi</h2>
            <div class="flex items-center gap-3 text-sm text-slate-500 font-light">
              <span class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                {{ filteredPOs.length }} Purchase Orders
              </span>
              <span class="text-slate-300">•</span>
              <span>{{ filtered.length }} Model Aktif</span>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative group">
              <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
              </svg>
              <input 
                v-model="search" 
                type="text" 
                placeholder="Cari PO atau nama model..."
                class="pl-10 pr-10 py-2.5 text-sm font-normal border-0 bg-slate-100 rounded-2xl focus:bg-white focus:ring-2 focus:ring-amber-400 w-64 transition-all outline-none"
              />
              <button v-if="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
              </button>
            </div>

            <!-- Filter select stylized -->
            <div class="relative">
              <select 
                v-model="filterStage"
                class="appearance-none pl-4 pr-10 py-2.5 text-sm font-semibold border-0 bg-slate-100 rounded-2xl focus:ring-2 focus:ring-amber-400 outline-none cursor-pointer"
              >
                <option value="">Semua Tahap</option>
                <option v-for="s in stageOrder" :key="s" :value="s">{{ stageMeta[s].label }}</option>
                <option value="selesai">Selesai</option>
                <option value="pending">Belum Mulai</option>
              </select>
              <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredPOs.length === 0" class="py-32 flex flex-col items-center justify-center text-center">
          <div class="relative mb-6">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center animate-pulse">
              <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-bold text-slate-900">Tidak ada hasil ditemukan</h3>
          <p class="text-sm text-slate-500 mt-1 max-w-xs mx-auto">Coba sesuaikan pencarian atau filter Anda untuk mendapatkan hasil yang berbeda.</p>
        </div>

        <!-- Collaborative PO Groups -->
        <div v-else class="divide-y divide-slate-100">
          <div v-for="group in filteredPOs" :key="group.po" class="group/po">
            
            <!-- Elegant PO Header -->
            <div 
              @click="togglePo(group.po)"
              class="w-full h-16 flex items-center justify-between px-8 bg-white hover:bg-slate-50/50 cursor-pointer transition-colors"
            >
              <div class="flex items-center gap-6">
                <!-- Toggle icon -->
                <div class="w-6 h-6 rounded-full flex items-center justify-center border border-slate-200 text-slate-400 group-hover/po:border-amber-400 group-hover/po:text-amber-500 transition-all"
                  :class="isExpanded(group.po) ? 'bg-amber-50 border-amber-200 text-amber-500 rotate-180' : ''">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>

                <div class="flex flex-col">
                  <div class="flex items-center gap-2">
                    <span class="text-base font-semibold text-slate-900 tracking-tight">{{ group.po }}</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-semibold text-slate-500 uppercase tracking-widest leading-none">
                      {{ group.rows.length }} models
                    </span>
                  </div>
                </div>
              </div>

              <!-- Badges summary -->
              <div class="flex gap-2">
                <span v-for="stg in group.uniqueStages" :key="stg"
                  class="text-[10px] px-2.5 py-1 rounded-full font-semibold uppercase tracking-wider shadow-sm border border-white"
                  :class="stageMeta[stg]?.badgeClass ?? 'bg-slate-100 text-slate-500'">
                  {{ stageMeta[stg]?.shortLabel ?? stg }}
                </span>
              </div>
            </div>

            <!-- Detailed Model Cards -->
            <div v-show="isExpanded(group.po)" class="bg-slate-50/30 overflow-hidden">
              <div class="px-8 py-2">
                <div
                  v-for="(row, rIdx) in group.rows"
                  :key="row.model"
                  class="my-4 bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm hover:shadow-md hover:border-slate-300 transition-all"
                >
                  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Model Info -->
                    <div class="flex items-start gap-4 min-w-[200px]">
                      <div class="pt-1">
                        <h4 class="text-base font-semibold text-slate-800 leading-none">{{ row.model }}</h4>
                        <div class="mt-3" :class="currentStageStatusClass(row.current_stage)">
                          <span class="text-[10px] font-semibold uppercase tracking-[0.12em] flex items-center gap-1.5">
                            <span v-if="row.current_stage !== 'selesai' && row.current_stage !== 'pending'" class="relative flex h-1.5 w-1.5">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-current opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-current"></span>
                            </span>
                            {{ row.current_label }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Modern Stepper Pipeline -->
                    <div class="flex-1 max-w-2xl px-2">
                      <div class="relative flex items-center justify-between w-full h-10">
                        <!-- Continuous Background Track -->
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-[3px] bg-slate-100 rounded-full"></div>
                        
                        <!-- Dynamic Progress Fill -->
                        <div 
                          class="absolute left-0 top-1/2 -translate-y-1/2 h-[3px] bg-emerald-500 rounded-full transition-all duration-1000 ease-in-out"
                          :style="{ width: calculateProgress(row.stages) + '%' }"
                        ></div>
                        
                        <!-- Nodes -->
                        <template v-for="(s, i) in stageOrder" :key="s">
                          <div class="relative z-10 flex flex-col items-center group/node">
                            <!-- The Dot -->
                            <div 
                              class="w-5 h-5 rounded-full border-[3px] flex items-center justify-center transition-all duration-500 shadow-sm"
                              :class="stepperNodeClass(row.stages[s])"
                            >
                              <div v-if="row.stages[s] === 'done'" class="w-2.5 h-2.5 bg-white rounded-full scale-100"></div>
                              <div v-else-if="row.stages[s] === 'active'" class="w-2.5 h-2.5 bg-amber-400 rounded-full animate-pulse"></div>
                            </div>

                            <!-- Floating Label -->
                            <div class="absolute top-7 whitespace-nowrap text-center">
                              <span class="text-[9px] font-semibold uppercase tracking-widest transition-colors duration-300" :class="stepperLabelClass(row.stages[s])">
                                {{ stageMeta[s].shortLabel }}
                              </span>
                            </div>
                          </div>
                        </template>

                        <!-- Final Node (Selesai) -->
                        <div class="relative z-10 flex flex-col items-center group/node">
                           <div 
                              class="w-5 h-5 rounded-full border-[3px] flex items-center justify-center transition-all duration-500 shadow-sm"
                              :class="row.current_stage === 'selesai' ? 'bg-emerald-500 border-emerald-500' : 'bg-white border-slate-200'"
                            >
                              <svg v-if="row.current_stage === 'selesai'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                              </svg>
                            </div>
                            <div class="absolute top-7 whitespace-nowrap text-center">
                              <span class="text-[9px] font-semibold uppercase tracking-widest transition-colors duration-300" :class="row.current_stage === 'selesai' ? 'text-emerald-600' : 'text-slate-300'">
                                SELESAI
                              </span>
                            </div>
                        </div>
                      </div>
                    </div>
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

const stageOrder = ['potong', 'jahit', 'cuci', 'finishing']

const stageMeta = {
  potong:    { label: 'Proses Potong',  shortLabel: 'Potong',    textColor: 'text-amber-600',   bgColor: 'bg-amber-500',   ringColor: 'ring-amber-500',   badgeClass: 'bg-amber-100 text-amber-700' },
  jahit:     { label: 'Proses Jahit',   shortLabel: 'Jahit',     textColor: 'text-orange-600',  bgColor: 'bg-orange-500',  ringColor: 'ring-orange-500',  badgeClass: 'bg-orange-100 text-orange-700' },
  cuci:      { label: 'Proses Cuci',    shortLabel: 'Cuci',      textColor: 'text-cyan-600',    bgColor: 'bg-cyan-500',    ringColor: 'ring-cyan-500',    badgeClass: 'bg-cyan-100 text-cyan-700' },
  finishing: { label: 'Finishing',      shortLabel: 'Packing',   textColor: 'text-purple-600',  bgColor: 'bg-purple-500',  ringColor: 'ring-purple-500',  badgeClass: 'bg-purple-100 text-purple-700' },
  selesai:   { label: 'Selesai',        shortLabel: 'Selesai',   textColor: 'text-emerald-600', bgColor: 'bg-emerald-500', ringColor: 'ring-emerald-500', badgeClass: 'bg-emerald-100 text-emerald-700' },
  pending:   { label: 'Belum Mulai',    shortLabel: 'Standby',   textColor: 'text-slate-400',   bgColor: 'bg-slate-500',   ringColor: 'ring-slate-500',   badgeClass: 'bg-slate-100 text-slate-500' },
}

// Calculate progress percentage based on stages
const calculateProgress = (stages) => {
  const steps = stageOrder.length // 4 steps
  const doneCount = stageOrder.filter(s => stages[s] === 'done').length
  
  // If all 4 are done, progress is 100%
  // Each step is 1/4 (25%)
  // However, we have 4 points + 1 final point (Selesai).
  // Total of 5 points.
  // Segments: 1-2, 2-3, 3-4, 4-Selesai (4 segments)
  return (doneCount / steps) * 100
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

// Expand state — default all expanded for visibility or kept as is
const expandedPos = ref(new Set())
const togglePo = (po) => {
  const next = new Set(expandedPos.value)
  if (next.has(po)) next.delete(po)
  else next.add(po)
  expandedPos.value = next
}
const isExpanded = (po) => expandedPos.value.has(po)

// Redesigned style helpers
const stepperNodeClass = (status) => ({
  'bg-emerald-500 border-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]': status === 'done',
  'bg-white border-amber-400':                                               status === 'active',
  'bg-white border-slate-200':                                               status === 'pending',
})

const stepperLabelClass = (status) => ({
  'text-emerald-600': status === 'done',
  'text-amber-500':   status === 'active',
  'text-slate-300':   status === 'pending',
})

const currentStageStatusClass = (stage) => ({
  'text-emerald-600': stage === 'selesai',
  'text-amber-500':   stage !== 'selesai' && stage !== 'pending',
  'text-slate-400':   stage === 'pending',
})
</script>

<style scoped>
/* Smooth entering animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-in {
  animation: fadeIn 0.5s ease-out forwards;
}
</style>

