<template>
    <DataTable title="Bahan Masuk" :data="data" :columns="columns" basePath="/bahan-masuk" @open-create="() => {}" hideCreate hideActions>
        <template #filters>
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Lokasi</label>
                        <SearchableSelect v-model="statusFilter" :options="statusOptions" placeholder="Semua Status" @update:modelValue="applyFilters" />
                    </div>
                    <div class="flex items-end">
                        <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">Reset Filter</button>
                    </div>
                </div>
            </div>
        </template>

        <template #cell-tanggal="{ item }">
            <span class="text-gray-600">{{ formatDate(item.tanggal) }}</span>
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

        <template #cell-lokasi="{ item }">
            <span v-if="item.lokasi === 'Gudang'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                Gudang
            </span>
            <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full" :title="item.no_sj_garmen ? `SJ: ${item.no_sj_garmen}` : ''">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                Garmen
            </span>
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
    supplierOptions: Array,
    namaBahanOptions: Array,
    filters: Object,
});

const columns = [
    { key: 'tanggal', label: 'Tanggal' },
    { key: 'kode_bahan', label: 'Kode Bahan' },
    { key: 'nama_bahan', label: 'Nama Bahan' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'quantity', label: 'Qty' },
    { key: 'rp_per_yard', label: 'Harga/Yard' },
    { key: 'total_harga', label: 'Total' },
    { key: 'lokasi', label: 'Lokasi' },
];

const namaBahanFilter = ref(props.filters?.nama_bahan || '');
const supplierFilter = ref(props.filters?.supplier || '');
const statusFilter = ref(props.filters?.status || '');

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'gudang', label: 'Di Gudang' },
    { value: 'garmen', label: 'Di Garmen' },
];

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
        '/bahan-masuk',
        {
            nama_bahan: namaBahanFilter.value || undefined,
            supplier: supplierFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const resetFilters = () => {
    namaBahanFilter.value = '';
    supplierFilter.value = '';
    statusFilter.value = '';
    router.get('/bahan-masuk');
};

const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');
</script>
