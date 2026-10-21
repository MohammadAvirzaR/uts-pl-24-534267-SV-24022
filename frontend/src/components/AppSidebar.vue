<template>
  <aside :class="['app-sidebar', { 'sidebar-open': isOpen }]">
    <!-- Brand Header -->
    <div class="sidebar-brand">
      <div class="brand-logo-mark">
        <Layers :size="20" class="brand-icon" />
      </div>
      <div class="brand-info">
        <span class="brand-name">Project Tracker</span>
        <span class="brand-workspace">Workspace</span>
      </div>
      <button v-if="isOpen" @click="$emit('close')" class="btn-ghost mobile-close-btn" aria-label="Close sidebar">
        <X :size="18" />
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <div class="nav-section-label">MAIN NAVIGATION</div>

      <router-link
        to="/dashboard"
        class="nav-item"
        :class="{ active: route.path === '/dashboard' || route.path === '/' }"
        @click="$emit('close')"
      >
        <LayoutDashboard :size="18" class="nav-icon" />
        <span class="nav-label">Overview</span>
      </router-link>

      <router-link
        to="/projects"
        class="nav-item"
        :class="{ active: route.path.startsWith('/projects') }"
        @click="$emit('close')"
      >
        <FolderKanban :size="18" class="nav-icon" />
        <span class="nav-label">Projects</span>
      </router-link>

      <router-link
        to="/tasks"
        class="nav-item"
        :class="{ active: route.path === '/tasks' }"
        @click="$emit('close')"
      >
        <CheckSquare2 :size="18" class="nav-icon" />
        <span class="nav-label">Tasks</span>
      </router-link>
    </nav>

    <!-- Sidebar Footer / User Profile & Logout -->
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar" :title="user?.name || 'User'">
          {{ userInitials }}
        </div>
        <div class="user-details">
          <div class="user-name" :title="user?.name">{{ user?.name || 'User' }}</div>
          <div class="user-role-badge">
            <span class="role-pill">{{ userRoleLabel }}</span>
          </div>
        </div>
      </div>

      <button @click="handleLogout" class="logout-btn" title="Sign out of account">
        <LogOut :size="16" class="logout-icon" />
        <span>Sign Out</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import {
  Layers,
  LayoutDashboard,
  FolderKanban,
  CheckSquare2,
  LogOut,
  X
} from 'lucide-vue-next'

defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  const parts = user.value.name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return parts[0].slice(0, 2).toUpperCase()
})

const userRoleLabel = computed(() => {
  if (user.value?.role === 'pm') return 'Project Lead / PM'
  if (user.value?.role === 'member') return 'Team Member'
  return 'Member'
})

const handleLogout = async () => {
  emit('close')
  await authStore.logout()
  toastStore.info('You have been signed out')
  router.push('/login')
}
</script>

<style scoped>
.sidebar-brand {
  height: var(--header-height);
  padding: 0 var(--space-5);
  display: flex;
  align-items: center;
  gap: var(--space-3);
  border-bottom: 1px solid var(--border-subtle);
}

.brand-logo-mark {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-sm);
  background: linear-gradient(135deg, #6558D9 0%, #4D3FB5 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
  box-shadow: 0 2px 5px rgba(101, 88, 217, 0.25);
}

.brand-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.brand-name {
  font-size: 0.9375rem;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.01em;
  line-height: 1.2;
}

.brand-workspace {
  font-size: 0.6875rem;
  font-weight: 500;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.mobile-close-btn {
  display: none;
}

@media (max-width: 1024px) {
  .mobile-close-btn {
    display: inline-flex;
  }
}

.sidebar-nav {
  flex: 1;
  padding: var(--space-4) var(--space-3);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.nav-section-label {
  font-size: 0.6875rem;
  font-weight: 600;
  color: var(--text-muted);
  letter-spacing: 0.06em;
  padding: var(--space-2) var(--space-3);
  margin-bottom: var(--space-1);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: 0.625rem var(--space-3);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
  transition: all 0.15s ease;
  position: relative;
}

.nav-item:hover {
  background-color: var(--bg-subtle);
  color: var(--text-primary);
}

.nav-item.active {
  background-color: var(--accent-subtle);
  color: var(--accent-primary);
  font-weight: 600;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 15%;
  bottom: 15%;
  width: 3px;
  background-color: var(--accent-primary);
  border-radius: 0 4px 4px 0;
}

.nav-icon {
  flex-shrink: 0;
  opacity: 0.85;
}

.nav-item.active .nav-icon {
  opacity: 1;
}

.sidebar-footer {
  padding: var(--space-4);
  border-top: 1px solid var(--border-subtle);
  background-color: #FAFAFC;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.user-card {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2);
  border-radius: var(--radius-sm);
  background-color: var(--bg-surface);
  border: 1px solid var(--border-subtle);
}

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-color: #E7E5F9;
  color: var(--accent-primary);
  font-weight: 600;
  font-size: 0.8125rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.25;
}

.user-role-badge {
  margin-top: 2px;
}

.role-pill {
  display: inline-block;
  font-size: 0.6875rem;
  font-weight: 500;
  color: var(--accent-primary);
  background-color: var(--accent-subtle);
  padding: 1px 6px;
  border-radius: var(--radius-full);
}

.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  width: 100%;
  padding: 0.5rem;
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary);
  background-color: transparent;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.logout-btn:hover {
  background-color: var(--bg-subtle);
  color: var(--danger-dot);
  border-color: var(--border-subtle);
}

.logout-icon {
  opacity: 0.8;
}
</style>
