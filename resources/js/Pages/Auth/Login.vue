<template>
  <div class="min-h-screen flex">
    <!-- Left: Animated Background -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-amber-600 via-orange-500 to-yellow-400 items-center justify-center">
      <!-- Animated circles -->
      <div class="absolute inset-0">
        <div v-for="i in 12" :key="i"
          class="absolute rounded-full opacity-20 animate-float"
          :class="circleClass(i)"
          :style="circleStyle(i)"
        ></div>
      </div>
      <!-- Wavy lines -->
      <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice">
        <path d="M0,100 Q200,50 400,100 T800,100 L800,0 L0,0 Z" fill="white"/>
        <path d="M0,200 Q200,150 400,200 T800,200" fill="none" stroke="white" stroke-width="2"/>
        <path d="M0,350 Q200,300 400,350 T800,350" fill="none" stroke="white" stroke-width="2"/>
        <path d="M0,500 Q200,450 400,500 T800,500" fill="none" stroke="white" stroke-width="2"/>
      </svg>
      <!-- Fabric texture overlay -->
      <div class="absolute inset-0 opacity-5"
        style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 20px 20px;">
      </div>
      <!-- Content -->
      <div class="relative z-10 text-center text-white px-12">
        <div class="mb-8">
          <div class="w-24 h-24 mx-auto bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border-2 border-white/40 shadow-2xl mb-6">
            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </div>
        </div>
        <h1 class="text-4xl font-bold mb-4 drop-shadow-lg">GarmenApp</h1>
        <p class="text-xl text-white/90 mb-2 font-light">Sistem Manajemen Garmen</p>
        <p class="text-white/70 text-sm max-w-xs mx-auto leading-relaxed">
          Kelola seluruh proses produksi garmen Anda dengan mudah, dari bahan masuk hingga pengiriman ke toko.
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-3">
          <div v-for="badge in badges" :key="badge"
            class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm text-white border border-white/30 shadow">
            {{ badge }}
          </div>
        </div>
      </div>
      <!-- Bottom wave -->
      <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1200 80" preserveAspectRatio="none">
        <path d="M0,40 Q300,80 600,40 T1200,40 L1200,80 L0,80 Z" fill="rgba(255,255,255,0.1)"/>
      </svg>
    </div>

    <!-- Right: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 px-6 py-12">
      <div class="w-full max-w-md">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="w-16 h-16 mx-auto bg-amber-500 rounded-full flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-800">GarmenApp</h2>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Selamat Datang</h2>
            <p class="text-gray-500 mt-1 text-sm">Masuk ke akun Anda untuk melanjutkan</p>
          </div>

          <!-- Error Alert -->
          <div v-if="Object.keys(errors).length"
            class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-red-600 text-sm">{{ errors.email }}</p>
          </div>

          <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                  </svg>
                </div>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="admin@garmen.com"
                  autocomplete="email"
                  class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-gray-50 focus:bg-white"
                  :class="{ 'border-red-300 bg-red-50': errors.email }"
                />
              </div>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                </div>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  class="w-full pl-10 pr-12 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition bg-gray-50 focus:bg-white"
                />
                <button type="button" @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                  <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Submit -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-amber-200 hover:shadow-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ form.processing ? 'Memproses...' : 'Masuk' }}</span>
            </button>
          </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
          &copy; {{ new Date().getFullYear() }} GarmenApp. Hak cipta dilindungi.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineProps({ errors: { type: Object, default: () => ({}) } })

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const badges = ['Bahan Masuk', 'Proses Jahit', 'Finishing', 'Penjualan']

const circleClass = (i) => {
  const sizes = ['w-16 h-16', 'w-24 h-24', 'w-32 h-32', 'w-40 h-40', 'w-8 h-8', 'w-20 h-20']
  const colors = ['bg-white', 'bg-yellow-200', 'bg-orange-200', 'bg-amber-300']
  return `${sizes[i % sizes.length]} ${colors[i % colors.length]}`
}

const circleStyle = (i) => {
  const positions = [
    { top: '5%', left: '10%' }, { top: '15%', left: '70%' }, { top: '40%', left: '5%' },
    { top: '60%', left: '75%' }, { top: '80%', left: '20%' }, { top: '70%', left: '50%' },
    { top: '25%', left: '40%' }, { top: '50%', left: '55%' }, { top: '85%', left: '80%' },
    { top: '10%', left: '85%' }, { top: '45%', left: '30%' }, { top: '90%', left: '60%' },
  ]
  const pos = positions[i % positions.length]
  const duration = 3 + (i * 0.7)
  const delay = i * 0.3
  return {
    ...pos,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`,
  }
}

const submit = () => {
  form.post('/login')
}
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-20px) scale(1.05); }
}
.animate-float {
  animation: float linear infinite;
}
</style>
