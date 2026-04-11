<template>
  <AdminLayout title="Stok Bahan">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Stok Bahan</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ data?.total ?? 0 }} kode bahan terdaftar</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
          <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Stok otomatis terupdate dari Bahan Masuk & Bahan Keluar
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium w-10">No</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Kode Bahan</th>
              <th class="text-right px-4 py-3 text-gray-500 font-medium">Total Masuk (yard)</th>
              <th class="text-right px-4 py-3 text-gray-500 font-medium">Total Keluar (yard)</th>
              <th class="text-right px-4 py-3 text-gray-500 font-medium">Sisa Stok (yard)</th>
              <th class="text-center px-4 py-3 text-gray-500 font-medium">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(item, index) in data?.data" :key="item.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + index + 1 }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ item.kode_bahan }}</td>
              <td class="px-4 py-3 text-right text-green-700">{{ formatYard(item.total_masuk) }}</td>
              <td class="px-4 py-3 text-right text-red-600">{{ formatYard(item.total_keluar) }}</td>
              <td class="px-4 py-3 text-right">
                <span class="font-semibold" :class="item.sisa_stok > 0 ? 'text-amber-700' : 'text-red-500'">
                  {{ formatYard(item.sisa_stok) }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                  :class="item.sisa_stok > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                  {{ item.sisa_stok > 0 ? 'Tersedia' : 'Habis' }}
                </span>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                <p class="text-sm">Belum ada data stok. Tambahkan data Bahan Masuk.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">Menampilkan {{ data.from }}-{{ data.to }} dari {{ data.total }} data</p>
        <div class="flex items-center gap-1">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1.5 text-sm rounded-lg transition-colors"
            :class="link.active ? 'bg-amber-500 text-white' : 'text-gray-600 hover:bg-gray-100'"
            :preserve-scroll="true"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ data: Object })

const formatYard = (val) => Number(val ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
</script>
