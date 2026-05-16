<template>
    <DataTable title="Pembayaran Supplier" :data="data" :columns="columns" basePath="/pembayaran-supplier" @open-create="() => {}" hideCreate customActions>
        <template #cell-no_nota="{ item }">
            <button @click="openDetail(item)" class="text-amber-600 hover:text-amber-700 font-mono font-semibold hover:underline underline-offset-2 transition">{{ item.no_nota }}</button>
        </template>

        <template #cell-no_surat_jalan="{ item }">
            <button @click="openSuratJalanDetail(item.no_surat_jalan)" class="text-blue-600 hover:text-blue-700 font-mono font-medium hover:underline underline-offset-2 transition">
                {{ item.no_surat_jalan }}
            </button>
        </template>

        <template #cell-tanggal="{ item }">
            <span class="text-gray-600">{{ formatDate(item.tanggal) }}</span>
        </template>

        <template #cell-total_tagihan="{ item }">
            <span class="font-medium text-gray-800">{{ formatRupiah(item.total_tagihan) }}</span>
        </template>

        <template #cell-total_dibayar="{ item }">
            <span class="font-medium text-green-700">{{ formatRupiah(item.total_dibayar) }}</span>
        </template>

        <template #cell-sisa_tagihan="{ item }">
            <span class="font-semibold" :class="item.sisa_tagihan > 0 ? 'text-red-600' : 'text-gray-400'">{{ formatRupiah(item.sisa_tagihan) }}</span>
        </template>

        <template #cell-status_bayar="{ item }">
            <span v-if="item.status_bayar === 'lunas'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                Lunas
            </span>
            <span v-else-if="item.status_bayar === 'belum_lunas'" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                Belum Lunas
            </span>
            <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-50 text-gray-500 text-xs font-medium rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                Belum Ada Bahan
            </span>
        </template>

        <template #actions="{ item }">
            <button
                v-if="item.sisa_tagihan > 0"
                @click="openBayar(item)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                </svg>
                Bayar
            </button>
        </template>
    </DataTable>

    <!-- Detail Modal -->
    <div v-if="detailOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="detailOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">History Pembayaran</h3>
                    <p class="text-sm text-gray-500">{{ selectedItem?.no_nota }} — {{ selectedItem?.no_surat_jalan }}</p>
                </div>
                <button @click="detailOpen = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Total Tagihan</p>
                        <p class="text-sm font-bold text-gray-800">{{ formatRupiah(selectedItem?.total_tagihan) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-600">Sudah Dibayar</p>
                        <p class="text-sm font-bold text-green-800">{{ formatRupiah(selectedItem?.total_dibayar) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-xs text-red-600">Sisa Tagihan</p>
                        <p class="text-sm font-bold text-red-800">{{ formatRupiah(selectedItem?.sisa_tagihan) }}</p>
                    </div>
                </div>

                <table v-if="selectedItem?.pembayarans?.length" class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase w-10">No</th>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Jumlah</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Metode</th>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 uppercase w-16">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(p, idx) in selectedItem.pembayarans" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-500">{{ idx + 1 }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ formatDate(p.tanggal_bayar) }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatRupiah(p.jumlah) }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="p.metode === 'cash' ? 'bg-green-50 text-green-700' : 'bg-purple-50 text-purple-700'">
                                    {{ p.metode }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ p.keterangan ?? '—' }}</td>
                            <td class="px-4 py-2 text-center">
                                <button @click="deletePembayaran(p.id)" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-center text-gray-400 py-6 text-sm">Belum ada pembayaran.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button @click="detailOpen = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Bayar Modal -->
    <div v-if="bayarOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="bayarOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Pembayaran</h3>
                <p class="text-sm text-gray-500">
                    {{ bayarItem?.no_nota }} — Sisa:
                    <span class="font-semibold text-red-600">{{ formatRupiah(bayarItem?.sisa_tagihan) }}</span>
                </p>
            </div>
            <form @submit.prevent="submitBayar" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Bayar
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="bayarForm.tanggal_bayar"
                        type="date"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        :value="formatInputPrice(bayarForm.jumlah)"
                        @input="handleJumlahInput"
                        type="text"
                        inputmode="numeric"
                        required
                        placeholder="100.000"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Metode
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="bayarForm.metode" type="radio" value="cash" class="text-amber-500 focus:ring-amber-400" />
                            <span class="text-sm">Cash</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="bayarForm.metode" type="radio" value="transfer" class="text-amber-500 focus:ring-amber-400" />
                            <span class="text-sm">Transfer</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <input
                        v-model="bayarForm.keterangan"
                        type="text"
                        placeholder="Catatan..."
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                    />
                </div>
                <button
                    type="submit"
                    :disabled="saving"
                    class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-lg transition shadow-sm disabled:opacity-50"
                >
                    {{ saving ? 'Menyimpan...' : 'Bayar' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Surat Jalan Detail Modal -->
    <div v-if="sjDetailOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="sjDetailOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Surat Jalan Masuk</h3>
                    <p class="text-sm text-gray-500 font-mono">{{ sjDetailNo }}</p>
                </div>
                <button @click="sjDetailOpen = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div v-if="sjDetailLoading" class="text-center py-8">
                    <svg class="animate-spin w-8 h-8 text-amber-500 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                </div>
                <div v-else-if="sjDetailItems.length">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase w-10">No</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Kode Bahan</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Harga/Yard</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, idx) in sjDetailItems" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-2 font-mono font-medium text-gray-800">{{ item.kode_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.nama_bahan }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ item.supplier }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan ?? 'yard' }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ formatRupiah(item.rp_per_yard) }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ formatRupiah(item.total_harga) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total:</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-gray-800">{{ formatRupiah(sjDetailItems.reduce((s, i) => s + parseFloat(i.total_harga || 0), 0)) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p v-else class="text-center text-gray-400 py-6 text-sm">Belum ada bahan yang terhubung.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button @click="sjDetailOpen = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Tutup</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    data: Object,
    rekeningOptions: Array,
    filters: Object,
});

const columns = [
    { key: 'no_nota', label: 'No. Nota' },
    { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
    { key: 'tanggal', label: 'Tanggal' },
    { key: 'total_tagihan', label: 'Total Tagihan' },
    { key: 'total_dibayar', label: 'Sudah Bayar' },
    { key: 'sisa_tagihan', label: 'Sisa' },
    { key: 'status_bayar', label: 'Status' },
];

const detailOpen = ref(false);
const selectedItem = ref(null);
const bayarOpen = ref(false);
const bayarItem = ref(null);
const saving = ref(false);
const sjDetailOpen = ref(false);
const sjDetailLoading = ref(false);
const sjDetailItems = ref([]);
const sjDetailNo = ref('');

const bayarForm = ref({
    no_nota: '',
    tanggal_bayar: new Date().toISOString().split('T')[0],
    jumlah: 0,
    metode: 'cash',
    keterangan: '',
});

const openDetail = (item) => {
    selectedItem.value = item;
    detailOpen.value = true;
};

const openBayar = (item) => {
    bayarItem.value = item;
    bayarForm.value = {
        no_nota: item.no_nota,
        tanggal_bayar: new Date().toISOString().split('T')[0],
        jumlah: item.sisa_tagihan,
        metode: 'cash',
        keterangan: '',
    };
    bayarOpen.value = true;
};

const handleJumlahInput = (e) => {
    const raw = e.target.value.replace(/[^\d]/g, '');
    const num = parseInt(raw) || 0;
    bayarForm.value.jumlah = num;
    e.target.value = num ? new Intl.NumberFormat('id-ID').format(num) : '';
};

const formatInputPrice = (val) => {
    if (!val) return '';
    return new Intl.NumberFormat('id-ID').format(val);
};

const submitBayar = () => {
    saving.value = true;
    router.post('/pembayaran-supplier', bayarForm.value, {
        onSuccess: () => {
            bayarOpen.value = false;
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};

const deletePembayaran = async (id) => {
    const result = await Swal.fire({
        title: 'Hapus Pembayaran?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    });
    if (result.isConfirmed) {
        router.delete(`/pembayaran-supplier/${id}`, { preserveScroll: true });
        detailOpen.value = false;
    }
};

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-');
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const openSuratJalanDetail = async (noSJ) => {
    sjDetailNo.value = noSJ;
    sjDetailOpen.value = true;
    sjDetailLoading.value = true;
    sjDetailItems.value = [];

    try {
        const response = await fetch(`/surat-jalan-masuk/detail?no_surat_jalan=${encodeURIComponent(noSJ)}`, {
            headers: { Accept: 'application/json' },
        });
        sjDetailItems.value = await response.json();
    } catch (error) {
        console.error('Error loading detail:', error);
    } finally {
        sjDetailLoading.value = false;
    }
};
</script>
