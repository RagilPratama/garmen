<template>
    <AdminLayout title="Scan Barcode - Input Harga">
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
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
                        <h1 class="text-xl font-semibold text-gray-800">Scan Barcode</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Scan barcode untuk input harga bahan</p>
                    </div>
                </div>

                <!-- Scan Method Tabs -->
                <div class="flex gap-2 mb-4">
                    <button
                        @click="scanMethod = 'manual'"
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
                        Input Manual (Recommended)
                    </button>
                    <button
                        @click="scanMethod = 'camera'"
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
                        Scan dengan Kamera (Beta)
                    </button>
                </div>

                <!-- Camera Scanner -->
                <div v-if="scanMethod === 'camera'" class="space-y-4">
                    <!-- Requirements Info -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-xs text-amber-800">
                                <p class="font-semibold mb-1">Persyaratan Camera Scanner:</p>
                                <ul class="list-disc pl-4 space-y-0.5">
                                    <li>Browser modern (Chrome, Firefox, Safari)</li>
                                    <li>Izinkan akses kamera saat diminta</li>
                                    <li>Jika gagal, gunakan mode "Input Manual"</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div v-if="!cameraStarted" class="text-center py-8">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-50 flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Klik tombol di bawah untuk mengaktifkan kamera</p>

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

                    <!-- Camera Preview Container (always rendered but hidden) -->
                    <div v-show="cameraStarted">
                        <!-- Camera Preview -->
                        <div class="relative">
                            <div id="reader" class="rounded-lg overflow-hidden border-2 border-blue-200"></div>
                            <div class="absolute top-3 right-3 flex gap-2">
                                <button @click="stopCamera" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <!-- Scanning Indicator -->
                            <div class="absolute bottom-3 left-1/2 transform -translate-x-1/2">
                                <div class="bg-blue-500 text-white px-4 py-2 rounded-full text-xs font-medium shadow-lg flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Mencari barcode...
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                            <p class="text-xs text-blue-700 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Arahkan kamera ke barcode. Scanner akan otomatis membaca barcode. Pastikan barcode dalam kotak scan area.
                            </p>
                        </div>

                        <!-- Debug Console -->
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs">
                            <p class="font-semibold mb-2">Debug Console:</p>
                            <p class="text-gray-600">Buka browser console (F12) untuk melihat log scanning.</p>
                            <p class="text-gray-600 mt-1">Jika barcode terdeteksi, akan muncul log "=== BARCODE DETECTED ==="</p>
                        </div>
                    </div>
                </div>

                <!-- Manual Input -->
                <div v-if="scanMethod === 'manual'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scan atau Ketik Kode Barcode</label>
                        <div class="flex gap-2">
                            <input
                                v-model="scanInput"
                                @keyup.enter="searchBarcode"
                                type="text"
                                placeholder="BRC-1234567890123"
                                autofocus
                                class="flex-1 px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white font-mono"
                            />
                            <button
                                @click="searchBarcode"
                                :disabled="loading || !scanInput"
                                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span v-else>Cari</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">💡 Tip: Gunakan barcode scanner untuk scan otomatis, atau ketik manual lalu tekan Enter</p>
                    </div>
                </div>
            </div>

            <!-- Result -->
            <div v-if="barcodeData" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Detail Bahan</h2>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Barcode</p>
                        <p class="text-sm font-mono font-semibold text-gray-800">{{ barcodeData.barcode_code }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Kode Bahan</p>
                        <p class="text-sm font-semibold text-gray-800">{{ barcodeData.kode_bahan }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Tanggal Generate</p>
                        <p class="text-sm font-semibold text-gray-800">{{ formatDate(barcodeData.tanggal) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        <p class="text-sm font-semibold" :class="barcodeData.harga_sudah_diisi ? 'text-green-600' : 'text-amber-600'">
                            {{ barcodeData.harga_sudah_diisi ? 'Sudah Lengkap' : 'Belum Lengkap' }}
                        </p>
                    </div>
                </div>

                <!-- Input Form for Complete Data -->
                <div v-if="barcodeData" class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">
                        {{ barcodeData.harga_sudah_diisi ? 'Ubah Data Bahan' : 'Lengkapi Data Bahan' }}
                    </h3>
                    <form @submit.prevent="submitCompleteData" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Supplier
                                    <span class="text-red-500">*</span>
                                </label>
                                <SearchableSelect v-model="completeDataForm.supplier" :options="supplierOptions" placeholder="-- Pilih Supplier --" searchPlaceholder="Cari supplier..." />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Bahan
                                    <span class="text-red-500">*</span>
                                </label>
                                <SearchableSelect v-model="completeDataForm.nama_bahan" :options="bahanOptions" placeholder="-- Pilih Nama Bahan --" searchPlaceholder="Cari nama bahan..." />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Quantity
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model.number="completeDataForm.quantity"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    placeholder="100.50"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan
                                    <span class="text-red-500">*</span>
                                </label>
                                <SearchableSelect v-model="completeDataForm.satuan" :options="satuanOptions" placeholder="-- Pilih Satuan --" searchPlaceholder="Cari satuan..." />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga per {{ completeDataForm.satuan || 'Satuan' }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    :value="formatInputPrice(completeDataForm.rp_per_yard)"
                                    @input="handlePriceInput"
                                    type="text"
                                    inputmode="numeric"
                                    required
                                    placeholder="50.000"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Keluar per {{ completeDataForm.satuan || 'Satuan' }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    :value="formatInputPrice(completeDataForm.harga_keluar)"
                                    @input="handleHargaKeluarInput"
                                    type="text"
                                    inputmode="numeric"
                                    required
                                    placeholder="75.000"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    No. Surat Jalan
                                    <span class="text-red-500">*</span>
                                </label>
                                <SearchableSelect
                                    v-model="completeDataForm.no_surat_jalan"
                                    :options="suratJalanOptions"
                                    placeholder="-- Pilih No. Surat Jalan --"
                                    searchPlaceholder="Cari surat jalan..."
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Masuk Barang
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="completeDataForm.tanggal_masuk"
                                    type="date"
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Kepemilikan
                                    <span class="text-red-500">*</span>
                                </label>
                                <SearchableSelect
                                    v-model="completeDataForm.kepemilikan_id"
                                    :options="kepemilikanOptions"
                                    placeholder="-- Pilih Kepemilikan --"
                                    searchPlaceholder="Cari kepemilikan..."
                                />
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 grid gap-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-900">Total Harga Masuk:</span>
                                <span class="text-lg font-bold text-blue-900">{{ formatRupiah(totalHargaComplete) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-900">Total Harga Keluar:</span>
                                <span class="text-lg font-bold text-blue-900">{{ formatRupiah(totalHargaKeluar) }}</span>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="saving"
                                class="flex-1 px-6 py-3 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <svg v-if="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span>{{ saving ? 'Menyimpan...' : 'Simpan Data Lengkap' }}</span>
                            </button>
                            <button type="button" @click="goBackToList" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Batal</button>
                        </div>
                    </form>
                </div>

                <!-- Already Complete -->
                <div v-else class="border-t border-gray-200 pt-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-green-900 mb-2">Data Sudah Lengkap</p>
                                <div class="grid grid-cols-2 gap-3 text-sm text-green-800">
                                    <div>
                                        <p class="text-xs text-green-600">Supplier:</p>
                                        <p class="font-semibold">{{ barcodeData.supplier }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-green-600">Nama Bahan:</p>
                                        <p class="font-semibold">{{ barcodeData.nama_bahan }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-green-600">Quantity:</p>
                                        <p class="font-semibold">{{ barcodeData.quantity }} {{ barcodeData.satuan }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-green-600">Harga per {{ barcodeData.satuan }}:</p>
                                        <p class="font-semibold">{{ formatRupiah(barcodeData.rp_per_yard) }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-xs text-green-600">Total Harga:</p>
                                        <p class="font-semibold text-lg">{{ formatRupiah(barcodeData.total_harga) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="reset" class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Scan Barcode Lain</button>
                        <button
                            v-if="scanMethod === 'camera'"
                            @click="restartCamera"
                            class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Scan Lagi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-900">{{ errorMessage }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onUnmounted, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    suppliers: Array,
    masterBahan: Array,
    suratJalanMasuk: Array,
    kepemilikans: Array,
});

// Computed supplier options for SearchableSelect
const supplierOptions = computed(() => {
    return (
        props.suppliers?.map((s) => ({
            value: s.nama,
            label: s.nama,
        })) || []
    );
});

// Computed bahan options for SearchableSelect
const bahanOptions = computed(() => {
    return (
        props.masterBahan?.map((b) => ({
            value: b.nama_bahan,
            label: b.nama_bahan,
        })) || []
    );
});

// Computed surat jalan masuk options for SearchableSelect
const suratJalanOptions = computed(() => {
    return (
        props.suratJalanMasuk?.map((sj) => ({
            value: sj.no_surat_jalan,
            label: sj.no_surat_jalan,
        })) || []
    );
});

// Computed kepemilikan options for SearchableSelect
const kepemilikanOptions = computed(() => {
    return (
        props.kepemilikans?.map((k) => ({
            value: k.id,
            label: k.nama_kepemilikan,
        })) || []
    );
});

// Satuan options
const satuanOptions = [
    { value: 'yard', label: 'Yard' },
    { value: 'meter', label: 'Meter' },
    { value: 'kg', label: 'Kilogram (Kg)' },
];

// Dynamic import html5-qrcode
let Html5Qrcode = null;
let html5QrcodeLoaded = false;

// Load library
onMounted(async () => {
    try {
        const module = await import('html5-qrcode');
        Html5Qrcode = module.Html5Qrcode;
        html5QrcodeLoaded = true;
        console.log('html5-qrcode loaded successfully');
    } catch (err) {
        console.error('Failed to load html5-qrcode:', err);
        html5QrcodeLoaded = false;
    }

    // Check if code is passed via URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');
    if (code) {
        scanMethod.value = 'manual';
        scanInput.value = code;
        searchBarcode();
    }
});

// Helper to get CSRF token safely
const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        return meta.content;
    }
    // Fallback: try to get from cookie
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }
    console.error('CSRF token not found');
    return '';
};

const scanMethod = ref('manual'); // Default to manual
const scanInput = ref('');
const barcodeData = ref(null);
const priceInput = ref(0);
const completeDataForm = ref({
    supplier: '',
    nama_bahan: '',
    quantity: 0,
    satuan: 'yard',
    rp_per_yard: 0,
    harga_keluar: 0,
    no_surat_jalan: '',
    tanggal_masuk: new Date().toISOString().split('T')[0],
    kepemilikan_id: null,
});
const loading = ref(false);
const saving = ref(false);
const errorMessage = ref('');
const cameraStarted = ref(false);

let html5QrCode = null;

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');
    if (code) {
        scanInput.value = code;
        searchBarcode();
    }
});

// Cleanup camera on unmount
onUnmounted(() => {
    if (html5QrCode && cameraStarted.value) {
        stopCamera();
    }
});

const totalHarga = computed(() => {
    if (!barcodeData.value) return 0;
    return barcodeData.value.quantity * (priceInput.value || 0);
});

const totalHargaComplete = computed(() => {
    return (completeDataForm.value.quantity || 0) * (completeDataForm.value.rp_per_yard || 0);
});

const totalHargaKeluar = computed(() => {
    return (completeDataForm.value.quantity || 0) * (completeDataForm.value.harga_keluar || 0);
});

const startCamera = async () => {
    try {
        console.log('Starting camera...');

        // Check if library loaded
        if (!html5QrcodeLoaded || !Html5Qrcode) {
            throw new Error('Library barcode scanner belum ter-load. Silakan refresh halaman dan coba lagi.');
        }

        // Check if getUserMedia is supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Browser Anda tidak support akses kamera. Gunakan browser modern seperti Chrome, Firefox, atau Safari.');
        }

        console.log('Browser supports camera API');

        // Request camera permission first
        try {
            console.log('Requesting camera permission...');
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            });
            console.log('Camera permission granted');
            // Stop the test stream
            stream.getTracks().forEach((track) => track.stop());
        } catch (permErr) {
            console.error('Permission error:', permErr);
            if (permErr.name === 'NotAllowedError' || permErr.name === 'PermissionDeniedError') {
                throw new Error('Akses kamera ditolak. Silakan izinkan akses kamera di browser settings.');
            } else if (permErr.name === 'NotFoundError') {
                throw new Error('Kamera tidak ditemukan. Pastikan device Anda memiliki kamera.');
            } else if (permErr.name === 'NotReadableError') {
                throw new Error('Kamera sedang digunakan aplikasi lain. Tutup aplikasi tersebut dan coba lagi.');
            } else {
                throw new Error(`Error akses kamera: ${permErr.message || permErr.name}`);
            }
        }

        // Show camera container first (so #reader element exists)
        cameraStarted.value = true;

        // Wait for DOM to update
        await new Promise((resolve) => setTimeout(resolve, 100));

        console.log('Initializing Html5Qrcode...');
        html5QrCode = new Html5Qrcode('reader');

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 150 },
            // Support all barcode formats
            formatsToSupport: [
                0, // QR_CODE
                1, // AZTEC
                2, // CODABAR
                3, // CODE_39
                4, // CODE_93
                5, // CODE_128
                6, // DATA_MATRIX
                7, // MAXICODE
                8, // ITF
                9, // EAN_13
                10, // EAN_8
                11, // PDF_417
                12, // RSS_14
                13, // RSS_EXPANDED
                14, // UPC_A
                15, // UPC_E
                16, // UPC_EAN_EXTENSION
            ],
        };

        console.log('Starting scanner with config:', config);
        await html5QrCode.start(
            { facingMode: 'environment' }, // Use back camera
            config,
            onScanSuccess,
            onScanError,
        );

        console.log('Camera started successfully');

        await Swal.fire({
            icon: 'success',
            title: 'Kamera Aktif!',
            text: 'Arahkan kamera ke barcode',
            timer: 2000,
            showConfirmButton: false,
        });
    } catch (err) {
        console.error('Error starting camera:', err);

        // Reset camera state on error
        cameraStarted.value = false;

        let errorMessage = err.message || 'Gagal mengakses kamera';
        let errorHtml = `
      <div class="text-left space-y-2">
        <p class="font-semibold">${errorMessage}</p>
        <div class="mt-3 p-3 bg-gray-100 rounded text-xs font-mono">
          <p class="font-semibold mb-1">Debug Info:</p>
          <p>Error: ${err.name || 'Unknown'}</p>
          <p>Message: ${err.message || 'No message'}</p>
          <p>Stack: ${err.stack ? err.stack.substring(0, 200) : 'No stack'}</p>
        </div>
        <div class="mt-3 p-3 bg-blue-50 rounded text-sm">
          <p class="font-semibold mb-2">💡 Tips:</p>
          <ul class="list-disc pl-5 space-y-1">
            <li>Refresh halaman (Ctrl+Shift+R) dan coba lagi</li>
            <li>Pastikan browser memiliki izin akses kamera</li>
            <li>Gunakan browser Chrome atau Firefox</li>
            <li>Tutup aplikasi lain yang gunakan kamera</li>
            <li>Gunakan mode "Input Manual" sebagai alternatif</li>
          </ul>
        </div>
      </div>
    `;

        await Swal.fire({
            icon: 'error',
            title: 'Gagal Mengakses Kamera',
            html: errorHtml,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK',
            width: '600px',
        });

        // Auto switch to manual mode
        scanMethod.value = 'manual';
    }
};

const stopCamera = async () => {
    if (html5QrCode && cameraStarted.value) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
            cameraStarted.value = false;
            console.log('Camera stopped');
        } catch (err) {
            console.error('Error stopping camera:', err);
        }
    }
};

