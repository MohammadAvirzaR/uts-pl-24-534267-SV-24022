<template>
  <div v-if="authStore.isAuthenticated" class="app-wrapper">
    <!-- Mobile Drawer Overlay -->
    <div
      :class="['sidebar-overlay', { 'overlay-open': isSidebarOpen }]"
      @click="isSidebarOpen = false"
    ></div>

    <!-- Persistent Sidebar -->
    <AppSidebar
      :is-open="isSidebarOpen"
      @close="isSidebarOpen = false"
    />

    <!-- Main Workspace -->
    <div class="app-main">
      <AppHeader @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      <main class="app-content">
        <router-view />
      </main>
    </div>

    <!-- Global Toast Notifications -->
    <ToastContainer />
  </div>

  <!-- Guest / Auth Pages (e.g. Login) -->
  <div v-else class="auth-wrapper">
    <router-view />
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from './stores/auth'
import AppSidebar from './components/AppSidebar.vue'
import AppHeader from './components/AppHeader.vue'
import ToastContainer from './components/ToastContainer.vue'

const authStore = useAuthStore()
const isSidebarOpen = ref(false)
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  background-color: var(--bg-app);
}
</style>
