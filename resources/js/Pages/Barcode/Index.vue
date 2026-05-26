<template>
    <AdminLayout title="Barcode Generator">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Barcode Generator</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Generate barcode untuk tracking bahan masuk</p>
                </div>
                <button @click="printBarcodes" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                        />
                    </svg>
                    Print Semua
                </button>
            </div>

            <!-- Form Input -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="text-center py-8">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Generate Barcode Otomatis</h2>
                    <p class="text-sm text-gray-600 mb-6">Klik tombol di bawah untuk generate barcode dengan kode unik</p>

                    <!-- Batch Quantity Input -->
                    <div class="max-w-xs mx-auto mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Barcode yang Akan Di-generate</label>
                        <input
                            v-model.number="batchQuantity"
                            type="number"
                            min="1"
                            max="100"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                            placeholder="1"
                        />
                        <p class="text-xs text-gray-500 mt-1">Maksimal 100 barcode per generate</p>
                    </div>

                    <button
                        @click="generateBarcodes"
                        type="button"
                        :disabled="generating"
                        class="px-8 py-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-base font-semibold rounded-xl transition shadow-lg hover:shadow-xl flex items-center gap-3 mx-auto disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="generating" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ generating ? 'Sedang Generate...' : `Generate ${batchQuantity} Barcode` }}
                    </button>

                    <p class="text-xs text-gray-500 mt-4">
                        💡 Kode bahan akan di-generate otomatis dengan format:
                        <span class="font-mono font-semibold text-amber-600">C227, C228 ...</span>
                    </p>
                </div>
            </div>

            <!-- Generated Barcodes -->
            <div v-if="barcodes.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Barcode Belum Lengkap ({{ barcodes.length }})</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Barcode yang belum diisi supplier & harga. Scan barcode untuk melengkapi data.</p>
                    </div>
                </div>

                <!-- Barcode Grid -->
                <div id="barcode-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="(item, idx) in barcodes" :key="idx" class="barcode-item border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">Kode: {{ item.kodeBahan }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ item.code }}</p>
                            </div>
                        </div>

                        <!-- Barcode SVG -->
                        <div class="flex justify-center mb-3 bg-white p-2">
                            <svg
                                :ref="
                                    (el) => {
                                        if (el) generateBarcodeImage(el, item.code);
                                    }
                                "
                            ></svg>
                        </div>

                        <!-- Info -->
                        <div class="space-y-1 text-xs text-gray-600 border-t border-gray-100 pt-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Kode Bahan:</span>
                                <span class="font-medium font-mono">{{ item.kodeBahan }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal:</span>
                                <span class="font-medium">{{ formatDate(item.date) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                            <button @click="printSingle(idx)" class="flex-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-medium rounded transition">Print</button>
                            <button @click="downloadSingle(idx)" class="flex-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded transition">Download</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="text-6xl mb-4">📦</div>
                <p class="text-gray-500 text-sm">Belum ada barcode yang dibuat</p>
                <p class="text-gray-400 text-xs mt-1">Isi form di atas untuk generate barcode</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import JsBarcode from 'jsbarcode';
import Swal from 'sweetalert2';

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

const props = defineProps({
    suppliers: Array,
    bahanHistory: Object,
    belumLengkap: Array,
});

const savedItems = ref([]); // Not used anymore but keep for compatibility
const barcodes = ref([]);
const batchQuantity = ref(1);
const generating = ref(false);

const form = ref({
    code: '',
    kodeBahan: '',
    date: new Date().toISOString().split('T')[0],
});

// Load barcode yang belum lengkap dari database saat halaman dimuat
onMounted(() => {
    if (props.belumLengkap && props.belumLengkap.length > 0) {
        barcodes.value = props.belumLengkap.map((item) => ({
            id: item.id,
            code: item.barcode_code,
            kodeBahan: item.kode_bahan,
            date: item.tanggal,
        }));
    }
});

const generateBarcodes = async () => {
    const quantity = Math.min(Math.max(1, batchQuantity.value || 1), 100); // Between 1-100

    // Show loading
    Swal.fire({
        title: 'Sedang Generate...',
        html: `Mohon tunggu, sedang membuat ${quantity} barcode`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    generating.value = true;
    let successCount = 0;
    let failCount = 0;
    const errors = [];
    const generatedCodes = [];

    for (let i = 0; i < quantity; i++) {
        try {
            const response = await fetch('/barcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    tanggal: form.value.date,
                }),
            });

            if (!response.ok) {
                const contentType = response.headers.get('content-type');
                let errorData;

                if (contentType && contentType.includes('application/json')) {
                    errorData = await response.json();
                } else {
                    const text = await response.text();
                    throw new Error(`HTTP ${response.status}`);
                }

                throw new Error(errorData.message || `HTTP ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                barcodes.value.push({
                    id: result.data.id,
                    code: result.data.kode_bahan,
                    kodeBahan: result.data.kode_bahan,
                    date: form.value.date,
                });
                generatedCodes.push(result.data.kode_bahan);
                successCount++;
            } else {
                failCount++;
                errors.push(result.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Error saving barcode:', error);
            failCount++;
            errors.push(error.message);
        }

        // Small delay to prevent race conditions
        await new Promise((resolve) => setTimeout(resolve, 50));
    }

    await nextTick();
    generating.value = false;
    Swal.close();

    if (failCount === 0) {
        await Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            html: `
        <div class="text-center">
          <p class="mb-3">Berhasil generate ${successCount} barcode!</p>
          <div class="bg-gray-50 rounded-lg p-3 max-h-40 overflow-y-auto">
            <p class="text-xs font-semibold text-gray-700 mb-2">Kode yang dibuat:</p>
            <div class="flex flex-wrap gap-2 justify-center">
              ${generatedCodes.map((code) => `<span class="px-2 py-1 bg-amber-100 text-amber-800 rounded text-xs font-mono font-semibold">${code}</span>`).join('')}
            </div>
          </div>
        </div>
      `,
            confirmButtonColor: '#10b981',
            width: '600px',
        });
    } else {
        await Swal.fire({
            icon: failCount === quantity ? 'error' : 'warning',
            title: failCount === quantity ? 'Gagal!' : 'Sebagian Berhasil',
            html: `
        <div class="text-left">
          <p class="mb-2">Berhasil: ${successCount} barcode</p>
          <p class="mb-2">Gagal: ${failCount} barcode</p>
          ${
              errors.length > 0
                  ? `
            <div class="mt-3 p-2 bg-red-50 rounded text-sm max-h-40 overflow-y-auto">
              <p class="font-semibold mb-1">Error:</p>
              <ul class="list-disc pl-5">
                ${errors
                    .slice(0, 5)
                    .map((e) => `<li>${e}</li>`)
                    .join('')}
                ${errors.length > 5 ? `<li>... dan ${errors.length - 5} error lainnya</li>` : ''}
              </ul>
            </div>
          `
                  : ''
          }
        </div>
      `,
            confirmButtonColor: '#f59e0b',
        });
    }
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

const generateBarcodeImage = (element, code) => {
    if (!element || !code) return;
    try {
        JsBarcode(element, code, {
            format: 'CODE128',
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 12,
            margin: 5,
        });
    } catch (e) {
        console.error('Error generating barcode:', e);
    }
};

const removeBarcode = (index) => {
    barcodes.value.splice(index, 1);
};

const resetForm = () => {
    form.value = {
        code: '',
        kodeBahan: '',
        date: new Date().toISOString().split('T')[0],
    };
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const printSingle = (index) => {
    const item = barcodes.value[index];
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
    <html>
      <head>
        <title>Print Barcode - ${item.code}</title>
        <style>
          @page {
            size: 60mm 40mm;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
          }
          .barcode-page {
            width: 60mm;
            height: 40mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            page-break-after: always;
            padding: 2mm;
            box-sizing: border-box;
          }
          .barcode-page:last-child {
            page-break-after: auto;
          }
          .barcode-sticker { 
            text-align: center;
            width: 100%;
          }
          .barcode-sticker svg {
            max-width: 100%;
            height: auto;
          }
          .info-text {
            margin-top: 1mm;
            font-size: 8pt;
            line-height: 1.3;
            color: #000;
            font-weight: 600;
          }
        </style>
      </head>
      <body>
        <div class="barcode-page">
          <div class="barcode-sticker">
            <svg id="barcode1"></svg>
            <div class="info-text">${item.kodeBahan}</div>
          </div>
        </div>
        <div class="barcode-page">
          <div class="barcode-sticker">
            <svg id="barcode2"></svg>
            <div class="info-text">${item.kodeBahan}</div>
          </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <script>
          JsBarcode("#barcode1", "${item.code}", { format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0 });
          JsBarcode("#barcode2", "${item.code}", { format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0 });
          setTimeout(() => window.print(), 100);
        <\/script>
      </body>
    </html>
  `);
    printWindow.document.close();
};

const downloadSingle = (index) => {
    const item = barcodes.value[index];
    const canvas = document.createElement('canvas');
    JsBarcode(canvas, item.code, {
        format: 'CODE128',
        width: 2,
        height: 80,
        displayValue: true,
    });

    const link = document.createElement('a');
    link.download = `barcode-${item.code}.png`;
    link.href = canvas.toDataURL();
    link.click();
};

const printBarcodes = () => {
    if (barcodes.value.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Barcode',
            text: 'Belum ada barcode untuk di-print!',
            confirmButtonColor: '#f59e0b',
        });
        return;
    }

    const printWindow = window.open('', '_blank');
    let html = `
    <html>
      <head>
        <title>Print All Barcodes</title>
        <style>
          @page {
            size: 60mm 40mm;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
          }
          .barcode-page {
            width: 60mm;
            height: 40mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            page-break-after: always;
            padding: 2mm;
            box-sizing: border-box;
          }
          .barcode-page:last-child {
            page-break-after: auto;
          }
          .barcode-sticker { 
            text-align: center;
            width: 100%;
          }
          .barcode-sticker svg {
            max-width: 100%;
            height: auto;
          }
          .info-text {
            margin-top: 1mm;
            font-size: 8pt;
            line-height: 1.3;
            color: #000;
            font-weight: 600;
          }
        </style>
      </head>
      <body>
  `;

    let barcodeIdx = 0;
    barcodes.value.forEach((item) => {
        // Print 2x per barcode
        for (let copy = 0; copy < 2; copy++) {
            html += `
        <div class="barcode-page">
          <div class="barcode-sticker">
            <svg id="barcode-${barcodeIdx}"></svg>
            <div class="info-text">${item.kodeBahan}</div>
          </div>
        </div>
      `;
            barcodeIdx++;
        }
    });

    html += `
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <script>
  `;

    barcodeIdx = 0;
    barcodes.value.forEach((item) => {
        for (let copy = 0; copy < 2; copy++) {
            html += `JsBarcode("#barcode-${barcodeIdx}", "${item.code}", { format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0 });\n`;
            barcodeIdx++;
        }
    });

    html += `
          setTimeout(() => window.print(), 100);
        <\/script>
      </body>
    </html>
  `;

    printWindow.document.write(html);
    printWindow.document.close();
};
</script>

<style scoped>
.barcode-item {
    transition: all 0.2s;
}
.barcode-item:hover {
    transform: translateY(-2px);
}

@media print {
    .barcode-item {
        page-break-inside: avoid;
    }
}
</style>
