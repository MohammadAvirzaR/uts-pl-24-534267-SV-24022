import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])

  function show(message, type = 'info', duration = 3500) {
    const id = Date.now() + Math.random()
    toasts.value.push({ id, message, type })
    if (duration > 0) {
      setTimeout(() => {
        remove(id)
      }, duration)
    }
  }

  function success(message, duration = 3500) {
    show(message, 'success', duration)
  }

  function error(message, duration = 4500) {
    show(message, 'error', duration)
  }

  function warning(message, duration = 4000) {
    show(message, 'warning', duration)
  }

  function info(message, duration = 3500) {
    show(message, 'info', duration)
  }

  function remove(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return { toasts, show, success, error, warning, info, remove }
})
