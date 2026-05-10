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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print Semua
        </button>
      </div>

      <!-- Form Input -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-800">Input Data Bahan Masuk</h2>
          <button 
            v-if="savedItems.length > 0"
            @click="generateAllBarcodes" 
            type="button"
            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Generate {{ savedItems.length }} Barcode
          </button>
        </div>
        
        <form @submit.prevent="addToList" class="space-y-4">
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

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                placeholder="100.50"/>
            </div>
          </div>
          
          <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Tambah ke List
            </button>
            <button type="button" @click="resetForm" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
              Reset
            </button>
          </div>
        </form>

        <!-- Saved Items Table -->
        <div v-if="savedItems.length > 0" class="mt-6 border-t border-gray-200 pt-6">
          <h3 class="text-sm font-semibold text-gray-800 mb-3">Data yang Akan Di-generate ({{ savedItems.length }} item)</h3>
          <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Kode Bahan</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                  <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                  <th class="px-3 py-2 w-16"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(item, idx) in savedItems" :key="idx" class="hover:bg-gray-50">
                  <td class="px-3 py-2 text-gray-600">{{ idx + 1 }}</td>
                  <td class="px-3 py-2 text-gray-800 font-medium">{{ item.supplier }}</td>
                  <td class="px-3 py-2 text-gray-800">{{ item.kodeBahan }}</td>
                  <td class="px-3 py-2 text-gray-600">{{ item.model }}</td>
                  <td class="px-3 py-2 text-right text-gray-800 font-medium">{{ item.quantity }} yard</td>
                  <td class="px-3 py-2 text-center">
                    <button @click="removeFromList(idx)" type="button" class="text-red-400 hover:text-red-600 transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-3 flex justify-between items-center">
            <button @click="clearList" type="button" class="text-sm text-red-600 hover:text-red-700 font-medium">
              Hapus Semua
            </button>
            <p class="text-sm text-gray-600">
              Total: <span class="font-semibold text-gray-800">{{ savedItems.length }} item</span>
            </p>
          </div>
        </div>
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
                <span class="font-medium" :class="item.pricePerYard > 0 ? '' : 'text-amber-600'">
                  {{ item.pricePerYard > 0 ? formatRupiah(item.pricePerYard) : 'Belum diisi' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Total:</span>
                <span class="font-medium" :class="item.totalPrice > 0 ? '' : 'text-amber-600'">
                  {{ item.totalPrice > 0 ? formatRupiah(item.totalPrice) : 'Belum diisi' }}
                </span>
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
import Swal from 'sweetalert2'

// Helper to get CSRF token safely
const getCsrfToken = () => {
  const meta = document.querySelector('meta[name="csrf-token"]')
  if (meta) {
    return meta.content
  }
  // Fallback: try to get from cookie
  const cookies = document.cookie.split(';')
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=')
    if (name === 'XSRF-TOKEN') {
      return decodeURIComponent(value)
    }
  }
  console.error('CSRF token not found')
  return ''
}

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
const savedItems = ref([]) // Array untuk menyimpan data sebelum generate

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

const addToList = () => {
  if (!form.value.supplier || !form.value.kodeBahan || !form.value.model || !form.value.quantity) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Supplier, Kode Bahan, Nama Bahan, dan Yard harus diisi!',
      confirmButtonColor: '#f59e0b'
    })
    return
  }

  savedItems.value.push({
    suratJalan: form.value.suratJalan,
    supplier: form.value.supplier,
    kodeBahan: form.value.kodeBahan,
    model: form.value.model,
    quantity: parseFloat(form.value.quantity) || 0,
    date: form.value.date
  })

  // Reset form kecuali supplier dan surat jalan
  form.value.kodeBahan = ''
  form.value.model = ''
  form.value.quantity = 0
  isManualInput.value = false
  
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Data berhasil ditambahkan ke list',
    timer: 1500,
    showConfirmButton: false
  })
}

const removeFromList = (index) => {
  savedItems.value.splice(index, 1)
}

const clearList = () => {
  Swal.fire({
    icon: 'warning',
    title: 'Hapus Semua Data?',
    text: 'Semua data dalam list akan dihapus',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      savedItems.value = []
      Swal.fire({
        icon: 'success',
        title: 'Terhapus!',
        text: 'Semua data berhasil dihapus',
        timer: 1500,
        showConfirmButton: false
      })
    }
  })
}

