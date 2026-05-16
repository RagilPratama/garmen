<template>
    <DataTable title="Surat Jalan Garmen" :data="data" :columns="columns" basePath="/surat-jalan-garmen" @open-create="goCreate" customActions>
        <template #cell-no_surat_jalan="{ item }">
            <a :href="`/surat-jalan-garmen/${item.id}`" class="text-amber-600 hover:text-amber-700 font-semibold hover:underline underline-offset-2 transition font-mono">
                {{ item.no_surat_jalan }}
            </a>
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
</template>

<script setup>
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

const goCreate = () => {
    router.get('/surat-jalan-garmen/create');
};

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-');
const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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
            foot: [['', '', '', 'Total:', `${sj.items.reduce((s, i) => s + parseFloat(i.quantity || 0), 0).toFixed(2)} yard`]],
            headStyles: { fillColor: [240, 240, 240], textColor: 0, fontStyle: 'bold', fontSize: 8 },
            bodyStyles: { fontSize: 8, textColor: 30 },
            footStyles: { fillColor: [245, 245, 245], textColor: 0, fontStyle: 'bold', fontSize: 8 },
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
