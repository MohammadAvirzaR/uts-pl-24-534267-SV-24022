<template>
  <div class="toast-container" aria-live="polite">
    <div
      v-for="toast in toastStore.toasts"
      :key="toast.id"
      :class="['toast', `toast-${toast.type}`]"
    >
      <component :is="iconComponent(toast.type)" class="toast-icon" :size="18" />
      <span class="flex-1">{{ toast.message }}</span>
      <button
        @click="toastStore.remove(toast.id)"
        class="btn-ghost toast-close"
        title="Dismiss"
        aria-label="Dismiss"
      >
        <X :size="14" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast'
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next'

const toastStore = useToastStore()

const iconComponent = (type) => {
  switch (type) {
    case 'success':
      return CheckCircle2
    case 'error':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    default:
      return Info
  }
}
</script>

<style scoped>
.toast-icon {
  flex-shrink: 0;
}
.toast-success .toast-icon {
  color: var(--success-dot);
}
.toast-error .toast-icon {
  color: var(--danger-dot);
}
.toast-warning .toast-icon {
  color: var(--warning-dot);
}
.toast-info .toast-icon {
  color: var(--accent-primary);
}
.toast-close {
  padding: 2px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-xs);
  color: var(--text-muted);
}
.toast-close:hover {
  color: var(--text-primary);
}
</style>
