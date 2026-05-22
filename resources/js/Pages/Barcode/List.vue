<template>
    <AdminLayout title="List Barcode">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">List Barcode</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Daftar semua barcode yang sudah di-generate</p>
                </div>
                <div class="flex gap-2">
                    <a href="/barcode" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Generate Baru
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Barcode</p>
                            <p class="text-2xl font-bold text-gray-800">{{ stats.total }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Sudah Ada Harga</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.sudah_harga }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Belum Ada Harga</p>
                            <p class="text-2xl font-bold text-amber-600">{{ stats.belum_harga }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="searchQuery"
                            @input="debounceSearch"
                            type="text"
                            placeholder="Cari barcode, kode, nama..."
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Harga</label>
                        <SearchableSelect v-model="statusFilter" :options="statusOptions" placeholder="Semua Status" @update:modelValue="applyFilters" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                        <SearchableSelect v-model="supplierFilter" :options="supplierOptions" placeholder="Semua Supplier" @update:modelValue="applyFilters" />
                    </div>

                    <div class="flex items-end">
                        <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Reset Filter</button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedBarcodes.length > 0" class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold text-blue-600">{{ selectedBarcodes.length }}</span>
                        barcode terpilih
                    </div>
                    <div class="flex gap-2">
                        <button @click="clearSelection" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Batal Pilih</button>
                        <button @click="printSelectedBarcodes" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                />
                            </svg>
                            Cetak Terpilih ({{ selectedBarcodes.length }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-center">
                                    <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. Surat Jalan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga/Yard</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Keluar</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="barcode in barcodes.data" :key="barcode.id" class="hover:bg-gray-50 transition" :class="{ 'bg-blue-50': isSelected(barcode.id) }">
                                <td class="px-4 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(barcode.id)"
                                        @change="toggleSelect(barcode)"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs font-medium text-gray-800">{{ barcode.barcode_code }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600">{{ barcode.no_surat_jalan ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600">{{ barcode.nama_bahan }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600">{{ barcode.supplier }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium text-gray-800">{{ barcode.quantity }} {{ barcode.satuan }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span v-if="barcode.rp_per_yard" class="text-sm font-medium text-gray-800">
                                        {{ formatRupiah(barcode.rp_per_yard) }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span v-if="barcode.harga_keluar" class="text-sm font-medium text-gray-800">
                                        {{ formatRupiah(barcode.harga_keluar) }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span v-if="barcode.total_harga" class="text-sm font-semibold text-gray-800">
                                        {{ formatRupiah(barcode.total_harga) }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span v-if="barcode.harga_sudah_diisi" class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Sudah
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Belum
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs text-gray-500">{{ formatDate(barcode.tanggal) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a
                                            v-if="!barcode.harga_sudah_diisi"
                                            :href="`/barcode/scan?code=${barcode.barcode_code}`"
                                            class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded transition"
                                        >
                                            Lengkapi Data
                                        </a>
                                        <button @click="printBarcode(barcode)" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-medium rounded transition">Print</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="barcodes.data.length === 0" class="text-center py-12">
                    <div class="text-6xl mb-4">📦</div>
                    <p class="text-gray-500 text-sm">Tidak ada barcode ditemukan</p>
                    <p class="text-gray-400 text-xs mt-1">Coba ubah filter atau generate barcode baru</p>
                </div>

                <!-- Pagination -->
                <div v-if="barcodes.data.length > 0" class="border-t border-gray-200 px-4 py-3 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Menampilkan {{ barcodes.from }} - {{ barcodes.to }} dari {{ barcodes.total }} barcode</div>
                        <div class="flex gap-2">
                            <a
                                v-for="link in barcodes.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1.5 text-sm rounded transition',
                                    link.active
                                        ? 'bg-blue-500 text-white font-medium'
                                        : link.url
                                          ? 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
                                          : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    barcodes: Object,
    suppliers: Array,
    stats: Object,
    filters: Object,
});

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const supplierFilter = ref(props.filters.supplier || '');
const selectedBarcodes = ref([]);

// Check if all barcodes on current page are selected
const isAllSelected = computed(() => {
    return props.barcodes.data.length > 0 && props.barcodes.data.every((b) => selectedBarcodes.value.some((s) => s.id === b.id));
});

// Check if a barcode is selected
const isSelected = (id) => {
    return selectedBarcodes.value.some((b) => b.id === id);
};

// Toggle select all barcodes on current page
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        // Deselect all on current page
        const currentIds = props.barcodes.data.map((b) => b.id);
        selectedBarcodes.value = selectedBarcodes.value.filter((b) => !currentIds.includes(b.id));
    } else {
        // Select all on current page
        props.barcodes.data.forEach((barcode) => {
            if (!isSelected(barcode.id)) {
                selectedBarcodes.value.push(barcode);
            }
        });
    }
};

// Toggle select single barcode
const toggleSelect = (barcode) => {
    const index = selectedBarcodes.value.findIndex((b) => b.id === barcode.id);
    if (index > -1) {
        selectedBarcodes.value.splice(index, 1);
    } else {
        selectedBarcodes.value.push(barcode);
    }
};

// Clear all selection
const clearSelection = () => {
    selectedBarcodes.value = [];
};

// Print selected barcodes
const printSelectedBarcodes = () => {
    if (selectedBarcodes.value.length === 0) return;

    const printWindow = window.open('', '_blank');

    let htmlContent = `
    <html>
      <head>
        <title>Print Barcode - ${selectedBarcodes.value.length} items</title>
        <style>
          @page { size: 60mm 40mm; margin: 0; }
          body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
          .barcode-page { width: 60mm; height: 40mm; display: flex; flex-direction: column; align-items: center; justify-content: center; page-break-after: always; padding: 2mm; box-sizing: border-box; }
          .barcode-page:last-child { page-break-after: auto; }
          .barcode-sticker { text-align: center; width: 100%; }
          .barcode-sticker svg { max-width: 100%; height: auto; }
          .info-text { margin-top: 1mm; font-size: 8pt; font-weight: 600; color: #000; }
        </style>
      </head>
      <body>
  `;

    let idx = 0;
    selectedBarcodes.value.forEach((barcode) => {
        for (let copy = 0; copy < 2; copy++) {
            htmlContent += `
        <div class="barcode-page">
          <div class="barcode-sticker">
            <svg id="barcode-${idx}"></svg>
            <div class="info-text">${barcode.kode_bahan}</div>
          </div>
        </div>
      `;
            idx++;
        }
    });

    htmlContent += `<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script><script>`;

    idx = 0;
    selectedBarcodes.value.forEach((barcode) => {
        for (let copy = 0; copy < 2; copy++) {
            htmlContent += `JsBarcode("#barcode-${idx}", "${barcode.barcode_code}", { format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0 });\n`;
            idx++;
        }
    });

    htmlContent += `setTimeout(() => window.print(), 300);<\/script></body></html>`;

    printWindow.document.write(htmlContent);
    printWindow.document.close();
};

// Status options for SearchableSelect
const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'sudah_harga', label: 'Sudah Ada Harga' },
    { value: 'belum_harga', label: 'Belum Ada Harga' },
];

// Supplier options for SearchableSelect
const supplierOptions = computed(() => {
    const options = [{ value: '', label: 'Semua Supplier' }];
    props.suppliers.forEach((supplier) => {
        // Filter out null/empty suppliers
        if (supplier && supplier.trim() !== '') {
            options.push({ value: supplier, label: supplier });
        }
    });
    return options;
});

let searchTimeout = null;

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(
        '/barcode/list',
        {
            search: searchQuery.value,
            status: statusFilter.value,
            supplier: supplierFilter.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    supplierFilter.value = '';
    router.get('/barcode/list');
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const printBarcode = (barcode) => {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
    <html>
      <head>
        <title>Print Barcode - ${barcode.barcode_code}</title>
        <style>
          @page { size: 60mm 40mm; margin: 0; }
          body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
          .barcode-page { width: 60mm; height: 40mm; display: flex; flex-direction: column; align-items: center; justify-content: center; page-break-after: always; padding: 2mm; box-sizing: border-box; }
          .barcode-page:last-child { page-break-after: auto; }
          .barcode-sticker { text-align: center; width: 100%; }
          .barcode-sticker svg { max-width: 100%; height: auto; }
          .info-text { margin-top: 1mm; font-size: 8pt; font-weight: 600; color: #000; }
        </style>
      </head>
      <body>
        <div class="barcode-page">
          <div class="barcode-sticker">
            <svg id="barcode1"></svg>
            <div class="info-text">${barcode.kode_bahan}</div>
          </div>
        </div>
        <div class="barcode-page">
          <div class="barcode-sticker">
            <svg id="barcode2"></svg>
            <div class="info-text">${barcode.kode_bahan}</div>
          </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <script>
          JsBarcode("#barcode1", "${barcode.barcode_code}", { format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0 });
          JsBarcode("#barcode2", "${barcode.barcode_code}", { format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0 });
          setTimeout(() => window.print(), 100);
        <\/script>
      </body>
    </html>
  `);
    printWindow.document.close();
};
</script>
