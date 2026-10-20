<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <nav v-if="authStore.isAuthenticated" class="bg-indigo-600 text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
          <h1 class="text-xl font-bold">📋 Project Tracker</h1>
          <router-link to="/projects" class="hover:text-indigo-200 transition">Projects</router-link>
          <router-link to="/tasks" class="hover:text-indigo-200 transition">Tasks</router-link>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm opacity-80">{{ authStore.user?.name }}</span>
          <button @click="handleLogout" class="bg-indigo-500 hover:bg-indigo-400 px-3 py-1 rounded text-sm transition">
            Logout
          </button>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