const onScanSuccess = (decodedText, decodedResult) => {
    console.log('=== BARCODE DETECTED ===');
    console.log('Decoded text:', decodedText);
    console.log('Decoded result:', decodedResult);
    console.log('Format:', decodedResult?.result?.format);

    // Stop camera after successful scan
    stopCamera();

    // Set the scanned code and search
    scanInput.value = decodedText;

    // Show success feedback first
    Swal.fire({
        icon: 'success',
        title: 'Barcode Terdeteksi!',
        text: `Code: ${decodedText}`,
        timer: 1500,
        showConfirmButton: false,
    }).then(() => {
        // Then search
        searchBarcode();
    });
};

const onScanError = (errorMessage) => {
    // Log errors for debugging (but don't show to user)
    if (errorMessage && !errorMessage.includes('NotFoundException')) {
        console.log('Scan error:', errorMessage);
    }
};

const parseNumeric = (value) => {
    if (typeof value === 'number') return value;
    if (!value) return 0;
    const normalized = String(value).trim().replace(/\./g, '').replace(/,/g, '.');
    const cleaned = normalized.replace(/[^0-9.\-]/g, '');
    return parseFloat(cleaned) || 0;
};

const formatInputPrice = (value) => {
    if (value === null || value === undefined || value === '') return '';
    const num = typeof value === 'number' ? value : parseNumeric(value);
    if (Number.isNaN(num) || num === 0) return '';
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
        minimumFractionDigits: 0,
    }).format(num);
};

