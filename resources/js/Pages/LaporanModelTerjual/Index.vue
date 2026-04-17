<template>
  <AdminLayout title="Laporan Model Terjual">
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <!-- Title -->
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-gray-800">Laporan Model Terjual</h2>
              <p class="text-xs text-gray-500">Rekap jumlah pcs per model dari Toko & Gudang</p>
            </div>
          </div>

          <!-- Controls -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative group">
              <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input v-model="searchQuery" type="text" placeholder="Cari model..."
                class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl w-44 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"/>
            </div>

            <!-- Date range -->
            <input v-model="startDate" type="date"
              class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"
              title="Dari tanggal"/>
            <span class="text-gray-400 text-sm">—</span>
            <input v-model="endDate" type="date"
              class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"
              title="Sampai tanggal"/>

            <!-- Export PDF -->
            <button @click="exportPdf" :disabled="isExporting"
              class="flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
              <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              {{ isExporting ? 'Membuat PDF...' : 'Export PDF' }}
            </button>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-y sm:divide-y-0 divide-gray-100 border-b border-gray-100">
          <div class="px-5 py-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Model</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ summary.total_model }}</p>
            <p class="text-xs text-gray-400 mt-0.5">jenis model</p>
          </div>
          <div class="px-5 py-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Terjual</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ summary.total_pcs.toLocaleString('id-ID') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">pcs gabungan</p>
          </div>
          <div class="px-5 py-4">
            <p class="text-xs text-amber-600 uppercase tracking-wide font-medium">Via Gudang</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ summary.total_pcs_gudang.toLocaleString('id-ID') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">pcs</p>
          </div>
          <div class="px-5 py-4">
            <p class="text-xs text-indigo-600 uppercase tracking-wide font-medium">Via Toko</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ summary.total_pcs_toko.toLocaleString('id-ID') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">pcs</p>
          </div>
          <div class="px-5 py-4">
            <p class="text-xs text-emerald-600 uppercase tracking-wide font-medium">Total Omset</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">{{ formatRupiah(summary.total_omset) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">gabungan</p>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-10">No</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Model</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-amber-600 uppercase tracking-wide">Pcs Gudang</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-indigo-600 uppercase tracking-wide">Pcs Toko</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-700 uppercase tracking-wide">Total Pcs</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Omset Gudang</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Omset Toko</th>
                <th class="text-right px-5 py-3.5 text-xs font-semibold text-emerald-600 uppercase tracking-wide">Total Omset</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filtered.length === 0">
                <td colspan="8" class="px-5 py-16 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                      <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                      </svg>
                    </div>
                    <p class="text-sm text-gray-400">Belum ada data penjualan</p>
                  </div>
                </td>
              </tr>
              <tr v-for="(row, idx) in filtered" :key="row.model"
                class="border-b border-gray-50 hover:bg-indigo-50/30 transition-colors"
                :class="idx % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">
                <td class="px-5 py-3.5">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500">{{ idx + 1 }}</span>
                </td>
                <td class="px-5 py-3.5 font-semibold text-gray-800">{{ row.model }}</td>
                <td class="px-5 py-3.5 text-right">
                  <span class="text-amber-600 font-medium">{{ row.pcs_gudang > 0 ? row.pcs_gudang.toLocaleString('id-ID') : '—' }}</span>
                </td>
                <td class="px-5 py-3.5 text-right">
                  <span class="text-indigo-600 font-medium">{{ row.pcs_toko > 0 ? row.pcs_toko.toLocaleString('id-ID') : '—' }}</span>
                </td>
                <td class="px-5 py-3.5 text-right">
                  <span class="inline-flex items-center justify-end gap-1">
                    <span class="font-bold text-gray-800 text-base">{{ row.total_pcs.toLocaleString('id-ID') }}</span>
                    <span class="text-xs text-gray-400">pcs</span>
                  </span>
                </td>
                <td class="px-5 py-3.5 text-right text-gray-600 text-xs">{{ row.omset_gudang > 0 ? formatRupiah(row.omset_gudang) : '—' }}</td>
                <td class="px-5 py-3.5 text-right text-gray-600 text-xs">{{ row.omset_toko > 0 ? formatRupiah(row.omset_toko) : '—' }}</td>
                <td class="px-5 py-3.5 text-right font-semibold text-emerald-600">{{ formatRupiah(row.total_omset) }}</td>
              </tr>
            </tbody>
            <!-- Footer total -->
            <tfoot v-if="filtered.length > 0">
              <tr class="bg-indigo-50 border-t-2 border-indigo-100 font-semibold text-sm">
                <td class="px-5 py-3.5 text-indigo-600 font-bold" colspan="2">TOTAL {{ filtered.length }} Model</td>
                <td class="px-5 py-3.5 text-right text-amber-600">{{ filtered.reduce((s,r) => s + r.pcs_gudang, 0).toLocaleString('id-ID') }}</td>
                <td class="px-5 py-3.5 text-right text-indigo-600">{{ filtered.reduce((s,r) => s + r.pcs_toko, 0).toLocaleString('id-ID') }}</td>
                <td class="px-5 py-3.5 text-right text-gray-800 font-bold">{{ filtered.reduce((s,r) => s + r.total_pcs, 0).toLocaleString('id-ID') }} <span class="text-xs font-normal text-gray-400">pcs</span></td>
                <td class="px-5 py-3.5 text-right text-gray-600 text-xs">{{ formatRupiah(filtered.reduce((s,r) => s + r.omset_gudang, 0)) }}</td>
                <td class="px-5 py-3.5 text-right text-gray-600 text-xs">{{ formatRupiah(filtered.reduce((s,r) => s + r.omset_toko, 0)) }}</td>
                <td class="px-5 py-3.5 text-right text-emerald-600 font-bold">{{ formatRupiah(filtered.reduce((s,r) => s + r.total_omset, 0)) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

const props = defineProps({
  data:    { type: Array,  default: () => [] },
  summary: { type: Object, default: () => ({ total_model: 0, total_pcs: 0, total_pcs_gudang: 0, total_pcs_toko: 0, total_omset: 0 }) },
})

const searchQuery = ref(new URLSearchParams(window.location.search).get('search') || '')
const startDate   = ref(new URLSearchParams(window.location.search).get('start_date') || '')
const endDate     = ref(new URLSearchParams(window.location.search).get('end_date') || '')
const isExporting = ref(false)

// Client-side filter on top of server-side (instant UX)
const filtered = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  return q ? props.data.filter(r => r.model?.toLowerCase().includes(q)) : props.data
})

