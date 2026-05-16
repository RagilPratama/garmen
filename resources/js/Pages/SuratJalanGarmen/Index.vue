<template>
    <DataTable title="Surat Jalan Garmen" :data="data" :columns="columns" basePath="/surat-jalan-garmen" @open-create="goCreate" customActions>
        <template #cell-no_surat_jalan="{ item }">
            <button @click="showDetail(item.id)" class="text-amber-600 hover:text-amber-700 font-semibold hover:underline underline-offset-2 transition font-mono">
                {{ item.no_surat_jalan }}
            </button>
        </template>

        <template #cell-tanggal="{ item }">
            <span class="text-gray-600">{{ formatDate(item.tanggal) }}</span>
        </template>

        <template #cell-items_count="{ item }">
            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">{{ item.items_count }} roll</span>
        </template>

        <template #cell-items_sum_quantity="{ item }">
            <span class="font-semibold text-gray-800">{{ formatYard(item.items_sum_quantity) }} yard</span>
        </template>

        <template #actions="{ item }">
            <button
                @click="printSuratJalan(item.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                    />
                </svg>
                Print
            </button>
            <a
                :href="`/surat-jalan-garmen/${item.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Barang
            </a>
        </template>
    </DataTable>

    <!-- Detail Modal -->
    <div v-if="detailOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="detailOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Surat Jalan Garmen</h3>
                    <p class="text-sm text-gray-500 font-mono">{{ detailData?.no_surat_jalan }}</p>
                </div>
                <button @click="detailOpen = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div v-if="detailLoading" class="text-center py-8">
                    <svg class="animate-spin w-8 h-8 text-amber-500 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                </div>

                <div v-else-if="detailData">
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">No. Surat Jalan</p>
                            <p class="text-sm font-mono font-semibold text-gray-800">{{ detailData.no_surat_jalan }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">Tanggal</p>
                            <p class="text-sm font-semibold text-gray-800">{{ formatDate(detailData.tanggal) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-1">Jumlah Item</p>
                            <p class="text-sm font-semibold text-gray-800">{{ detailData.items?.length ?? 0 }} roll</p>
                        </div>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase w-10">No</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Kode Bahan</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Harga Keluar</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, idx) in detailData.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-2 font-mono font-medium text-gray-800">{{ item.kode_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.nama_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.supplier }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ formatRupiah(item.harga_keluar) }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatRupiah(item.total_harga) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total:</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-gray-800">{{ formatYard(detailTotalQty) }} yard</td>
                                <td></td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-gray-800">{{ formatRupiah(detailTotalHarga) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button @click="detailOpen = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Tutup</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';

defineProps({ data: Object, nextSuratJalan: String });

const columns = [
    { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
    { key: 'tanggal', label: 'Tanggal' },
    { key: 'items_count', label: 'Jumlah Item' },
    { key: 'items_sum_quantity', label: 'Total Qty' },
    { key: 'keterangan', label: 'Keterangan' },
];

const detailOpen = ref(false);
const detailLoading = ref(false);
const detailData = ref(null);

const detailTotalQty = computed(() => {
    if (!detailData.value?.items) return 0;
    return detailData.value.items.reduce((sum, i) => sum + (parseFloat(i.quantity) || 0), 0);
});

const detailTotalHarga = computed(() => {
    if (!detailData.value?.items) return 0;
    return detailData.value.items.reduce((sum, i) => sum + (parseFloat(i.total_harga) || 0), 0);
});

const goCreate = () => {
    router.get('/surat-jalan-garmen/create');
};

const showDetail = async (id) => {
    detailOpen.value = true;
    detailLoading.value = true;
    detailData.value = null;

    try {
        const response = await fetch(`/surat-jalan-garmen/${id}`, {
            headers: { Accept: 'application/json' },
        });
        detailData.value = await response.json();
    } catch (error) {
        console.error('Error loading detail:', error);
    } finally {
        detailLoading.value = false;
    }
};

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-');
const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

const printSuratJalan = async (id) => {
    try {
        const response = await fetch(`/surat-jalan-garmen/${id}`, {
            headers: { Accept: 'application/json' },
        });
        const sj = await response.json();
        if (!sj || !sj.items) return;

        const { jsPDF } = await import('jspdf');
        const { default: autoTable } = await import('jspdf-autotable');

        const tanggal = sj.tanggal ? new Date(sj.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
        const today = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 15;
        let y = margin;

        doc.setFontSize(18);
        doc.setFont('helvetica', 'bold');
        doc.text('NEWGARMEN', margin, y);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(80);
        doc.text('Jl. Raya Garmen No. 1, Kota, Provinsi 00000', margin, y + 6);

        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0);
        doc.text('SURAT JALAN', pageW - margin, y, { align: 'right' });
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(80);
        doc.text('Gudang → Garmen', pageW - margin, y + 5, { align: 'right' });

        y += 15;
        doc.setDrawColor(0);
        doc.setLineWidth(0.5);
        doc.line(margin, y, pageW - margin, y);
        y += 8;

        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(0);
        const info = [
            ['Tanggal', tanggal],
            ['No. Surat Jalan', sj.no_surat_jalan],
            ['Keterangan', sj.keterangan ?? '—'],
        ];
        info.forEach(([label, val], i) => {
            doc.setTextColor(100);
            doc.text(label, margin, y + i * 5);
            doc.setTextColor(0);
            doc.setFont('helvetica', 'bold');
            doc.text(`: ${val}`, margin + 30, y + i * 5);
            doc.setFont('helvetica', 'normal');
        });
        y += info.length * 5 + 8;

        const tableRows = sj.items.map((item, i) => [i + 1, item.kode_bahan, item.nama_bahan, item.supplier, `${Number(item.quantity).toFixed(2)} ${item.satuan ?? 'yard'}`]);
        autoTable(doc, {
            startY: y,
            margin: { left: margin, right: margin },
            head: [['No', 'Kode Bahan', 'Nama Bahan', 'Supplier', 'Qty']],
            body: tableRows,
            headStyles: { fillColor: [240, 240, 240], textColor: 0, fontStyle: 'bold', fontSize: 8 },
            bodyStyles: { fontSize: 8, textColor: 30 },
            columnStyles: { 0: { halign: 'center', cellWidth: 10 }, 4: { halign: 'right', cellWidth: 30 } },
        });

        y = doc.lastAutoTable.finalY + 14;
        const ttdW = (pageW - margin * 2) / 4;
        ['Pengirim — Gudang', 'Security', 'Penerima — Garmen', 'Admin'].forEach((label, i) => {
            const x = margin + i * ttdW + ttdW / 2;
            doc.setFontSize(8);
            doc.setTextColor(80);
            doc.text(label, x, y, { align: 'center' });
            doc.setDrawColor(150);
            doc.line(x - ttdW / 2 + 4, y + 20, x + ttdW / 2 - 4, y + 20);
            doc.setTextColor(0);
            doc.text('( ______________ )', x, y + 24, { align: 'center' });
        });

        y += 32;
        doc.setFontSize(7);
        doc.setTextColor(150);
        doc.line(margin, y, pageW - margin, y);
        doc.text(`Dokumen ini dibuat secara otomatis. Newgarmen — ${today}`, margin, y + 4);

        doc.save(`SuratJalan_${sj.no_surat_jalan.replace(/[^a-zA-Z0-9-_]/g, '_')}.pdf`);
    } catch (error) {
        console.error('Error printing:', error);
    }
};
</script>