const handlePriceInput = (e) => {
    const input = e.target;
    const cursorPos = input.selectionStart;
    const oldValue = input.value;
    const oldLength = oldValue.length;

    const numericValue = parseNumeric(input.value);
    completeDataForm.value.rp_per_yard = numericValue;

    const formattedValue = formatInputPrice(numericValue);
    input.value = formattedValue;

    const newLength = formattedValue.length;
    const diff = newLength - oldLength;
    const newCursorPos = Math.max(0, cursorPos + diff);
    input.setSelectionRange(newCursorPos, newCursorPos);
};

const handleHargaKeluarInput = (e) => {
    const input = e.target;
    const cursorPos = input.selectionStart;
    const oldValue = input.value;
    const oldLength = oldValue.length;

    const numericValue = parseNumeric(input.value);
    completeDataForm.value.harga_keluar = numericValue;

    const formattedValue = formatInputPrice(numericValue);
    input.value = formattedValue;

    const newLength = formattedValue.length;
    const diff = newLength - oldLength;
    const newCursorPos = Math.max(0, cursorPos + diff);
    input.setSelectionRange(newCursorPos, newCursorPos);
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const searchBarcode = async () => {
    if (!scanInput.value) return;

    loading.value = true;
    errorMessage.value = '';
    barcodeData.value = null;

    try {
        const response = await fetch('/barcode/find', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                barcode_code: scanInput.value,
            }),
        });

        const result = await response.json();

        if (result.success) {
            barcodeData.value = result.data;
            priceInput.value = result.data.rp_per_yard || 0;

            // Populate form if data is already complete (for editing)
            if (result.data.harga_sudah_diisi) {
                completeDataForm.value = {
                    supplier: result.data.supplier || '',
                    nama_bahan: result.data.nama_bahan || '',
                    quantity: result.data.quantity || 0,
                    satuan: result.data.satuan || 'yard',
                    rp_per_yard: result.data.rp_per_yard || 0,
                    harga_keluar: result.data.harga_keluar || 0,
                    no_surat_jalan: result.data.no_surat_jalan || '',
                    tanggal_masuk: result.data.tanggal || new Date().toISOString().split('T')[0],
                    kepemilikan_id: result.data.kepemilikan_id || null,
                };
            }
        } else {
            errorMessage.value = result.message || 'Barcode tidak ditemukan';
        }
    } catch (error) {
        console.error('Error searching barcode:', error);
        errorMessage.value = 'Terjadi kesalahan saat mencari barcode';
    } finally {
        loading.value = false;
    }
};

