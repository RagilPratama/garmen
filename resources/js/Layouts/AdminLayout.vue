<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Mobile backdrop -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-black/50 z-40 lg:hidden"
    />

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gray-900 transition-all duration-300 ease-in-out"
      :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-16'"
    >
      <!-- Logo -->
      <div class="flex items-center h-16 px-4 bg-amber-500 flex-shrink-0">
        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 2a8 8 0 100 16A8 8 0 0010 2zm0 14a6 6 0 100-12 6 6 0 000 12zm-1-5a1 1 0 102 0V7a1 1 0 10-2 0v4zm1-6a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
          </svg>
        </div>
        <span v-if="sidebarOpen" class="ml-3 text-white font-bold text-lg truncate">GarmenApp</span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto sidebar-scroll py-4 space-y-1 px-2">
        <template v-for="item in navItems" :key="item.name">
          <!-- Group Header -->
          <div v-if="item.type === 'header'" class="px-2 pt-4 pb-1">
            <span v-if="sidebarOpen" class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ item.label }}</span>
            <div v-else class="border-t border-gray-700 my-1"></div>
          </div>
          <!-- Nav Link -->
          <Link v-else
            :href="item.href"
            @click="() => { if (typeof window !== 'undefined' && window.innerWidth < 1024) sidebarOpen = false }"
            class="flex items-center rounded-lg px-2 py-2.5 text-sm font-medium transition-colors group"
            :class="isActive(item.href)
              ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30'
              : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
          >
            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
            <span v-if="sidebarOpen" class="ml-3 truncate">{{ item.label }}</span>
            <div v-if="!sidebarOpen" class="absolute left-16 bg-gray-800 text-white text-xs rounded px-2 py-1 ml-2 opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50 shadow-lg">
              {{ item.label }}
            </div>
          </Link>
        </template>
      </nav>

      <!-- User + Logout -->
      <div class="flex-shrink-0 border-t border-gray-800 p-3">
        <div class="flex items-center gap-3" :class="sidebarOpen ? '' : 'justify-center'">
          <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
            <span class="text-white text-sm font-bold">{{ userInitial }}</span>
          </div>
          <div v-if="sidebarOpen" class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user?.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $page.props.auth.user?.email }}</p>
          </div>
          <button v-if="sidebarOpen" @click="logout"
            class="text-gray-400 hover:text-white transition-colors"
            title="Logout">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col transition-all duration-300 min-w-0" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-16'">
      <!-- Top Bar -->
      <header class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center px-4 gap-4 sticky top-0 z-40">
        <button @click="sidebarOpen = !sidebarOpen"
          class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h1 class="flex-1 text-lg font-semibold text-gray-800">{{ title }}</h1>
        <!-- Flash success -->
        <div v-if="$page.props.flash?.message"
          class="flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 text-sm rounded-lg border border-green-200">
          <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          {{ $page.props.flash.message }}
        </div>
        <!-- Flash error -->
        <div v-if="$page.props.flash?.error"
          class="flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200 max-w-sm">
          <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span class="line-clamp-2">{{ $page.props.flash.error }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-500">{{ new Date().toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) }}</span>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-auto">
        <Transition
          enter-active-class="transition-opacity duration-150"
          leave-active-class="transition-opacity duration-100"
          enter-from-class="opacity-0"
          leave-to-class="opacity-0"
          mode="out-in">
          <div v-if="isNavigating && isDashboard" key="skeleton">
            <DashboardSkeleton />
          </div>
          <div v-else key="content">
            <slot />
          </div>
        </Transition>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, h, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import DashboardSkeleton from '@/Components/DashboardSkeleton.vue'

const props = defineProps({ title: { type: String, default: 'Dashboard' } })
const page = usePage()
const sidebarOpen = ref(typeof window !== 'undefined' && window.innerWidth >= 1024)
const isNavigating = ref(false)
const nextUrl = ref('')
const isDashboard = computed(() => {
  const target = nextUrl.value || page.url
  return target.split('?')[0] === '/dashboard'
})

let stopStart, stopFinish
onMounted(() => {
  stopStart  = router.on('start',  (e) => { nextUrl.value = e.detail?.visit?.url?.pathname ?? ''; isNavigating.value = true  })
  stopFinish = router.on('finish', ()  => { isNavigating.value = false })
})
onUnmounted(() => {
  stopStart?.()
  stopFinish?.()
})

const userInitial = computed(() => {
  const name = page.props.auth?.user?.name || 'A'
  return name.charAt(0).toUpperCase()
})

const isActive = (href) => {
  const url = page.url.split('?')[0]
  if (href === '/dashboard') return url === '/dashboard'
  return url === href || url.startsWith(href + '/') || url.startsWith(href + '?')
}

const logout = () => {
  router.post('/logout')
}

// Simple icon components
const IconDashboard = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })]) }
const IconBox = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })]) }
const IconScissors = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z' })]) }
const IconNeedle = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4' })]) }
const IconWater = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' })]) }
const IconPackage = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' })]) }
const IconDefect  = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z' })]) }
const IconTruck = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z' }), h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .001M13 16H9m4 0h2m0-10h2l3 4v6h-5m0-10z' })]) }
const IconShop = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' })]) }
const IconCash = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' })]) }
const IconArrowOut = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 10h4m0 0l-4-4m4 4l-4 4M3 12a9 9 0 0118 0' })]) }
const IconStock = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })]) }
const IconWarehouse = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })]) }
const IconMap = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7' })]) }
const IconSendReady = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })]) }
const IconReport = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })]) }
const IconChart  = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z' }), h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z' })]) }
const IconCalculator = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' })]) }

