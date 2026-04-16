<template>
  <AdminLayout title="Barang Siap Kirim">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-xl font-semibold text-gray-800">Barang Siap Kirim</h1>
        <p class="text-sm text-gray-500 mt-0.5">Daftar model dan jumlah pcs yang sudah selesai finishing dan siap untuk didistribusikan</p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="bg-white rounded-xl border border-emerald-100 shadow-sm px-5 py-4">
          <p class="text-xs text-emerald-600 uppercase tracking-wide font-semibold">Siap Kirim</p>
          <p class="text-2xl font-bold text-emerald-600 mt-1">{{ summary.total_siap_kirim }}</p>
          <p class="text-xs text-gray-400 mt-0.5">pcs ready</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm px-5 py-4">
          <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold">Sudah Dikirim</p>
          <p class="text-2xl font-bold text-blue-600 mt-1">{{ summary.total_sudah_kirim }}</p>
          <p class="text-xs text-gray-400 mt-0.5">pcs ke kantor</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-100 shadow-sm px-5 py-4">
          <p class="text-xs text-amber-600 uppercase tracking-wide font-semibold">Total Jadi</p>
          <p class="text-2xl font-bold text-amber-600 mt-1">{{ summary.total_jadi }}</p>
          <p class="text-xs text-gray-400 mt-0.5">total barang jadi</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm px-5 py-4">
          <p class="text-xs text-purple-600 uppercase tracking-wide font-semibold">Jenis Model</p>
          <p class="text-2xl font-bold text-purple-600 mt-1">{{ summary.total_model }}</p>
          <p class="text-xs text-gray-400 mt-0.5">model aktif</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Table Header with Search -->
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
          <div>
            <h2 class="font-semibold text-gray-800 text-sm">Daftar Barang Siap Kirim</h2>
            <p class="text-xs text-gray-400">Barang selesai finishing yang belum dikirim ke kantor</p>
          </div>
          <div class="sm:ml-auto">
            <input
              v-model="search"
              type="text"
              placeholder="Cari model..."
              class="w-full sm:w-56 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent bg-white"
            />
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <th class="px-6 py-3 text-left font-medium">No</th>
                <th class="px-6 py-3 text-left font-medium">Model</th>
                <th class="px-6 py-3 text-right font-medium">Total Barang Jadi</th>
                <th class="px-6 py-3 text-right font-medium">Sudah Dikirim</th>
                <th class="px-6 py-3 text-right font-medium">Siap Kirim (pcs)</th>
                <th class="px-6 py-3 text-right font-medium">Harga / pcs</th>
                <th class="px-6 py-3 text-right font-medium">Nilai Siap Kirim</th>
                <th class="px-6 py-3 text-center font-medium">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="filtered.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-gray-400 text-sm">Belum ada barang siap kirim.</p>
                  </div>
                </td>
              </tr>
              <tr
                v-for="(row, idx) in filtered"
                :key="row.model"
                class="hover:bg-gray-50/60 transition-colors"
              >
                <td class="px-6 py-3.5 text-gray-400 text-xs">{{ idx + 1 }}</td>
                <td class="px-6 py-3.5 font-semibold text-gray-800">{{ row.model }}</td>
                <td class="px-6 py-3.5 text-right text-gray-600">{{ row.total_jadi }}</td>
                <td class="px-6 py-3.5 text-right text-blue-500 font-medium">{{ row.sudah_kirim }}</td>
                <td class="px-6 py-3.5 text-right">
                  <span
                    class="inline-flex items-center justify-center min-w-[48px] font-bold text-base"
                    :class="row.siap_kirim > 0 ? 'text-emerald-600' : 'text-gray-400'"
                  >
                    {{ row.siap_kirim }}
                  </span>
                </td>
                <td class="px-6 py-3.5 text-right text-gray-600">
                  <span v-if="row.harga_per_pcs > 0" class="font-medium text-gray-700">
                    {{ formatRupiah(row.harga_per_pcs) }}
                  </span>
                  <span v-else class="text-gray-400 text-xs italic">-</span>
                </td>
                <td class="px-6 py-3.5 text-right">
                  <span
                    v-if="row.nilai_siap_kirim > 0"
                    class="font-semibold text-amber-700"
                  >
                    {{ formatRupiah(row.nilai_siap_kirim) }}
                  </span>
                  <span v-else class="text-gray-400 text-xs italic">-</span>
                </td>
                <td class="px-6 py-3.5 text-center">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium',
                      row.siap_kirim > 0
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-gray-100 text-gray-500'
                    ]"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="row.siap_kirim > 0 ? 'bg-emerald-500' : 'bg-gray-400'"
                    ></span>
                    {{ row.siap_kirim > 0 ? 'Siap Kirim' : 'Sudah Dikirim' }}
                  </span>
                </td>
              </tr>
            </tbody>
            <!-- Footer total -->
            <tfoot v-if="filtered.length > 0">
              <tr class="bg-gray-50 font-semibold text-gray-700 text-sm border-t border-gray-100">
                <td class="px-6 py-3" colspan="2">Total</td>
                <td class="px-6 py-3 text-right">{{ filtered.reduce((s, r) => s + r.total_jadi, 0) }}</td>
                <td class="px-6 py-3 text-right text-blue-600">{{ filtered.reduce((s, r) => s + r.sudah_kirim, 0) }}</td>
                <td class="px-6 py-3 text-right text-emerald-600">{{ filtered.reduce((s, r) => s + r.siap_kirim, 0) }}</td>
                <td></td>
                <td class="px-6 py-3 text-right text-amber-700">{{ formatRupiah(filtered.reduce((s, r) => s + r.nilai_siap_kirim, 0)) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Legend / Info -->
      <div class="bg-amber-50 border border-amber-100 rounded-xl px-5 py-4">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <p class="text-sm font-semibold text-amber-800">Keterangan</p>
            <p class="text-xs text-amber-700 mt-1 leading-relaxed">
              <strong>Siap Kirim</strong> = Total Barang Jadi (dari Proses Finishing) &minus; Sudah Dikirim ke Kantor.<br>
              Data ini diperbarui secara otomatis setiap kali ada penambahan data Finishing atau Barang Masuk Kantor.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  barangSiapKirim: { type: Array, default: () => [] },
  summary: {
    type: Object,
    default: () => ({
      total_model: 0,
      total_siap_kirim: 0,
      total_sudah_kirim: 0,
      total_jadi: 0,
    }),
  },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.barangSiapKirim
  return props.barangSiapKirim.filter(r => r.model?.toLowerCase().includes(q))
})

function formatRupiah(value) {
  if (!value || value === 0) return 'Rp 0'
  return 'Rp ' + Number(value).toLocaleString('id-ID')
}
</script>
