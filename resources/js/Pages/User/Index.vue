<template>
    <DataTable title="Manajemen User" :data="users" :columns="columns" base-path="/users" @open-create="openCreate" @open-edit="openEdit">
        <!-- Custom cell for name with avatar -->
        <template #cell-name="{ item }">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">{{ item.name.charAt(0).toUpperCase() }}</span>
                </div>
                <span class="text-gray-700 font-medium">{{ item.name }}</span>
            </div>
        </template>

        <!-- Custom cell for role with badge -->
        <template #cell-role="{ item }">
            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full" :class="getRoleBadgeClass(item.role)">
                {{ getRoleLabel(item.role) }}
            </span>
        </template>

        <!-- Modal Form -->
        <template #modal>
            <Modal v-model="showModal" :title="editItem ? 'Edit User' : 'Tambah User'">
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                            placeholder="Nama lengkap"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                            placeholder="email@example.com"
                            required
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Role
                            <span class="text-red-500">*</span>
                        </label>
                        <SearchableSelect v-model="form.role" :options="roleOptions" placeholder="Pilih Role" />
                        <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
                        <p v-if="form.role === 'adminjomei' || form.role === 'adminkamiko'" class="mt-1 text-xs text-gray-500">
                            Toko akan otomatis di-set ke {{ form.role === 'adminjomei' ? 'Jomei' : 'Kamiko' }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                            <span v-if="!editItem" class="text-red-500">*</span>
                            <span v-else class="text-gray-500 text-xs">(Kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                            placeholder="Minimal 8 karakter"
                            :required="!editItem"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Konfirmasi Password
                            <span v-if="!editItem || (editItem && form.password)" class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-white"
                            placeholder="Ulangi password"
                        />
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Buttons -->
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
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    users: Object,
    roles: Array,
});

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Role' },
];

const showModal = ref(false);
const editItem = ref(null);

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});

// Convert roles array to SearchableSelect format
const roleOptions = computed(() => {
    return props.roles.map((role) => ({
        value: role.value,
        label: role.label,
    }));
});

const openCreate = () => {
    editItem.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (user) => {
    editItem.value = user;
    form.name = user.name || '';
    form.email = user.email || '';
    form.role = user.role || '';
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editItem.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editItem.value) {
        form.transform((payload) => {
            const data = {
                name: payload.name,
                email: payload.email,
                role: payload.role,
            };

            if (payload.password) {
                data.password = payload.password;
                data.password_confirmation = payload.password_confirmation;
            }

            return data;
        }).put(`/users/${editItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
            onFinish: () => {
                form.transform((data) => data);
            },
        });
    } else {
        form.post('/users', {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

const getRoleLabel = (role) => {
    const roleMap = {
        superadmin: 'Super Admin',
        admingarmen: 'Admin Garmen',
        adminkantor: 'Admin Kantor',
        adminjomei: 'Admin Jomei',
        adminkamiko: 'Admin Kamiko',
    };
    return roleMap[role] || role;
};

const getRoleBadgeClass = (role) => {
    const classMap = {
        superadmin: 'bg-purple-100 text-purple-800',
        admingarmen: 'bg-blue-100 text-blue-800',
        adminkantor: 'bg-green-100 text-green-800',
        adminjomei: 'bg-amber-100 text-amber-800',
        adminkamiko: 'bg-orange-100 text-orange-800',
    };
    return classMap[role] || 'bg-gray-100 text-gray-800';
};
</script>
