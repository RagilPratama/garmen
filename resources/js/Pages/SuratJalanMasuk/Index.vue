<template>
    <DataTable
        title="Surat Jalan Bahan Masuk"
        :data="data"
        :columns="columns"
        basePath="/surat-jalan-masuk"
        :hide-create="isAdminGarmen"
        :hide-actions="isAdminGarmen"
        @open-create="goCreate"
        customActions
    >
        <template #cell-no_surat_jalan="{ item }">
            <button @click="showDetail(item)" class="text-amber-600 hover:text-amber-700 font-semibold hover:underline underline-offset-2 transition font-mono">
                {{ item.no_surat_jalan }}
            </button>
        </template>

        <template #cell-tanggal="{ item }">
            <span class="text-gray-600">{{ formatDate(item.tanggal) }}</span>
        </template>

        <template #cell-jumlah_item="{ item }">
            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">{{ item.jumlah_item }} roll</span>
        </template>

        <template #cell-total_qty="{ item }">
            <span class="font-semibold text-gray-800">{{ formatYard(item.total_qty) }} yard</span>
        </template>

        <template #cell-total_harga="{ item }" v-if="!isAdminGarmen">
            <span class="font-semibold text-gray-800">{{ formatRupiah(item.total_harga) }}</span>
        </template>

        <template #actions="{ item }">
            <button @click="showDetail(item)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors mr-2">
                Detail
            </button>
            <button @click="printSuratJalan(item)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors mr-2">
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
            <button v-if="!isAdminGarmen" @click="confirmDelete(item.id)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                </svg>
                Hapus
            </button>
        </template>
    </DataTable>

    <!-- Detail Modal -->
    <div v-if="detailOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="detailOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Surat Jalan Masuk</h3>
                    <p class="text-sm text-gray-500 font-mono">{{ detailNoSJ }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="detailOpen = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div v-if="detailLoading" class="text-center py-8">
                    <svg class="animate-spin w-8 h-8 text-amber-500 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                </div>

                <div v-else-if="detailItems.length">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase w-10">No</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Kode Bahan</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th v-if="!isAdminGarmen" class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Harga/Yard</th>
                                <th v-if="!isAdminGarmen" class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, idx) in detailItems" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-2 font-mono font-medium text-gray-800">{{ item.kode_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.nama_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.supplier }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan ?? 'yard' }}</td>
                                <td v-if="!isAdminGarmen" class="px-4 py-2 text-right text-gray-700">{{ formatRupiah(item.rp_per_yard) }}</td>
                                <td v-if="!isAdminGarmen" class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatRupiah(item.total_harga) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-400">
                    <p class="text-sm">Belum ada bahan yang terhubung dengan surat jalan ini.</p>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button @click="detailOpen = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Tutup</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    data: Object,
    nextSuratJalan: String,
});

const page = usePage();
const isAdminGarmen = computed(() => page.props.auth.user?.role === 'admingarmen');

const columns = computed(() => {
    const base = [
        { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
        { key: 'tanggal', label: 'Tanggal' },
        { key: 'jumlah_item', label: 'Jumlah Item' },
        { key: 'total_qty', label: 'Total Qty' },
        { key: 'total_harga', label: 'Total Harga' },
        { key: 'keterangan', label: 'Keterangan' },
    ];
    return isAdminGarmen.value ? base.filter((col) => col.key !== 'total_harga') : base;
});

const detailOpen = ref(false);
const detailLoading = ref(false);
const detailItems = ref([]);
const detailNoSJ = ref('');
const detailId = ref(null);

const goCreate = () => {
    router.get('/surat-jalan-masuk/create');
};

const showDetail = async (item) => {
    detailNoSJ.value = item.no_surat_jalan;
    detailId.value = item.id;
    detailOpen.value = true;
    detailLoading.value = true;
    detailItems.value = [];

    try {
        const response = await fetch(`/surat-jalan-masuk/detail?no_surat_jalan=${encodeURIComponent(item.no_surat_jalan)}`, {
            headers: { Accept: 'application/json' },
        });
        detailItems.value = await response.json();
    } catch (error) {
        console.error('Error loading detail:', error);
    } finally {
        detailLoading.value = false;
    }
};

const confirmDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Hapus Surat Jalan?',
        text: 'Data akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    });
    if (result.isConfirmed) {
        router.delete(`/surat-jalan-masuk/${id}`, { preserveScroll: true });
    }
};

const printSuratJalan = async (item) => {
    // Fetch detail items
    let items = [];
    try {
        const response = await fetch(`/surat-jalan-masuk/detail?no_surat_jalan=${encodeURIComponent(item.no_surat_jalan)}`, {
            headers: { Accept: 'application/json' },
        });
        items = await response.json();
    } catch (e) {}

    const { jsPDF } = await import('jspdf');
    const { default: autoTable } = await import('jspdf-autotable');

    const tanggal = item.tanggal ? new Date(item.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
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
    doc.text('SURAT JALAN MASUK', pageW - margin, y, { align: 'right' });

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
        ['No. Surat Jalan', item.no_surat_jalan],
        ['Keterangan', item.keterangan ?? '—'],
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

    if (items.length > 0) {
        const tableRows = items.map((b, i) => [i + 1, b.kode_bahan, b.nama_bahan, b.supplier, `${Number(b.quantity).toFixed(2)} ${b.satuan ?? 'yard'}`]);
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
    } else {
        // Tabel kosong
        autoTable(doc, {
            startY: y,
            margin: { left: margin, right: margin },
            head: [['No', 'Kode Bahan', 'Nama Bahan', 'Supplier', 'Qty']],
            body: Array.from({ length: 10 }, (_, i) => [i + 1, '', '', '', '']),
            headStyles: { fillColor: [240, 240, 240], textColor: 0, fontStyle: 'bold', fontSize: 8 },
            bodyStyles: { fontSize: 8, textColor: 30, minCellHeight: 8 },
            columnStyles: { 0: { halign: 'center', cellWidth: 10 }, 4: { halign: 'right', cellWidth: 30 } },
        });
        y = doc.lastAutoTable.finalY + 14;
    }

    const ttdW = (pageW - margin * 2) / 4;
    ['Pengirim / Supplier', 'Sopir / Kurir', 'Security', 'Penerima — Gudang'].forEach((label, i) => {
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

    doc.save(`SuratJalanMasuk_${item.no_surat_jalan.replace(/[^a-zA-Z0-9-_]/g, '_')}.pdf`);
};

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-');
const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
</script>
