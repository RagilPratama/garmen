<template>
  <AdminLayout :title="title">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">{{ title }}</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ data?.total ?? 0 }} total data</p>
        </div>
        <button @click="$emit('open-create')" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Data
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium w-10">No</th>
              <th v-for="col in columns" :key="col.key"
                class="text-left px-4 py-3 text-gray-500 font-medium whitespace-nowrap">
                {{ col.label }}
              </th>
              <th class="text-center px-4 py-3 text-gray-500 font-medium w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(item, index) in data?.data" :key="item.id"
              class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + index + 1 }}</td>
              <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]">
                  <template v-if="col.type === 'status'">
                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                      :class="statusClass(item[col.key])">{{ item[col.key] }}</span>
                  </template>
                  <template v-else-if="col.type === 'currency'">
                    <span class="text-gray-700">{{ formatRupiah(item[col.key]) }}</span>
                  </template>
                  <template v-else-if="col.type === 'date'">
                    <span class="text-gray-700">{{ formatDate(item[col.key]) }}</span>
                  </template>
                  <template v-else>
                    <span class="text-gray-700">{{ item[col.key] ?? '-' }}</span>
                  </template>
                </slot>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                  <button @click="$emit('open-edit', item)"
                    class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="confirmDelete(item.id)"
                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!data?.data?.length">
              <td :colspan="columns.length + 2" class="px-4 py-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm">Belum ada data. Klik "Tambah Data" untuk memulai.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Menampilkan {{ data.from }}-{{ data.to }} dari {{ data.total }} data
        </p>
        <div class="flex items-center gap-1">
          <Link v-for="link in data.links" :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1.5 text-sm rounded-lg transition-colors"
            :class="link.active ? 'bg-amber-500 text-white' : 'text-gray-600 hover:bg-gray-100'"
            :preserve-scroll="true"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="deleteId" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="deleteId = null"></div>
      <div class="relative bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
        <p class="text-gray-600 text-sm mb-5">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex gap-3 justify-end">
          <button @click="deleteId = null"
            class="px-4 py-2 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
            Batal
          </button>
          <button @click="doDelete"
            class="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600 rounded-lg">
            Hapus
          </button>
        </div>
      </div>
    </div>

    <!-- Form Modal Slot -->
    <slot name="modal" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  title: String,
  data: Object,
  columns: Array,
  basePath: String,
})

defineEmits(['open-create', 'open-edit'])

const deleteId = ref(null)

const confirmDelete = (id) => { deleteId.value = id }
const doDelete = () => {
  router.delete(`${props.basePath}/${deleteId.value}`, {
    onSuccess: () => { deleteId.value = null }
  })
}

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
const statusClass = (s) => ({
  'bg-green-100 text-green-700': s === 'diterima' || s === 'lunas',
  'bg-yellow-100 text-yellow-700': s === 'pending',
  'bg-red-100 text-red-700': s === 'ditolak' || s === 'batal',
})
</script>
