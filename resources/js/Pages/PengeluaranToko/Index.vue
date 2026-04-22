<template>
    <AdminLayout title="Pengeluaran Toko">
        <div class="space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="bg-gradient-to-br from-red-400 to-red-500 rounded-xl p-5 text-white shadow-md"
                >
                    <p
                        class="text-xs font-medium uppercase tracking-wide opacity-80"
                    >
                        Total Pengeluaran
                    </p>
                    <p class="text-2xl font-bold mt-1">
                        {{ formatRupiah(totalPengeluaran) }}
                    </p>
                    <p class="text-xs opacity-70 mt-1">All time</p>
                </div>
                <div
                    class="bg-gradient-to-br from-orange-400 to-orange-500 rounded-xl p-5 text-white shadow-md"
                >
                    <p
                        class="text-xs font-medium uppercase tracking-wide opacity-80"
                    >
                        Pengeluaran Bulan Ini
                    </p>
                    <p class="text-2xl font-bold mt-1">
                        {{ formatRupiah(pengeluaranBulanIni) }}
                    </p>
                    <p class="text-xs opacity-70 mt-1">
                        {{
                            new Date().toLocaleDateString("id-ID", {
                                month: "long",
                                year: "numeric",
                            })
                        }}
                    </p>
                </div>
                <div
                    class="bg-gradient-to-br from-violet-400 to-violet-500 rounded-xl p-5 text-white shadow-md"
                >
                    <p
                        class="text-xs font-medium uppercase tracking-wide opacity-80"
                    >
                        Rata-rata per Bulan
                    </p>
                    <p class="text-2xl font-bold mt-1">
                        {{ formatRupiah(totalPengeluaran / 12) }}
                    </p>
                    <p class="text-xs opacity-70 mt-1">Estimasi</p>
                </div>
            </div>

            <!-- Main Card -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
            >
                <div
                    class="h-1 bg-gradient-to-r from-red-400 via-orange-500 to-red-400"
                ></div>

                <!-- Header -->
                <div
                    class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center"
                        >
                            <svg
                                class="w-5 h-5 text-red-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800">
                                Pengeluaran Toko
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ data?.total ?? 0 }} Transaksi
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
                                placeholder="Cari kategori / keterangan..."
                                class="pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent w-64 transition-all"
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
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md whitespace-nowrap"
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
                            Tambah Pengeluaran
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
                                    Kategori
                                </th>
                                <th
                                    class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Keterangan
                                </th>
                                <th
                                    class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Jumlah
                                </th>
                                <th
                                    class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Metode
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
                                v-for="(item, index) in data?.data"
                                :key="item.id"
                                class="border-b border-gray-50 hover:bg-red-50/40 transition-colors group"
                                :class="
                                    index % 2 === 0
                                        ? 'bg-white'
                                        : 'bg-gray-50/30'
                                "
                            >
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-medium text-gray-500 group-hover:bg-red-100 group-hover:text-red-700 transition-colors"
                                    >
                                        {{
                                            (data.current_page - 1) *
                                                data.per_page +
                                            index +
                                            1
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    <span
                                        class="inline-flex items-center gap-1.5"
                                    >
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
                                        {{ formatDate(item.tanggal) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full"
                                        :class="getKategoriClass(item.kategori)"
                                    >
                                        {{ item.kategori }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ item.keterangan }}
                                </td>
                                <td
                                    class="px-5 py-3.5 text-right font-semibold text-red-600"
                                >
                                    {{ formatRupiah(item.jumlah) }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full capitalize"
                                        :class="
                                            item.metode_bayar === 'cash'
                                                ? 'bg-green-100 text-green-700'
                                                : item.metode_bayar ===
                                                    'transfer'
                                                  ? 'bg-blue-100 text-blue-700'
                                                  : 'bg-purple-100 text-purple-700'
                                        "
                                    >
                                        {{ item.metode_bayar }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div
                                        class="flex items-center justify-center gap-1.5"
                                    >
                                        <button
                                            @click="openEdit(item)"
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
                                            @click="confirmDelete(item.id)"
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
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!data?.data?.length">
                                <td colspan="7" class="px-4 py-16 text-center">
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
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                                />
                                            </svg>
                                        </div>
                                        <p
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            {{
                                                searchQuery
                                                    ? "Data tidak ditemukan"
                                                    : "Belum ada pengeluaran"
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
                        Transaksi
                    </p>
                    <div
                        class="flex items-center gap-1 flex-wrap justify-center"
                    >
                        <a
                            v-for="link in data.links"
                            :key="link.label"
                            :href="
                                link.url ? appendSearch(link.url) : undefined
                            "
                            @click.prevent="
                                link.url &&
                                router.visit(appendSearch(link.url), {
                                    preserveScroll: true,
                                })
                            "
                            class="min-w-[32px] h-8 px-2.5 flex items-center justify-center text-xs rounded-lg transition-all"
                            :class="
                                link.active
                                    ? 'bg-red-500 text-white font-semibold shadow-sm'
                                    : link.url
                                      ? 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200 cursor-pointer'
                                      : 'text-gray-300 cursor-default pointer-events-none'
                            "
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE/EDIT MODAL -->
        <Modal
            v-model="showModal"
            :title="editMode ? 'Edit Pengeluaran' : 'Tambah Pengeluaran'"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Tanggal <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.tanggal"
                        type="date"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition bg-white"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Kategori <span class="text-red-500">*</span></label
                    >
                    <select
                        v-model="form.kategori"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition bg-white"
                    >
                        <option value="">Pilih Kategori</option>
                        <option value="plastik">Plastik</option>
                        <option value="upah_tukang">Upah Tukang</option>
                        <option value="listrik">Listrik</option>
                        <option value="air">Air</option>
                        <option value="sewa">Sewa</option>
                        <option value="transportasi">Transportasi</option>
                        <option value="perlengkapan">Perlengkapan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Keterangan <span class="text-red-500">*</span></label
                    >
                    <textarea
                        v-model="form.keterangan"
                        required
                        rows="3"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition bg-white resize-none"
                        placeholder="Deskripsi pengeluaran..."
                    ></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Jumlah <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model.number="form.jumlah"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition bg-white"
                        placeholder="0"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Metode Bayar <span class="text-red-500">*</span></label
                    >
                    <div class="flex gap-4 mt-2">
                        <label
                            v-for="m in ['cash', 'transfer', 'debit']"
                            :key="m"
                            class="flex items-center gap-2 cursor-pointer"
                        >
                            <input
                                v-model="form.metode_bayar"
                                type="radio"
                                :value="m"
                                class="text-red-500"
                            />
                            <span class="text-sm text-gray-700 capitalize">{{
                                m
                            }}</span>
                        </label>
                    </div>
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
                        class="px-5 py-2.5 text-sm text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2"
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
                        {{ editMode ? "Update" : "Simpan" }}
                    </button>
                </div>
            </form>
        </Modal>

        <ConfirmDialog
            v-model="showConfirm"
            title="Hapus Pengeluaran?"
            message="Tindakan ini tidak dapat dibatalkan."
            :loading="deleteLoading"
            @confirm="doDelete"
        />
    </AdminLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/Modal.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    data: Object,
    totalPengeluaran: { type: Number, default: 0 },
    pengeluaranBulanIni: { type: Number, default: 0 },
    pengeluaranPerKategori: { type: Object, default: () => ({}) },
    isAdmin: { type: Boolean, default: false },
    userTokoId: { type: Number, default: null },
});

const formatDate = (d) =>
    d
        ? new Date(d).toLocaleDateString("id-ID", {
              day: "2-digit",
              month: "short",
              year: "numeric",
          })
        : "—";
const formatRupiah = (val) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(val ?? 0);

const getKategoriClass = (kategori) => {
    const classes = {
        plastik: "bg-blue-100 text-blue-700",
        upah_tukang: "bg-green-100 text-green-700",
        listrik: "bg-yellow-100 text-yellow-700",
        air: "bg-cyan-100 text-cyan-700",
        sewa: "bg-purple-100 text-purple-700",
        transportasi: "bg-orange-100 text-orange-700",
        perlengkapan: "bg-pink-100 text-pink-700",
        lainnya: "bg-gray-100 text-gray-700",
    };
    return classes[kategori] || "bg-gray-100 text-gray-700";
};

// Search
const searchQuery = ref(
    new URLSearchParams(window.location.search).get("search") ?? "",
);
let searchTimer = null;
watch(searchQuery, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            "/pengeluaran-toko",
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

// Modal
const showModal = ref(false);
const editMode = ref(false);
const editId = ref(null);
const processing = ref(false);
const form = ref({
    toko_id: props.userTokoId,
    tanggal: "",
    kategori: "",
    keterangan: "",
    jumlah: "",
    metode_bayar: "cash",
});

const openCreate = () => {
    editMode.value = false;
    editId.value = null;
    form.value = {
        toko_id: props.userTokoId,
        tanggal: new Date().toISOString().substring(0, 10),
        kategori: "",
        keterangan: "",
        jumlah: "",
        metode_bayar: "cash",
    };
    showModal.value = true;
};

const openEdit = (item) => {
    editMode.value = true;
    editId.value = item.id;
    form.value = {
        toko_id: item.toko_id,
        tanggal: item.tanggal,
        kategori: item.kategori,
        keterangan: item.keterangan,
        jumlah: item.jumlah,
        metode_bayar: item.metode_bayar,
    };
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    const url = editMode.value
        ? `/pengeluaran-toko/${editId.value}`
        : "/pengeluaran-toko";
    const method = editMode.value ? "put" : "post";

    router[method](url, form.value, {
        onSuccess: () => {
            showModal.value = false;
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
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
    router.delete(`/pengeluaran-toko/${deleteId.value}`, {
        onSuccess: () => {
            deleteId.value = null;
            showConfirm.value = false;
            deleteLoading.value = false;
        },
        onError: () => {
            deleteLoading.value = false;
        },
    });
};
</script>
