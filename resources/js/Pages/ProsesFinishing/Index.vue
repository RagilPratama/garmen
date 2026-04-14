<template>
  <AdminLayout title="Proses Finishing & Packing">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-violet-400 via-purple-500 to-fuchsia-400"></div>

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-800">Proses Finishing & Packing</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ data?.total ?? 0 }} PO</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="searchQuery" type="text" placeholder="Cari PO / model..."
              class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent w-52 transition-all"/>
            <button v-if="searchQuery" @click="searchQuery = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
              <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-600 hover:to-purple-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Data
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. PO</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl Proses</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah Model</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Pcs</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Pcs Barang Jadi</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(group, index) in data?.data" :key="group.po"
              class="border-b border-gray-50 hover:bg-violet-50/40 transition-colors group cursor-pointer"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
              @click="openDetail(group)">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-violet-100 group-hover:text-violet-700 transition-colors">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-semibold text-violet-600">{{ group.po }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-600">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(group.tanggal_proses) }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">{{ group.jumlah_model }} model</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-full">{{ group.total_pcs.toLocaleString('id-ID') }} pcs</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full">{{ group.total_pcs_jadi.toLocaleString('id-ID') }} pcs</span>
              </td>
              <td class="px-5 py-3.5 text-center" @click.stop>
                <button @click="openDetail(group)"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Detail
                </button>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="7" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
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
        <p class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-700">{{ data.from }}–{{ data.to }}</span> dari <span class="font-semibold text-gray-700">{{ data.total }}</span> PO</p>
        <div class="flex items-center gap-1 flex-wrap justify-center">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url ? appendSearch(link.url) : '#'"
            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
            :class="link.active ? 'bg-violet-500 text-white font-semibold shadow-sm' : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200' : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true" v-html="link.label"/>
        </div>
      </div>
    </div>

    <!-- ── DETAIL MODAL ── -->
    <Modal v-model="showDetail" :title="'Detail PO: ' + (detailGroup?.po ?? '')" size="xl">
      <div v-if="detailGroup" class="space-y-4">
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
          <span><span class="font-medium text-gray-700">Tgl Proses:</span> {{ formatDate(detailGroup.tanggal_proses) }}</span>
          <span><span class="font-medium text-gray-700">Jumlah Model:</span> {{ detailGroup.jumlah_model }}</span>
          <span><span class="font-medium text-gray-700">Total Pcs:</span> {{ detailGroup.total_pcs }} pcs</span>
          <span><span class="font-medium text-gray-700">Total Barang Jadi:</span> {{ detailGroup.total_pcs_jadi }} pcs</span>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 rounded-tl-lg">Model</th>
              <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Pcs</th>
              <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Pcs Barang Jadi</th>
              <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Tgl Selesai</th>
              <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Harga / pcs</th>
              <th class="w-16 rounded-tr-lg"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="mRow in detailGroup.models" :key="mRow.id" class="hover:bg-gray-50">
              <td class="px-4 py-2.5 font-medium text-gray-700">{{ mRow.model }}</td>
              <td class="px-4 py-2.5 text-center text-gray-600">{{ mRow.pcs ?? '—' }}</td>
              <td class="px-4 py-2.5 text-center">
                <span :class="mRow.pcs_barang_jadi ? 'text-emerald-700 font-semibold' : 'text-gray-400'">{{ mRow.pcs_barang_jadi || '—' }}</span>
              </td>
              <td class="px-4 py-2.5 text-gray-500 text-xs">{{ mRow.tanggal_selesai ? formatDate(mRow.tanggal_selesai) : '—' }}</td>
              <td class="px-4 py-2.5 text-right">
                <span v-if="mRow.harga" class="text-violet-700 font-semibold text-xs">{{ formatRupiah(mRow.harga) }}</span>
                <span v-else class="text-gray-400 text-xs">—</span>
              </td>
              <td class="px-4 py-2.5 text-center">
                <button @click="openEdit(mRow)" class="text-blue-400 hover:text-blue-600 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Modal>

    <!-- ── CREATE MODAL ── -->
    <Modal v-model="showModal" title="Tambah Proses Finishing" size="lg">
      <div v-if="!poOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Belum ada data dari <strong class="ml-1">Proses Cuci</strong>. Lengkapi kolom Pcs Kembali terlebih dahulu.
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nomor PO <span class="text-red-500">*</span></label>
          <SearchableSelect v-model="selectedPo" :options="uniquePos.map(p => ({ value: p, label: p }))"
            placeholder="-- Pilih Nomor PO --" @update:modelValue="onPoChange"/>
        </div>

        <!-- All models shown simultaneously -->
        <div v-if="selectedPo && modelsForPo.length" class="rounded-lg border border-violet-100 bg-violet-50/40 overflow-hidden">
          <div class="px-3 py-2 bg-violet-100/70 border-b border-violet-100 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              <span class="text-xs font-semibold text-violet-700">Model PO {{ selectedPo }} dari cuci</span>
            </div>
            <span class="text-xs text-violet-600">{{ modelsForPo.length }} model</span>
          </div>
          <div class="divide-y divide-violet-100">
            <div v-for="m in modelsForPo" :key="m.model" class="px-3 py-2.5">
              <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-medium text-gray-700 flex-1">{{ m.model }}</span>
                <span class="text-xs font-semibold bg-white border border-violet-200 text-violet-700 px-2 py-0.5 rounded-full shrink-0">{{ m.max_pcs }} pcs</span>
                <div class="flex items-center gap-3 shrink-0">
                  <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Pcs:</label>
                    <input v-model="pcsPerModel[m.model]" type="number" min="0"
                      class="w-20 px-2 py-1.5 border border-violet-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-white text-right"
                      placeholder="0"/>
                  </div>
                  <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-400 whitespace-nowrap">Barang jadi:</label>
                    <input v-model="jadiPerModel[m.model]" type="number" min="1"
                      class="w-20 px-2 py-1.5 border border-violet-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-300 bg-white text-right text-violet-700"
                      placeholder="—"/>
                  </div>
                  <span class="text-xs text-gray-400">pcs</span>
                </div>
              </div>
            </div>
          </div>
          <div class="px-3 py-2 bg-violet-50 border-t border-violet-100 flex items-center justify-between">
            <span class="text-xs text-gray-500">Total pcs</span>
            <span class="text-sm font-bold text-violet-700">{{ totalPcs }} pcs</span>
          </div>
        </div>

        <div v-if="selectedPo">
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Proses <span class="text-red-500">*</span></label>
          <input v-model="form.tanggal_proses" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
          <p v-if="form.errors.tanggal_proses" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_proses }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button type="submit" :disabled="form.processing || !selectedPo" class="px-5 py-2.5 text-sm text-white bg-violet-500 hover:bg-violet-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Tambah Data
          </button>
        </div>
      </form>
    </Modal>

    <!-- ── EDIT MODAL ── -->
    <Modal v-model="showEditModal" title="Update Selesai Finishing" size="md">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div v-if="editRow" class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600">
          <span class="font-medium text-gray-700">Model:</span> {{ editRow.model }}
          <span class="ml-4 font-medium text-gray-700">Pcs:</span> {{ editRow.pcs }}
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
            <input v-model="editForm.tanggal_selesai" type="date" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pcs Barang Jadi</label>
            <input v-model="editForm.pcs_barang_jadi" type="number" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white" placeholder="0"/>
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga / pcs (Rp)</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
              <input v-model="editForm.harga" type="number" min="0" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white" placeholder="0"/>
            </div>
            <p v-if="editForm.harga && editRow?.pcs_barang_jadi" class="mt-1 text-xs text-violet-600">
              Total: {{ formatRupiah(editForm.harga * editRow.pcs_barang_jadi) }} ({{ editRow.pcs_barang_jadi }} pcs)
            </p>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showEditModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 text-sm text-white bg-violet-500 hover:bg-violet-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
            <svg v-if="editForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
  data:       Object,
  poOptions:  { type: Array, default: () => [] },
})

