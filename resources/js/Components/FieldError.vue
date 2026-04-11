<template>
  <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
</template>
<script setup>
defineProps({ error: String })
</script>
