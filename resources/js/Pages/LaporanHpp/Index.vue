<template>
  <AdminLayout title="Laporan HPP">
    <div class="space-y-6">
      <!-- Header & Dashboard -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">Laporan HPP</h2>
          <p class="text-sm text-gray-500">Analisa Harga Pokok Produksi per PO (Bahan + Ongkos)</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative">
                <input 
                    v-model="search" 
                    type="text" 
                    placeholder="Cari PO atau Model..." 
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64 text-sm"
                />
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button @click="exportPdf" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl flex items-center gap-2 text-sm font-semibold transition-all shadow-sm shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export PDF
            </button>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rata-rata HPP</p>
                <p class="text-xl font-bold text-gray-800">{{ formatRupiah(avgHpp) }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total PO Dianalisa</p>
                <p class="text-xl font-bold text-gray-800">{{ data.length }} PO</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Investasi Produksi</p>
                <p class="text-xl font-bold text-gray-800">{{ formatRupiah(totalInvestasi) }}</p>
            </div>
        </div>
      </div>

      <!-- Main Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50/50">
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">PO & Model</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Rincian Bahan</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Rincian Produksi</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">PCS Jadi</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right bg-indigo-50/30 text-indigo-700 font-mono">HPP / PCS</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="item in filteredData" :key="item.po" class="hover:bg-gray-50/50 transition-colors">
              <td class="px-6 py-4">
                <p class="font-bold text-gray-900 border-l-4 border-indigo-500 pl-2 mb-0.5">{{ item.po }}</p>
                <p class="text-xs text-gray-500 pl-3 italic">{{ item.model }}</p>
              </td>
              <td class="px-6 py-4">
                <div class="space-y-1">
                  <p class="text-xs text-gray-700 flex justify-between gap-4">
                    <span>{{ item.kode_bahan }} ({{ item.yard_pakai }} Yrd)</span>
                    <span class="font-medium">@ {{ formatRupiah(item.rp_per_yard) }}</span>
                  </p>
                  <p class="text-xs font-bold text-gray-900 text-right border-t border-gray-100 pt-1">
                    {{ formatRupiah(item.total_biaya_bahan) }}
                  </p>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="space-y-1">
                  <p class="text-xs text-gray-700 flex justify-between gap-4">
                    <span>Ongkos Jahit+Cuci+Fin</span>
                    <span class="font-medium">@ {{ formatRupiah(item.ongkos_produksi_satuan) }}</span>
                  </p>
                  <p class="text-xs font-bold text-gray-900 text-right border-t border-gray-100 pt-1">
                    {{ formatRupiah(item.total_ongkos_produksi) }}
                  </p>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                  {{ item.pcs_jadi }} Pcs
                </span>
              </td>
              <td class="px-6 py-4 text-right bg-indigo-50/30 group">
                <div class="text-xs text-gray-400 mb-0.5 opacity-0 group-hover:opacity-100 transition-opacity">Total: {{ formatRupiah(item.total_modal_po) }}</div>
                <div class="text-lg font-black text-indigo-600 font-mono">
                  {{ formatRupiah(item.hpp_satuan) }}
                </div>
              </td>
            </tr>
            <tr v-if="filteredData.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                Data tidak ditemukan...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

const props = defineProps({
  data: Array,
  filters: Object
});

const search = ref(props.filters.search || '');

// Client-side debounce
let timeout;
watch(search, (val) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.visit(route('laporan-hpp.index'), {
            data: { search: val },
            preserveState: true,
            replace: true
        });
    }, 500);
});

const filteredData = computed(() => props.data);

const avgHpp = computed(() => {
    if (props.data.length === 0) return 0;
    const itemsWithHpp = props.data.filter(i => i.hpp_satuan > 0);
    if (itemsWithHpp.length === 0) return 0;
    return itemsWithHpp.reduce((sum, item) => sum + item.hpp_satuan, 0) / itemsWithHpp.length;
});

const totalInvestasi = computed(() => {
    return props.data.reduce((sum, item) => sum + item.total_modal_po, 0);
});

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(val || 0);
};

const formatRupiahClean = (val) => {
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(val || 0);
};

const exportPdf = () => {
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    
    // Header
    doc.setFillColor(79, 70, 229);
    doc.rect(0, 0, 297, 25, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(18);
    doc.setFont('helvetica', 'bold');
    doc.text('LAPORAN HARGA POKOK PRODUKSI (HPP)', 15, 12);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text(`Dicetak pada: ${new Date().toLocaleString('id-ID')}`, 15, 18);
    
    // Summary line
    doc.setTextColor(40, 40, 40);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text(`Rata-rata HPP: ${formatRupiah(avgHpp.value)}`, 15, 35);
    doc.text(`Total Produksi: ${props.data.length} PO`, 100, 35);
    doc.text(`Total Modal: ${formatRupiah(totalInvestasi.value)}`, 180, 35);
    
    const tableData = props.data.map((item, idx) => [
        idx + 1,
        `${item.po}\n(${item.model})`,
        `${item.kode_bahan}\n${item.yard_pakai} Yrd @ ${formatRupiahClean(item.rp_per_yard)}`,
        `${formatRupiahClean(item.total_biaya_bahan)}`,
        `${item.pcs_jadi} Pcs\n@ ${formatRupiahClean(item.ongkos_produksi_satuan)}`,
        `${formatRupiahClean(item.total_ongkos_produksi)}`,
        `${formatRupiahClean(item.total_modal_po)}`,
        `${formatRupiahClean(item.hpp_satuan)}`,
    ]);

    autoTable(doc, {
        startY: 40,
        head: [['#', 'PO / Model', 'Rincian Bahan', 'Subtotal Bahan', 'Rincian Prod', 'Subtotal Prod', 'Total Modal', 'HPP / PCS']],
        body: tableData,
        theme: 'grid',
        headStyles: { fillColor: [79, 70, 229], halign: 'center' },
        columnStyles: {
            0: { halign: 'center', cellWidth: 10 },
            3: { halign: 'right' },
            5: { halign: 'right' },
            6: { halign: 'right', fontStyle: 'bold' },
            7: { halign: 'right', fontStyle: 'bold', fillColor: [243, 244, 255] },
        },
        styles: { fontSize: 8, cellPadding: 3 },
    });

    const safeSearch = search.value ? `-${search.value.replace(/[^a-z0-9]/gi, '_').toLowerCase()}` : '';
    doc.save(`laporan-hpp${safeSearch}.pdf`);
};

</script>

<style scoped>
.font-mono {
    font-family: 'Courier New', Courier, monospace;
}
</style>