const IconUsers = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' })]) }
const IconBank = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', class: 'w-5 h-5' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 10h18M3 14h18M7 6l5-3 5 3M4 10v8a1 1 0 001 1h14a1 1 0 001-1v-8' })]) }

const navItems = [
  { type: 'link', name: 'dashboard', label: 'Dashboard', href: '/dashboard', icon: IconDashboard },
  { type: 'header', label: 'Data Master' },
  { type: 'link', name: 'master-model', label: 'Master Model', href: '/master-model', icon: IconUsers },
  { type: 'link', name: 'supplier', label: 'Supplier', href: '/supplier', icon: IconUsers },
  { type: 'link', name: 'rekening', label: 'Rekening', href: '/rekening', icon: IconBank },
  { type: 'header', label: 'Inventori' },
  { type: 'link', name: 'stok-bahan', label: 'Stok Bahan', href: '/stok-bahan', icon: IconStock },
  { type: 'link', name: 'stok-barang', label: 'Stok Barang', href: '/stok-barang', icon: IconWarehouse },
  { type: 'link', name: 'rincian-bahan', label: 'Rincian Bahan', href: '/rincian-bahan', icon: IconBox },
  { type: 'header', label: 'Bahan' },
  { type: 'link', name: 'bahan-masuk', label: 'Bahan Masuk', href: '/bahan-masuk', icon: IconBox },
  { type: 'link', name: 'bahan-keluar', label: 'Bahan Keluar', href: '/bahan-keluar', icon: IconArrowOut },
  { type: 'header', label: 'Produksi' },
  { type: 'link', name: 'tracking-po', label: 'Tracking PO', href: '/tracking-po', icon: IconMap },
  { type: 'link', name: 'stok-bahan-garmen', label: 'Stok Bahan Garmen', href: '/stok-bahan-garmen', icon: IconArrowOut },
  { type: 'link', name: 'bahan-proses-potong', label: 'Proses Potong', href: '/bahan-proses-potong', icon: IconScissors },
  { type: 'link', name: 'proses-jahit', label: 'Proses Jahit', href: '/proses-jahit', icon: IconNeedle },
  { type: 'link', name: 'proses-cuci', label: 'Proses Cuci', href: '/proses-cuci', icon: IconWater },
  { type: 'link', name: 'proses-finishing', label: 'Finishing & Packing', href: '/proses-finishing', icon: IconPackage },
  { type: 'link', name: 'defect', label: 'Laporan Defect', href: '/defect', icon: IconDefect },
  { type: 'link', name: 'barang-siap-kirim', label: 'Barang Siap Kirim', href: '/barang-siap-kirim', icon: IconSendReady },
  { type: 'header', label: 'Distribusi' },
  { type: 'link', name: 'barang-masuk-kantor', label: 'Masuk Ke Kantor', href: '/barang-masuk-kantor', icon: IconTruck },
  { type: 'link', name: 'barang-kirim-toko', label: 'Kirim Ke Toko', href: '/barang-kirim-toko', icon: IconShop },
  { type: 'header', label: 'Penjualan' },
  { type: 'link', name: 'proses-jual', label: 'Jual Toko', href: '/proses-jual', icon: IconCash },
  { type: 'link', name: 'jual-gudang', label: 'Jual Gudang', href: '/jual-gudang', icon: IconCash },
  { type: 'link', name: 'laporan-penjualan',      label: 'Laporan Penjualan',    href: '/laporan-penjualan',      icon: IconReport },
  { type: 'link', name: 'laporan-model-terjual',  label: 'Model Terjual',        href: '/laporan-model-terjual',  icon: IconChart  },
  { type: 'link', name: 'laporan-hpp',            label: 'Laporan HPP',          href: '/laporan-hpp',            icon: IconCalculator },
]
</script>

<style scoped>
.sidebar-scroll {
  scrollbar-width: thin;
  scrollbar-color: #f59e0b #1f2937; /* amber thumb, gray-800 track */
}
.sidebar-scroll::-webkit-scrollbar {
  width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-track {
  background: #1f2937; /* gray-800 */
  border-radius: 9999px;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
  background-color: #374151; /* gray-700 — subtle default */
  border-radius: 9999px;
}
.sidebar-scroll:hover::-webkit-scrollbar-thumb {
  background-color: #f59e0b; /* amber-400 on hover */
}
</style>
