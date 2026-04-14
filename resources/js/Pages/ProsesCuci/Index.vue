<template>
  <AdminLayout title="Proses Cuci">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-cyan-400 via-sky-500 to-blue-400"></div>

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-cyan-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3"/></svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-800">Proses Cuci</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ data?.total ?? 0 }} PO</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="searchQuery" type="text" placeholder="Cari PO / model..."
              class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent w-52 transition-all"/>
            <button v-if="searchQuery" @click="searchQuery = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
              <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-500 to-sky-500 hover:from-cyan-600 hover:to-sky-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap">
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
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl Kirim Cuci</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Surat Jalan</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah Model</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Pcs Kirim</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(group, index) in data?.data" :key="group.po"
              class="border-b border-gray-50 hover:bg-cyan-50/40 transition-colors group cursor-pointer"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
              @click="openDetail(group)">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-cyan-100 group-hover:text-cyan-700 transition-colors">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-semibold text-cyan-600">{{ group.po }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-600">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(group.tanggal_kirim_cuci) }}
                </span>
              </td>
              <td class="px-5 py-3.5" @click.stop>
                <button v-if="group.no_surat_jalan" @click="printSuratJalan(group)"
                  class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline underline-offset-2 transition">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                  {{ group.no_surat_jalan }}
                </button>
                <span v-else class="text-gray-400 text-xs">—</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">{{ group.jumlah_model }} model</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-cyan-50 text-cyan-700 text-xs font-semibold rounded-full">{{ group.total_pcs_kirim.toLocaleString('id-ID') }} pcs</span>
              </td>
              <td class="px-5 py-3.5 text-center" @click.stop>
                <button @click="openDetail(group)"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-cyan-600 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Detail
                </button>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="7" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3"/></svg>
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
            :class="link.active ? 'bg-cyan-500 text-white font-semibold shadow-sm' : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200' : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true" v-html="link.label"/>
        </div>
      </div>
    </div>

    <!-- ── DETAIL MODAL ── -->
    <Modal v-model="showDetail" :title="'Detail PO: ' + (detailGroup?.po ?? '')" size="xl">
      <div v-if="detailGroup" class="space-y-4">
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
          <span><span class="font-medium text-gray-700">Tgl Kirim:</span> {{ formatDate(detailGroup.tanggal_kirim_cuci) }}</span>
          <span>
            <span class="font-medium text-gray-700">No. SJ:</span>
            <button v-if="detailGroup.no_surat_jalan" @click="printSuratJalan(detailGroup)"
              class="ml-1 text-blue-600 hover:underline font-semibold">{{ detailGroup.no_surat_jalan }}</button>
            <span v-else class="ml-1 text-gray-400">—</span>
          </span>
          <span><span class="font-medium text-gray-700">Jumlah Model:</span> {{ detailGroup.jumlah_model }}</span>
          <span><span class="font-medium text-gray-700">Total Pcs Kirim:</span> {{ detailGroup.total_pcs_kirim }} pcs</span>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 rounded-tl-lg">Model</th>
              <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Pcs Kirim</th>
              <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Pcs Kembali</th>
              <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Tgl Kembali</th>
              <th class="w-16 rounded-tr-lg"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="mRow in detailGroup.models" :key="mRow.id" class="hover:bg-gray-50">
              <td class="px-4 py-2.5 font-medium text-gray-700">{{ mRow.model }}</td>
              <td class="px-4 py-2.5 text-center text-gray-600">{{ mRow.pcs_kirim ?? '—' }}</td>
              <td class="px-4 py-2.5 text-center">
                <span :class="mRow.pcs_kembali ? 'text-emerald-700 font-semibold' : 'text-gray-400'">
                  {{ mRow.pcs_kembali || '—' }}
                </span>
              </td>
              <td class="px-4 py-2.5 text-gray-500 text-xs">{{ mRow.tanggal_kembali_dari_cuci ? formatDate(mRow.tanggal_kembali_dari_cuci) : '—' }}</td>
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
    <Modal v-model="showModal" title="Tambah Proses Cuci" size="lg">
      <div v-if="!poOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Belum ada data dari <strong class="ml-1">Proses Jahit</strong>. Lengkapi kolom Pcs Hasil Jahit terlebih dahulu.
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nomor PO <span class="text-red-500">*</span></label>
          <SearchableSelect v-model="selectedPo" :options="uniquePos.map(p => ({ value: p, label: p }))"
            placeholder="-- Pilih Nomor PO --" @update:modelValue="onPoChange"/>
        </div>

        <!-- All models shown simultaneously -->
        <div v-if="selectedPo && modelsForPo.length" class="rounded-lg border border-cyan-100 bg-cyan-50/40 overflow-hidden">
          <div class="px-3 py-2 bg-cyan-100/70 border-b border-cyan-100 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              <span class="text-xs font-semibold text-cyan-700">Model hasil jahit PO {{ selectedPo }}</span>
            </div>
            <span class="text-xs text-cyan-600">{{ modelsForPo.length }} model</span>
          </div>
          <div class="divide-y divide-cyan-100">
            <div v-for="m in modelsForPo" :key="m.model" class="px-3 py-2.5">
              <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-medium text-gray-700 flex-1">{{ m.model }}</span>
                <span class="text-xs font-semibold bg-white border border-cyan-200 text-cyan-700 px-2 py-0.5 rounded-full shrink-0">{{ m.max_pcs }} pcs</span>
                <div class="flex items-center gap-1.5 shrink-0">
                  <label class="text-xs text-gray-500">Pcs kirim:</label>
                  <input v-model="pcsPerModel[m.model]" type="number" min="0"
                    class="w-24 px-2.5 py-1.5 border border-cyan-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 bg-white text-right"
                    placeholder="0"/>
                  <span class="text-xs text-gray-400">pcs</span>
                </div>
              </div>
            </div>
          </div>
          <div class="px-3 py-2 bg-cyan-50 border-t border-cyan-100 flex items-center justify-between">
            <span class="text-xs text-gray-500">Total pcs kirim</span>
            <span class="text-sm font-bold text-cyan-700">{{ totalPcsKirim }} pcs</span>
          </div>
        </div>

        <div v-if="selectedPo" class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kirim Cuci <span class="text-red-500">*</span></label>
            <input v-model="form.tanggal_kirim_cuci" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition bg-white"/>
            <p v-if="form.errors.tanggal_kirim_cuci" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_kirim_cuci }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
            <div class="relative">
              <input v-model="form.no_surat_jalan" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition bg-white pr-14" placeholder="No. Surat Jalan"/>
              <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-cyan-100 text-cyan-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button type="submit" :disabled="form.processing || !selectedPo" class="px-5 py-2.5 text-sm text-white bg-cyan-500 hover:bg-cyan-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Tambah Data
          </button>
        </div>
      </form>
    </Modal>

    <!-- ── EDIT MODAL ── -->
    <Modal v-model="showEditModal" title="Update Kembali Cuci" size="md">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div v-if="editRow" class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600">
          <span class="font-medium text-gray-700">Model:</span> {{ editRow.model }}
          <span class="ml-4 font-medium text-gray-700">Pcs Kirim:</span> {{ editRow.pcs_kirim }}
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tgl Kembali dari Cuci</label>
            <input v-model="editForm.tanggal_kembali_dari_cuci" type="date" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition bg-white"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pcs Kembali</label>
            <input v-model="editForm.pcs_kembali" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition bg-white" placeholder="0"/>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showEditModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 text-sm text-white bg-cyan-500 hover:bg-cyan-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
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
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
  data:           Object,
  poOptions:      { type: Array, default: () => [] },
  nextSuratJalan: { type: String, default: '' },
})

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatDateLong = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—'

