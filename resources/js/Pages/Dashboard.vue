<template>
  <AdminLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Greeting -->
      <div>
        <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ greeting }}, selamat datang kembali.</p>
      </div>

      <!-- Omset Cards - Admin -->
      <div v-if="isAdmin" class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Omset Toko — Bulan Ini</p>
          <p class="text-xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetTokoBulanIni)">{{ formatRupiah(omsetTokoBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate" :title="'All time lunas: ' + formatRupiah(omsetTokoTotal)">All time lunas: {{ formatRupiah(omsetTokoTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-400 to-orange-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Omset Gudang — Bulan Ini</p>
          <p class="text-xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetGudangBulanIni)">{{ formatRupiah(omsetGudangBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate" :title="'All time lunas: ' + formatRupiah(omsetGudangTotal)">All time lunas: {{ formatRupiah(omsetGudangTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Total Omset Bulan Ini</p>
          <p class="text-xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetTokoBulanIni + omsetGudangBulanIni)">{{ formatRupiah(omsetTokoBulanIni + omsetGudangBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">Gabungan toko + gudang</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Total Omset All Time</p>
          <p class="text-xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetTokoTotal + omsetGudangTotal)">{{ formatRupiah(omsetTokoTotal + omsetGudangTotal) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">Status lunas</p>
        </div>
      </div>

      <!-- Omset Cards - Toko -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-violet-400 to-violet-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Omset Bulan Ini</p>
          <p class="text-2xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetTokoBulanIni)">{{ formatRupiah(omsetTokoBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">{{ userToko }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-400 to-red-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Pengeluaran Bulan Ini</p>
          <p class="text-2xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(pengeluaranTokoBulanIni)">{{ formatRupiah(pengeluaranTokoBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">
            <a href="/pengeluaran-toko" class="underline hover:text-white/80">Lihat detail →</a>
          </p>
        </div>
        <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Laba Bersih Bulan Ini</p>
          <p class="text-2xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetTokoBulanIni - pengeluaranTokoBulanIni)">{{ formatRupiah(omsetTokoBulanIni - pengeluaranTokoBulanIni) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">Omset - Pengeluaran</p>
        </div>
      </div>

      <!-- Omset Per Toko (Admin Only) -->
      <div v-if="isAdmin && (omsetJomei > 0 || omsetKamiko > 0)" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-pink-400 to-pink-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Omset Jomei — Bulan Ini</p>
          <p class="text-xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetJomei)">{{ formatRupiah(omsetJomei) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">Toko Jomei</p>
        </div>
        <div class="bg-gradient-to-br from-purple-400 to-purple-500 rounded-xl p-5 text-white shadow-md overflow-hidden">
          <p class="text-xs font-medium uppercase tracking-wide opacity-80 truncate">Omset Kamiko — Bulan Ini</p>
          <p class="text-xl font-bold mt-1 leading-tight truncate" :title="formatRupiah(omsetKamiko)">{{ formatRupiah(omsetKamiko) }}</p>
          <p class="text-xs opacity-70 mt-1 truncate">Toko Kamiko</p>
        </div>
      </div>
  
      <!-- Hutang & Piutang Row - Admin -->
      <div v-if="isAdmin && (sisaHutang > 0 || sisaPiutang > 0)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Hutang Bahan -->
        <div v-if="sisaHutang > 0" class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs text-red-600 font-semibold tracking-wide truncate">Hutang Bahan</p>
            <p class="text-xl font-bold text-red-700 mt-0.5 truncate">{{ formatRupiah(sisaHutang) }}</p>
            <p class="text-xs text-red-500 mt-0.5 truncate">{{ jumlahNotaBelumLunas }} nota belum lunas &mdash; <a href="/bahan-masuk" class="underline hover:text-red-700">Lihat detail →</a></p>
          </div>
        </div>

        <!-- Piutang Penjualan -->
        <div v-if="sisaPiutang > 0" class="bg-orange-50 border border-orange-200 rounded-xl px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs text-orange-600 font-semibold tracking-wide truncate">Piutang Penjualan</p>
            <p class="text-xl font-bold text-orange-700 mt-0.5 truncate">{{ formatRupiah(sisaPiutang) }}</p>
            <p class="text-xs text-orange-500 mt-0.5 truncate">{{ jumlahNotaPiutang }} nota belum lunas &mdash;
              <a href="/jual-gudang" class="underline hover:text-orange-700">Gudang</a> &middot;
              <a href="/proses-jual" class="underline hover:text-orange-700">Toko</a>
            </p>
          </div>
        </div>
      </div>

      <!-- Piutang Toko Only -->
      <div v-if="!isAdmin && sisaPiutang > 0" class="grid grid-cols-1 gap-4">
        <div class="bg-orange-50 border border-orange-200 rounded-xl px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs text-orange-600 font-semibold tracking-wide truncate">Piutang Penjualan</p>
            <p class="text-xl font-bold text-orange-700 mt-0.5 truncate">{{ formatRupiah(sisaPiutang) }}</p>
            <p class="text-xs text-orange-500 mt-0.5 truncate">{{ jumlahNotaPiutang }} nota belum lunas &mdash;
              <a href="/proses-jual" class="underline hover:text-orange-700">Lihat detail →</a>
            </p>
          </div>
        </div>
      </div>

      <!-- Stok Cards -->
      <div v-if="isAdmin" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 font-medium">Stok Bahan</p>
            <div class="flex items-baseline gap-1 mt-0.5">
              <p class="text-xl font-bold text-gray-800 truncate" :title="totalSisaBahan.toLocaleString('id-ID')">{{ totalSisaBahan.toLocaleString('id-ID') }}</p>
              <span class="text-sm font-normal text-gray-400 shrink-0">yard</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">{{ stokBahan }} jenis bahan</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 font-medium">Stok Kantor</p>
            <div class="flex items-baseline gap-1 mt-0.5">
              <p class="text-xl font-bold text-gray-800 truncate" :title="stokKantor.toLocaleString('id-ID')">{{ stokKantor.toLocaleString('id-ID') }}</p>
              <span class="text-sm font-normal text-gray-400 shrink-0">pcs</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">Siap jual dari gudang</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 font-medium">Stok Toko</p>
             <div class="flex items-baseline gap-1 mt-0.5">
              <p class="text-xl font-bold text-gray-800 truncate" :title="stokToko.toLocaleString('id-ID')">{{ stokToko.toLocaleString('id-ID') }}</p>
              <span class="text-sm font-normal text-gray-400 shrink-0">pcs</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">Siap jual dari toko</p>
          </div>
        </div>
      </div>

      <!-- Stok Toko Only (untuk user toko) -->
      <div v-else class="grid grid-cols-1 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 font-medium">Stok Toko {{ userToko }}</p>
             <div class="flex items-baseline gap-1 mt-0.5">
              <p class="text-xl font-bold text-gray-800 truncate" :title="stokToko.toLocaleString('id-ID')">{{ stokToko.toLocaleString('id-ID') }}</p>
              <span class="text-sm font-normal text-gray-400 shrink-0">pcs</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">Siap jual dari toko</p>
          </div>
        </div>
      </div>

      <!-- Kas Toko Section -->
      <div v-if="saldoKasToko && saldoKasToko.length > 0">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <!-- Saldo Kas Cards - 50% -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-semibold text-gray-800">Saldo Kas Toko</h3>
              <Link href="/kas-toko" class="text-blue-500 hover:text-blue-600 text-xs font-medium">Kelola Kas →</Link>
            </div>
            
            <div class="p-5 space-y-4">
              <div
                v-for="kas in saldoKasToko"
                :key="kas.kode_toko"
                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-100"
              >
                <div class="flex items-center justify-between mb-3">
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-green-600 mb-1">{{ kas.nama_toko }}</p>
                    <p class="text-2xl font-bold text-green-700 truncate" :title="formatRupiah(kas.saldo)">
                      {{ formatRupiah(kas.saldo) }}
                    </p>
                    <p class="text-xs text-green-500 mt-1">Total Saldo Kas</p>
                  </div>
                  <div class="p-3 bg-green-100 rounded-full shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                
                <!-- Breakdown per metode -->
                <div class="pt-3 border-t border-green-200 space-y-2">
                  <div class="flex justify-between items-center">
                    <span class="text-xs text-green-600 flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-green-500"></span>
                      Cash
                    </span>
                    <span class="text-xs font-semibold text-green-700">{{ formatRupiah(kas.saldo_cash) }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-xs text-blue-600 flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                      Transfer
                    </span>
                    <span class="text-xs font-semibold text-blue-700">{{ formatRupiah(kas.saldo_transfer) }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-xs text-purple-600 flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                      Debit
                    </span>
                    <span class="text-xs font-semibold text-purple-700">{{ formatRupiah(kas.saldo_debit) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Transaksi Terbaru - 50% -->
          <div v-if="recentKasToko && recentKasToko.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Transaksi Kas Terbaru</h3>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-2.5 text-xs text-gray-400 font-medium">Tanggal</th>
                    <th v-if="isAdmin" class="text-left px-4 py-2.5 text-xs text-gray-400 font-medium">Toko</th>
                    <th class="text-left px-4 py-2.5 text-xs text-gray-400 font-medium">Kategori</th>
                    <th class="text-right px-4 py-2.5 text-xs text-gray-400 font-medium">Jumlah</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="kas in recentKasToko" :key="kas.id" class="hover:bg-gray-50/60">
                    <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">{{ formatDate(kas.tanggal) }}</td>
                    <td v-if="isAdmin" class="px-4 py-3 text-gray-700 font-medium text-xs">{{ kas.toko }}</td>
                    <td class="px-4 py-3 text-gray-700 text-xs">{{ kas.kategori }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-xs"
                        :class="kas.jenis === 'masuk' ? 'text-green-600' : 'text-red-600'">
                      {{ kas.jenis === 'masuk' ? '+' : '-' }} {{ formatRupiah(kas.jumlah) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Kas Gudang & Garmen Section (Admin Only) -->
      <div v-if="isAdmin && (saldoKasGudang || saldoKasGarmen)" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Kas Gudang -->
        <div v-if="saldoKasGudang" class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Kas Gudang (Kantor)</h3>
            <Link href="/kas-gudang" class="text-blue-500 hover:text-blue-600 text-xs font-medium">Kelola Kas →</Link>
          </div>
          
          <div class="p-5">
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-100">
              <div class="flex items-center justify-between mb-3">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium text-blue-600 mb-1">Total Saldo Kas Gudang</p>
                  <p class="text-2xl font-bold text-blue-700 truncate" :title="formatRupiah(Number(saldoKasGudang.saldo_cash || 0) + Number(saldoKasGudang.saldo_transfer || 0) + Number(saldoKasGudang.saldo_debit || 0))">
                    {{ formatRupiah(Number(saldoKasGudang.saldo_cash || 0) + Number(saldoKasGudang.saldo_transfer || 0) + Number(saldoKasGudang.saldo_debit || 0)) }}
                  </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full shrink-0">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
              </div>
              
              <!-- Breakdown per metode -->
              <div class="pt-3 border-t border-blue-200 space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-green-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    Cash
                  </span>
                  <span class="text-xs font-semibold text-green-700">{{ formatRupiah(Number(saldoKasGudang.saldo_cash || 0)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-blue-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Transfer
                  </span>
                  <span class="text-xs font-semibold text-blue-700">{{ formatRupiah(Number(saldoKasGudang.saldo_transfer || 0)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-purple-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    Debit
                  </span>
                  <span class="text-xs font-semibold text-purple-700">{{ formatRupiah(Number(saldoKasGudang.saldo_debit || 0)) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Kas Garmen -->
        <div v-if="saldoKasGarmen" class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Kas Garmen</h3>
            <Link href="/kas-garmen" class="text-orange-500 hover:text-orange-600 text-xs font-medium">Kelola Kas →</Link>
          </div>
          
          <div class="p-5">
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-4 border border-orange-100">
              <div class="flex items-center justify-between mb-3">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium text-orange-600 mb-1">Total Saldo Kas Garmen</p>
                  <p class="text-2xl font-bold text-orange-700 truncate" :title="formatRupiah(Number(saldoKasGarmen.saldo_cash || 0) + Number(saldoKasGarmen.saldo_transfer || 0) + Number(saldoKasGarmen.saldo_debit || 0))">
                    {{ formatRupiah(Number(saldoKasGarmen.saldo_cash || 0) + Number(saldoKasGarmen.saldo_transfer || 0) + Number(saldoKasGarmen.saldo_debit || 0)) }}
                  </p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full shrink-0">
                  <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              </div>
              
              <!-- Breakdown per metode -->
              <div class="pt-3 border-t border-orange-200 space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-green-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    Cash
                  </span>
                  <span class="text-xs font-semibold text-green-700">{{ formatRupiah(Number(saldoKasGarmen.saldo_cash || 0)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-blue-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Transfer
                  </span>
                  <span class="text-xs font-semibold text-blue-700">{{ formatRupiah(Number(saldoKasGarmen.saldo_transfer || 0)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-purple-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    Debit
                  </span>
                  <span class="text-xs font-semibold text-purple-700">{{ formatRupiah(Number(saldoKasGarmen.saldo_debit || 0)) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pipeline Produksi (Admin Only) -->
      <div v-if="isAdmin" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Pipeline Produksi <span class="text-xs font-normal text-gray-400 ml-1">(total PO per stage)</span></h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div v-for="stage in pipelineStages" :key="stage.key" class="rounded-xl border p-4 flex flex-col items-center gap-2" :class="stage.border">
            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="stage.bg">
              <span class="text-xl font-bold" :class="stage.color">{{ pipeline[stage.key] ?? 0 }}</span>
            </div>
            <p class="text-xs text-center font-medium text-gray-600">{{ stage.label }}</p>
            <p class="text-xs text-center" :class="(pipeline[stage.key] ?? 0) > 0 ? 'text-amber-500 font-semibold' : 'text-gray-300'">
              {{ (pipeline[stage.key] ?? 0) > 0 ? `${pipeline[stage.key]} PO` : 'Belum ada' }}
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

      <!-- Recent Tables - Admin -->
      <div v-if="isAdmin" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
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
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in recentBahanMasuk" :key="item.id" class="hover:bg-gray-50/60">
                <td class="px-5 py-3 text-gray-800 font-medium">{{ item.supplier }}</td>
                <td class="px-5 py-3 text-gray-500">{{ item.kode_bahan }}</td>
                <td class="px-5 py-3 text-right text-gray-700">{{ item.yard }}</td>
              </tr>
              <tr v-if="!recentBahanMasuk?.length">
                <td colspan="3" class="px-5 py-8 text-center text-gray-300 text-sm">Belum ada data</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Recent Penjualan -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Penjualan Terbaru</h3>
            <div class="flex gap-3">
              <Link href="/proses-jual" class="text-amber-500 hover:text-amber-600 text-xs font-medium">Toko →</Link>
              <Link href="/jual-gudang" class="text-orange-500 hover:text-orange-600 text-xs font-medium">Gudang →</Link>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left px-3 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Buyer</th>
                  <th class="text-left px-3 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Model</th>
                  <th class="text-right px-3 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Total</th>
                  <th class="text-center px-2 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Ch.</th>
                  <th class="text-center px-2 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="item in recentPenjualan" :key="item.channel+item.id" class="hover:bg-gray-50/60">
                  <td class="px-3 py-2.5 text-gray-800 font-medium max-w-[100px] truncate">{{ item.buyer }}</td>
                  <td class="px-3 py-2.5 text-gray-500 max-w-[100px] truncate">{{ item.model }}</td>
                  <td class="px-3 py-2.5 text-right text-gray-700 font-medium whitespace-nowrap">{{ formatRupiah(item.total_harga) }}</td>
                  <td class="px-2 py-2.5 text-center">
                    <span class="px-1.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap"
                      :class="item.channel === 'Toko' ? 'bg-amber-50 text-amber-600' : 'bg-orange-50 text-orange-600'">
                      {{ item.channel }}
                    </span>
                  </td>
                  <td class="px-2 py-2.5 text-center">
                    <span class="px-1.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap" :class="statusClass(item.status)">{{ item.status }}</span>
                  </td>
                </tr>
                <tr v-if="!recentPenjualan?.length">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-300 text-sm">Belum ada data</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Recent Penjualan - Toko Only -->
      <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-800">Penjualan Terbaru</h3>
          <Link href="/proses-jual" class="text-violet-500 hover:text-violet-600 text-xs font-medium">Lihat semua →</Link>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-4 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Tanggal</th>
                <th class="text-left px-4 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Buyer</th>
                <th class="text-left px-4 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Model</th>
                <th class="text-center px-4 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Pcs</th>
                <th class="text-right px-4 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Total</th>
                <th class="text-center px-4 py-2.5 text-xs text-gray-400 font-medium whitespace-nowrap">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in recentPenjualan" :key="item.id" class="hover:bg-violet-50/40">
                <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">{{ formatDate(item.tanggal_nota) }}</td>
                <td class="px-4 py-3 text-gray-800 font-medium max-w-[120px] truncate">{{ item.buyer }}</td>
                <td class="px-4 py-3 text-gray-600 max-w-[120px] truncate">{{ item.model }}</td>
                <td class="px-4 py-3 text-center font-semibold text-violet-600">{{ item.pcs }}</td>
                <td class="px-4 py-3 text-right text-gray-700 font-semibold whitespace-nowrap">{{ formatRupiah(item.total_harga) }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap" :class="statusClass(item.status)">{{ item.status }}</span>
                </td>
              </tr>
              <tr v-if="!recentPenjualan?.length">
                <td colspan="6" class="px-4 py-12 text-center text-gray-300 text-sm">Belum ada data penjualan</td>
              </tr>
            </tbody>
          </table>
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
  sisaHutang:           { type: Number, default: 0 },
  jumlahNotaBelumLunas: { type: Number, default: 0 },
  sisaPiutang:          { type: Number, default: 0 },
  jumlahNotaPiutang:    { type: Number, default: 0 },
  omsetTokoTotal:      { type: Number, default: 0 },
  omsetGudangTotal:    { type: Number, default: 0 },
  omsetTokoBulanIni:   { type: Number, default: 0 },
  omsetGudangBulanIni: { type: Number, default: 0 },
  omsetJomei:          { type: Number, default: 0 },
  omsetKamiko:         { type: Number, default: 0 },
  stokBahan:           { type: Number, default: 0 },
  totalSisaBahan:      { type: Number, default: 0 },
  stokKantor:          { type: Number, default: 0 },
  stokToko:            { type: Number, default: 0 },
  pipeline:            { type: Object, default: () => ({}) },
  recentBahanMasuk:    { type: Array,  default: () => [] },
  recentPenjualan:     { type: Array,  default: () => [] },
  topModels:           { type: Array,  default: () => [] },
  isAdmin:             { type: Boolean, default: false },
  userToko:            { type: String, default: null },
  pengeluaranTokoBulanIni: { type: Number, default: 0 },
  saldoKasToko:        { type: Array, default: () => [] },
  recentKasToko:       { type: Array, default: () => [] },
  saldoKasGudang:      { type: Object, default: null },
  saldoKasGarmen:      { type: Object, default: null },
})

const hours = new Date().getHours()
const greeting = computed(() => hours < 12 ? 'Selamat pagi' : hours < 15 ? 'Selamat siang' : hours < 18 ? 'Selamat sore' : 'Selamat malam')

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

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
