<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <slot />
  </div>
</template>
<script setup>
defineProps({ label: String, required: Boolean })
</script>
