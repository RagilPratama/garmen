<template>
  <AdminLayout title="Bahan Proses Potong">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-orange-400"></div>

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-800">Bahan Proses Potong</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ data?.total ?? 0 }} PO</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="searchQuery" type="text" placeholder="Cari PO / model..."
              class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent w-52 transition-all"/>
            <button v-if="searchQuery" @click="searchQuery = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors">
              <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-orange-500 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Data
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-50/80 border-b border-gray-100">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">No</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. PO</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Potong</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah Model</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Hasil Potong (pcs)</th>
              <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(group, index) in data?.data" :key="group.po"
              class="border-b border-gray-50 hover:bg-amber-50/40 transition-colors group cursor-pointer"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
              @click="openDetail(group)">
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-amber-100 group-hover:text-amber-700 transition-colors">
                  {{ (data.current_page - 1) * data.per_page + index + 1 }}
                </span>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-semibold text-amber-600 hover:text-amber-700">{{ group.po }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-600">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(group.tanggal_potong) }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">{{ group.jumlah_model }} model</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full">{{ group.total_hasil_potongan.toLocaleString('id-ID') }} pcs</span>
              </td>
              <td class="px-5 py-3.5 text-center" @click.stop>
                <button @click="openDetail(group)"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Detail
                </button>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td colspan="6" class="px-4 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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
        <p class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-700">{{ data.from }}–{{ data.to }}</span> dari <span class="font-semibold text-gray-700">{{ data.total }}</span> PO</p>
        <div class="flex items-center gap-1 flex-wrap justify-center">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url ? appendSearch(link.url) : '#'"
            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
            :class="link.active ? 'bg-amber-500 text-white font-semibold shadow-sm' : link.url ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200' : 'text-gray-300 cursor-default pointer-events-none'"
            :preserve-scroll="true"
            v-html="link.label"/>
        </div>
      </div>
    </div>

    <!-- ── DETAIL MODAL ── -->
    <Modal v-model="showDetail" :title="'Detail PO: ' + (detailGroup?.po ?? '')" size="2xl">
      <div v-if="detailGroup" class="space-y-4">
        <div class="flex items-center gap-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
          <span><span class="font-medium text-gray-700">Tanggal:</span> {{ formatDate(detailGroup.tanggal_potong) }}</span>
          <span><span class="font-medium text-gray-700">Jumlah Model:</span> {{ detailGroup.jumlah_model }}</span>
          <span><span class="font-medium text-gray-700">Total Hasil:</span> {{ detailGroup.total_hasil_potongan.toLocaleString('id-ID') }} pcs</span>
        </div>

        <div v-for="mRow in detailGroup.models" :key="mRow.model" class="border border-gray-200 rounded-lg overflow-hidden">
          <!-- Model header -->
          <div class="flex items-center justify-between px-4 py-2.5 bg-amber-50 border-b border-amber-100">
            <div class="flex items-center gap-3">
              <span class="font-semibold text-amber-800 text-sm">{{ mRow.model }}</span>
              <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">{{ mRow.hasil_potongan.toLocaleString('id-ID') }} pcs</span>
            </div>
            <button @click="openEdit(mRow)"
              class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Edit Model
            </button>
          </div>
          <!-- Bahans table -->
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Kode Bahan</th>
                <th class="text-right px-4 py-2 text-xs font-medium text-gray-500 w-28">Yard</th>
                <th class="w-12"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="bahan in mRow.bahans" :key="bahan.id" class="border-t border-gray-100 hover:bg-gray-50">
                <td class="px-4 py-2 font-medium text-gray-700">{{ bahan.kode_bahan }}</td>
                <td class="px-4 py-2 text-right text-gray-600">{{ fmt(bahan.yard) }}</td>
                <td class="px-4 py-2 text-center">
                  <button @click="confirmDelete(bahan.id)"
                    class="text-gray-300 hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Modal>

    <!-- ── DELETE CONFIRM ── -->
    <ConfirmDialog
      v-model="showConfirm"
      title="Hapus Data?"
      message="Tindakan ini tidak dapat dibatalkan."
      :loading="deleteLoading"
      @confirm="doDelete"
    />

    <!-- ── CREATE MODAL ── -->
    <Modal v-model="showModal" title="Tambah Proses Potong" size="2xl">
      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Potong <span class="text-red-500">*</span></label>
            <input v-model="form.tanggal_potong" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
            <p v-if="form.errors.tanggal_potong" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_potong }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PO <span class="text-red-500">*</span></label>
            <div class="relative">
              <input v-model="form.po" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white pr-16" placeholder="Nomor PO"/>
              <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Auto</span>
            </div>
            <p v-if="form.errors.po" class="mt-1 text-xs text-red-500">{{ form.errors.po }}</p>
          </div>
        </div>

        <div class="space-y-3">
          <label class="block text-sm font-medium text-gray-700">Daftar Model <span class="text-red-500">*</span></label>
          <div v-for="(mRow, mIdx) in form.models" :key="mIdx" class="border border-gray-200 rounded-lg p-3 space-y-3 bg-gray-50/50">
            <div class="flex items-start gap-3">
              <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Model <span class="text-red-500">*</span></label>
                <SearchableSelect v-model="mRow.model" :options="modelOptions.map(m => ({ value: m, label: m }))" placeholder="-- Pilih Model --"/>
                <p v-if="form.errors[`models.${mIdx}.model`]" class="mt-1 text-xs text-red-500">{{ form.errors[`models.${mIdx}.model`] }}</p>
              </div>
              <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Hasil Potong (pcs)</label>
                <input v-model="mRow.hasil_potongan" type="number" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
              </div>
              <button type="button" @click="removeModel(mIdx)" :disabled="form.models.length === 1" class="mt-5 text-gray-300 hover:text-red-400 transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
            <div class="pl-2 border-l-2 border-amber-200 space-y-2">
              <label class="block text-xs font-medium text-gray-500">Bahan yang Digunakan</label>
              <div v-for="(bahan, bIdx) in mRow.bahans" :key="bIdx" class="flex items-center gap-2">
                <div class="flex-1">
                  <SearchableSelect v-model="bahan.kode_bahan" :options="bahanOptions.map(b => ({ value: b.kode_bahan, label: b.kode_bahan + ' (' + fmt(b.total_yard) + ' yard)' }))" placeholder="-- Pilih Kode Bahan --"/>
                </div>
                <div class="w-28">
                  <input v-model="bahan.yard" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="Yard"/>
                </div>
                <button type="button" @click="removeBahan(mIdx, bIdx)" :disabled="mRow.bahans.length === 1" class="text-gray-300 hover:text-red-400 transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
              <button type="button" @click="addBahan(mIdx)" class="flex items-center gap-1 text-xs text-amber-600 hover:text-amber-700 font-medium transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Bahan
              </button>
            </div>
          </div>
          <button type="button" @click="addModel" class="flex items-center gap-1.5 text-sm text-amber-600 hover:text-amber-700 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Model
          </button>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
          <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Tambah Data
          </button>
        </div>
      </form>
    </Modal>

    <!-- ── EDIT MODAL ── -->
    <Modal v-model="showEditModal" title="Edit Model" size="2xl">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Potong <span class="text-red-500">*</span></label>
            <input v-model="editForm.tanggal_potong" type="date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PO <span class="text-red-500">*</span></label>
            <input v-model="editForm.po" type="text" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Model <span class="text-red-500">*</span></label>
            <SearchableSelect v-model="editForm.model" :options="modelOptions.map(m => ({ value: m, label: m }))" placeholder="-- Pilih Model --"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Potong (pcs)</label>
            <input v-model="editForm.hasil_potongan" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white" placeholder="0"/>
          </div>
        </div>

        <div class="pl-2 border-l-2 border-amber-200 space-y-2">
          <label class="block text-sm font-medium text-gray-700">Bahan yang Digunakan</label>
          <div v-for="(bahan, bIdx) in editForm.bahans" :key="bIdx" class="flex items-center gap-2">
            <div class="flex-1">
              <SearchableSelect v-model="bahan.kode_bahan"
                :options="bahanOptions.map(b => ({ value: b.kode_bahan, label: b.kode_bahan + ' (' + fmt(b.total_yard) + ' yard)' }))"
                placeholder="-- Pilih Kode Bahan --"/>
            </div>
            <div class="w-32">
              <input v-model="bahan.yard" type="number" step="0.01" min="0"
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                placeholder="Yard"/>
            </div>
            <button type="button" @click="editRemoveBahan(bIdx)" :disabled="editForm.bahans.length === 1"
              class="text-gray-300 hover:text-red-400 transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button type="button" @click="editAddBahan"
            class="flex items-center gap-1 text-xs text-amber-600 hover:text-amber-700 font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Bahan
          </button>
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
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, router, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
  data: Object,
  bahanOptions: { type: Array, default: () => [] },
  modelOptions:  { type: Array, default: () => [] },
  nextPo:        { type: String, default: '' },
})

