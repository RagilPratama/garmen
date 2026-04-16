<template>
  <AdminLayout title="Jual dari Toko">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-violet-400 via-purple-500 to-violet-400"></div>

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-800">Jual dari Toko</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ data?.total ?? 0 }} Nota</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="searchQuery" type="text" placeholder="Cari nota / buyer / model..."
              class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent w-56 transition-all"/>
            <button v-if="searchQuery" @click="searchQuery = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
              <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-600 hover:to-purple-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Data
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Nota</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tgl Nota</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Buyer</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Harga</th>
              <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sisa Piutang</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(group, index) in data?.data" :key="group.no_nota"
              class="border-b border-gray-50 hover:bg-violet-50/40 transition-colors group cursor-pointer"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
              @click="openDetail(group)">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-violet-100 group-hover:text-violet-700 transition-colors">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-mono text-xs bg-violet-50 text-violet-700 border border-violet-100 px-2 py-0.5 rounded">{{ group.no_nota || '—' }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-600">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(group.tanggal_nota) }}
                </span>
              </td>
              <td class="px-5 py-3.5 font-medium text-gray-700">{{ group.buyer }}</td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full"
                  :class="group.sisa_piutang > 0 ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700'">
                  {{ group.sisa_piutang > 0 ? 'Belum Lunas' : 'Lunas' }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right font-semibold text-gray-700">{{ formatRupiah(group.total_harga) }}</td>
              <td class="px-5 py-3.5 text-right">
                <span v-if="group.sisa_piutang > 0" class="text-xs font-semibold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-full">
                  {{ formatRupiah(group.sisa_piutang) }}
                </span>
                <span v-else class="text-xs text-emerald-600 font-semibold">✓ Lunas</span>
              </td>
              <td class="px-5 py-3.5 text-center" @click.stop>
                <button @click="openDetail(group)"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Detail
                </button>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="7" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                  </div>
                  <p class="text-sm font-medium text-gray-500">{{ searchQuery ? 'Data tidak ditemukan' : 'Belum ada data' }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.last_page > 1" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-700">{{ data.from }}–{{ data.to }}</span> dari <span class="font-semibold text-gray-700">{{ data.total }}</span> Nota</p>
        <div class="flex items-center gap-1 flex-wrap justify-center">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url ? appendSearch(link.url) : '#'"
            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
            :class="link.active ? 'bg-violet-500 text-white font-semibold shadow-sm' : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200' : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true" v-html="link.label"/>
        </div>
      </div>
    </div>

    <!-- DETAIL MODAL -->
    <Modal v-model="showDetail" :title="'Detail Nota: ' + (detailGroup?.no_nota ?? '—')" size="xl">
      <div v-if="detailGroup" class="space-y-4">
        <!-- Header info -->
        <div class="grid grid-cols-2 gap-3 bg-gray-50 rounded-xl p-4 text-sm">
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Buyer</p>
            <p class="font-medium text-gray-800">{{ detailGroup.buyer }}</p>
          </div>
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Tanggal Nota</p>
            <p class="font-medium text-gray-800">{{ formatDate(detailGroup.tanggal_nota) }}</p>
          </div>
        </div>

        <!-- Models table -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Model</th>
                <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Pcs</th>
                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Harga Satuan</th>
                <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Diskon</th>
                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Total</th>
                <th class="w-16"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="mRow in detailGroup.models" :key="mRow.id" class="hover:bg-violet-50/30 transition-colors">
                <td class="px-4 py-2.5 font-medium text-gray-700">{{ mRow.model }}</td>
                <td class="px-4 py-2.5 text-center font-semibold text-violet-700">{{ mRow.pcs }}</td>
                <td class="px-4 py-2.5 text-right text-gray-600">{{ formatRupiah(mRow.harga_satuan) }}</td>
                <td class="px-4 py-2.5 text-center">
                  <span v-if="mRow.diskon > 0" class="text-xs font-medium text-red-500 bg-red-50 px-1.5 py-0.5 rounded">{{ mRow.diskon }}%</span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-2.5 text-right font-semibold text-gray-700">{{ formatRupiah(mRow.total_harga) }}</td>
                <td class="px-4 py-2.5 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button @click="openEdit(mRow)" class="text-blue-400 hover:text-blue-600 transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button @click="confirmDelete(mRow.id)" class="text-red-400 hover:text-red-600 transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
              <tr>
                <td colspan="4" class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Grand Total</td>
                <td class="px-4 py-2.5 text-right font-bold text-violet-600">{{ formatRupiah(detailGroup.total_harga) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Payment Summary -->
        <div class="grid grid-cols-3 gap-3 border border-gray-200 rounded-xl p-4 text-sm text-center">
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Total Tagihan</p>
            <p class="font-bold text-gray-800">{{ formatRupiah(detailGroup.total_harga) }}</p>
          </div>
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Sudah Dibayar</p>
            <p class="font-bold text-emerald-600">{{ formatRupiah(detailGroup.total_bayar) }}</p>
          </div>
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Sisa Piutang</p>
            <p class="font-bold" :class="detailGroup.sisa_piutang > 0 ? 'text-orange-600' : 'text-emerald-600'">
              {{ detailGroup.sisa_piutang <= 0 ? 'LUNAS' : formatRupiah(detailGroup.sisa_piutang) }}
            </p>
          </div>
        </div>

        <!-- Riwayat Pembayaran -->
        <div v-if="detailGroup.pembayarans?.length" class="border border-gray-200 rounded-lg overflow-hidden">
          <div class="px-3 py-2 bg-gray-50 border-b border-gray-200">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Riwayat Pembayaran</span>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Tanggal</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Metode</th>
                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Jumlah</th>
                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Keterangan</th>
                <th class="px-3 py-2 w-8"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in detailGroup.pembayarans" :key="p.id" class="hover:bg-gray-50/50">
                <td class="px-3 py-2">{{ formatDate(p.tanggal_bayar) }}</td>
                <td class="px-3 py-2">
                  <span :class="p.metode === 'cash' ? 'bg-green-100 text-green-700' : p.metode === 'transfer' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                    {{ p.metode }}
                  </span>
                  <span v-if="p.metode === 'transfer' && p.rekening_id" class="ml-1.5 text-xs text-gray-500">
                    {{ rekeningLabel(p.rekening_id) }}
                  </span>
                </td>
                <td class="px-3 py-2 text-right font-medium text-gray-800">{{ formatRupiah(p.jumlah) }}</td>
                <td class="px-3 py-2 text-gray-500 text-xs">{{ p.keterangan ?? '—' }}</td>
                <td class="px-3 py-2 text-center">
                  <button @click="deletePembayaran(p.id)" type="button"
                    class="text-red-400 hover:text-red-600 transition" title="Hapus riwayat">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer buttons -->
        <div class="flex justify-between pt-1">
          <button @click="openPay"
            :disabled="detailGroup.sisa_piutang <= 0"
            class="px-5 py-2.5 text-sm text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Bayar
          </button>
          <button @click="showDetail = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Tutup</button>
        </div>
      </div>
    </Modal>

    <!-- PAYMENT MODAL -->
    <Modal v-model="showPayModal" :title="'Bayar — ' + (detailGroup?.no_nota ?? '')">
      <form v-if="detailGroup" @submit.prevent="submitPembayaran" class="space-y-4">
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm">
          Sisa piutang:
          <span class="font-bold text-orange-600">{{ formatRupiah(detailGroup.sisa_piutang) }}</span>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
          <input v-model="payForm.tanggal_bayar" type="date" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
          <input v-model.number="payForm.jumlah" type="number" min="1" :max="detailGroup.sisa_piutang" required
            placeholder="0"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Metode <span class="text-red-500">*</span></label>
          <div class="flex gap-4 mt-1">
            <label v-for="m in ['cash','transfer','debit']" :key="m" class="flex items-center gap-2 cursor-pointer">
              <input v-model="payForm.metode" type="radio" :value="m" class="text-violet-500"/>
              <span class="text-sm text-gray-700 capitalize">{{ m }}</span>
            </label>
          </div>
        </div>
        <!-- Rekening (hanya jika transfer) -->
        <div v-if="payForm.metode === 'transfer'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Rekening Tujuan <span class="text-red-500">*</span></label>
          <div v-if="!rekeningOptions.length" class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Belum ada data master rekening.
          </div>
          <select v-else v-model="payForm.rekening_id" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white">
            <option value="" disabled>-- Pilih rekening --</option>
            <option v-for="r in rekeningOptions" :key="r.id" :value="r.id">
              {{ r.bank }} — {{ r.nama }}{{ r.nomor_rekening ? ' (' + r.nomor_rekening + ')' : '' }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
          <input v-model="payForm.keterangan" type="text" placeholder="Opsional..."
            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showPayModal = false"
            class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
          <button type="submit" :disabled="payLoading"
            class="px-5 py-2.5 text-sm text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
            <svg v-if="payLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Simpan Pembayaran
          </button>
        </div>
      </form>
    </Modal>

    <!-- CREATE MODAL -->
    <Modal v-model="showModal" title="Tambah Jual Toko" size="xl">
      <div v-if="!stokOptions.length" class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Stok toko kosong. Kirimkan barang ke toko terlebih dahulu.
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Header fields -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. Nota</label>
            <div class="relative">
              <input v-model="noNota" type="text"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white pr-16"
                placeholder="NT-TOKO-001"/>
              <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-violet-100 text-violet-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Nota <span class="text-red-500">*</span></label>
            <input v-model="tanggalNota" type="date" required
              class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buyer <span class="text-red-500">*</span></label>
            <input v-model="buyer" type="text" required
              class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"
              placeholder="Nama toko / pembeli"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select v-model="status" required
              class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white">
              <option value="pending">Pending</option>
              <option value="lunas">Lunas</option>
              <option value="batal">Batal</option>
            </select>
          </div>
        </div>

        <!-- Stock checklist -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-gray-700">Pilih Stok Model <span class="text-red-500">*</span></label>
            <span v-if="checkedItems.size > 0" class="text-xs font-semibold text-violet-600 bg-violet-50 border border-violet-200 px-2 py-0.5 rounded-full">
              {{ checkedItems.size }} model · {{ totalPcsSelected }} pcs
            </span>
          </div>
          <div class="relative mb-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="modalSearch" type="text" placeholder="Filter model..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition"/>
          </div>
          <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100">
            <label v-for="item in filteredOptions" :key="item.model"
              class="flex items-center gap-3 px-3 py-2.5 hover:bg-violet-50/50 cursor-pointer transition-colors"
              :class="checkedItems.has(item.model) ? 'bg-violet-50/60' : ''">
              <input type="checkbox" 
                :checked="checkedItems.has(item.model)" 
                @change="(e) => toggleItem(item, e)"
                class="w-4 h-4 rounded border-gray-300 text-violet-500 focus:ring-violet-400 shrink-0"/>
              <span class="text-sm font-medium text-gray-700 flex-1 min-w-0 truncate">{{ item.model }}</span>
              <span class="text-xs text-gray-400 shrink-0">stok: {{ item.sisa_stok }} pcs</span>
              <template v-if="checkedItems.has(item.model)">
                <input :value="formatRupiah(hargaPerItem[item.model] || 0)" type="text" disabled
                  class="w-28 px-2 py-1.5 border border-violet-200 bg-gray-100 rounded-lg text-sm text-gray-500 text-right shrink-0 cursor-not-allowed"
                  placeholder="harga/pcs"/>
                <input v-model.number="pcsPerItem[item.model]" type="number" min="1" :max="item.sisa_stok"
                  @click.stop
                  class="w-20 px-2 py-1.5 border border-violet-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-white text-right shrink-0"
                  placeholder="pcs"/>
              </template>
            </label>
            <div v-if="!filteredOptions.length" class="px-4 py-8 text-center text-sm text-gray-400">
              Tidak ada stok toko yang tersedia
            </div>
          </div>
          <!-- Total -->
          <div v-if="subtotal > 0" class="mt-3 flex flex-col gap-1">
            <div class="flex items-center justify-between px-4 py-2 bg-violet-50 border border-violet-200 rounded-lg">
              <span class="text-sm font-medium text-violet-700">Subtotal:</span>
              <span class="text-base font-semibold text-violet-800">{{ formatRupiah(subtotal) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
              <span class="text-sm font-medium text-gray-700">Diskon:</span>
              <div class="flex items-center gap-2">
                <input v-model.number="discount" type="number" min="0" max="100" step="0.1"
                  class="w-20 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 text-right"
                  placeholder="0"/>
                <span class="text-sm text-gray-600">%</span>
                <span class="text-base font-semibold text-red-500 ml-2">-{{ formatRupiah(discountNominal) }}</span>
              </div>
            </div>
            <div class="flex items-center justify-between px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
              <span class="text-sm font-medium text-green-700">Total Setelah Diskon:</span>
              <span class="text-base font-bold text-green-800">{{ formatRupiah(totalNilai) }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button type="submit" :disabled="processing || !checkedItems.size" class="px-5 py-2.5 text-sm text-white bg-violet-500 hover:bg-violet-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
            <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Tambah Data
          </button>
        </div>
      </form>
    </Modal>

    <!-- EDIT MODAL -->
    <Modal v-model="showEditModal" title="Edit Item" size="sm">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div v-if="editRow" class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600">
          <span class="font-medium text-gray-700">Model:</span> {{ editRow.model }}
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Pcs <span class="text-red-500">*</span></label>
          <input v-model.number="editForm.pcs" type="number" min="1" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan <span class="text-red-500">*</span></label>
          <input v-model.number="editForm.harga_satuan" type="number" min="0" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent transition bg-white"/>
          <p v-if="editForm.pcs && editForm.harga_satuan" class="mt-1 text-xs text-violet-600">
            Total: {{ formatRupiah(editForm.pcs * editForm.harga_satuan) }}
          </p>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showEditModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 text-sm text-white bg-violet-500 hover:bg-violet-600 rounded-lg disabled:opacity-60 flex items-center gap-2">
            <svg v-if="editForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog
      v-model="showConfirm"
      title="Hapus Data?"
      message="Tindakan ini tidak dapat dibatalkan."
      :loading="deleteLoading"
      @confirm="doDelete"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
  data:            Object,
  stokOptions:     { type: Array, default: () => [] },
  nextNota:        { type: String, default: '' },
  rekeningOptions: { type: Array, default: () => [] },
})

const formatDate   = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0)
const statusClass  = (s) => {
  if (s === 'lunas')   return 'bg-green-100 text-green-700'
  if (s === 'pending') return 'bg-yellow-100 text-yellow-700'
  return 'bg-red-100 text-red-600'
}

// Search
const searchQuery = ref(new URLSearchParams(window.location.search).get('search') ?? '')
let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/proses-jual', { search: val || undefined }, { preserveState: true, replace: true })
  }, 350)
})
const appendSearch = (url) => {
  if (!searchQuery.value) return url
  const u = new URL(url, window.location.origin)
  u.searchParams.set('search', searchQuery.value)
  return u.pathname + u.search
}

// Detail modal
const showDetail  = ref(false)
const detailGroup = ref(null)
const openDetail  = (group) => { detailGroup.value = { ...group }; showDetail.value = true }

// Pembayaran
const showPayModal = ref(false)
const payForm     = ref({ tanggal_bayar: new Date().toISOString().substring(0, 10), jumlah: '', metode: 'cash', rekening_id: '', keterangan: '' })
const payLoading  = ref(false)

const rekeningLabel = (id) => {
  const r = props.rekeningOptions.find(r => r.id === id)
  if (!r) return ''
  return r.nomor_rekening ? `${r.bank} — ${r.nama} (${r.nomor_rekening})` : `${r.bank} — ${r.nama}`
}

const openPay = () => {
  payForm.value = { tanggal_bayar: new Date().toISOString().substring(0, 10), jumlah: '', metode: 'cash', rekening_id: '', keterangan: '' }
  showPayModal.value = true
}

const submitPembayaran = () => {
  payLoading.value = true
  router.post(`/penjualan-pembayaran/${detailGroup.value.no_nota}`, {
    ...payForm.value,
    channel: 'toko',
  }, {
    onSuccess: () => { payLoading.value = false; showPayModal.value = false; showDetail.value = false },
    onError:   () => { payLoading.value = false },
  })
}

const deletePembayaran = (id) => {
  if (!confirm('Hapus pembayaran ini?')) return
  router.delete(`/penjualan-pembayaran/${id}`, {
    onSuccess: () => { showDetail.value = false },
  })
}

// Create modal
const showModal   = ref(false)
const noNota      = ref('')
const tanggalNota = ref('')
const buyer       = ref('')
const status      = ref('pending')
const discount    = ref(0)
const checkedItems = ref(new Set())
const pcsPerItem   = ref({})
const hargaPerItem = ref({})
const processing   = ref(false)
const modalSearch  = ref('')

const filteredOptions = computed(() => {
  if (!modalSearch.value) return props.stokOptions
  const q = modalSearch.value.toLowerCase()
  return props.stokOptions.filter(o => o.model.toLowerCase().includes(q))
})

const toggleItem = (item, e) => {
  const newSet = new Set(checkedItems.value)
  if (e.target.checked) {
    newSet.add(item.model)
    if (!pcsPerItem.value[item.model]) pcsPerItem.value[item.model] = 1
    if (!hargaPerItem.value[item.model]) {
      hargaPerItem.value[item.model] = item.harga_satuan ?? 0
    }
  } else {
    newSet.delete(item.model)
  }
  checkedItems.value = newSet
}

const totalPcsSelected = computed(() =>
  [...checkedItems.value].reduce((sum, key) => sum + (parseInt(pcsPerItem.value[key]) || 0), 0)
)
const subtotal = computed(() =>
  [...checkedItems.value].reduce((sum, key) => {
    const pcs   = parseInt(pcsPerItem.value[key])    || 0
    const harga = parseFloat(hargaPerItem.value[key]) || 0
    return sum + pcs * harga
  }, 0)
)
const discountNominal = computed(() => {
  const disc = parseFloat(discount.value) || 0
  return subtotal.value * disc / 100
})
const totalNilai = computed(() => subtotal.value - discountNominal.value)

const openCreate = () => {
  noNota.value      = props.nextNota
  tanggalNota.value = new Date().toISOString().substring(0, 10)
  buyer.value       = ''
  status.value      = 'pending'
  discount.value    = 0
  checkedItems.value = new Set()
  pcsPerItem.value   = {}
  hargaPerItem.value = {}
  modalSearch.value  = ''
  showModal.value    = true
}

const submit = () => {
  processing.value = true
  const models = [...checkedItems.value].map(model => ({
    model,
    pcs:          parseInt(pcsPerItem.value[model])    || 0,
    harga_satuan: parseFloat(hargaPerItem.value[model]) || 0,
  }))
  router.post('/proses-jual', {
    no_nota:      noNota.value || null,
    tanggal_nota: tanggalNota.value,
    buyer:        buyer.value,
    status:       status.value,
    models,
    discount:     parseFloat(discount.value) || 0, // persen
  }, {
    onSuccess: () => { showModal.value = false; processing.value = false },
    onError:   () => { processing.value = false },
  })
}

// Edit modal
const showEditModal = ref(false)
const editRow       = ref(null)
const editForm      = useForm({ pcs: '', harga_satuan: '' })

const openEdit = (mRow) => {
  editRow.value         = mRow
  editForm.pcs          = mRow.pcs ?? ''
  editForm.harga_satuan = mRow.harga_satuan != null ? parseFloat(mRow.harga_satuan) : ''
  editForm.clearErrors()
  showEditModal.value = true
}
const submitEdit = () => {
  editForm.put(`/proses-jual/${editRow.value.id}`, {
    onSuccess: () => {
      showEditModal.value = false
      showDetail.value    = false
    },
  })
}

// Delete
const deleteId      = ref(null)
const showConfirm   = ref(false)
const deleteLoading = ref(false)
const confirmDelete = (id) => { deleteId.value = id; showConfirm.value = true }
const doDelete = () => {
  deleteLoading.value = true
  router.delete(`/proses-jual/${deleteId.value}`, {
    onSuccess: () => {
      deleteId.value      = null
      showConfirm.value   = false
      deleteLoading.value = false
      showDetail.value    = false
    },
    onError: () => { deleteLoading.value = false },
  })
}
</script>