const generateAllBarcodes = async () => {
  if (savedItems.value.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Tidak Ada Data',
      text: 'Belum ada data untuk di-generate!',
      confirmButtonColor: '#f59e0b'
    })
    return
  }

  const result = await Swal.fire({
    icon: 'question',
    title: 'Generate Barcode?',
    text: `Generate ${savedItems.value.length} barcode?`,
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Generate!',
    cancelButtonText: 'Batal'
  })

  if (!result.isConfirmed) return

  // Show loading
  Swal.fire({
    title: 'Sedang Generate...',
    html: 'Mohon tunggu, sedang membuat barcode',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading()
    }
  })

  let successCount = 0
  let failCount = 0
  const errors = []

  for (const item of savedItems.value) {
    const timestamp = Date.now() + successCount // Ensure unique
    const uniqueCode = `BRC-${timestamp}`

    try {
      const response = await fetch('/barcode', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({
          barcode_code: uniqueCode,
          no_surat_jalan: item.suratJalan,
          supplier: item.supplier,
          kode_bahan: item.kodeBahan,
          nama_bahan: item.model,
          quantity: item.quantity,
          satuan: 'yard',
          rp_per_yard: null,
          tanggal: item.date
        })
      })

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Network error' }))
        throw new Error(errorData.message || `HTTP ${response.status}`)
      }

      const result = await response.json()
      
      if (result.success) {
        barcodes.value.push({
          code: uniqueCode,
          suratJalan: item.suratJalan,
          supplier: item.supplier,
          kodeBahan: item.kodeBahan,
          model: item.model,
          quantity: item.quantity,
          pricePerYard: 0,
          totalPrice: 0,
          date: item.date
        })
        successCount++
      } else {
        failCount++
        errors.push(`${item.kodeBahan}: ${result.message || 'Unknown error'}`)
      }
    } catch (error) {
      console.error('Error saving barcode:', error)
      failCount++
      errors.push(`${item.kodeBahan}: ${error.message}`)
    }

    // Small delay to ensure unique timestamps
    await new Promise(resolve => setTimeout(resolve, 10))
  }

  await nextTick()
  
  Swal.close()
  
  if (failCount === 0) {
    await Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: `Berhasil generate ${successCount} barcode!`,
      confirmButtonColor: '#10b981'
    })
    savedItems.value = []
  } else {
    await Swal.fire({
      icon: failCount === savedItems.value.length ? 'error' : 'warning',
      title: failCount === savedItems.value.length ? 'Gagal!' : 'Sebagian Berhasil',
      html: `
        <div class="text-left">
          <p class="mb-2">Berhasil: ${successCount} barcode</p>
          <p class="mb-2">Gagal: ${failCount} barcode</p>
          ${errors.length > 0 ? `
            <div class="mt-3 p-2 bg-red-50 rounded text-sm">
              <p class="font-semibold mb-1">Error:</p>
              <ul class="list-disc pl-5">
                ${errors.slice(0, 5).map(e => `<li>${e}</li>`).join('')}
                ${errors.length > 5 ? `<li>... dan ${errors.length - 5} error lainnya</li>` : ''}
              </ul>
            </div>
          ` : ''}
        </div>
      `,
      confirmButtonColor: '#f59e0b'
    })
    
    if (successCount > 0) {
      savedItems.value = []
    }
  }
}

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
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Supplier, Kode Bahan, dan Nama Bahan harus diisi!',
      confirmButtonColor: '#f59e0b'
    })
    return
  }

  // Generate unique code based on timestamp
  const timestamp = Date.now()
  const uniqueCode = `BRC-${timestamp}`

  // Parse numeric values
  const qty = parseFloat(form.value.quantity) || 0
  const price = parseNumeric(form.value.pricePerYard)
  const total = qty * price

  const barcodeData = {
    code: uniqueCode,
    suratJalan: form.value.suratJalan,
    supplier: form.value.supplier,
    kodeBahan: form.value.kodeBahan,
    model: form.value.model,
    quantity: qty,
    pricePerYard: price,
    totalPrice: total,
    date: form.value.date
  }

  // Save to database
  try {
    const response = await fetch('/barcode', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
      },
      body: JSON.stringify({
        barcode_code: uniqueCode,
        no_surat_jalan: form.value.suratJalan,
        supplier: form.value.supplier,
        kode_bahan: form.value.kodeBahan,
        nama_bahan: form.value.model,
        quantity: qty,
        satuan: 'yard',
        rp_per_yard: price > 0 ? price : null,
        tanggal: form.value.date
      })
    })

    const result = await response.json()
    
    if (result.success) {
      barcodes.value.push(barcodeData)
      await nextTick()
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Barcode berhasil disimpan!',
        timer: 1500,
        showConfirmButton: false
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Gagal menyimpan barcode: ' + result.message,
        confirmButtonColor: '#ef4444'
      })
    }
  } catch (error) {
    console.error('Error saving barcode:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Terjadi kesalahan saat menyimpan barcode',
      confirmButtonColor: '#ef4444'
    })
  }
}

const removeBarcode = (index) => {
  barcodes.value.splice(index, 1)
}

const clearAll = () => {
  Swal.fire({
    icon: 'warning',
    title: 'Hapus Semua Barcode?',
    text: 'Semua barcode yang sudah dibuat akan dihapus',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      barcodes.value = []
      Swal.fire({
        icon: 'success',
        title: 'Terhapus!',
        text: 'Semua barcode berhasil dihapus',
        timer: 1500,
        showConfirmButton: false
      })
    }
  })
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
            padding: 3mm;
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
          .info-text {
            margin-top: 1mm;
            font-size: 7pt;
            line-height: 1.3;
            color: #000;
          }
          .info-text .label {
            font-weight: 600;
          }
        </style>
      </head>
      <body>
        <div class="barcode-sticker">
          <svg id="barcode"></svg>
          <div class="info-text">
            <div><span class="label">Kode:</span> ${item.kodeBahan}</div>
            <div><span class="label">Supplier:</span> ${item.supplier}</div>
            <div><span class="label">Qty:</span> ${item.quantity} yard</div>
          </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <script>
          JsBarcode("#barcode", "${item.code}", {
            format: "CODE128",
            width: 2,
            height: 40,
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
    Swal.fire({
      icon: 'warning',
      title: 'Tidak Ada Barcode',
      text: 'Belum ada barcode untuk di-print!',
      confirmButtonColor: '#f59e0b'
    })
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
            gap: 6mm;
          }
          .barcode-item { 
            text-align: center;
            border: 1px dashed #ddd;
            padding: 4mm 8mm;
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
          .info-text {
            margin-top: 2mm;
            font-size: 9pt;
            line-height: 1.4;
            color: #000;
            text-align: center;
          }
          .info-text .label {
            font-weight: 600;
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
        <div class="info-text">
          <div><span class="label">Kode:</span> ${item.kodeBahan} | <span class="label">Supplier:</span> ${item.supplier} | <span class="label">Qty:</span> ${item.quantity} yard</div>
        </div>
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
        height: 50,
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