const formatDate   = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatRupiah = (v) => v != null ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v) : '—'

// Search
const searchQuery = ref(new URLSearchParams(window.location.search).get('search') ?? '')
let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/proses-finishing', { search: val || undefined }, { preserveState: true, replace: true })
  }, 350)
})
const appendSearch = (url) => {
  if (!searchQuery.value) return url
  const u = new URL(url, window.location.origin)
  u.searchParams.set('search', searchQuery.value)
  return u.pathname + u.search
}

// ── Detail modal ──
const showDetail  = ref(false)
const detailGroup = ref(null)
const openDetail  = (group) => { detailGroup.value = group; showDetail.value = true }

// ── Create modal ──
const showModal   = ref(false)
const selectedPo  = ref('')
const uniquePos   = computed(() => [...new Set(props.poOptions.map(o => o.po))])
const modelsForPo = computed(() => selectedPo.value ? props.poOptions.filter(o => o.po === selectedPo.value) : [])
const form        = useForm({ tanggal_proses: '' })
const pcsPerModel  = ref({})
const jadiPerModel = ref({})
const totalPcs    = computed(() =>
  Object.values(pcsPerModel.value).reduce((sum, v) => sum + (parseInt(v) || 0), 0)
)
const onPoChange = () => {
  pcsPerModel.value = {}; jadiPerModel.value = {}
  modelsForPo.value.forEach(m => { pcsPerModel.value[m.model] = ''; jadiPerModel.value[m.model] = '' })
}
const openCreate = () => {
  selectedPo.value = ''; pcsPerModel.value = {}; jadiPerModel.value = {}
  form.reset(); form.clearErrors(); showModal.value = true
}
const submit = () => {
  const models = modelsForPo.value.map(m => ({
    model:           m.model,
    pcs:             parseInt(pcsPerModel.value[m.model]) || 0,
    pcs_barang_jadi: parseInt(jadiPerModel.value[m.model]) || null,
  }))
  router.post('/proses-finishing', {
    po:             selectedPo.value,
    tanggal_proses: form.tanggal_proses,
    models,
  }, {
    onSuccess: () => { showModal.value = false; form.reset(); selectedPo.value = ''; pcsPerModel.value = {}; jadiPerModel.value = {} },
    onError:   (e) => { form.setError(e) },
  })
}

// ── Edit modal ──
const showEditModal = ref(false)
const editRow       = ref(null)
const editId        = ref(null)
const editForm      = useForm({ tanggal_selesai: '', pcs_barang_jadi: '', harga: '' })
const openEdit = (mRow) => {
  editRow.value  = mRow
  editId.value   = mRow.id
  editForm.tanggal_selesai  = mRow.tanggal_selesai?.substring?.(0, 10) ?? ''
  editForm.pcs_barang_jadi  = mRow.pcs_barang_jadi ?? ''
  editForm.harga            = mRow.harga ?? ''
  editForm.clearErrors()
  showEditModal.value = true
}
const submitEdit = () => {
  editForm.put(`/proses-finishing/${editId.value}`, {
    onSuccess: () => { showEditModal.value = false; showDetail.value = false },
  })
}
</script>
