<template>
  <div class="space-y-6 animate-pulse">

    <!-- Greeting -->
    <div>
      <div class="h-6 w-28 bg-gray-200 rounded-md"></div>
      <div class="h-3.5 w-56 bg-gray-100 rounded-md mt-1.5"></div>
    </div>

    <!-- Omset Cards — grid cols-1 sm:cols-2 gap-4 lg:cols-4 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:grid-cols-4">
      <div v-for="(c, i) in [['from-amber-300 to-amber-400'], ['from-orange-300 to-orange-400'], ['from-emerald-300 to-emerald-400'], ['from-emerald-500 to-teal-500']]" :key="i"
        class="bg-gradient-to-br rounded-xl p-5 shadow-md space-y-2" :class="c[0]">
        <div class="h-2.5 w-32 bg-white/50 rounded"></div>
        <div class="h-7 w-36 bg-white/60 rounded-md mt-1"></div>
        <div class="h-2.5 w-40 bg-white/40 rounded mt-1"></div>
      </div>
    </div>

    <!-- Hutang banner -->
    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-red-100 shrink-0"></div>
      <div class="flex-1 space-y-1.5">
        <div class="h-3 w-16 bg-red-200 rounded"></div>
        <div class="h-6 w-40 bg-red-200 rounded-md"></div>
        <div class="h-3 w-56 bg-red-100 rounded"></div>
      </div>
    </div>

    <!-- Stok Cards — cols-1 sm:cols-3 -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div v-for="(bg, i) in ['bg-blue-50', 'bg-indigo-50', 'bg-emerald-50']" :key="i"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl shrink-0" :class="bg"></div>
        <div class="space-y-1.5">
          <div class="h-3 w-20 bg-gray-200 rounded"></div>
          <div class="h-7 w-28 bg-gray-200 rounded-md"></div>
          <div class="h-3 w-32 bg-gray-100 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Pipeline Produksi -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <div class="flex items-center gap-2 mb-4">
        <div class="h-4 w-36 bg-gray-200 rounded"></div>
        <div class="h-3 w-36 bg-gray-100 rounded"></div>
      </div>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div v-for="(border, i) in ['border-amber-100', 'border-orange-100', 'border-cyan-100', 'border-purple-100']" :key="i"
          class="rounded-xl border p-4 flex flex-col items-center gap-2" :class="border">
          <div class="w-10 h-10 rounded-full bg-gray-200"></div>
          <div class="h-3 w-20 bg-gray-200 rounded"></div>
          <div class="h-2.5 w-12 bg-gray-100 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Top Model Terlaris -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <div class="h-4 w-32 bg-gray-200 rounded"></div>
          <div class="h-3 w-48 bg-gray-100 rounded"></div>
        </div>
        <div class="h-3.5 w-10 bg-amber-100 rounded"></div>
      </div>
      <div class="space-y-3">
        <div v-for="(w, i) in ['90%', '72%', '55%', '38%', '22%']" :key="i" class="flex items-center gap-3">
          <!-- rank badge -->
          <div class="w-7 h-7 rounded-full shrink-0"
            :class="i===0?'bg-amber-200':i===1?'bg-gray-200':i===2?'bg-orange-200':'bg-gray-100'"></div>
          <!-- name + bar -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
              <div class="h-3.5 bg-gray-200 rounded" :style="`width:${w}`"></div>
              <div class="h-3 w-14 bg-gray-100 rounded ml-3 shrink-0"></div>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full"
                :class="i===0?'bg-amber-200':i===1?'bg-gray-300':i===2?'bg-orange-200':'bg-blue-100'"
                :style="`width:${w}`"></div>
            </div>
          </div>
          <!-- omset -->
          <div class="h-4 w-24 bg-gray-200 rounded shrink-0"></div>
        </div>
      </div>
    </div>

    <!-- Recent Tables (2 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

      <!-- Bahan Masuk Terbaru -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="h-4 w-40 bg-gray-200 rounded"></div>
          <div class="h-3 w-20 bg-amber-100 rounded"></div>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-5 py-2.5"><div class="h-2.5 w-16 bg-gray-200 rounded"></div></th>
              <th class="text-left px-5 py-2.5"><div class="h-2.5 w-10 bg-gray-200 rounded"></div></th>
              <th class="text-right px-5 py-2.5"><div class="h-2.5 w-8 bg-gray-200 rounded ml-auto"></div></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(w, i) in [75, 55, 88, 62, 45]" :key="i">
              <td class="px-5 py-3"><div class="h-3.5 bg-gray-200 rounded" :style="`width:${w}%`"></div></td>
              <td class="px-5 py-3"><div class="h-3.5 w-20 bg-gray-100 rounded"></div></td>
              <td class="px-5 py-3"><div class="h-3.5 w-10 bg-gray-100 rounded ml-auto"></div></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Penjualan Terbaru -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="h-4 w-36 bg-gray-200 rounded"></div>
          <div class="flex gap-3">
            <div class="h-3 w-10 bg-amber-100 rounded"></div>
            <div class="h-3 w-14 bg-orange-100 rounded"></div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-3 py-2.5"><div class="h-2.5 w-16 bg-gray-200 rounded"></div></th>
                <th class="text-left px-3 py-2.5"><div class="h-2.5 w-16 bg-gray-200 rounded"></div></th>
                <th class="text-right px-3 py-2.5"><div class="h-2.5 w-10 bg-gray-200 rounded ml-auto"></div></th>
                <th class="text-center px-2 py-2.5"><div class="h-2.5 w-6 bg-gray-200 rounded mx-auto"></div></th>
                <th class="text-center px-2 py-2.5"><div class="h-2.5 w-10 bg-gray-200 rounded mx-auto"></div></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="(w, i) in [80, 65, 90, 55, 75]" :key="i">
                <td class="px-3 py-2.5"><div class="h-3.5 bg-gray-200 rounded" :style="`width:${w}%`"></div></td>
                <td class="px-3 py-2.5"><div class="h-3.5 bg-gray-100 rounded" :style="`width:${100-w}%`"></div></td>
                <td class="px-3 py-2.5"><div class="h-3.5 w-20 bg-gray-200 rounded ml-auto"></div></td>
                <td class="px-2 py-2.5 text-center"><div class="h-5 w-10 bg-amber-100 rounded-full mx-auto"></div></td>
                <td class="px-2 py-2.5 text-center"><div class="h-5 w-12 bg-green-100 rounded-full mx-auto"></div></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>