// Search
const searchQuery = ref(new URLSearchParams(window.location.search).get('search') ?? '')
let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/proses-cuci', { search: val || undefined }, { preserveState: true, replace: true })
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
const form        = useForm({ tanggal_kirim_cuci: '', no_surat_jalan: '' })
const pcsPerModel = ref({})
const totalPcsKirim = computed(() =>
  Object.values(pcsPerModel.value).reduce((sum, v) => sum + (parseInt(v) || 0), 0)
)
const onPoChange = () => {
  pcsPerModel.value = {}
  modelsForPo.value.forEach(m => { pcsPerModel.value[m.model] = '' })
}
const openCreate = () => {
  selectedPo.value = ''; pcsPerModel.value = {}
  form.reset(); form.no_surat_jalan = props.nextSuratJalan; form.clearErrors(); showModal.value = true
}
const submit = () => {
  const models = modelsForPo.value.map(m => ({
    model: m.model,
    pcs_kirim: parseInt(pcsPerModel.value[m.model]) || 0,
  }))
  router.post('/proses-cuci', {
    tanggal_kirim_cuci: form.tanggal_kirim_cuci,
    no_surat_jalan:     form.no_surat_jalan,
    po:                 selectedPo.value,
    models,
  }, {
    onSuccess: () => { showModal.value = false; form.reset(); selectedPo.value = ''; pcsPerModel.value = {} },
    onError:   (e) => { form.setError(e) },
  })
}

// ── Edit modal ──
const showEditModal = ref(false)
const editRow       = ref(null)
const editId        = ref(null)
const editForm      = useForm({ tanggal_kembali_dari_cuci: '', pcs_kembali: '' })
const openEdit = (mRow) => {
  editRow.value  = mRow
  editId.value   = mRow.id
  editForm.tanggal_kembali_dari_cuci = mRow.tanggal_kembali_dari_cuci?.substring?.(0, 10) ?? ''
  editForm.pcs_kembali               = mRow.pcs_kembali ?? ''
  editForm.clearErrors()
  showEditModal.value = true
}
const submitEdit = () => {
  editForm.put(`/proses-cuci/${editId.value}`, {
    onSuccess: () => { showEditModal.value = false; showDetail.value = false },
  })
}

