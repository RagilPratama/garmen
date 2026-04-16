<template>
    <AdminLayout title="Barang Masuk Kantor">
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
        >
            <div
                class="h-1 bg-gradient-to-r from-teal-400 via-cyan-500 to-teal-400"
            ></div>

            <!-- Header -->
            <div
                class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center"
                    >
                        <svg
                            class="w-5 h-5 text-teal-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">
                            Barang Masuk Kantor
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ data?.total ?? 0 }} PO
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"
                            />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari model / surat jalan..."
                            class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent w-52 transition-all"
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors"
                        >
                            <svg
                                class="w-2.5 h-2.5 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <button
                        @click="openCreate"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 hover:from-teal-600 hover:to-cyan-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Tambah Data
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th
                                class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12"
                            >
                                No
                            </th>
                            <th
                                class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                            >
                                No. Surat Jalan
                            </th>
                            <th
                                class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                            >
                                Tgl Kirim
                            </th>
                            <th
                                class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                            >
                                Jumlah Model
                            </th>
                            <th
                                class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                            >
                                Total Pcs
                            </th>
                            <th
                                class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(group, index) in data?.data"
                            :key="group.no_surat_jalan"
                            class="border-b border-gray-50 hover:bg-teal-50/40 transition-colors group cursor-pointer"
                            :class="
                                index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'
                            "
                            @click="openDetail(group)"
                        >
                            <td class="px-5 py-3.5">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-teal-100 group-hover:text-teal-700 transition-colors"
                                >
                                    {{
                                        (data.current_page - 1) *
                                            data.per_page +
                                        index +
                                        1
                                    }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-mono bg-teal-50 text-teal-700 border border-teal-100 px-2 py-0.5 rounded"
                                >
                                    {{ group.no_surat_jalan || '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg
                                        class="w-3.5 h-3.5 text-gray-400 shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    {{ formatDate(group.tanggal_kirim) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full"
                                    >{{ group.jumlah_model }} model</span
                                >
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="inline-flex items-center justify-center px-2.5 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-full"
                                    >{{
                                        group.total_pcs.toLocaleString("id-ID")
                                    }}
                                    pcs</span
                                >
                            </td>
                            <td class="px-5 py-3.5 text-center" @click.stop>
                                <button
                                    @click="openDetail(group)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors"
                                >
                                    <svg
                                        class="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!data?.data?.length">
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-8 h-8 text-gray-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                    </div>
                                    <p
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        {{
                                            searchQuery
                                                ? "Data tidak ditemukan"
                                                : "Belum ada data"
                                        }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="data?.last_page > 1"
                class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3"
            >
                <p class="text-xs text-gray-500">
                    Menampilkan
                    <span class="font-semibold text-gray-700"
                        >{{ data.from }}–{{ data.to }}</span
                    >
                    dari
                    <span class="font-semibold text-gray-700">{{
                        data.total
                    }}</span>
                    PO
                </p>
                <div class="flex items-center gap-1 flex-wrap justify-center">
                    <Link
                        v-for="link in data.links"
                        :key="link.label"
                        :href="link.url ? appendSearch(link.url) : '#'"
                        class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
                        :class="
                            link.active
                                ? 'bg-teal-500 text-white font-semibold shadow-sm'
                                : link.url
                                  ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200'
                                  : 'text-gray-300 cursor-default pointer-events-none'
                        "
                        :preserve-scroll="true"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- DETAIL MODAL -->
        <Modal
            v-model="showDetail"
            :title="'Detail Surat Jalan: ' + (detailGroup?.no_surat_jalan ?? '')"
            size="xl"
        >
            <div v-if="detailGroup" class="space-y-4">
                <div
                    class="flex flex-wrap items-center gap-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3"
                >
                    <span
                        ><span class="font-medium text-gray-700"
                            >Tgl Kirim:</span
                        >
                        {{ formatDate(detailGroup.tanggal_kirim) }}</span
                    >
                    <span
                        ><span class="font-medium text-gray-700">No. SJ:</span>
                        <span class="ml-1 font-mono text-teal-600">{{
                            detailGroup.no_surat_jalan || "—"
                        }}</span>
                    </span>
                    <span
                        ><span class="font-medium text-gray-700"
                            >Jumlah Model:</span
                        >
                        {{ detailGroup.jumlah_model }}</span
                    >
                    <span
                        ><span class="font-medium text-gray-700"
                            >Total Pcs:</span
                        >
                        {{ detailGroup.total_pcs }} pcs</span
                    >
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 rounded-tl-lg"
                            >
                                Model
                            </th>
                            <th
                                class="text-center px-4 py-2.5 text-xs font-medium text-gray-500"
                            >
                                Pcs Barang Jadi
                            </th>
                            <th class="w-20 rounded-tr-lg"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="mRow in detailGroup.models"
                            :key="mRow.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-2.5 font-medium text-gray-700">
                                {{ mRow.model }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="text-teal-700 font-semibold">{{
                                    mRow.pcs_barang_jadi
                                }}</span>
                            </td>
                            <td
                                class="px-4 py-2.5 text-center flex items-center justify-center gap-2"
                            >
                                <button
                                    @click="openEdit(mRow)"
                                    class="text-blue-400 hover:text-blue-600 transition"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDelete(mRow.id)"
                                    class="text-red-400 hover:text-red-600 transition"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Modal>

        <!-- CREATE MODAL -->
        <Modal v-model="showModal" title="Tambah Barang Masuk Kantor" size="xl">
            <div
                v-if="!modelOptions.length"
                class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3"
            >
                <svg
                    class="w-4 h-4 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                Belum ada data dari
                <strong class="ml-1">Proses Finishing</strong>. Lengkapi Pcs
                Barang Jadi terlebih dahulu.
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <!-- SJ + Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >No. Surat Jalan</label
                        >
                        <div class="relative">
                            <input
                                v-model="noSuratJalan"
                                type="text"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition bg-white pr-16"
                                placeholder="SJ-KANTOR-001"
                            />
                            <span
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs bg-teal-100 text-teal-700 px-1.5 py-0.5 rounded font-medium"
                                >Auto</span
                            >
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Tanggal Kirim
                            <span class="text-red-500">*</span></label
                        >
                        <input
                            v-model="tanggalKirim"
                            type="date"
                            required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition bg-white"
                        />
                    </div>
                </div>

                <!-- Stock list -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700"
                            >Pilih Stok Finishing
                            <span class="text-red-500">*</span></label
                        >
                        <span
                            v-if="checkedItems.size > 0"
                            class="text-xs font-semibold text-teal-600 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-full"
                        >
                            {{ checkedItems.size }} item · {{ totalPcs }} pcs
                        </span>
                    </div>
                    <!-- Search in modal -->
                    <div class="relative mb-2">
                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"
                            />
                        </svg>
                        <input
                            v-model="modalSearch"
                            type="text"
                            placeholder="Filter PO / model..."
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition"
                        />
                    </div>
                    <!-- Item list -->
                    <div
                        class="max-h-72 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100"
                    >
                        <label
                            v-for="item in filteredOptions"
                            :key="itemKey(item)"
                            class="flex items-center gap-3 px-3 py-2.5 hover:bg-teal-50/50 cursor-pointer transition-colors"
                            :class="
                                checkedItems.has(itemKey(item))
                                    ? 'bg-teal-50/60'
                                    : ''
                            "
                        >
                            <input
                                type="checkbox"
                                :checked="checkedItems.has(itemKey(item))"
                                @change="toggleItem(item, $event)"
                                class="w-4 h-4 rounded border-gray-300 text-teal-500 focus:ring-teal-400 shrink-0"
                            />
                            <span
                                class="text-sm font-medium text-gray-700 flex-1 min-w-0 truncate"
                                >{{ item.model }}</span
                            >
                            <span class="text-xs text-gray-400 shrink-0"
                                >stok: {{ item.max_pcs }} pcs</span
                            >
                            <template v-if="checkedItems.has(itemKey(item))">
                                <input
                                    v-model.number="pcsPerItem[itemKey(item)]"
                                    type="number"
                                    min="1"
                                    @click.stop
                                    class="w-20 px-2 py-1.5 border border-teal-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-white text-right shrink-0"
                                    placeholder="Pcs"
                                />
                            </template>
                        </label>
                        <div
                            v-if="!filteredOptions.length"
                            class="px-4 py-8 text-center text-sm text-gray-400"
                        >
                            Tidak ada stok finishing yang tersedia
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 pt-2 border-t border-gray-100"
                >
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="processing || !checkedItems.size"
                        class="px-5 py-2.5 text-sm text-white bg-teal-500 hover:bg-teal-600 rounded-lg disabled:opacity-60 flex items-center gap-2"
                    >
                        <svg
                            v-if="processing"
                            class="animate-spin w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            />
                        </svg>
                        Tambah Data
                    </button>
                </div>
            </form>
        </Modal>

        <!-- EDIT MODAL -->
        <Modal v-model="showEditModal" title="Edit Pcs Barang Jadi" size="sm">
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div
                    v-if="editRow"
                    class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600"
                >
                    <span class="font-medium text-gray-700">Model:</span>
                    {{ editRow.model }}
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Pcs Barang Jadi
                        <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="editForm.pcs_barang_jadi"
                        type="number"
                        min="1"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition bg-white"
                        placeholder="0"
                    />
                    <p
                        v-if="editForm.errors?.pcs_barang_jadi"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ editForm.errors.pcs_barang_jadi }}
                    </p>
                </div>
                <div
                    class="flex justify-end gap-3 pt-2 border-t border-gray-100"
                >
                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="px-5 py-2.5 text-sm text-white bg-teal-500 hover:bg-teal-600 rounded-lg disabled:opacity-60 flex items-center gap-2"
                    >
                        <svg
                            v-if="editForm.processing"
                            class="animate-spin w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            />
                        </svg>
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
import { ref, computed, watch } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    data: Object,
    modelOptions: { type: Array, default: () => [] },
    nextSuratJalan: { type: String, default: "" },
});

const formatDate = (d) =>
    d
        ? new Date(d).toLocaleDateString("id-ID", {
              day: "2-digit",
              month: "short",
              year: "numeric",
          })
        : "—";

// Search
const searchQuery = ref(
    new URLSearchParams(window.location.search).get("search") ?? "",
);
let searchTimer = null;
watch(searchQuery, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            "/barang-masuk-kantor",
            { search: val || undefined },
            { preserveState: true, replace: true },
        );
    }, 350);
});
const appendSearch = (url) => {
    if (!searchQuery.value) return url;
    const u = new URL(url, window.location.origin);
    u.searchParams.set("search", searchQuery.value);
    return u.pathname + u.search;
};

