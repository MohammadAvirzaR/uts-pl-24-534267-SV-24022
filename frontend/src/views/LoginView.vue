<template>
  <div class="login-wrapper">
    <div class="login-card card">
      <!-- Brand Header -->
      <div class="login-brand">
        <div class="brand-badge">
          <Layers :size="24" class="brand-icon" />
        </div>
        <h1 class="brand-title">Project Tracker</h1>
        <p class="brand-subtitle">Executive Management Workspace</p>
      </div>

      <!-- Error Alert -->
      <div v-if="error" class="alert alert-error">
        <AlertCircle :size="16" class="flex-shrink-0" />
        <span>{{ error }}</span>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email" class="form-label">
            Work Email Address
          </label>
          <div class="search-wrapper">
            <Mail :size="16" class="search-icon" />
            <input
              id="email"
              v-model="email"
              type="email"
              class="form-input"
              placeholder="name@company.com"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <div class="form-group">
          <div class="flex items-center justify-between mb-1">
            <label for="password" class="form-label" style="margin-bottom: 0;">
              Password
            </label>
          </div>
          <div class="search-wrapper">
            <Lock :size="16" class="search-icon" />
            <input
              id="password"
              v-model="password"
              type="password"
              class="form-input"
              placeholder="••••••••••••"
              required
              autocomplete="current-password"
            />
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-full submit-btn" :disabled="loading">
          <span v-if="loading" class="spinner-small mr-2"></span>
          <span>{{ loading ? 'Signing In...' : 'Sign In to Workspace' }}</span>
        </button>
      </form>

      <!-- Demo Credentials Helper -->
      <div class="demo-card">
        <div class="demo-header">
          <Key :size="14" class="demo-icon" />
          <span class="demo-title">Evaluation Account</span>
        </div>
        <p class="demo-desc">
          Pre-configured administrator credentials for system evaluation.
        </p>
        <button type="button" @click="fillDemoCredentials" class="btn btn-outline btn-xs w-full mt-2">
          Use Admin Demo (admin@example.com)
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import {
  Layers,
  Mail,
  Lock,
  Key,
  AlertCircle
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toast = useToastStore()
const router = useRouter()

const email = ref('admin@example.com')
const password = ref('password123')
const error = ref('')
const loading = ref(false)

const fillDemoCredentials = () => {
  email.value = 'admin@example.com'
  password.value = 'password123'
}

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    const data = await authStore.login(email.value, password.value)
    toast.success(`Welcome back, ${data.user?.name || 'Administrator'}`)
    router.push('/dashboard')
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      'Invalid email or password. Please verify your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-4);
  background-color: var(--bg-app);
  background-image: radial-gradient(circle at 50% 15%, rgba(101, 88, 217, 0.05) 0%, transparent 60%);
}

.login-card {
  width: 100%;
  max-width: 400px;
  padding: var(--space-8);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-subtle);
}

.login-brand {
  text-align: center;
  margin-bottom: var(--space-6);
}

.brand-badge {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, #6558D9 0%, #4D3FB5 100%);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
  box-shadow: 0 4px 10px rgba(101, 88, 217, 0.25);
  margin-bottom: var(--space-3);
}

.brand-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.01em;
}

.brand-subtitle {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin-top: 2px;
}

.submit-btn {
  height: 42px;
  font-size: 0.9375rem;
  margin-top: var(--space-2);
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  display: inline-block;
  animation: spin 0.8s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.demo-card {
  margin-top: var(--space-6);
  padding: var(--space-3);
  background-color: #F8F9FD;
  border: 1px solid #E4E7F4;
  border-radius: var(--radius-sm);
}

.demo-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-primary);
}

.demo-icon {
  color: var(--accent-primary);
}

.demo-desc {
  font-size: 0.6875rem;
  color: var(--text-secondary);
  margin-top: 2px;
}
</style>
