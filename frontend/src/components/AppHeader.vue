<template>
  <header class="app-header">
    <div class="header-left">
      <button
        @click="$emit('toggle-sidebar')"
        class="btn-ghost menu-toggle-btn"
        aria-label="Toggle navigation menu"
      >
        <Menu :size="20" />
      </button>

      <div class="breadcrumb-container">
        <span class="breadcrumb-root">Workspace</span>
        <ChevronRight :size="14" class="breadcrumb-separator" />
        <span class="breadcrumb-current">{{ currentTitle }}</span>
      </div>
    </div>

    <div class="header-right">
      <slot name="actions"></slot>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Menu, ChevronRight } from 'lucide-vue-next'

defineEmits(['toggle-sidebar'])

const route = useRoute()

const currentTitle = computed(() => {
  if (route.path === '/dashboard' || route.path === '/') return 'Overview'
  if (route.path.startsWith('/projects/') && route.params.id) return 'Project Details'
  if (route.path.startsWith('/projects')) return 'Projects'
  if (route.path === '/tasks') return 'Tasks'
  return 'Workspace'
})
</script>

<style scoped>
.header-left {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.menu-toggle-btn {
  display: none;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  color: var(--text-secondary);
}

@media (max-width: 1024px) {
  .menu-toggle-btn {
    display: inline-flex;
  }
}

.breadcrumb-container {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: 0.875rem;
}

.breadcrumb-root {
  color: var(--text-muted);
  font-weight: 500;
}

.breadcrumb-separator {
  color: var(--text-muted);
}

.breadcrumb-current {
  color: var(--text-primary);
  font-weight: 600;
}

.header-right {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}
</style>
