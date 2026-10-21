<template>
  <div v-if="show" class="modal-backdrop" @click.self="$emit('cancel')">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true">
      <div class="modal-header">
        <h3 class="modal-title">{{ title }}</h3>
        <button @click="$emit('cancel')" class="btn-ghost" title="Close" aria-label="Close">
          <X :size="18" />
        </button>
      </div>
      <div class="modal-body">
        <p class="text-secondary" style="font-size: 0.9375rem; line-height: 1.5;">
          {{ message }}
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" @click="$emit('cancel')" class="btn btn-outline" :disabled="loading">
          Cancel
        </button>
        <button
          type="button"
          :class="['btn', confirmVariant === 'danger' ? 'btn-danger-solid' : 'btn-primary']"
          :disabled="loading"
          @click="$emit('confirm')"
        >
          <span v-if="loading">Processing...</span>
          <span v-else>{{ confirmText }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { X } from 'lucide-vue-next'

defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  confirmVariant: {
    type: String,
    default: 'danger'
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['confirm', 'cancel'])
</script>
