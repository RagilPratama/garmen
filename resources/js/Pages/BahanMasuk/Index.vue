<template>
  <DataTable
    title="Bahan Masuk"
    :data="data"
    :columns="columns"
    base-path="/bahan-masuk"
    @open-create="openCreate"
    @open-edit="openEdit"
  >
    <!-- Clickable no_nota cell -->
    <template #cell-no_nota="{ item, value }">
      <button @click.stop="openDetail(item)"
        class="text-amber-600 hover:text-amber-700 font-semibold hover:underline underline-offset-2 transition">
        {{ value ?? '—' }}
      </button>
    </template>

    <!-- Sisa tagihan cell -->
    <template #cell-sisa_tagihan="{ item }">
      <span v-if="(item.sisa_tagihan ?? item.grand_total) <= 0"
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
        Lunas
      </span>
      <span v-else class="text-red-600 font-medium text-sm">
        {{ formatRupiah(item.sisa_tagihan ?? item.grand_total) }}
      </span>
    </template>

    <!-- Pay button in actions column -->
    <template #actions="{ item }">
      <button v-if="(item.sisa_tagihan ?? item.grand_total) > 0"
        @click="openDetailAndPay(item)"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors"
        title="Bayar">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Bayar
      </button>
    </template>

    <template #modal>

      <!-- ── EDIT MODAL ── -->
      <Modal v-model="showEditModal" :title="`Edit Nota — ${editNota?.no_nota ?? ''}`" size="2xl">
        <form v-if="editNota" @submit.prevent="submitEdit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
              <input v-model="editForm.tanggal" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="editForm.errors.tanggal" class="mt-1 text-xs text-red-500">{{ editForm.errors.tanggal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
              <input v-model="editForm.no_surat_jalan" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="No. Surat Jalan"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Nota</label>
              <input v-model="editForm.no_nota" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="No. Nota"/>
              <p v-if="editForm.errors.no_nota" class="mt-1 text-xs text-red-500">{{ editForm.errors.no_nota }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
              <SearchableSelect
                v-model="editForm.supplier"
                :options="supplierOptions"
                placeholder="-- Pilih supplier --"
              />
              <p v-if="editForm.errors.supplier" class="mt-1 text-xs text-red-500">{{ editForm.errors.supplier }}</p>
            </div>
          </div>

          <!-- Bahan history chips (edit) -->
          <div v-if="editForm.supplier && editBahanOptions.length" class="space-y-2">
            <p class="text-xs font-medium text-gray-500">Bahan pernah dipesan dari <span class="text-amber-600 font-semibold">{{ editForm.supplier }}</span> — klik untuk tambah:</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="opt in editBahanOptions" :key="opt.kode_bahan"
                type="button"
                @click="addFromHistory(opt, 'edit')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-full hover:bg-amber-100 transition-colors"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ opt.kode_bahan }}<span v-if="opt.nama_bahan" class="text-amber-500">— {{ opt.nama_bahan }}</span>
              </button>
            </div>
          </div>

          <!-- Items table -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Daftar Bahan <span class="text-red-500">*</span></span>
            </div>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Kode Bahan</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Nama Bahan</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Yard</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Rp/Yard</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Total</th>
                    <th class="px-2 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(item, idx) in editForm.items" :key="idx">
                    <td class="px-2 py-1.5">
                      <input v-model="item.kode_bahan" type="text" required placeholder="KB-001" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                      <p v-if="editForm.errors[`items.${idx}.kode_bahan`]" class="mt-0.5 text-xs text-red-500">{{ editForm.errors[`items.${idx}.kode_bahan`] }}</p>
                    </td>
                    <td class="px-2 py-1.5">
                      <input v-model="item.nama_bahan" type="text" placeholder="Nama bahan" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                    </td>
                    <td class="px-2 py-1.5">
                      <input v-model="item.yard" type="number" min="0" step="any" required placeholder="0.00" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                      <p v-if="editForm.errors[`items.${idx}.yard`]" class="mt-0.5 text-xs text-red-500">{{ editForm.errors[`items.${idx}.yard`] }}</p>
                    </td>
                    <td class="px-2 py-1.5">
                      <input v-model="item.rp_per_yard" type="number" min="0" required placeholder="0" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                      <p v-if="editForm.errors[`items.${idx}.rp_per_yard`]" class="mt-0.5 text-xs text-red-500">{{ editForm.errors[`items.${idx}.rp_per_yard`] }}</p>
                    </td>
                    <td class="px-2 py-1.5 text-gray-600 whitespace-nowrap text-xs">
                      {{ formatRupiah(itemTotal(item)) }}
                    </td>
                    <td class="px-2 py-1.5 text-center">
                      <button type="button" @click="editForm.items.splice(idx, 1)" :disabled="editForm.items.length === 1"
                        class="p-1 text-red-400 hover:text-red-600 disabled:opacity-30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="px-3 py-2 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <button type="button" @click="editForm.items.push({ kode_bahan: '', nama_bahan: '', yard: '', rp_per_yard: '' })"
                  class="text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  Tambah Bahan
                </button>
                <span class="text-xs text-gray-500">Total: {{ formatRupiah(editGrandTotal) }}</span>
              </div>
            </div>
            <p v-if="editForm.errors.items" class="mt-1 text-xs text-red-500">{{ editForm.errors.items }}</p>
          </div>

          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showEditModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="editForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </Modal>

      <!-- ── CREATE FORM (multi-item) ── -->
      <Modal v-model="showCreateModal" title="Tambah Bahan Masuk" size="2xl">
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
              <input v-model="createForm.tanggal" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
              <p v-if="createForm.errors.tanggal" class="mt-1 text-xs text-red-500">{{ createForm.errors.tanggal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Surat Jalan</label>
              <div class="relative">
                <input v-model="createForm.no_surat_jalan" type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white pr-16" placeholder="No. Surat Jalan"/>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Nota</label>
              <div class="relative">
                <input :value="nextNota" type="text" readonly class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed pr-16"/>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
              <SearchableSelect
                v-model="createForm.supplier"
                :options="supplierOptions"
                placeholder="-- Pilih supplier --"
              />
              <p v-if="createForm.errors.supplier" class="mt-1 text-xs text-red-500">{{ createForm.errors.supplier }}</p>
            </div>
          </div>

          <!-- Bahan history chips (create) -->
          <div v-if="createForm.supplier && createBahanOptions.length" class="space-y-2">
            <p class="text-xs font-medium text-gray-500">Bahan pernah dipesan dari <span class="text-amber-600 font-semibold">{{ createForm.supplier }}</span> — klik untuk tambah:</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="opt in createBahanOptions" :key="opt.kode_bahan"
                type="button"
                @click="addFromHistory(opt, 'create')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-full hover:bg-amber-100 transition-colors"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ opt.kode_bahan }}<span v-if="opt.nama_bahan" class="text-amber-500">— {{ opt.nama_bahan }}</span>
              </button>
            </div>
          </div>

          <!-- Items table -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Daftar Bahan <span class="text-red-500">*</span></span>
            </div>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Kode Bahan</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Nama Bahan</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Yard</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Rp/Yard</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Total</th>
                    <th class="px-2 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(item, idx) in createForm.items" :key="idx">
                    <td class="px-2 py-1.5">
                      <input v-model="item.kode_bahan" type="text" required placeholder="KB-001" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                      <p v-if="createForm.errors[`items.${idx}.kode_bahan`]" class="mt-0.5 text-xs text-red-500">{{ createForm.errors[`items.${idx}.kode_bahan`] }}</p>
                    </td>
                    <td class="px-2 py-1.5">
                      <input v-model="item.nama_bahan" type="text" placeholder="Nama bahan" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                    </td>
                    <td class="px-2 py-1.5">
                      <input v-model="item.yard" type="number" min="0" step="any" required placeholder="0.00" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                      <p v-if="createForm.errors[`items.${idx}.yard`]" class="mt-0.5 text-xs text-red-500">{{ createForm.errors[`items.${idx}.yard`] }}</p>
                    </td>
                    <td class="px-2 py-1.5">
                      <input v-model="item.rp_per_yard" type="number" min="0" required placeholder="0" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"/>
                      <p v-if="createForm.errors[`items.${idx}.rp_per_yard`]" class="mt-0.5 text-xs text-red-500">{{ createForm.errors[`items.${idx}.rp_per_yard`] }}</p>
                    </td>
                    <td class="px-2 py-1.5 text-gray-600 whitespace-nowrap text-xs">
                      {{ formatRupiah(itemTotal(item)) }}
                    </td>
                    <td class="px-2 py-1.5 text-center">
                      <button type="button" @click="removeItem(idx)" :disabled="createForm.items.length === 1"
                        class="p-1 text-red-400 hover:text-red-600 disabled:opacity-30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="px-3 py-2 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <button type="button" @click="addItem"
                  class="text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  Tambah Bahan
                </button>
                <span class="text-xs text-gray-500">Total: {{ formatRupiah(grandTotal) }}</span>
              </div>
            </div>
            <p v-if="createForm.errors.items" class="mt-1 text-xs text-red-500">{{ createForm.errors.items }}</p>
          </div>

          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="createForm.processing" class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="createForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              Tambah Data
            </button>
          </div>
        </form>
      </Modal>

      <!-- ── DETAIL MODAL ── -->
      <Modal v-model="showDetailModal" :title="`Detail Nota — ${selectedNota?.no_nota ?? ''}`" size="lg">
        <div v-if="selectedNota" class="space-y-4">
          <div class="grid grid-cols-2 gap-3 bg-gray-50 rounded-xl p-4 text-sm">
            <div>
              <p class="text-gray-400 text-xs mb-0.5">Tanggal</p>
              <p class="font-medium text-gray-800">{{ formatDate(selectedNota.tanggal) }}</p>
            </div>
            <div>
              <p class="text-gray-400 text-xs mb-0.5">No. Surat Jalan</p>
              <p class="font-medium text-gray-800">{{ selectedNota.no_surat_jalan ?? '—' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-gray-400 text-xs mb-0.5">Supplier</p>
              <p class="font-medium text-gray-800">{{ selectedNota.supplier }}</p>
            </div>
          </div>

          <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Kode Bahan</th>
                  <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Nama Bahan</th>
                  <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Yard</th>
                  <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Rp/Yard</th>
                  <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in selectedNota.items" :key="item.id"
                  class="hover:bg-amber-50/30 transition-colors">
                  <td class="px-3 py-2.5 font-medium text-gray-800">{{ item.kode_bahan }}</td>
                  <td class="px-3 py-2.5 text-gray-600">{{ item.nama_bahan ?? '—' }}</td>
                  <td class="px-3 py-2.5 text-right text-gray-700">{{ item.yard }}</td>
                  <td class="px-3 py-2.5 text-right text-gray-700">{{ formatRupiah(item.rp_per_yard) }}</td>
                  <td class="px-3 py-2.5 text-right font-medium text-gray-800">{{ formatRupiah(item.total) }}</td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                  <td colspan="4" class="px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase">Grand Total</td>
                  <td class="px-3 py-2.5 text-right font-bold text-amber-600">{{ formatRupiah(selectedNota.grand_total) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Payment Summary -->
          <div class="grid grid-cols-3 gap-3 border border-gray-200 rounded-xl p-4 text-sm text-center">
            <div>
              <p class="text-gray-400 text-xs mb-0.5">Total Tagihan</p>
              <p class="font-bold text-gray-800">{{ formatRupiah(selectedNota.grand_total) }}</p>
            </div>
            <div>
              <p class="text-gray-400 text-xs mb-0.5">Total Dibayar</p>
              <p class="font-bold text-green-600">{{ formatRupiah(selectedNota.total_dibayar ?? 0) }}</p>
            </div>
            <div>
              <p class="text-gray-400 text-xs mb-0.5">Sisa Tagihan</p>
              <p class="font-bold" :class="(selectedNota.sisa_tagihan ?? selectedNota.grand_total) <= 0 ? 'text-green-600' : 'text-red-600'">
                {{ (selectedNota.sisa_tagihan ?? selectedNota.grand_total) <= 0 ? 'LUNAS' : formatRupiah(selectedNota.sisa_tagihan ?? selectedNota.grand_total) }}
              </p>
            </div>
          </div>

          <!-- Payment History -->
          <div v-if="selectedNota.pembayaran?.length" class="border border-gray-200 rounded-lg overflow-hidden">
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
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="p in selectedNota.pembayaran" :key="p.id" class="hover:bg-gray-50/50">
                  <td class="px-3 py-2">{{ formatDate(p.tanggal_bayar) }}</td>
                  <td class="px-3 py-2">
                    <span :class="p.metode === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                      {{ p.metode }}
                    </span>
                    <span v-if="p.metode === 'transfer' && p.rekening_id" class="ml-1.5 text-xs text-gray-500">
                      {{ rekeningLabel(p.rekening_id) }}
                    </span>
                  </td>
                  <td class="px-3 py-2 text-right font-medium text-gray-800">{{ formatRupiah(p.jumlah) }}</td>
                  <td class="px-3 py-2 text-gray-500 text-xs">{{ p.keterangan ?? '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-between pt-1">
            <button @click="openPay"
              :disabled="(selectedNota.sisa_tagihan ?? selectedNota.grand_total) <= 0"
              class="px-5 py-2.5 text-sm text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-green-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Bayar
            </button>
            <button @click="showDetailModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Tutup</button>
          </div>
        </div>
      </Modal>

      <!-- ── PAYMENT MODAL ── -->
      <Modal v-model="showPayModal" :title="`Bayar — ${selectedNota?.no_nota ?? ''}`">
        <form v-if="selectedNota" @submit.prevent="submitPay" class="space-y-4">
          <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm">
            Sisa tagihan:
            <span class="font-bold text-red-600">{{ formatRupiah(selectedNota.sisa_tagihan ?? selectedNota.grand_total) }}</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
            <input v-model="payForm.tanggal_bayar" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            <p v-if="payForm.errors.tanggal_bayar" class="mt-1 text-xs text-red-500">{{ payForm.errors.tanggal_bayar }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
            <input v-model="payForm.jumlah" type="number" min="1" required placeholder="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            <p v-if="payForm.errors.jumlah" class="mt-1 text-xs text-red-500">{{ payForm.errors.jumlah }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Metode <span class="text-red-500">*</span></label>
            <div class="flex gap-6 mt-1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="payForm.metode" type="radio" value="cash" class="text-amber-500"/>
                <span class="text-sm text-gray-700">Cash</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="payForm.metode" type="radio" value="transfer" class="text-amber-500"/>
                <span class="text-sm text-gray-700">Transfer</span>
              </label>
            </div>
            <p v-if="payForm.errors.metode" class="mt-1 text-xs text-red-500">{{ payForm.errors.metode }}</p>
          </div>
          <div v-if="payForm.metode === 'transfer'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rekening Tujuan <span class="text-red-500">*</span></label>
            <SearchableSelect
              v-model="payForm.rekening_id"
              :options="rekeningOptions.map(r => ({ value: r.id, label: r.bank + ' — ' + r.nama + (r.nomor_rekening ? ' (' + r.nomor_rekening + ')' : '') }))"
              placeholder="-- Pilih rekening --"
            />
            <p v-if="payForm.errors.rekening_id" class="mt-1 text-xs text-red-500">{{ payForm.errors.rekening_id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
            <input v-model="payForm.keterangan" type="text" placeholder="Opsional..." class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <button type="button" @click="showPayModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" :disabled="payForm.processing" class="px-5 py-2.5 text-sm text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
              <svg v-if="payForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              Simpan Pembayaran
            </button>
          </div>
        </form>
      </Modal>

    </template>
  </DataTable>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DataTable from '@/Components/DataTable.vue'
import Modal from '@/Components/Modal.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
  data: Object,
  supplierOptions:  { type: Array, default: () => [] },
  rekeningOptions:  { type: Array, default: () => [] },
  nextSuratJalan:   { type: String, default: '' },
  nextNota:         { type: String, default: '' },
  nextKodeBahan:    { type: String, default: '' },
  supplierBahanMap: { type: Object, default: () => ({}) },
})

// Bahan options from previous orders for the selected supplier
const createBahanOptions = ref([])
const editBahanOptions = ref([])

const buildBahanOptions = (supplier) => {
  return props.supplierBahanMap[supplier] ?? []
}

// Add a history bahan to the items list (fill first empty row or append)
const addFromHistory = (opt, formType) => {
  const form = formType === 'create' ? createForm : editForm
  const emptyIdx = form.items.findIndex(i => !i.kode_bahan)
  if (emptyIdx >= 0) {
    form.items[emptyIdx].kode_bahan = opt.kode_bahan
    form.items[emptyIdx].nama_bahan = opt.nama_bahan ?? ''
  } else {
    form.items.push({ kode_bahan: opt.kode_bahan, nama_bahan: opt.nama_bahan ?? '', yard: '', rp_per_yard: '' })
  }
}

const columns = [
  { key: 'tanggal',        label: 'Tanggal',        type: 'date' },
  { key: 'no_surat_jalan', label: 'No. Surat Jalan' },
  { key: 'no_nota',        label: 'No. Nota' },
  { key: 'supplier',       label: 'Supplier' },
  { key: 'items_count',    label: 'Jml Bahan' },
  { key: 'grand_total',    label: 'Total',          type: 'currency' },
  { key: 'sisa_tagihan',   label: 'Sisa Tagihan' },
]

const formatRupiah   = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const formatDate     = (val) => { if (!val) return '—'; return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }

const incrementCode = (code) => {
  const match = code.match(/^([A-Z]+-)(\d+)$/)
  if (!match) return code
  const n = parseInt(match[2], 10) + 1
  return match[1] + String(n).padStart(match[2].length, '0')
}

// ── Create Modal ──
const showCreateModal = ref(false)

const createForm = useForm({
  tanggal: '',
  no_surat_jalan: '',
  supplier: '',
  items: [{ kode_bahan: '', nama_bahan: '', yard: '', rp_per_yard: '' }],
})

watch(() => createForm.supplier, (supplier) => {
  createBahanOptions.value = buildBahanOptions(supplier)
})

const itemTotal  = (item) => (parseFloat(item.yard) || 0) * (parseFloat(item.rp_per_yard) || 0)
const grandTotal = computed(() => createForm.items.reduce((sum, item) => sum + itemTotal(item), 0))

const nextItemCode = () => {
  const last = createForm.items.at(-1)?.kode_bahan
  if (last && /^[A-Z]+-\d+$/.test(last)) return incrementCode(last)
  return incrementCode(props.nextKodeBahan)
}

const addItem    = () => createForm.items.push({ kode_bahan: nextItemCode(), nama_bahan: '', yard: '', rp_per_yard: '' })
const removeItem = (idx) => createForm.items.splice(idx, 1)

const openCreate = () => {
  createForm.reset()
  createForm.no_surat_jalan = props.nextSuratJalan
  createForm.items = [{ kode_bahan: props.nextKodeBahan, nama_bahan: '', yard: '', rp_per_yard: '' }]
  createForm.clearErrors()
  showCreateModal.value = true
}

const submitCreate = () => {
  createForm.post('/bahan-masuk', {
    onSuccess: () => {
      showCreateModal.value = false
      createForm.reset()
      createForm.items = [{ kode_bahan: '', nama_bahan: '', yard: '', rp_per_yard: '' }]
    },
  })
}

// ── Edit Modal ──
const showEditModal = ref(false)
const editNota      = ref(null)

const editForm = useForm({
  tanggal: '', no_surat_jalan: '', no_nota: '', supplier: '',
  items: [{ kode_bahan: '', nama_bahan: '', yard: '', rp_per_yard: '' }],
})

watch(() => editForm.supplier, (supplier) => {
  editBahanOptions.value = buildBahanOptions(supplier)
})

const editGrandTotal = computed(() => editForm.items.reduce((sum, item) => sum + itemTotal(item), 0))

const openEdit = (nota) => {
  editNota.value = nota
  editForm.tanggal        = nota.tanggal?.substring(0, 10) ?? ''
  editForm.no_surat_jalan = nota.no_surat_jalan ?? ''
  editForm.no_nota        = nota.no_nota ?? ''
  editForm.supplier       = nota.supplier ?? ''
  editForm.items          = nota.items.map(i => ({
    kode_bahan:  i.kode_bahan  ?? '',
    nama_bahan:  i.nama_bahan  ?? '',
    yard:        i.yard        ?? '',
    rp_per_yard: i.rp_per_yard != null ? parseFloat(i.rp_per_yard) : '',
  }))
  editForm.clearErrors()
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(`/bahan-masuk/${editNota.value.no_nota}`, {
    onSuccess: () => { showEditModal.value = false },
  })
}

// ── Detail Modal ──
const showDetailModal = ref(false)
const selectedNota    = ref(null)

const openDetail = (item) => {
  selectedNota.value = item
  showDetailModal.value = true
}

const openDetailAndPay = (item) => {
  selectedNota.value = item
  openPay()
}

// ── Payment Modal ──
const showPayModal = ref(false)

const payForm = useForm({
  tanggal_bayar: '',
  jumlah:        '',
  metode:        'cash',
  rekening_id:   null,
  keterangan:    '',
})

const rekeningLabel = (id) => {
  const r = props.rekeningOptions.find(r => r.id === id)
  if (!r) return ''
  return r.nomor_rekening ? `${r.bank} — ${r.nama} (${r.nomor_rekening})` : `${r.bank} — ${r.nama}`
}

const openPay = () => {
  payForm.tanggal_bayar = new Date().toISOString().substring(0, 10)
  payForm.jumlah        = ''
  payForm.metode        = 'cash'
  payForm.rekening_id   = null
  payForm.keterangan    = ''
  payForm.clearErrors()
  showPayModal.value = true
}

const submitPay = () => {
  const noNota = selectedNota.value.no_nota
  payForm.post(`/bahan-masuk/${noNota}/pembayaran`, {
    onSuccess: () => {
      showPayModal.value = false
      const updated = props.data.data.find(n => n.no_nota === noNota)
      if (updated) selectedNota.value = updated
    },
  })
}
</script>
