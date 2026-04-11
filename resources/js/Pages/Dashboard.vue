<template>
  <AdminLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Greeting -->
      <div>
        <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ greeting }}, selamat datang kembali.</p>
      </div>

      <!-- Omset Cards -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="col-span-2 lg:col-span-1 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl p-5 text-white shadow-md">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80">Omset Toko — Bulan Ini</p>
          <p class="text-2xl font-bold mt-1 leading-tight">{{ formatRupiah(omsetTokoBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1">All time lunas: {{ formatRupiah(omsetTokoTotal) }}</p>
        </div>
        <div class="col-span-2 lg:col-span-1 bg-gradient-to-br from-orange-400 to-orange-500 rounded-xl p-5 text-white shadow-md">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80">Omset Gudang — Bulan Ini</p>
          <p class="text-2xl font-bold mt-1 leading-tight">{{ formatRupiah(omsetGudangBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1">All time lunas: {{ formatRupiah(omsetGudangTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl p-5 text-white shadow-md">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80">Total Omset Bulan Ini</p>
          <p class="text-2xl font-bold mt-1 leading-tight">{{ formatRupiah(omsetTokoBulanIni + omsetGudangBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1">Gabungan toko + gudang</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl p-5 text-white shadow-md">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80">Total Omset All Time</p>
          <p class="text-2xl font-bold mt-1 leading-tight">{{ formatRupiah(omsetTokoTotal + omsetGudangTotal) }}</p>
          <p class="text-xs opacity-70 mt-1">Status lunas</p>
        </div>
      </div>

      <!-- Stok Cards -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Stok Bahan</p>
            <p class="text-2xl font-bold text-gray-800">{{ totalSisaBahan.toLocaleString('id-ID') }} <span class="text-sm font-normal text-gray-400">yard</span></p>
            <p class="text-xs text-gray-400">{{ stokBahan }} jenis bahan</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Stok Kantor</p>
            <p class="text-2xl font-bold text-gray-800">{{ stokKantor.toLocaleString('id-ID') }} <span class="text-sm font-normal text-gray-400">pcs</span></p>
            <p class="text-xs text-gray-400">Siap jual dari gudang</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Stok Toko</p>
            <p class="text-2xl font-bold text-gray-800">{{ stokToko.toLocaleString('id-ID') }} <span class="text-sm font-normal text-gray-400">pcs</span></p>
            <p class="text-xs text-gray-400">Siap jual dari toko</p>
          </div>
        </div>
      </div>

      <!-- Pipeline Produksi -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Pipeline Produksi <span class="text-xs font-normal text-gray-400 ml-1">(sedang berjalan)</span></h3>
        <div class="grid grid-cols-4 gap-3">
          <div v-for="stage in pipelineStages" :key="stage.key" class="rounded-xl border p-4 flex flex-col items-center gap-2" :class="stage.border">
            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="stage.bg">
              <span class="text-xl font-bold" :class="stage.color">{{ pipeline[stage.key] ?? 0 }}</span>
            </div>
            <p class="text-xs text-center font-medium text-gray-600">{{ stage.label }}</p>
            <p class="text-xs text-center" :class="(pipeline[stage.key] ?? 0) > 0 ? 'text-amber-500 font-semibold' : 'text-gray-300'">
              {{ (pipeline[stage.key] ?? 0) > 0 ? 'Sedang proses' : 'Tidak ada' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Top Model Terlaris -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-800">Model Terlaris <span class="text-xs font-normal text-gray-400 ml-1">(all time, berdasarkan pcs terjual)</span></h3>
          <span class="text-xs text-amber-500 font-medium">Top 5</span>
        </div>
        <div v-if="!topModels?.length" class="text-center py-6 text-gray-300 text-sm">Belum ada data penjualan</div>
        <div v-else class="space-y-3">
          <div
            v-for="(item, i) in topModels"
            :key="item.model"
            class="flex items-center gap-3"
          >
            <!-- Rank badge -->
            <div
              class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
              :class="i === 0 ? 'bg-amber-400 text-white' : i === 1 ? 'bg-gray-300 text-gray-700' : i === 2 ? 'bg-orange-300 text-white' : 'bg-gray-100 text-gray-400'"
            >{{ i + 1 }}</div>
            <!-- Model name + bar -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700 truncate">{{ item.model }}</span>
                <span class="text-xs text-gray-500 ml-3 shrink-0">{{ item.total_pcs.toLocaleString('id-ID') }} pcs</span>
              </div>
              <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all"
                  :class="i === 0 ? 'bg-amber-400' : i === 1 ? 'bg-gray-400' : i === 2 ? 'bg-orange-300' : 'bg-blue-200'"
                  :style="{ width: (item.total_pcs / topModels[0].total_pcs * 100) + '%' }"
                ></div>
              </div>
            </div>
            <!-- Omset -->
            <div class="text-right shrink-0">
              <p class="text-sm font-semibold text-gray-800">{{ formatRupiah(item.total_omset) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Recent Bahan Masuk -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Bahan Masuk Terbaru</h3>
            <Link href="/bahan-masuk" class="text-amber-500 hover:text-amber-600 text-xs font-medium">Lihat semua →</Link>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-5 py-2.5 text-xs text-gray-400 font-medium">Supplier</th>
                <th class="text-left px-5 py-2.5 text-xs text-gray-400 font-medium">Kode</th>
                <th class="text-right px-5 py-2.5 text-xs text-gray-400 font-medium">Yard</th>
                <th class="text-center px-5 py-2.5 text-xs text-gray-400 font-medium">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in recentBahanMasuk" :key="item.id" class="hover:bg-gray-50/60">
                <td class="px-5 py-3 text-gray-800 font-medium">{{ item.supplier }}</td>
                <td class="px-5 py-3 text-gray-500">{{ item.kode_bahan }}</td>
                <td class="px-5 py-3 text-right text-gray-700">{{ item.yard }}</td>
                <td class="px-5 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ item.status }}</span>
                </td>
              </tr>
              <tr v-if="!recentBahanMasuk?.length">
                <td colspan="4" class="px-5 py-8 text-center text-gray-300 text-sm">Belum ada data</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Recent Penjualan -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Penjualan Terbaru</h3>
            <div class="flex gap-3">
              <Link href="/proses-jual" class="text-amber-500 hover:text-amber-600 text-xs font-medium">Toko →</Link>
              <Link href="/jual-gudang" class="text-orange-500 hover:text-orange-600 text-xs font-medium">Gudang →</Link>
            </div>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-5 py-2.5 text-xs text-gray-400 font-medium">Buyer</th>
                <th class="text-left px-5 py-2.5 text-xs text-gray-400 font-medium">Model</th>
                <th class="text-right px-5 py-2.5 text-xs text-gray-400 font-medium">Total</th>
                <th class="text-center px-5 py-2.5 text-xs text-gray-400 font-medium">Channel</th>
                <th class="text-center px-5 py-2.5 text-xs text-gray-400 font-medium">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in recentPenjualan" :key="item.channel+item.id" class="hover:bg-gray-50/60">
                <td class="px-5 py-3 text-gray-800 font-medium">{{ item.buyer }}</td>
                <td class="px-5 py-3 text-gray-500 truncate max-w-24">{{ item.model }}</td>
                <td class="px-5 py-3 text-right text-gray-700 font-medium">{{ formatRupiah(item.total_harga) }}</td>
                <td class="px-5 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="item.channel === 'Toko' ? 'bg-amber-50 text-amber-600' : 'bg-orange-50 text-orange-600'">
                    {{ item.channel }}
                  </span>
                </td>
                <td class="px-5 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ item.status }}</span>
                </td>
              </tr>
              <tr v-if="!recentPenjualan?.length">
                <td colspan="5" class="px-5 py-8 text-center text-gray-300 text-sm">Belum ada data</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Alur Proses -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Alur Proses Produksi</h3>
        <div class="flex flex-wrap items-center gap-2">
          <div v-for="(step, i) in processSteps" :key="step.name" class="flex items-center gap-2">
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs shadow"
                :class="step.color">{{ i + 1 }}</div>
              <span class="text-xs text-gray-500 mt-1 text-center w-16 leading-tight">{{ step.name }}</span>
            </div>
            <svg v-if="i < processSteps.length - 1" class="w-4 h-4 text-gray-200 shrink-0 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  omsetTokoTotal:      { type: Number, default: 0 },
  omsetGudangTotal:    { type: Number, default: 0 },
  omsetTokoBulanIni:   { type: Number, default: 0 },
  omsetGudangBulanIni: { type: Number, default: 0 },
  stokBahan:           { type: Number, default: 0 },
  totalSisaBahan:      { type: Number, default: 0 },
  stokKantor:          { type: Number, default: 0 },
  stokToko:            { type: Number, default: 0 },
  pipeline:            { type: Object, default: () => ({}) },
  recentBahanMasuk:    { type: Array,  default: () => [] },
  recentPenjualan:     { type: Array,  default: () => [] },
  topModels:           { type: Array,  default: () => [] },
})

const hours = new Date().getHours()
const greeting = computed(() => hours < 12 ? 'Selamat pagi' : hours < 15 ? 'Selamat siang' : hours < 18 ? 'Selamat sore' : 'Selamat malam')

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)

const statusClass = (status) => ({
  'bg-green-100 text-green-700':  status === 'diterima' || status === 'lunas',
  'bg-yellow-100 text-yellow-700': status === 'pending',
  'bg-red-100 text-red-700':      status === 'ditolak' || status === 'batal',
})

const pipelineStages = [
  { key: 'potong',    label: 'Proses Potong',    bg: 'bg-amber-50',  color: 'text-amber-600',  border: 'border-amber-100' },
  { key: 'jahit',     label: 'Proses Jahit',     bg: 'bg-orange-50', color: 'text-orange-600', border: 'border-orange-100' },
  { key: 'cuci',      label: 'Proses Cuci',      bg: 'bg-cyan-50',   color: 'text-cyan-600',   border: 'border-cyan-100' },
  { key: 'finishing', label: 'Finishing & Pack',  bg: 'bg-purple-50', color: 'text-purple-600', border: 'border-purple-100' },
]

const processSteps = [
  { name: 'Bahan Masuk',  color: 'bg-blue-500' },
  { name: 'Bahan Keluar', color: 'bg-indigo-500' },
  { name: 'Potong',       color: 'bg-amber-500' },
  { name: 'Jahit',        color: 'bg-orange-500' },
  { name: 'Cuci',         color: 'bg-cyan-500' },
  { name: 'Finishing',    color: 'bg-purple-500' },
  { name: 'Ke Kantor',    color: 'bg-teal-500' },
  { name: 'Ke Toko',      color: 'bg-green-500' },
  { name: 'Jual',         color: 'bg-emerald-600' },
]
</script>
