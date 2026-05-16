<template>
    <DataTable title="Stok Bahan" :data="data" :columns="columns" basePath="/stok-bahan" @open-create="() => {}" hideCreate hideActions>
        <template #filters>
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Nama Bahan</label>
                        <SearchableSelect
                            v-model="namaBahanFilter"
                            :options="namaBahanSelectOptions"
                            placeholder="Semua Nama Bahan"
                            searchPlaceholder="Cari nama bahan..."
                            @update:modelValue="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Supplier</label>
                        <SearchableSelect
                            v-model="supplierFilter"
                            :options="supplierSelectOptions"
                            placeholder="Semua Supplier"
                            searchPlaceholder="Cari supplier..."
                            @update:modelValue="applyFilters"
                        />
                    </div>
                    <div class="flex items-end gap-2">
                        <button @click="resetFilters" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">Reset Filter</button>
                    </div>
                </div>
                <!-- Export Buttons -->
                <div class="flex gap-2 mt-4">
                    <a :href="exportExcelUrl" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Export Excel
                    </a>
                    <a :href="exportPdfUrl" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                            />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </template>

        <template #cell-quantity="{ item }">
            <span class="font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan ?? 'yard' }}</span>
        </template>

        <template #cell-rp_per_yard="{ item }">
            <span class="font-medium text-gray-700">{{ formatRupiah(item.rp_per_yard) }}</span>
        </template>

        <template #cell-total_harga="{ item }">
            <span class="font-semibold text-gray-800">{{ formatRupiah(item.total_harga) }}</span>
        </template>
    </DataTable>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    data: Object,
    namaBahanOptions: Array,
    supplierOptions: Array,
    filters: Object,
});

const columns = [
    { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
    { key: 'kode_bahan', label: 'Kode Bahan' },
    { key: 'nama_bahan', label: 'Nama Bahan' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'quantity', label: 'Qty' },
    { key: 'rp_per_yard', label: 'Harga/Yard' },
    { key: 'total_harga', label: 'Total Harga' },
];

const namaBahanFilter = ref(props.filters?.nama_bahan || '');
const supplierFilter = ref(props.filters?.supplier || '');

const namaBahanSelectOptions = computed(() => {
    const options = [{ value: '', label: 'Semua Nama Bahan' }];
    if (props.namaBahanOptions) {
        props.namaBahanOptions.forEach((nama) => {
            options.push({ value: nama, label: nama });
        });
    }
    return options;
});

const supplierSelectOptions = computed(() => {
    const options = [{ value: '', label: 'Semua Supplier' }];
    if (props.supplierOptions) {
        props.supplierOptions.forEach((s) => {
            options.push({ value: s, label: s });
        });
    }
    return options;
});

const applyFilters = () => {
    router.get(
        '/stok-bahan',
        {
            nama_bahan: namaBahanFilter.value || undefined,
            supplier: supplierFilter.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const resetFilters = () => {
    namaBahanFilter.value = '';
    supplierFilter.value = '';
    router.get('/stok-bahan');
};

const exportExcelUrl = computed(() => {
    const params = new URLSearchParams();
    if (namaBahanFilter.value) params.set('nama_bahan', namaBahanFilter.value);
    if (supplierFilter.value) params.set('supplier', supplierFilter.value);
    const qs = params.toString();
    return '/stok-bahan/export-excel' + (qs ? '?' + qs : '');
});

const exportPdfUrl = computed(() => {
    const params = new URLSearchParams();
    if (namaBahanFilter.value) params.set('nama_bahan', namaBahanFilter.value);
    if (supplierFilter.value) params.set('supplier', supplierFilter.value);
    const qs = params.toString();
    return '/stok-bahan/export-pdf' + (qs ? '?' + qs : '');
});

const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
</script>
