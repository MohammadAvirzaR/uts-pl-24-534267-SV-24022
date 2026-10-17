<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="card" style="width: 100%; max-width: 28rem;">
      <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-indigo-600">📋 Project Tracker</h2>
        <p class="text-gray-500 mt-2">Sign in to manage your projects</p>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>

      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label for="email">Email</label>
          <input id="email" v-model="email" type="email" placeholder="admin@example.com" required />
        </div>

        <div class="mb-4">
          <label for="password">Password</label>
          <input id="password" v-model="password" type="password" placeholder="password123" required />
        </div>

        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <div class="mt-4 text-center text-sm text-gray-500">
        <p>Demo: admin@example.com / password123</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('admin@example.com')
const password = ref('password123')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await authStore.login(email.value, password.value)
    router.push('/projects')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>
