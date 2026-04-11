<template>
  <div class="relative" ref="wrapper">
    <!-- Trigger -->
    <button
      type="button"
      @click="toggle"
      @keydown.enter.prevent="toggle"
      @keydown.space.prevent="toggle"
      @keydown.arrow-down.prevent="open"
      class="w-full flex items-center justify-between px-4 py-2.5 border rounded-lg text-sm bg-white transition text-left"
      :class="[
        isOpen ? 'border-amber-400 ring-2 ring-amber-400' : 'border-gray-200',
        disabled ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'cursor-pointer hover:border-amber-300',
      ]"
      :disabled="disabled"
    >
      <span :class="selectedLabel ? 'text-gray-800' : 'text-gray-400'">
        {{ selectedLabel || placeholder }}
      </span>
      <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <!-- Dropdown -->
    <div v-if="isOpen"
      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
    >
      <!-- Search -->
      <div class="p-2 border-b border-gray-100">
        <div class="relative">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            ref="searchInput"
            v-model="query"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-7 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-400 focus:border-amber-400"
            @keydown.enter.prevent="selectFirst"
            @keydown.escape="close"
            @keydown.arrow-down.prevent="moveDown"
            @keydown.arrow-up.prevent="moveUp"
          />
        </div>
      </div>

      <!-- Options list -->
      <ul ref="listEl" class="max-h-52 overflow-y-auto py-1">
        <!-- Empty option -->
        <li v-if="clearable && modelValue !== null && modelValue !== ''"
          @click="select(null)"
          class="px-4 py-2 text-sm text-gray-400 hover:bg-gray-50 cursor-pointer italic">
          — Hapus pilihan —
        </li>
        <li v-if="allowCustom && query.trim() && !normalised.find(o => o.value === query.trim())"
          @click="select(query.trim())"
          class="px-4 py-2 text-sm cursor-pointer text-amber-600 hover:bg-amber-50 italic border-b border-gray-100 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Gunakan "{{ query.trim() }}"
        </li>
        <li v-if="filtered.length === 0 && !(allowCustom && query.trim())"
          class="px-4 py-2 text-sm text-gray-400 text-center py-4">
          Tidak ada hasil
        </li>
        <li
          v-for="(opt, idx) in filtered"
          :key="opt.value"
          @click="select(opt.value)"
          class="px-4 py-2 text-sm cursor-pointer transition-colors flex items-center gap-2"
          :class="[
            opt.value === modelValue
              ? 'bg-amber-50 text-amber-700 font-medium'
              : 'text-gray-700 hover:bg-gray-50',
            focusedIndex === idx ? 'bg-gray-100' : '',
          ]"
        >
          <svg v-if="opt.value === modelValue" class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <span v-else class="w-3.5 flex-shrink-0"></span>
          {{ opt.label }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue:        { default: null },
  options:           { type: Array, default: () => [] },
  placeholder:       { type: String, default: '-- Pilih --' },
  searchPlaceholder: { type: String, default: 'Cari...' },
  disabled:          { type: Boolean, default: false },
  clearable:         { type: Boolean, default: false },
  labelKey:          { type: String, default: 'label' },
  valueKey:          { type: String, default: 'value' },
  allowCustom:       { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

// Normalise options to [{ value, label }]
const normalised = computed(() =>
  props.options.map(opt => {
    if (typeof opt === 'string' || typeof opt === 'number') {
      return { value: opt, label: String(opt) }
    }
    return { value: opt[props.valueKey], label: String(opt[props.labelKey]) }
  })
)

const isOpen       = ref(false)
const query        = ref('')
const focusedIndex = ref(-1)
const wrapper      = ref(null)
const searchInput  = ref(null)
const listEl       = ref(null)

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return normalised.value
  return normalised.value.filter(o => o.label.toLowerCase().includes(q))
})

const selectedLabel = computed(() => {
  const opt = normalised.value.find(o => o.value == props.modelValue)
  if (opt) return opt.label
  // For allowCustom: show the raw value if it's not in the options list
  if (props.allowCustom && props.modelValue !== null && props.modelValue !== '') return String(props.modelValue)
  return null
})

const open = async () => {
  if (props.disabled) return
  isOpen.value   = true
  query.value    = ''
  focusedIndex.value = -1
  await nextTick()
  searchInput.value?.focus()
}

const close = () => {
  isOpen.value = false
  query.value  = ''
}

const toggle = () => isOpen.value ? close() : open()

const select = (val) => {
  emit('update:modelValue', val)
  emit('change', val)
  close()
}

const selectFirst = () => {
  if (focusedIndex.value >= 0 && filtered.value[focusedIndex.value]) {
    select(filtered.value[focusedIndex.value].value)
  } else if (filtered.value.length === 1) {
    select(filtered.value[0].value)
  } else if (props.allowCustom && query.value.trim()) {
    select(query.value.trim())
  }
}

const moveDown = () => {
  focusedIndex.value = Math.min(focusedIndex.value + 1, filtered.value.length - 1)
}
const moveUp = () => {
  focusedIndex.value = Math.max(focusedIndex.value - 1, 0)
}

// Close on outside click
const onOutsideClick = (e) => {
  if (wrapper.value && !wrapper.value.contains(e.target)) close()
}
onMounted(() => document.addEventListener('mousedown', onOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onOutsideClick))
</script>
