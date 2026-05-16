<template>
    <DataTable title="Master Bahan" :data="data" :columns="columns" base-path="/master-bahan" @open-create="openCreate" @open-edit="openEdit">
        <template #modal>
            <Modal v-model="showModal" :title="editItem ? 'Edit Bahan' : 'Tambah Bahan'">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Bahan
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.nama_bahan"
                            type="text"
                            required
                            autofocus
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                            placeholder="Nama bahan"
                        />
                        <p v-if="form.errors.nama_bahan" class="mt-1 text-xs text-red-500">{{ form.errors.nama_bahan }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea
                            v-model="form.keterangan"
                            rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white resize-none"
                            placeholder="Keterangan tambahan (opsional)"
                        ></textarea>
                        <p v-if="form.errors.keterangan" class="mt-1 text-xs text-red-500">{{ form.errors.keterangan }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 text-sm text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ editItem ? 'Simpan Perubahan' : 'Tambah Data' }}
                        </button>
                    </div>
                </form>
            </Modal>
        </template>
    </DataTable>
</template>

<script setup>
import { ref, defineAsyncComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';

// Lazy load Modal component
const Modal = defineAsyncComponent(() => import('@/Components/Modal.vue'));

defineProps({ data: Object });

const columns = [
    { key: 'nama_bahan', label: 'Nama Bahan' },
    { key: 'keterangan', label: 'Keterangan' },
];

const showModal = ref(false);
const editItem = ref(null);
const form = useForm({ nama_bahan: '', keterangan: '' });

const openCreate = () => {
    editItem.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (item) => {
    editItem.value = item;
    form.nama_bahan = item.nama_bahan ?? '';
    form.keterangan = item.keterangan ?? '';
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    if (editItem.value) {
        form.put(`/master-bahan/${editItem.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post('/master-bahan', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};
</script>
