<template>
    <AdminLayout title="Kas Gudang (Kantor)">
        <div class="space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl p-5 text-white shadow-md"
                >
                    <p
                        class="text-xs font-medium uppercase tracking-wide opacity-80"
                    >
                        Cash
                    </p>
                    <p class="text-2xl font-bold mt-1">
                        {{ formatRupiah(Number(saldoKas?.saldo_cash || 0)) }}
                    </p>
                </div>
                <div
                    class="bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-xl p-5 text-white shadow-md"
                >
                    <p
                        class="text-xs font-medium uppercase tracking-wide opacity-80"
                    >
                        Transfer
                    </p>
                    <p class="text-2xl font-bold mt-1">
                        {{ formatRupiah(Number(saldoKas?.saldo_transfer || 0)) }}
                    </p>
                </div>
                <div
                    class="bg-gradient-to-br from-purple-400 to-purple-500 rounded-xl p-5 text-white shadow-md"
                >
                    <p
                        class="text-xs font-medium uppercase tracking-wide opacity-80"
                    >
                        Debit
                    </p>
                    <p class="text-2xl font-bold mt-1">
                        {{ formatRupiah(Number(saldoKas?.saldo_debit || 0)) }}
                    </p>
                </div>
            </div>

            <!-- Total Saldo -->
            <div
                class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl p-6 text-white shadow-lg"
            >
                <p
                    class="text-sm font-medium uppercase tracking-wide opacity-90"
                >
                    Total Saldo Kas Gudang
                </p>
                <p class="text-3xl font-bold mt-2">
                    {{
                        formatRupiah(
                            Number(saldoKas?.saldo_cash || 0) +
                                Number(saldoKas?.saldo_transfer || 0) +
                                Number(saldoKas?.saldo_debit || 0),
                        )
                    }}
                </p>
            </div>

            <!-- Main Card -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
            >
                <div
                    class="h-1 bg-gradient-to-r from-blue-400 via-cyan-500 to-blue-400"
                ></div>

                <!-- Header -->
                <div
                    class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center"
                        >
                            <svg
                                class="w-5 h-5 text-blue-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800">
                                Kas Gudang
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ kasData?.total ?? 0 }} Transaksi
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="openCreate"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap"
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
                            Tambah Transaksi
                        </button>
                        <button
                            v-if="$page.props.auth.user.role === 'admin'"
                            @click="openTransfer"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap"
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
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                                />
                            </svg>
                            Transfer ke Garmen
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row gap-3">
                        <input
                            type="date"
                            v-model="filters.tanggal_dari"
                            @change="applyFilters"
                            placeholder="Dari Tanggal"
                            class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                        />

                        <input
                            type="date"
                            v-model="filters.tanggal_sampai"
                            @change="applyFilters"
                            placeholder="Sampai Tanggal"
                            class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                        />

                        <select
                            v-model="filters.jenis"
                            @change="applyFilters"
                            class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="masuk">Masuk</option>
                            <option value="keluar">Keluar</option>
                        </select>

                        <button
                            @click="resetFilters"
                            class="px-4 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap"
                        >
                            Reset Filter
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
                                    Tanggal
                                </th>
                                <th
                                    class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Jenis
                                </th>
                                <th
                                    class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Metode
                                </th>
                                <th
                                    class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Kategori
                                </th>
                                <th
                                    class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Jumlah
                                </th>
                                <th
                                    class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Saldo
                                </th>
                                <th
                                    class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Keterangan
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
                                v-for="(kas, index) in kasData.data"
                                :key="kas.id"
                                class="border-b border-gray-50 hover:bg-blue-50/40 transition-colors group"
                                :class="
                                    index % 2 === 0
                                        ? 'bg-white'
                                        : 'bg-gray-50/30'
                                "
                            >
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-700 transition-colors"
                                    >
                                        {{
                                            (kasData.current_page - 1) *
                                                kasData.per_page +
                                            index +
                                            1
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    {{ formatDate(kas.tanggal) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full"
                                        :class="
                                            kas.jenis === 'masuk'
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700'
                                        "
                                    >
                                        {{
                                            kas.jenis === "masuk"
                                                ? "Masuk"
                                                : "Keluar"
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full capitalize"
                                        :class="
                                            getMetodeClass(kas.metode_bayar)
                                        "
                                    >
                                        {{ kas.metode_bayar }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ kas.kategori }}
                                </td>
                                <td
                                    class="px-5 py-3.5 text-right font-semibold"
                                    :class="
                                        kas.jenis === 'masuk'
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ kas.jenis === "masuk" ? "+" : "-" }}
                                    {{ formatRupiah(kas.jumlah) }}
                                </td>
                                <td
                                    class="px-5 py-3.5 text-right text-gray-700 font-semibold"
                                >
                                    {{ formatRupiah(kas.saldo_sesudah) }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    {{ kas.keterangan || "-" }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <button
                                        @click="confirmDelete(kas.id)"
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
                            <tr v-if="!kasData?.data?.length">
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center gap-3"
                                    >
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
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                        </div>
                                        <p
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Belum ada transaksi kas
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="kasData?.last_page > 1"
                    class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3"
                >
                    <p class="text-xs text-gray-500">
                        Menampilkan
                        <span class="font-semibold text-gray-700"
                            >{{ kasData.from }}–{{ kasData.to }}</span
                        >
                        dari
                        <span class="font-semibold text-gray-700">{{
                            kasData.total
                        }}</span>
                        Transaksi
                    </p>
                    <div
                        class="flex items-center gap-1 flex-wrap justify-center"
                    >
                        <component
                            v-for="link in kasData.links"
                            :key="link.label"
                            :is="link.url ? Link : 'span'"
                            :href="link.url"
                            @click.prevent="
                                link.url &&
                                router.visit(link.url, { preserveScroll: true })
                            "
                            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
                            :class="
                                link.active
                                    ? 'bg-blue-500 text-white font-semibold shadow-sm'
                                    : link.url
                                      ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200 cursor-pointer'
                                      : 'text-gray-300 cursor-default pointer-events-none'
                            "
                        >
                            <span v-html="link.label"></span>
                        </component>
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <Modal v-model="showModal" title="Tambah Transaksi Kas Gudang">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Tanggal <span class="text-red-500">*</span></label
                    >
                    <input
                        type="date"
                        v-model="form.tanggal"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Jenis <span class="text-red-500">*</span></label
                    >
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.jenis"
                                type="radio"
                                value="masuk"
                                class="text-blue-500"
                            />
                            <span class="text-sm text-gray-700">Masuk</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.jenis"
                                type="radio"
                                value="keluar"
                                class="text-blue-500"
                            />
                            <span class="text-sm text-gray-700">Keluar</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Metode Bayar <span class="text-red-500">*</span></label
                    >
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.metode_bayar"
                                type="radio"
                                value="cash"
                                class="text-blue-500"
                            />
                            <span class="text-sm text-gray-700">Cash</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.metode_bayar"
                                type="radio"
                                value="transfer"
                                class="text-blue-500"
                            />
                            <span class="text-sm text-gray-700">Transfer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.metode_bayar"
                                type="radio"
                                value="debit"
                                class="text-blue-500"
                            />
                            <span class="text-sm text-gray-700">Debit</span>
                        </label>
                    </div>
                </div>

                <!-- Rekening field - shown when transfer is selected -->
                <div
                    v-show="form.metode_bayar === 'transfer'"
                    class="transition-all"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Rekening</label
                    >
                    <select
                        v-model="form.rekening_id"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                    >
                        <option value="">Pilih Rekening (Opsional)</option>
                        <option
                            v-for="rek in rekenings || []"
                            :key="rek.id"
                            :value="rek.id"
                        >
                            {{ rek.bank }} - {{ rek.nama }}
                            {{
                                rek.nomor_rekening
                                    ? "(" + rek.nomor_rekening + ")"
                                    : ""
                            }}
                        </option>
                    </select>
                    <p
                        v-if="!rekenings || rekenings.length === 0"
                        class="text-xs text-gray-400 mt-1"
                    >
                        Belum ada rekening.
                        <a href="/rekening" class="text-blue-500 hover:underline"
                            >Tambah rekening</a
                        >
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Kategori <span class="text-red-500">*</span></label
                    >
                    <input
                        type="text"
                        v-model="form.kategori"
                        required
                        placeholder="Contoh: Penerimaan Barang, Operasional, dll"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Jumlah <span class="text-red-500">*</span></label
                    >
                    <input
                        type="number"
                        v-model.number="form.jumlah"
                        required
                        min="0"
                        step="0.01"
                        placeholder="0"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Keterangan</label
                    >
                    <textarea
                        v-model="form.keterangan"
                        rows="3"
                        placeholder="Deskripsi transaksi..."
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-white resize-none"
                    ></textarea>
                </div>

                <div
                    class="flex justify-end gap-3 pt-2 border-t border-gray-100"
                >
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-5 py-2.5 text-sm text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2"
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
                        Simpan
                    </button>
                </div>
            </form>
        </Modal>

        <ConfirmDialog
            v-model="showConfirm"
            title="Hapus Transaksi?"
            message="Tindakan ini tidak dapat dibatalkan."
            :loading="deleteLoading"
            @confirm="doDelete"
        />

        <!-- TRANSFER MODAL -->
        <Modal v-model="showTransferModal" title="Transfer ke Garmen">
            <form @submit.prevent="submitTransfer" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Tanggal <span class="text-red-500">*</span></label
                    >
                    <input
                        type="date"
                        v-model="transferForm.tanggal"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition bg-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Metode Bayar <span class="text-red-500">*</span></label
                    >
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="transferForm.metode_bayar"
                                type="radio"
                                value="cash"
                                class="text-purple-500"
                            />
                            <span class="text-sm text-gray-700">Cash</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="transferForm.metode_bayar"
                                type="radio"
                                value="transfer"
                                class="text-purple-500"
                            />
                            <span class="text-sm text-gray-700">Transfer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="transferForm.metode_bayar"
                                type="radio"
                                value="debit"
                                class="text-purple-500"
                            />
                            <span class="text-sm text-gray-700">Debit</span>
                        </label>
                    </div>
                </div>

                <!-- Rekening field - shown when transfer is selected -->
                <div
                    v-show="transferForm.metode_bayar === 'transfer'"
                    class="transition-all"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Rekening</label
                    >
                    <select
                        v-model="transferForm.rekening_id"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition bg-white"
                    >
                        <option value="">Pilih Rekening (Opsional)</option>
                        <option
                            v-for="rek in rekenings || []"
                            :key="rek.id"
                            :value="rek.id"
                        >
                            {{ rek.bank }} - {{ rek.nama }}
                            {{
                                rek.nomor_rekening
                                    ? "(" + rek.nomor_rekening + ")"
                                    : ""
                            }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Jumlah <span class="text-red-500">*</span></label
                    >
                    <input
                        type="number"
                        v-model.number="transferForm.jumlah"
                        required
                        min="0"
                        step="0.01"
                        placeholder="0"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition bg-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Keterangan</label
                    >
                    <textarea
                        v-model="transferForm.keterangan"
                        rows="3"
                        placeholder="Keterangan transfer..."
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition bg-white resize-none"
                    ></textarea>
                </div>

                <div
                    class="flex justify-end gap-3 pt-2 border-t border-gray-100"
                >
                    <button
                        type="button"
                        @click="showTransferModal = false"
                        class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="transferProcessing"
                        class="px-5 py-2.5 text-sm text-white bg-purple-500 hover:bg-purple-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2"
                    >
                        <svg
                            v-if="transferProcessing"
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
                        Transfer
                    </button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    kasData: Object,
    saldoKas: Object,
    rekenings: Array,
    filters: Object,
});

const showModal = ref(false);
const processing = ref(false);
const showConfirm = ref(false);
const deleteLoading = ref(false);
const deleteId = ref(null);
const showTransferModal = ref(false);
const transferProcessing = ref(false);

const form = reactive({
    tanggal: new Date().toISOString().split("T")[0],
    jenis: "",
    metode_bayar: "cash",
    rekening_id: "",
    kategori: "",
    jumlah: "",
    keterangan: "",
});

const filters = reactive({
    tanggal_dari: props.filters.tanggal_dari || "",
    tanggal_sampai: props.filters.tanggal_sampai || "",
    jenis: props.filters.jenis || "",
});

const transferForm = reactive({
    entitas_penerima: "garmen",
    tanggal: new Date().toISOString().split("T")[0],
    metode_bayar: "cash",
    rekening_id: "",
    jumlah: "",
    keterangan: "",
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

const applyFilters = () => {
    router.get(route("kas-gudang.index"), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.tanggal_dari = "";
    filters.tanggal_sampai = "";
    filters.jenis = "";
    applyFilters();
};

const openCreate = () => {
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    router.post(route("kas-gudang.store"), form, {
        onSuccess: () => {
            showModal.value = false;
            resetForm();
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const confirmDelete = (id) => {
    deleteId.value = id;
    showConfirm.value = true;
};

const doDelete = () => {
    deleteLoading.value = true;
    router.delete(route("kas-gudang.destroy", deleteId.value), {
        onFinish: () => {
            deleteLoading.value = false;
            showConfirm.value = false;
        },
    });
};

const resetForm = () => {
    form.tanggal = new Date().toISOString().split("T")[0];
    form.jenis = "";
    form.metode_bayar = "cash";
    form.rekening_id = "";
    form.kategori = "";
    form.jumlah = "";
    form.keterangan = "";
};

const getMetodeClass = (metode) => {
    const classes = {
        cash: "bg-green-100 text-green-700",
        transfer: "bg-blue-100 text-blue-700",
        debit: "bg-purple-100 text-purple-700",
    };
    return classes[metode] || "bg-gray-100 text-gray-700";
};

const openTransfer = () => {
    showTransferModal.value = true;
};

const submitTransfer = () => {
    transferProcessing.value = true;
    router.post(route("kas-gudang.transfer"), transferForm, {
        onSuccess: () => {
            showTransferModal.value = false;
            resetTransferForm();
        },
        onFinish: () => {
            transferProcessing.value = false;
        },
    });
};

const resetTransferForm = () => {
    transferForm.entitas_penerima = "garmen";
    transferForm.tanggal = new Date().toISOString().split("T")[0];
    transferForm.metode_bayar = "cash";
    transferForm.rekening_id = "";
    transferForm.jumlah = "";
    transferForm.keterangan = "";
};
</script>