// ── Surat Jalan PDF ──
const printSuratJalan = (group) => {
  const today = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
  const tanggal = formatDateLong(group.tanggal_kirim_cuci)

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
  const pageW = doc.internal.pageSize.getWidth()
  const margin = 15
  let y = margin

  // KOP SURAT
  doc.setFontSize(18)
  doc.setFont('helvetica', 'bold')
  doc.text('NEWGARMEN', margin, y)
  doc.setFontSize(8)
  doc.setFont('helvetica', 'normal')
  doc.setTextColor(80)
  doc.text('Jl. Raya Garmen No. 1, Kota, Provinsi 00000', margin, y + 6)
  doc.text('Telp: (021) 000-0000  |  Email: info@newgarmen.com', margin, y + 10)

  doc.setFontSize(14)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(0)
  doc.text('SURAT JALAN CUCI', pageW - margin, y, { align: 'right' })

  y += 15
  doc.setDrawColor(0)
  doc.setLineWidth(0.5)
  doc.line(margin, y, pageW - margin, y)
  y += 6

  // INFO GRID
  const col2 = pageW / 2 + 2
  doc.setFontSize(7)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(120)
  doc.text('INFORMASI PENGIRIMAN', margin, y)
  doc.text('TUJUAN', col2, y)
  y += 4

  doc.setFont('helvetica', 'normal')
  doc.setTextColor(0)
  doc.setFontSize(9)

  const infoLeft = [
    ['Tanggal Kirim', tanggal],
    ['No. Surat Jalan', group.no_surat_jalan ?? '—'],
    ['No. PO', group.po ?? '—'],
  ]
  const infoRight = [
    ['Dari', 'Gudang Newgarmen'],
    ['Ke', 'Tempat Pencucian'],
  ]

  infoLeft.forEach(([label, val], i) => {
    doc.setTextColor(100); doc.text(`${label}`, margin, y + i * 5)
    doc.setTextColor(0); doc.setFont('helvetica', 'bold')
    doc.text(`: ${val}`, margin + 33, y + i * 5)
    doc.setFont('helvetica', 'normal')
  })
  infoRight.forEach(([label, val], i) => {
    doc.setTextColor(100); doc.text(`${label}`, col2, y + i * 5)
    doc.setTextColor(0); doc.setFont('helvetica', 'bold')
    doc.text(`: ${val}`, col2 + 18, y + i * 5)
    doc.setFont('helvetica', 'normal')
  })

  y += infoLeft.length * 5 + 8

  // TABLE
  const tableRows = (group.models ?? []).map((m, i) => [
    i + 1,
    m.model ?? '—',
    Number(m.pcs_kirim ?? 0).toLocaleString('id-ID'),
  ])

  autoTable(doc, {
    startY: y,
    margin: { left: margin, right: margin },
    head: [['No', 'Model', 'Pcs Kirim']],
    body: tableRows,
    headStyles: { fillColor: [240, 240, 240], textColor: 0, fontStyle: 'bold', fontSize: 8 },
    bodyStyles: { fontSize: 8, textColor: 30 },
    columnStyles: {
      0: { halign: 'center', cellWidth: 10 },
      2: { halign: 'right', cellWidth: 30 },
    },
  })

  y = doc.lastAutoTable.finalY + 14

  // TANDA TANGAN
  const ttdW = (pageW - margin * 2) / 3
  const ttdLabels = ['Pengirim — Gudang', 'Sopir / Kurir', 'Penerima — Cuci']
  ttdLabels.forEach((label, i) => {
    const x = margin + i * ttdW + ttdW / 2
    doc.setFontSize(8); doc.setTextColor(80)
    doc.text(label, x, y, { align: 'center' })
    doc.setDrawColor(150)
    doc.line(x - ttdW / 2 + 4, y + 20, x + ttdW / 2 - 4, y + 20)
    doc.setTextColor(0)
    doc.text('( _________________ )', x, y + 24, { align: 'center' })
  })

  y += 32
  doc.setFontSize(7); doc.setTextColor(150); doc.setDrawColor(200)
  doc.line(margin, y, pageW - margin, y)
  doc.text(`Dokumen ini dibuat secara otomatis. Newgarmen — ${today}`, margin, y + 4)

  const filename = `SuratJalanCuci_${(group.no_surat_jalan ?? group.po).replace(/[^a-zA-Z0-9-_]/g, '_')}.pdf`
  doc.save(filename)
}
</script>
