<template>
    <AdminLayout :title="`Surat Jalan - ${suratJalan.no_surat_jalan}`">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">{{ suratJalan.no_surat_jalan }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ formatDate(suratJalan.tanggal) }} {{ suratJalan.keterangan ? `— ${suratJalan.keterangan}` : '' }}</p>
                </div>
                <a href="/surat-jalan-garmen" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">← Kembali</a>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Persetujuan Marker & Pola</h2>
                        <p class="text-xs text-gray-500">Admin gudang harus menyetujui kedua status sebelum mencetak surat jalan.</p>
                    </div>
                    <button
                        @click="printSuratJalan"
                        :disabled="!canPrint || approvalSaving"
                        class="px-4 py-2 bg-amber-500 text-white rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ approvalSaving ? 'Memproses...' : 'Print Surat Jalan' }}
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer">
                        <input
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 rounded border-gray-300"
                            v-model="approval.marker_approved"
                            :disabled="!canEditApproval || approvalSaving"
                            @change="saveApproval"
                        />
                        <span class="text-sm font-medium">Marker disetujui</span>
                    </label>
                    <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer">
                        <input
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 rounded border-gray-300"
                            v-model="approval.pola_approved"
                            :disabled="!canEditApproval || approvalSaving"
                            @change="saveApproval"
                        />
                        <span class="text-sm font-medium">Pola disetujui</span>
                    </label>
                    <label v-if="isSuperAdmin" class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer">
                        <input
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 rounded border-gray-300"
                            v-model="approval.superadmin_allow_print"
                            :disabled="approvalSaving"
                            @change="saveApproval"
                        />
                        <span class="text-sm font-medium">Izinkan cetak tanpa marker/pola</span>
                    </label>
                </div>

                <p v-if="isAdminGudang && approval.superadmin_allow_print" class="mt-3 text-sm text-emerald-700">
                    Superadmin telah mengizinkan cetak tanpa marker/pola.
                </p>
                <p v-else-if="isAdminGudang && !canPrint" class="mt-3 text-sm text-red-600">
                    Anda harus menyetujui marker dan pola agar dapat mencetak surat jalan.
                </p>
                <p v-else-if="isSuperAdmin && !canPrint" class="mt-3 text-sm text-emerald-600">
                    Superadmin dapat mencetak tanpa persetujuan marker/pola.
                </p>
            </div>
            <!-- Scan Barcode -->
            <div class="bg-white rounded-xl border-2 border-blue-200 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Scan Barcode</h2>
                        <p class="text-xs text-gray-500">Scan barcode bahan — otomatis masuk ke surat jalan ini</p>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-xs text-green-600 font-medium">Scanner Aktif</span>
                    </div>
                </div>

                <!-- Scan Method Tabs -->
                <div class="flex gap-2 mb-4">
                    <button
                        @click="switchToManual"
                        :class="['flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition', scanMethod === 'manual' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Input Manual
                    </button>
                    <button
                        @click="switchToCamera"
                        :class="['flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition', scanMethod === 'camera' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                            />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Scan Kamera
                    </button>
                </div>

                <!-- Manual Input -->
                <div v-if="scanMethod === 'manual'">
                    <div class="flex gap-2">
                        <input
                            ref="scanInputRef"
                            v-model="scanInput"
                            @keyup.enter="scanBarcode"
                            type="text"
                            placeholder="Scan atau ketik kode barcode..."
                            autofocus
                            class="flex-1 px-4 py-3 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                        />
                        <button
                            @click="scanBarcode"
                            :disabled="scanning || !scanInput"
                            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="scanning" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <span v-else>Tambah</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">💡 Gunakan barcode scanner untuk scan otomatis, atau ketik kode bahan lalu tekan Enter</p>
                </div>

                <!-- Camera Scanner -->
                <div v-if="scanMethod === 'camera'" class="space-y-4">
                    <div v-if="!cameraStarted" class="text-center py-6">
                        <button @click="startCamera" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2 mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                            Aktifkan Kamera
                        </button>
                    </div>

                    <div v-show="cameraStarted">
                        <div class="relative">
                            <div id="reader" class="rounded-lg overflow-hidden border-2 border-blue-200"></div>
                            <div class="absolute top-3 right-3">
                                <button @click="stopCamera" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="absolute bottom-3 left-1/2 transform -translate-x-1/2">
                                <div class="bg-blue-500 text-white px-4 py-2 rounded-full text-xs font-medium shadow-lg flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Mencari barcode...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scan Result Message -->
                <div
                    v-if="scanMessage"
                    class="mt-3 p-3 rounded-lg text-sm flex items-center gap-2"
                    :class="scanSuccess ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'"
                >
                    <svg v-if="scanSuccess" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ scanMessage }}
                </div>
            </div>

            <!-- Items List -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Daftar Bahan ({{ items.length }} item)</h2>
                    <p v-if="items.length" class="text-sm text-gray-500">
                        Total:
                        <span class="font-semibold text-gray-800">{{ formatYard(totalQty) }} yard</span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-10">No</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Kode Bahan</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-16">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, idx) in items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-3 font-mono font-medium text-gray-800">{{ item.kode_bahan }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ item.nama_bahan }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ item.supplier }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ formatYard(item.quantity) }} {{ item.satuan }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                    <p class="text-sm">Belum ada bahan. Scan barcode di atas untuk menambahkan.</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="items.length" class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">Total:</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-gray-800">{{ formatYard(totalQty) }} yard</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({ suratJalan: Object });