// Detail modal
const showDetail = ref(false);
const detailGroup = ref(null);
const openDetail = (group) => {
    detailGroup.value = group;
    showDetail.value = true;
};

// Create modal
const showModal = ref(false);
const noSuratJalan = ref("");
const tanggalKirim = ref("");
const checkedItems = ref(new Set());
const pcsPerItem = ref({});
const hargaPerItem = ref({});
const processing = ref(false);
const modalSearch = ref("");

const itemKey = (item) => item.model;
const filteredOptions = computed(() => {
    if (!modalSearch.value) return props.modelOptions;
    const q = modalSearch.value.toLowerCase();
    return props.modelOptions.filter((o) =>
        o.model.toLowerCase().includes(q)
    );
});
const toggleItem = (item, e) => {
    const key = itemKey(item);
    const newSet = new Set(checkedItems.value);
    if (e.target.checked) {
        newSet.add(key);
        if (!pcsPerItem.value[key]) pcsPerItem.value[key] = item.max_pcs;
        if (!hargaPerItem.value[key]) hargaPerItem.value[key] = 0;
    } else {
        newSet.delete(key);
    }
    checkedItems.value = newSet;
};
const totalPcs = computed(() =>
    [...checkedItems.value].reduce(
        (sum, key) => sum + (parseInt(pcsPerItem.value[key]) || 0),
        0,
    ),
);
const openCreate = () => {
    noSuratJalan.value = ''; // Kosongkan agar backend generate otomatis
    tanggalKirim.value = new Date().toISOString().substring(0, 10);
    checkedItems.value = new Set();
    pcsPerItem.value = {};
    hargaPerItem.value = {};
    modalSearch.value = "";
    showModal.value = true;
};
const submit = () => {
    processing.value = true;
    const models = [...checkedItems.value].map((model) => {
        return {
            model,
            pcs_barang_jadi: parseInt(pcsPerItem.value[model]) || 0,
        };
    });
    router.post(
        "/barang-masuk-kantor",
        {
            no_surat_jalan: noSuratJalan.value || null,
            tanggal_kirim: tanggalKirim.value,
            models,
        },
        {
            onSuccess: () => {
                showModal.value = false;
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
            },
        },
    );
};

// Edit modal
const showEditModal = ref(false);
const editRow = ref(null);
const editForm = useForm({ pcs_barang_jadi: "" });

const openEdit = (mRow) => {
    editRow.value = mRow;
    editForm.pcs_barang_jadi = mRow.pcs_barang_jadi ?? "";
    editForm.clearErrors();
    showEditModal.value = true;
};
const submitEdit = () => {
    editForm.put(`/barang-masuk-kantor/${editRow.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            showDetail.value = false;
        },
    });
};

// Delete
const deleteId = ref(null);
const showConfirm = ref(false);
const deleteLoading = ref(false);
const confirmDelete = (id) => {
    deleteId.value = id;
    showConfirm.value = true;
};
const doDelete = () => {
    deleteLoading.value = true;
    router.delete(`/barang-masuk-kantor/${deleteId.value}`, {
        onSuccess: () => {
            deleteId.value = null;
            showConfirm.value = false;
            deleteLoading.value = false;
            showDetail.value = false;
        },
        onError: () => {
            deleteLoading.value = false;
        },
    });
};
</script>
