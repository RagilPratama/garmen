<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-pulse">
    <!-- Gradient bar -->
    <div class="h-1 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200"></div>

    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-gray-200"></div>
        <div class="space-y-1.5">
          <div class="h-4 w-36 bg-gray-200 rounded-md"></div>
          <div class="h-3 w-20 bg-gray-100 rounded-md"></div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <div class="h-9 w-48 bg-gray-100 rounded-xl"></div>
        <div class="h-9 w-32 bg-gray-200 rounded-xl"></div>
      </div>
    </div>

    <!-- Table head -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-100">
            <th class="px-5 py-3.5 w-12"><div class="h-3 w-6 bg-gray-200 rounded"></div></th>
            <th class="px-5 py-3.5" v-for="i in cols - 1" :key="i">
              <div class="h-3 rounded bg-gray-200" :style="`width:${40 + (i * 17) % 50}px`"></div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row"
            class="border-b border-gray-50"
            :class="row % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">
            <!-- No column -->
            <td class="px-5 py-3.5">
              <div class="w-6 h-6 rounded-full bg-gray-200"></div>
            </td>
            <!-- Data columns -->
            <td class="px-5 py-3.5" v-for="col in cols - 1" :key="col">
              <div class="h-3.5 rounded"
                :class="col === 1 ? 'bg-gray-300' : 'bg-gray-200'"
                :style="`width:${55 + ((row + col) * 23) % 80}px`"/>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination stub -->
    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
      <div class="h-3 w-40 bg-gray-200 rounded"></div>
      <div class="flex gap-1">
        <div v-for="i in 5" :key="i" class="w-8 h-8 rounded-lg bg-gray-200"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: { type: Number, default: 4 },
  cols: { type: Number, default: 4 },
})
</script>