const page = usePage();

const items = ref([...(props.suratJalan.items || [])]);
const scanInput = ref('');
const scanInputRef = ref(null);
const scanning = ref(false);
const scanMessage = ref('');
const scanSuccess = ref(false);
const scanMethod = ref('manual');
const cameraStarted = ref(false);

const approval = ref({
    marker_approved: Boolean(props.suratJalan.marker_approved),
    pola_approved: Boolean(props.suratJalan.pola_approved),
    superadmin_allow_print: Boolean(props.suratJalan.superadmin_allow_print),
});
const approvalSaving = ref(false);

const isSuperAdmin = computed(() => page.props.auth.user?.role === 'superadmin');
const isAdminGudang = computed(() => page.props.auth.user?.role === 'admingudang');
const canEditApproval = computed(() => isSuperAdmin.value || isAdminGudang.value);
const canPrint = computed(
    () => isSuperAdmin.value ||
        (isAdminGudang.value && (approval.value.superadmin_allow_print || (approval.value.marker_approved && approval.value.pola_approved)))
);

let Html5Qrcode = null;
let html5QrCode = null;

const totalQty = computed(() => items.value.reduce((sum, i) => sum + (parseFloat(i.quantity) || 0), 0));
const totalHarga = computed(() => items.value.reduce((sum, i) => sum + (parseFloat(i.total_harga) || 0), 0));

const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
};

onMounted(async () => {
    focusInput();
    // Load html5-qrcode library
    try {
        const module = await import('html5-qrcode');
        Html5Qrcode = module.Html5Qrcode;
    } catch (err) {
        console.error('Failed to load html5-qrcode:', err);
    }
});

onUnmounted(() => {
    if (html5QrCode && cameraStarted.value) {
        stopCamera();
    }
});

const focusInput = () => {
    nextTick(() => {
        scanInputRef.value?.focus();
    });
};

const switchToManual = () => {
    if (cameraStarted.value) stopCamera();
    scanMethod.value = 'manual';
    focusInput();
};

const switchToCamera = () => {
    scanMethod.value = 'camera';
};

const startCamera = async () => {
    if (!Html5Qrcode) {
        scanMessage.value = 'Library kamera belum ter-load. Refresh halaman.';
        scanSuccess.value = false;
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        stream.getTracks().forEach((track) => track.stop());

        cameraStarted.value = true;
        await nextTick();

        html5QrCode = new Html5Qrcode('reader');
        await html5QrCode.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 150 } }, onCameraScanSuccess, () => {});
    } catch (err) {
        cameraStarted.value = false;
        scanMessage.value = 'Gagal mengakses kamera: ' + (err.message || err.name);
        scanSuccess.value = false;
    }
};

const stopCamera = async () => {
    if (html5QrCode && cameraStarted.value) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (err) {
            console.error('Error stopping camera:', err);
        }
        cameraStarted.value = false;
    }
};

const onCameraScanSuccess = (decodedText) => {
    // Stop camera dulu sebelum proses
    stopCamera();
    scanInput.value = decodedText;
    scanBarcode();
};

