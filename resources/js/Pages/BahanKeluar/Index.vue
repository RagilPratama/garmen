<template>
    <DataTable title="Bahan Keluar (Gudang → Garmen)" :data="data" :columns="columns" basePath="/bahan-keluar" @open-create="() => {}" hideCreate hideActions>
        <template #cell-no_surat_jalan="{ item }">
            <button @click="showDetail(item.no_surat_jalan)" class="text-amber-600 hover:text-amber-700 font-semibold hover:underline underline-offset-2 transition font-mono">
                {{ item.no_surat_jalan }}
            </button>
        </template>

        <template #cell-tanggal="{ item }">
            <span class="text-gray-600">{{ formatDate(item.tanggal) }}</span>
        </template>

        <template #cell-items_sum_quantity="{ item }">
            <span class="font-semibold text-gray-800">{{ formatYard(item.items_sum_quantity) }} yard</span>
        </template>

        <template #cell-items_count="{ item }">
            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">{{ item.items_count }} roll</span>
        </template>
    </DataTable>

    <!-- Detail Modal -->
    <div v-if="detailOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="detailOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Surat Jalan</h3>
                    <p class="text-sm text-gray-500 font-mono">{{ detailNoSJ }}</p>
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, idx) in detailData.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-2 font-mono font-medium text-gray-800">{{ item.kode_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.nama_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.supplier }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total:</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-gray-800">{{ formatYard(detailTotal) }} yard</td>
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
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    data: Object,
});

const columns = [
    { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
    { key: 'tanggal', label: 'Tanggal Kirim' },
    { key: 'items_count', label: 'Jumlah Item' },
    { key: 'items_sum_quantity', label: 'Total Qty' },
];

const detailOpen = ref(false);
const detailLoading = ref(false);
const detailData = ref(null);
const detailNoSJ = ref('');

const detailTotal = computed(() => {
    if (!detailData.value?.items) return 0;
    return detailData.value.items.reduce((sum, i) => sum + (parseFloat(i.quantity) || 0), 0);
});

const showDetail = async (noSJ) => {
    detailNoSJ.value = noSJ;
    detailOpen.value = true;
    detailLoading.value = true;
    detailData.value = null;

    try {
        const response = await fetch(`/bahan-keluar/detail?no_surat_jalan=${encodeURIComponent(noSJ)}`, {
            headers: { Accept: 'application/json' },
        });
        detailData.value = await response.json();
    } catch (error) {
        console.error('Error loading detail:', error);
    } finally {
        detailLoading.value = false;
    }
};

const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');
</script>
