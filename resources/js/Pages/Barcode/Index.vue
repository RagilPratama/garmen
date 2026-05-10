<template>
  <AdminLayout title="Barcode Generator">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-800">Barcode Generator</h1>
          <p class="text-sm text-gray-500 mt-0.5">Generate barcode untuk tracking bahan masuk (Demo)</p>
        </div>
        <button @click="printBarcodes" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print Semua
        </button>
      </div>

      <!-- Form Input -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Input Data Bahan Masuk</h2>
        <form @submit.prevent="generateBarcode" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
              <input v-model="form.suratJalan" type="text"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                placeholder="Contoh: SJ-2024-001"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
              <select v-model="form.supplier" @change="onSupplierChange" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white">
                <option value="" disabled>-- Pilih Supplier --</option>
                <option v-for="opt in supplierOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <!-- Bahan History Chips -->
          <div v-if="form.supplier && bahanOptions.length > 0 && !isManualInput" class="space-y-2">
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium text-gray-500">
                Bahan pernah dipesan dari <span class="text-amber-600 font-semibold">{{ form.supplier }}</span> — klik untuk pilih:
              </p>
              <button type="button" @click="enableManualInput"
                class="text-xs text-blue-600 hover:text-blue-700 font-medium underline">
                Input Manual
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="bahan in bahanOptions" :key="bahan.kode"
                type="button"
                @click="selectBahanFromHistory(bahan)"
                :class="form.kodeBahan === bahan.kode ? 'bg-amber-100 border-amber-300 text-amber-800' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border rounded-full transition-colors"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                {{ bahan.kode }}
                <span v-if="bahan.nama" class="text-amber-600">— {{ bahan.nama }}</span>
              </button>
            </div>
          </div>

          <!-- Manual Input Notice -->
          <div v-if="isManualInput" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
            <p class="text-xs text-blue-700">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Mode input manual aktif
            </p>
            <button type="button" @click="isManualInput = false"
              class="text-xs text-blue-600 hover:text-blue-700 font-medium underline">
              Kembali ke Pilihan
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan <span class="text-red-500">*</span></label>
              <input v-model="form.kodeBahan" type="text" required
                :readonly="!isManualInput && bahanOptions.length > 0"
                :class="!isManualInput && bahanOptions.length > 0 ? 'bg-gray-50 cursor-not-allowed' : 'bg-white'"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition"
                placeholder="Contoh: KB-001"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan <span class="text-red-500">*</span></label>
              <input v-model="form.model" type="text" required
                :readonly="!isManualInput && bahanOptions.length > 0 && form.kodeBahan"
                :class="!isManualInput && bahanOptions.length > 0 && form.kodeBahan ? 'bg-gray-50' : 'bg-white'"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition"
                placeholder="Contoh: Kain Katun Combed 30s"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Yard <span class="text-red-500">*</span></label>
              <input v-model.number="form.quantity" type="number" min="0" step="0.01" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                placeholder="100.50" @input="calculateTotal"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rp/Yard <span class="text-red-500">*</span></label>
              <input 
                :value="formatInputPrice(form.pricePerYard)" 
                @input="handlePriceInput"
                type="text" 
                inputmode="numeric" 
                required
                :readonly="!isManualInput && bahanOptions.length > 0 && form.kodeBahan"
                :class="!isManualInput && bahanOptions.length > 0 && form.kodeBahan ? 'bg-gray-50' : 'bg-white'"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition"
                placeholder="50.000"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga (otomatis)</label>
              <input :value="formatRupiah(totalPrice)" type="text" readonly
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-600 cursor-not-allowed"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Masuk</label>
              <input v-model="form.date" type="date"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            </div>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Generate Barcode
            </button>
            <button type="button" @click="resetForm" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Generated Barcodes -->
      <div v-if="barcodes.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-800">Barcode yang Sudah Dibuat ({{ barcodes.length }})</h2>
          <button @click="clearAll" class="text-sm text-red-600 hover:text-red-700 font-medium">
            Hapus Semua
          </button>
        </div>
        
        <!-- Barcode Grid -->
        <div id="barcode-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="(item, idx) in barcodes" :key="idx" class="barcode-item border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition">
            <div class="flex justify-between items-start mb-3">
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800">{{ item.model }}</p>
                <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ item.code }}</p>
              </div>
              <button @click="removeBarcode(idx)" class="text-gray-400 hover:text-red-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            
            <!-- Barcode SVG -->
            <div class="flex justify-center mb-3 bg-white p-2">
              <svg :ref="el => { if (el) generateBarcodeImage(el, item.code) }"></svg>
            </div>
            
            <!-- Info -->
            <div class="space-y-1 text-xs text-gray-600 border-t border-gray-100 pt-3">
              <div class="flex justify-between">
                <span class="text-gray-500">Barcode:</span>
                <span class="font-medium font-mono text-xs">{{ item.code }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Supplier:</span>
                <span class="font-medium">{{ item.supplier || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Kode Bahan:</span>
                <span class="font-medium">{{ item.kodeBahan || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">No. Surat Jalan:</span>
                <span class="font-medium">{{ item.suratJalan || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Qty:</span>
                <span class="font-medium">{{ item.quantity }} yard</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Harga/Yard:</span>
                <span class="font-medium">{{ formatRupiah(item.pricePerYard) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Total:</span>
                <span class="font-medium">{{ formatRupiah(item.totalPrice) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Tanggal:</span>
                <span class="font-medium">{{ formatDate(item.date) }}</span>
              </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
              <button @click="printSingle(idx)" class="flex-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-medium rounded transition">
                Print
              </button>
              <button @click="downloadSingle(idx)" class="flex-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded transition">
                Download
              </button>
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
import { ref, onMounted, nextTick, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import JsBarcode from 'jsbarcode'

const props = defineProps({
  suppliers: Array,
  bahanHistory: Object
})

const supplierOptions = computed(() => {
  return props.suppliers?.map(s => ({ value: s.nama, label: s.nama })) || []
})

// Get bahan options based on selected supplier
const bahanOptions = computed(() => {
  if (!form.value.supplier || !props.bahanHistory) return []
  const history = props.bahanHistory[form.value.supplier]
  if (!history) return []
  return history.map(item => ({
    kode: item.kode_bahan,
    nama: item.nama_bahan,
    harga: item.rp_per_yard
  }))
})

const showBahanDropdown = ref(false)
const isManualInput = ref(false)

const form = ref({
  code: '',
  suratJalan: '',
  supplier: '',
  kodeBahan: '',
  model: '',
  quantity: 0,
  pricePerYard: 0,
  date: new Date().toISOString().split('T')[0]
})

const totalPrice = ref(0)

const onSupplierChange = () => {
  // Reset bahan fields when supplier changes
  form.value.kodeBahan = ''
  form.value.model = ''
  form.value.pricePerYard = 0
  isManualInput.value = false
  showBahanDropdown.value = false
  calculateTotal()
}

const selectBahanFromHistory = (bahan) => {
  form.value.kodeBahan = bahan.kode
  form.value.model = bahan.nama || ''
  // Store as numeric value directly, not formatted
  form.value.pricePerYard = parseInt(bahan.harga) || 0
  showBahanDropdown.value = false
  isManualInput.value = false
  calculateTotal()
}

const enableManualInput = () => {
  isManualInput.value = true
  showBahanDropdown.value = false
  form.value.kodeBahan = ''
  form.value.model = ''
  form.value.pricePerYard = 0
  calculateTotal()
}

const parseNumeric = (value) => {
  if (typeof value === 'number') return value
  if (!value) return 0
  // Remove all non-digit characters except decimal point
  const cleaned = String(value).replace(/[^\d]/g, '')
  return parseInt(cleaned) || 0
}

const formatInputPrice = (value) => {
  if (!value && value !== 0) return ''
  // If already a number, format directly
  if (typeof value === 'number') {
    return new Intl.NumberFormat('id-ID').format(value)
  }
  // If string, parse then format
  const num = parseNumeric(value)
  if (!num) return ''
  return new Intl.NumberFormat('id-ID').format(num)
}

const handlePriceInput = (e) => {
  // Skip if readonly (from history)
  if (e.target.readOnly) return
  
  const input = e.target
  const cursorPos = input.selectionStart
  const oldValue = input.value
  const oldLength = oldValue.length
  
  // Get only numbers
  const numericValue = parseNumeric(input.value)
  
  // Update form value with numeric
  form.value.pricePerYard = numericValue
  
  // Format for display
  const formattedValue = formatInputPrice(numericValue)
  input.value = formattedValue
  
  // Restore cursor position
  const newLength = formattedValue.length
  const diff = newLength - oldLength
  const newCursorPos = Math.max(0, cursorPos + diff)
  input.setSelectionRange(newCursorPos, newCursorPos)
  
  // Calculate total
  calculateTotal()
}

const calculateTotal = () => {
  const qty = parseFloat(form.value.quantity) || 0
  const price = parseNumeric(form.value.pricePerYard)
  totalPrice.value = qty * price
}

const barcodes = ref([])

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)

const generateBarcodeImage = (element, code) => {
  if (!element || !code) return
  try {
    JsBarcode(element, code, {
      format: 'CODE128',
      width: 2,
      height: 60,
      displayValue: true,
      fontSize: 12,
      margin: 5
    })
  } catch (e) {
    console.error('Error generating barcode:', e)
  }
}

const generateBarcode = async () => {
  if (!form.value.model || !form.value.supplier || !form.value.kodeBahan) {
    alert('Supplier, Kode Bahan, dan Nama Bahan harus diisi!')
    return
  }

  // Generate unique code based on timestamp
  const timestamp = Date.now()
  const uniqueCode = `BRC-${timestamp}`

  // Parse numeric values
  const qty = parseFloat(form.value.quantity) || 0
  const price = parseNumeric(form.value.pricePerYard)
  const total = qty * price

  barcodes.value.push({
    code: uniqueCode,
    suratJalan: form.value.suratJalan,
    supplier: form.value.supplier,
    kodeBahan: form.value.kodeBahan,
    model: form.value.model,
    quantity: qty,
    pricePerYard: price,
    totalPrice: total,
    date: form.value.date
  })

  await nextTick()
}

const removeBarcode = (index) => {
  barcodes.value.splice(index, 1)
}

const clearAll = () => {
  if (confirm('Hapus semua barcode yang sudah dibuat?')) {
    barcodes.value = []
  }
}

const resetForm = () => {
  form.value = {
    code: '',
    suratJalan: '',
    supplier: '',
    kodeBahan: '',
    model: '',
    quantity: 0,
    pricePerYard: 0,
    date: new Date().toISOString().split('T')[0]
  }
  totalPrice.value = 0
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const printSingle = (index) => {
  const item = barcodes.value[index]
  const printWindow = window.open('', '_blank')
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
            padding: 5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 40mm;
          }
          .barcode-sticker { 
            text-align: center;
            width: 100%;
          }
          .barcode-sticker svg {
            max-width: 100%;
            height: auto;
          }
          .barcode-code {
            margin-top: 2mm;
            font-size: 10pt;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
          }
        </style>
      </head>
      <body>
        <div class="barcode-sticker">
          <svg id="barcode"></svg>
          <div class="barcode-code">${item.code}</div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <script>
          JsBarcode("#barcode", "${item.code}", {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: false,
            margin: 0
          });
          setTimeout(() => window.print(), 100);
        <\/script>
      </body>
    </html>
  `)
  printWindow.document.close()
}

const downloadSingle = (index) => {
  const item = barcodes.value[index]
  const canvas = document.createElement('canvas')
  JsBarcode(canvas, item.code, {
    format: 'CODE128',
    width: 2,
    height: 80,
    displayValue: true
  })
  
  const link = document.createElement('a')
  link.download = `barcode-${item.code}.png`
  link.href = canvas.toDataURL()
  link.click()
}

const printBarcodes = () => {
  if (barcodes.value.length === 0) {
    alert('Belum ada barcode untuk di-print!')
    return
  }

  const printWindow = window.open('', '_blank')
  let html = `
    <html>
      <head>
        <title>Print All Barcodes</title>
        <style>
          @page {
            size: A4 portrait;
            margin: 15mm 10mm;
          }
          body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
          }
          .barcode-list {
            display: flex;
            flex-direction: column;
            gap: 8mm;
          }
          .barcode-item { 
            text-align: center;
            border: 1px dashed #ddd;
            padding: 5mm 10mm;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
          }
          .barcode-item svg {
            max-width: 100%;
            height: auto;
          }
          .barcode-code {
            margin-top: 3mm;
            font-size: 11pt;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 1.5px;
            color: #000;
          }
          @media print {
            .barcode-item {
              border-color: #eee;
            }
          }
        </style>
      </head>
      <body>
        <div class="barcode-list">
  `

  barcodes.value.forEach((item, idx) => {
    html += `
      <div class="barcode-item">
        <svg id="barcode-${idx}"></svg>
        <div class="barcode-code">${item.code}</div>
      </div>
    `
  })

  html += `
        </div>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <script>
  `

  barcodes.value.forEach((item, idx) => {
    html += `
      JsBarcode("#barcode-${idx}", "${item.code}", {
        format: "CODE128",
        width: 2,
        height: 60,
        displayValue: false,
        margin: 5,
        fontSize: 14,
        textMargin: 5
      });
    `
  })

  html += `
          setTimeout(() => window.print(), 100);
        <\/script>
      </body>
    </html>
  `

  printWindow.document.write(html)
  printWindow.document.close()
}
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