const scanBarcode = async () => {
    if (!scanInput.value) return;
    scanning.value = true;
    scanMessage.value = '';
    scanSuccess.value = false;

    const code = scanInput.value.trim();

    // Cek duplikat di list lokal
    if (items.value.some((i) => i.kode_bahan === code)) {
        scanMessage.value = 'Barcode ini sudah ada di daftar';
        scanSuccess.value = false;
        scanning.value = false;
        scanInput.value = '';
        focusInput();
        return;
    }

    try {
        // Step 1: Cek barcode dulu (tanpa surat_jalan_id)
        const checkResponse = await fetch('/surat-jalan-garmen/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ barcode_code: code }),
        });

        const checkResult = await checkResponse.json();

        if (!checkResult.success) {
            scanMessage.value = checkResult.message;
            scanSuccess.value = false;
            scanning.value = false;
            scanInput.value = '';
            focusInput();
            return;
        }

        const response = await fetch('/surat-jalan-garmen/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({
                barcode_code: code,
                surat_jalan_id: props.suratJalan.id,
            }),
        });

        const result = await response.json();

        if (result.success) {
            items.value.push(result.data);
            scanMessage.value = result.message;
            scanSuccess.value = true;
            scanInput.value = '';
        } else {
            scanMessage.value = result.message;
            scanSuccess.value = false;
        }
    } catch (error) {
        scanMessage.value = 'Terjadi kesalahan saat scan barcode';
        scanSuccess.value = false;
    } finally {
        scanning.value = false;
        scanInput.value = '';
        focusInput();
    }
};

const removeItem = async (itemId) => {
    const result = await Swal.fire({
        title: 'Hapus item ini?',
        text: 'Stok bahan akan dikembalikan ke gudang.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`/surat-jalan-garmen/${props.suratJalan.id}/item/${itemId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
        });

        const data = await response.json();
        if (data.success) {
            items.value = items.value.filter((i) => i.id !== itemId);
            scanMessage.value = 'Item berhasil dihapus, stok dikembalikan';
            scanSuccess.value = true;
        }
    } catch (error) {
        scanMessage.value = 'Gagal menghapus item';
        scanSuccess.value = false;
    }
    focusInput();
};

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-');
const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
const formatInputPrice = (val) => {
    if (!val && val !== 0) return '';
    return new Intl.NumberFormat('id-ID').format(val);
};

const handleHargaInput = (item, event) => {
    const input = event.target;
    const raw = input.value.replace(/[^\d]/g, '');
    const num = parseInt(raw) || 0;
    item.harga_keluar = num;
    item.total_harga = item.quantity * num;
    input.value = num ? new Intl.NumberFormat('id-ID').format(num) : '';
};

const updateHarga = async (item, event) => {
    const raw = event.target.value.replace(/[^\d]/g, '');
    const harga = parseInt(raw) || 0;

    try {
        const response = await fetch(`/surat-jalan-garmen/${props.suratJalan.id}/item/${item.id}/harga`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ harga_keluar: harga }),
        });

        const result = await response.json();
        if (result.success) {
            item.harga_keluar = harga;
            item.total_harga = item.quantity * harga;
        }
    } catch (error) {
        console.error('Error updating harga:', error);
    }
};

const saveApproval = async () => {
    if (!canEditApproval.value) {
        return;
    }

    approvalSaving.value = true;

    try {
        const response = await fetch(`/surat-jalan-garmen/${props.suratJalan.id}/approval`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify(approval.value),
        });

        const result = await response.json();
        if (response.ok && result.success) {
            approval.value.marker_approved = Boolean(result.data.marker_approved);
            approval.value.pola_approved = Boolean(result.data.pola_approved);
            approval.value.superadmin_allow_print = Boolean(result.data.superadmin_allow_print);
            await Swal.fire({
                icon: 'success',
                title: 'Disimpan',
                text: 'Status approval marker, pola, dan override berhasil diperbarui.',
                timer: 1200,
                showConfirmButton: false,
            });
        } else {
            await Swal.fire('Gagal', result.message || 'Tidak dapat menyimpan approval.', 'error');
        }
    } catch (error) {
        await Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan approval.', 'error');
    } finally {
        approvalSaving.value = false;
    }
};