const page = usePage()
const searchQuery = ref(new URLSearchParams(window.location.search).get('search') ?? '')
let searchTimer = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/bahan-proses-potong', { search: val || undefined }, { preserveState: true, replace: true })
  }, 350)
})
const appendSearch = (url) => {
  if (!searchQuery.value) return url
  const u = new URL(url, window.location.origin)
  u.searchParams.set('search', searchQuery.value)
  return u.pathname + u.search
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
const fmt = (v) => Number(v ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

// ── Detail modal ──
const showDetail = ref(false)
const detailGroup = ref(null)
const openDetail = (group) => { detailGroup.value = group; showDetail.value = true }

// ── Delete ──
const deleteId      = ref(null)
const showConfirm   = ref(false)
const deleteLoading = ref(false)
const confirmDelete = (id) => { deleteId.value = id; showConfirm.value = true }
const doDelete = () => {
  deleteLoading.value = true
  router.delete(`/bahan-proses-potong/${deleteId.value}`, {
    onSuccess: () => {
      deleteId.value      = null
      showConfirm.value   = false
      deleteLoading.value = false
      showDetail.value    = false
    },
    onError: () => { deleteLoading.value = false },
  })
}

// ── Create form ──
const showModal = ref(false)
const emptyBahan = () => ({ kode_bahan: '', yard: '' })
const emptyModel = () => ({ model: '', hasil_potongan: '', bahans: [emptyBahan()] })
const form = useForm({ tanggal_potong: '', po: '', models: [emptyModel()] })

const addModel    = () => form.models.push(emptyModel())
const removeModel = (mIdx) => { if (form.models.length > 1) form.models.splice(mIdx, 1) }
const addBahan    = (mIdx) => form.models[mIdx].bahans.push(emptyBahan())
const removeBahan = (mIdx, bIdx) => { if (form.models[mIdx].bahans.length > 1) form.models[mIdx].bahans.splice(bIdx, 1) }

const openCreate = () => {
  form.reset()
  form.clearErrors()
  form.po     = props.nextPo
  form.models = [emptyModel()]
  showModal.value = true
}
const submit = () => {
  form.post('/bahan-proses-potong', {
    onSuccess: () => { showModal.value = false; form.reset(); form.models = [emptyModel()] }
  })
}

// ── Edit form ──
const showEditModal = ref(false)
const editForm = useForm({ tanggal_potong: '', po: '', model: '', hasil_potongan: '', bahans: [] })

const openEdit = (mRow) => {
  editForm.tanggal_potong = detailGroup.value?.tanggal_potong?.substring?.(0, 10) ?? ''
  editForm.po             = detailGroup.value?.po ?? ''
  editForm.model          = mRow.model ?? ''
  editForm.hasil_potongan = mRow.hasil_potongan ?? ''
  editForm.bahans         = mRow.bahans.map(b => ({ kode_bahan: b.kode_bahan, yard: b.yard }))
  editForm.clearErrors()
  showEditModal.value = true
}
const editAddBahan    = () => editForm.bahans.push({ kode_bahan: '', yard: '' })
const editRemoveBahan = (bIdx) => { if (editForm.bahans.length > 1) editForm.bahans.splice(bIdx, 1) }
const submitEdit = () => {
  editForm.post('/bahan-proses-potong/update-model', {
    onSuccess: () => { showEditModal.value = false; showDetail.value = false }
  })
}
</script>
