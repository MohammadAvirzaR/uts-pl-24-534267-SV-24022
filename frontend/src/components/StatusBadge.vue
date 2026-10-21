<template>
  <span :class="['badge', `badge-${normalizedStatus}`]">
    <span class="badge-dot"></span>
    <span>{{ label }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: 'todo'
  }
})

const normalizedStatus = computed(() => {
  return props.status ? props.status.toLowerCase().replace('-', '_') : 'todo'
})

const label = computed(() => {
  const map = {
    planning: 'Planning',
    active: 'Active',
    completed: 'Completed',
    on_hold: 'On Hold',
    todo: 'To Do',
    in_progress: 'In Progress',
    review: 'Review',
    done: 'Done'
  }
  return map[normalizedStatus.value] || props.status || 'Unknown'
})
</script>