// Debounced server reload
let timer = null
const pushFilters = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get('/laporan-model-terjual', {
      search:     searchQuery.value || undefined,
      start_date: startDate.value   || undefined,
      end_date:   endDate.value     || undefined,
    }, { preserveState: true, replace: true })
  }, 400)
}
watch(searchQuery, pushFilters)
watch(startDate,   pushFilters)
watch(endDate,     pushFilters)

const formatRupiah = (val) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)

const formatRupiahRaw = (val) =>
  new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(val || 0)

const buildPeriodeLabel = () => {
  const fmtDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : ''
  if (startDate.value && endDate.value) return `Periode: ${fmtDate(startDate.value)} s/d ${fmtDate(endDate.value)}`
  if (startDate.value) return `Dari: ${fmtDate(startDate.value)}`
  if (endDate.value)   return `Sampai: ${fmtDate(endDate.value)}`
  return 'Semua Periode'
}

// ─── PDF Export ─────────────────────────────────────────────────────────────
const exportPdf = () => {
  if (!filtered.value.length) return
  isExporting.value = true

  try {
    const rows = filtered.value
    const doc  = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })

    // A4 landscape usable: 297 - 14 - 14 = 269mm
    // Cols: No=10, Model=70, PcsGudang=30, PcsToko=30, TotalPcs=30, OmsetGudang=36, OmsetToko=36, TotalOmset=37 → 279? No:
    // 10+70+30+30+30+33+33+33 = 269 ✓
    const COL = [10, 70, 30, 30, 30, 33, 33, 33]

    const pageW = doc.internal.pageSize.getWidth()

    // Header
    doc.setFillColor(79, 70, 229)   // indigo-600
    doc.rect(0, 0, pageW, 22, 'F')
    doc.setTextColor(255, 255, 255)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(14)
    doc.text('LAPORAN MODEL TERJUAL', 14, 10)
    doc.setFontSize(8)
    doc.setFont('helvetica', 'normal')
    const periodeLabel = buildPeriodeLabel()
    doc.text(periodeLabel, 14, 16)
    doc.text(`Dicetak: ${new Date().toLocaleString('id-ID')}`, pageW - 14, 16, { align: 'right' })

    // Summary cards
    const summaryY = 26
    const cardW    = (pageW - 28) / 4
    const totTagihan = rows.reduce((s, r) => s + r.total_omset,  0)
    const totPcs     = rows.reduce((s, r) => s + r.total_pcs,    0)
    const totGudang  = rows.reduce((s, r) => s + r.pcs_gudang,   0)
    const totToko    = rows.reduce((s, r) => s + r.pcs_toko,     0)
    ;[
      { label: 'Total Model',    value: `${rows.length} model`,                    color: [224, 231, 255] },
      { label: 'Total Penjualan',value: `${totPcs.toLocaleString('id-ID')} pcs`,   color: [224, 231, 255] },
      { label: 'Via Gudang',     value: `${totGudang.toLocaleString('id-ID')} pcs`,color: [254, 243, 199] },
      { label: 'Via Toko',       value: `${totToko.toLocaleString('id-ID')} pcs`,  color: [224, 231, 255] },
    ].forEach((card, i) => {
      const x = 14 + i * cardW
      doc.setFillColor(...card.color)
      doc.roundedRect(x, summaryY, cardW - 2, 14, 2, 2, 'F')
      doc.setTextColor(80, 80, 80)
      doc.setFont('helvetica', 'bold')
      doc.setFontSize(7)
      doc.text(card.label.toUpperCase(), x + 3, summaryY + 5)
      doc.setFontSize(8)
      doc.setTextColor(30, 30, 30)
      doc.text(card.value, x + 3, summaryY + 11)
    })

    // Footer row
    const totalTagihan = rows.reduce((s, r) => s + r.total_omset, 0)
    const footRow = [
      '',
      'TOTAL',
      totGudang.toLocaleString('id-ID'),
      totToko.toLocaleString('id-ID'),
      totPcs.toLocaleString('id-ID'),
      `Rp ${formatRupiahRaw(rows.reduce((s,r) => s + r.omset_gudang, 0))}`,
      `Rp ${formatRupiahRaw(rows.reduce((s,r) => s + r.omset_toko,   0))}`,
      `Rp ${formatRupiahRaw(totalTagihan)}`,
    ]

    autoTable(doc, {
      startY: summaryY + 18,
      head: [['No', 'Model', 'Pcs Gudang', 'Pcs Toko', 'Total Pcs', 'Omset Gudang', 'Omset Toko', 'Total Omset']],
      body: rows.map((row, idx) => [
        idx + 1,
        row.model,
        row.pcs_gudang > 0 ? row.pcs_gudang.toLocaleString('id-ID') : '—',
        row.pcs_toko   > 0 ? row.pcs_toko.toLocaleString('id-ID')   : '—',
        row.total_pcs.toLocaleString('id-ID'),
        row.omset_gudang > 0 ? `Rp ${formatRupiahRaw(row.omset_gudang)}` : '—',
        row.omset_toko   > 0 ? `Rp ${formatRupiahRaw(row.omset_toko)}`   : '—',
        `Rp ${formatRupiahRaw(row.total_omset)}`,
      ]),
      foot: [footRow],
      showFoot: 'lastPage',
      styles: {
        fontSize: 8,
        cellPadding: { top: 2.5, bottom: 2.5, left: 2.5, right: 2.5 },
        overflow: 'linebreak',
        valign: 'middle',
      },
      headStyles: {
        fillColor: [79, 70, 229],
        textColor: 255,
        fontStyle: 'bold',
        fontSize: 8,
        halign: 'left',
        cellPadding: { top: 3, bottom: 3, left: 2.5, right: 2.5 },
      },
      footStyles: {
        fillColor: [224, 231, 255],
        textColor: [30, 30, 30],
        fontStyle: 'bold',
        fontSize: 8,
      },
      columnStyles: {
        0: { halign: 'left', cellWidth: COL[0] },
        1: { halign: 'left', cellWidth: COL[1] },
        2: { halign: 'left', cellWidth: COL[2] },
        3: { halign: 'left', cellWidth: COL[3] },
        4: { halign: 'left', cellWidth: COL[4] },
        5: { halign: 'left', cellWidth: COL[5] },
        6: { halign: 'left', cellWidth: COL[6] },
        7: { halign: 'left', cellWidth: COL[7] },
      },
      alternateRowStyles: { fillColor: [238, 242, 255] },
      didParseCell: (data) => {
        const col = data.column.index
        const sec = data.section
        // Footer label
        if (sec === 'foot' && col === 1) {
          data.cell.styles.textColor = [79, 70, 229]
          data.cell.styles.fontStyle = 'bold'
        }
        // Footer total omset — emerald
        if (sec === 'foot' && col === 7) {
          data.cell.styles.textColor = [5, 150, 105]
        }
        // Body: total pcs column bold
        if (sec === 'body' && col === 4) {
          data.cell.styles.fontStyle = 'bold'
        }
        // Body: total omset — emerald
        if (sec === 'body' && col === 7) {
          data.cell.styles.textColor = [5, 150, 105]
          data.cell.styles.fontStyle = 'bold'
        }
      },
      didDrawPage: (data) => {
        const pW = doc.internal.pageSize.getWidth()
        const pH = doc.internal.pageSize.getHeight()
        doc.setFontSize(7)
        doc.setTextColor(150)
        doc.text(`Halaman ${data.pageNumber}`, pW / 2, pH - 5, { align: 'center' })
      },
      margin: { top: summaryY + 18, left: 14, right: 14 },
    })

    const periodeSuffix = periodeLabel.replace(/[^\d-]/g, '').replace(/-+$/g, '')
    doc.save(`laporan-model-terjual${periodeSuffix ? '-' + periodeSuffix : ''}.pdf`)
  } finally {
    isExporting.value = false
  }
}
</script>
