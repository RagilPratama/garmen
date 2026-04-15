<template>
  <Teleport to="body">
    <Transition name="cd-backdrop">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        @mousedown.self="$emit('update:modelValue', false)"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />

        <!-- Panel -->
        <Transition name="cd-panel" appear>
          <div
            v-if="modelValue"
            class="relative bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full"
          >
            <!-- Icon -->
            <div class="cd-icon-wrapper relative flex items-center justify-center mx-auto mb-4">
              <!-- Ripple rings (danger only) -->
              <div v-if="variant === 'danger'" class="cd-ripple cd-ripple-a"></div>
              <div v-if="variant === 'danger'" class="cd-ripple cd-ripple-b"></div>

              <div
                class="w-14 h-14 rounded-full flex items-center justify-center relative z-10"
                :class="variant === 'warning' ? 'bg-amber-100' : 'cd-icon-circle bg-red-100'"
              >
                <!-- Trash icon (danger) -->
                <svg
                  v-if="variant === 'danger'"
                  class="w-7 h-7 text-red-500 cd-icon-shake"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
                <!-- Warning icon -->
                <svg
                  v-else
                  class="w-7 h-7 text-amber-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                  />
                </svg>
              </div>
            </div>

            <!-- Text -->
            <h3 class="text-base font-semibold text-gray-800 text-center mb-1">{{ title }}</h3>
            <p class="text-sm text-gray-500 text-center mb-6 leading-relaxed">{{ message }}</p>

            <!-- Actions -->
            <div class="flex gap-3">
              <button
                type="button"
                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                :disabled="loading"
                @click="$emit('update:modelValue', false)"
              >
                Batal
              </button>
              <button
                type="button"
                class="flex-1 px-4 py-2.5 text-sm font-medium text-white rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-70"
                :class="variant === 'warning'
                  ? 'bg-amber-500 hover:bg-amber-600'
                  : 'bg-red-500 hover:bg-red-600'"
                :disabled="loading"
                @click="$emit('confirm')"
              >
                <svg
                  v-if="loading"
                  class="animate-spin w-4 h-4"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ confirmLabel }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  modelValue:   { type: Boolean, default: false },
  title:        { type: String,  default: 'Hapus Data?' },
  message:      { type: String,  default: 'Tindakan ini tidak dapat dibatalkan.' },
  confirmLabel: { type: String,  default: 'Ya, Hapus' },
  loading:      { type: Boolean, default: false },
  variant:      { type: String,  default: 'danger' }, // 'danger' | 'warning'
})

defineEmits(['update:modelValue', 'confirm'])
</script>

<style scoped>
/* Backdrop fade */
.cd-backdrop-enter-active,
.cd-backdrop-leave-active { transition: opacity 0.2s ease; }
.cd-backdrop-enter-from,
.cd-backdrop-leave-to    { opacity: 0; }

/* Panel scale + slide */
.cd-panel-enter-active { transition: opacity 0.22s ease, transform 0.22s ease; }
.cd-panel-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.cd-panel-enter-from   { opacity: 0; transform: scale(0.92) translateY(8px); }
.cd-panel-leave-to     { opacity: 0; transform: scale(0.94) translateY(4px); }

/* ── Icon wrapper ── */
.cd-icon-wrapper {
  width: 56px;
  height: 56px;
  margin-bottom: 1rem;
}

/* Outer ripple rings */
.cd-ripple {
  position: absolute;
  inset: 0;
  border-radius: 9999px;
  background: transparent;
  border: 2px solid rgb(239 68 68 / 0.4);
  animation: cd-ripple-out 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.cd-ripple-b {
  animation-delay: 0.75s;
  border-color: rgb(239 68 68 / 0.2);
}

@keyframes cd-ripple-out {
  0%   { transform: scale(1);   opacity: 1; }
  100% { transform: scale(2.4); opacity: 0; }
}

/* Circle subtle pulse */
.cd-icon-circle {
  animation: cd-pulse 2.4s ease-in-out infinite;
}
@keyframes cd-pulse {
  0%, 100% { box-shadow: 0 0 0 0   rgb(239 68 68 / 0.2); }
  50%       { box-shadow: 0 0 0 10px rgb(239 68 68 / 0); }
}

/* Trash icon entrance shake */
.cd-icon-shake {
  animation: cd-shake 0.6s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
  animation-delay: 0.25s;
}
@keyframes cd-shake {
  0%, 100% { transform: rotate(0deg);   }
  15%       { transform: rotate(-14deg); }
  30%       { transform: rotate(11deg);  }
  45%       { transform: rotate(-9deg);  }
  60%       { transform: rotate(6deg);   }
  75%       { transform: rotate(-3deg);  }
  90%       { transform: rotate(1deg);   }
}
</style>