const submitCompleteData = async () => {
    if (
        !completeDataForm.value.supplier ||
        !completeDataForm.value.nama_bahan ||
        !completeDataForm.value.quantity ||
        !completeDataForm.value.rp_per_yard ||
        !completeDataForm.value.harga_keluar ||
        !completeDataForm.value.no_surat_jalan ||
        !completeDataForm.value.tanggal_masuk ||
        !completeDataForm.value.kepemilikan_id ||
        !barcodeData.value
    ) {
        await Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            text: 'Semua field yang bertanda * (required) harus diisi termasuk Kepemilikan!',
            confirmButtonColor: '#f59e0b',
        });
        return;
    }

    saving.value = true;

    try {
        const response = await fetch(`/barcode/${barcodeData.value.id}/complete`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                supplier: completeDataForm.value.supplier,
                nama_bahan: completeDataForm.value.nama_bahan,
                quantity: completeDataForm.value.quantity,
                satuan: completeDataForm.value.satuan,
                rp_per_yard: completeDataForm.value.rp_per_yard,
                harga_keluar: completeDataForm.value.harga_keluar,
                no_surat_jalan: completeDataForm.value.no_surat_jalan,
                tanggal_masuk: completeDataForm.value.tanggal_masuk,
                kepemilikan_id: completeDataForm.value.kepemilikan_id,
            }),
        });

        const result = await response.json();

        if (result.success) {
            barcodeData.value = result.data;

            const swalResult = await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message || 'Data berhasil disimpan!',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'OK',
                showCancelButton: scanMethod.value === 'camera',
                cancelButtonText: 'Scan Lagi',
                cancelButtonColor: '#3b82f6',
            });

            // If user clicks "Scan Lagi" (cancel button)
            if (swalResult.isDismissed && scanMethod.value === 'camera') {
                restartCamera();
            }
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menyimpan data: ' + result.message,
                confirmButtonColor: '#ef4444',
            });
        }
    } catch (error) {
        console.error('Error saving complete data:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat menyimpan data',
            confirmButtonColor: '#ef4444',
        });
    } finally {
        saving.value = false;
    }
};

const restartCamera = async () => {
    // Reset state
    barcodeData.value = null;
    priceInput.value = 0;
    completeDataForm.value = {
        supplier: '',
        nama_bahan: '',
        quantity: 0,
        satuan: 'yard',
        rp_per_yard: 0,
        harga_keluar: 0,
        no_surat_jalan: '',
        tanggal_masuk: new Date().toISOString().split('T')[0],
        kepemilikan_id: null,
    };
    errorMessage.value = '';
    scanInput.value = '';

    // Start camera again
    await startCamera();
};

const reset = () => {
    scanInput.value = '';
    barcodeData.value = null;
    priceInput.value = 0;
    completeDataForm.value = {
        supplier: '',
        nama_bahan: '',
        quantity: 0,
        satuan: 'yard',
        rp_per_yard: 0,
        harga_keluar: 0,
        no_surat_jalan: '',
        tanggal_masuk: new Date().toISOString().split('T')[0],
        kepemilikan_id: null,
    };
    errorMessage.value = '';

    // Stop camera if running
    if (cameraStarted.value) {
        stopCamera();
    }
};

const goBackToList = () => {
    window.location.href = '/barcode/list';
};
</script>
