<template>
    <AdminLayout title="Buat Surat Jalan Bahan Masuk">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Buat Surat Jalan Bahan Masuk</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Generate nomor surat jalan untuk bahan masuk</p>
                </div>
                <a href="/surat-jalan-masuk" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">← Kembali</a>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No Surat Jalan</label>
                            <input
                                v-model="form.no_surat_jalan"
                                type="text"
                                required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent bg-amber-50"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <input
                                v-model="form.tanggal"
                                type="date"
                                required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan (Opsional)</label>
                        <input
                            v-model="form.keterangan"
                            type="text"
                            placeholder="Catatan..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="w-full px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-orange-500 text-white text-sm font-semibold rounded-lg transition shadow-sm disabled:opacity-50"
                    >
                        {{ saving ? 'Menyimpan...' : 'Buat Surat Jalan' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ nextSuratJalan: String });

const form = ref({
    no_surat_jalan: props.nextSuratJalan,
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: '',
});

const saving = ref(false);

const submit = () => {
    saving.value = true;
    router.post('/surat-jalan-masuk', form.value, {
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>