const printSuratJalan = () => {
    if (!canPrint.value) {
        Swal.fire('Tidak dapat mencetak', 'Surat jalan harus disetujui marker dan pola sebelum dapat dicetak.', 'warning');
        return;
    }
    const sj = props.suratJalan;
    const tanggal = sj.tanggal ? new Date(sj.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
    const today = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

    import('jspdf').then(({ jsPDF }) => {
        import('jspdf-autotable').then(({ default: autoTable }) => {
            const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
            const pageW = doc.internal.pageSize.getWidth();
            const margin = 15;
            let y = margin;

            // ── KOP SURAT ──
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('NEWGARMEN', margin, y);

            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(80);
            doc.text('Jl. Raya Garmen No. 1, Kota, Provinsi 00000', margin, y + 6);
            doc.text('Telp: (021) 000-0000  |  Email: info@newgarmen.com', margin, y + 10);

            // Judul kanan
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
            y += 6;

            // ── INFO GRID ──
            const col2 = pageW / 2 + 2;
            doc.setFontSize(7);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(120);
            doc.text('INFORMASI PENGIRIMAN', margin, y);

            y += 4;
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(0);
            doc.setFontSize(9);

            const infoLeft = [
                ['Tanggal', tanggal],
                ['No. Surat Jalan', sj.no_surat_jalan],
                ['Keterangan', sj.keterangan ?? '—'],
            ];
            const infoRight = [
                ['Dari', 'Gudang'],
                ['Tujuan', 'Garmen'],
                ['Jumlah Item', `${items.value.length} roll`],
            ];

            infoLeft.forEach(([label, val], i) => {
                doc.setTextColor(100);
                doc.text(`${label}`, margin, y + i * 5);
                doc.setTextColor(0);
                doc.setFont('helvetica', 'bold');
                doc.text(`: ${val}`, margin + 30, y + i * 5);
                doc.setFont('helvetica', 'normal');
            });
            infoRight.forEach(([label, val], i) => {
                doc.setTextColor(100);
                doc.text(`${label}`, col2, y + i * 5);
                doc.setTextColor(0);
                doc.setFont('helvetica', 'bold');
                doc.text(`: ${val}`, col2 + 22, y + i * 5);
                doc.setFont('helvetica', 'normal');
            });

            y += infoLeft.length * 5 + 8;

            // ── TABEL BAHAN ──
            const tableRows = items.value.map((item, i) => [
                i + 1,
                item.kode_bahan ?? '—',
                item.nama_bahan ?? '—',
                item.supplier ?? '—',
                Number(item.quantity ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (item.satuan ?? 'yard'),
            ]);

            autoTable(doc, {
                startY: y,
                margin: { left: margin, right: margin },
                head: [['No', 'Kode Bahan', 'Nama Bahan', 'Supplier', 'Qty']],
                body: tableRows,
                foot: [['', '', '', 'Total:', formatYard(totalQty.value) + ' yard']],
                headStyles: { fillColor: [240, 240, 240], textColor: 0, fontStyle: 'bold', fontSize: 8 },
                bodyStyles: { fontSize: 8, textColor: 30 },
                footStyles: { fillColor: [245, 245, 245], textColor: 0, fontStyle: 'bold', fontSize: 8 },
                columnStyles: {
                    0: { halign: 'center', cellWidth: 10 },
                    1: { cellWidth: 28 },
                    4: { halign: 'right', cellWidth: 30 },
                },
            });

            y = doc.lastAutoTable.finalY + 14;

            // ── TANDA TANGAN (4 kolom: Pengirim, Security, Penerima, Admin) ──
            const ttdW = (pageW - margin * 2) / 4;
            const ttdLabels = ['Pengirim — Gudang', 'Security', 'Penerima — Garmen', 'Admin'];
            ttdLabels.forEach((label, i) => {
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
            doc.setDrawColor(200);
            doc.line(margin, y, pageW - margin, y);
            doc.text(`Dokumen ini dibuat secara otomatis. Newgarmen — ${today}`, margin, y + 4);

            const filename = `SuratJalan_${sj.no_surat_jalan.replace(/[^a-zA-Z0-9-_]/g, '_')}.pdf`;
            doc.save(filename);
        });
    });
};
</script>
