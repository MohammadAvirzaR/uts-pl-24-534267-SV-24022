<template>
  <span :class="['badge', `badge-${normalizedPriority}`]">
    <span>{{ label }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  priority: {
    type: String,
    default: 'medium'
  }
})

const normalizedPriority = computed(() => {
  return props.priority ? props.priority.toLowerCase() : 'medium'
})

const label = computed(() => {
  const map = {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
    critical: 'Critical'
  }
  return map[normalizedPriority.value] || props.priority || 'Medium'
})
</script>
