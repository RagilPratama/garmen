<template>
    <DataTable title="Stok Bahan Garmen" :data="data" :columns="columns" basePath="/stok-bahan-garmen" @open-create="() => {}" hideCreate hideActions>
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
                    <div class="flex items-end">
                        <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">Reset Filter</button>
                    </div>
                </div>
            </div>
        </template>

        <template #cell-tanggal_kirim="{ item }">
            <span class="text-gray-600">{{ formatDate(item.tanggal_kirim) }}</span>
        </template>

        <template #cell-quantity="{ item }">
            <span class="font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan ?? 'yard' }}</span>
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
    { key: 'tanggal_kirim', label: 'Tanggal Kirim' },
    { key: 'kode_bahan', label: 'Kode Bahan' },
    { key: 'nama_bahan', label: 'Nama Bahan' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'quantity', label: 'Qty' },
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
        '/stok-bahan-garmen',
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
    router.get('/stok-bahan-garmen');
};

const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');
</script>
