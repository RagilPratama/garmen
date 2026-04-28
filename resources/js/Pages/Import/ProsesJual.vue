<template>
  <AdminLayout title="Import Proses Jual">
    <div class="max-w-4xl mx-auto py-6 px-4">
      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Import Data Proses Jual</h1>

        <div class="mb-6 p-4 bg-blue-50 rounded">
          <h3 class="font-semibold mb-2">Format Excel:</h3>
          <ul class="text-sm space-y-1 list-disc list-inside">
            <li>no_nota, tanggal_nota, buyer, model, pcs, harga_satuan, diskon, total_harga, status</li>
            <li>tanggal_bayar, jumlah_bayar, metode_bayar (cash/transfer/debit), keterangan_bayar</li>
            <li>Status: piutang / lunas / batal</li>
            <li>Jika ada pembayaran, akan otomatis masuk ke kas toko</li>
          </ul>
          <div class="flex gap-3 mt-3">
            <a 
              :href="route('import.proses-jual.template')" 
              class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
              Download Template
            </a>
            <a 
              :href="route('import.proses-jual.export-models')" 
              class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Export Nama Model
            </a>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Toko</label>
            <select v-model="form.toko_id" required class="w-full border rounded px-3 py-2">
              <option value="">Pilih Toko</option>
              <option v-for="toko in tokos" :key="toko.id" :value="toko.id">
                {{ toko.nama_toko }}
              </option>
            </select>
            <div v-if="errors.toko_id" class="text-red-600 text-sm mt-1">{{ errors.toko_id }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">File Excel</label>
            <input 
              type="file" 
              @change="handleFile" 
              accept=".xlsx,.xls,.csv"
              required
              class="w-full border rounded px-3 py-2"
            >
            <div v-if="errors.file" class="text-red-600 text-sm mt-1">{{ errors.file }}</div>
          </div>

          <div class="flex gap-3">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Importing...' : 'Import' }}
            </button>
            <Link 
              :href="route('proses-jual.index')" 
              class="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400"
            >
              Batal
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  tokos: Array,
  models: Array,
  errors: Object,
})

const form = useForm({
  toko_id: '',
  file: null,
})

const handleFile = (e) => {
  form.file = e.target.files[0]
}

const submit = () => {
  form.post(route('import.proses-jual.store'))
}
</script>
