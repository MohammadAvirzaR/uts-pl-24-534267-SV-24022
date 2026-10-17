<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-800">My Projects</h2>
      <button @click="openCreateModal" class="btn btn-primary">+ New Project</button>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading projects...</div>

    <div v-else-if="projects.length === 0" class="text-center py-8 text-gray-500">
      <p class="text-lg">No projects yet</p>
      <p class="text-sm mt-2">Click "New Project" to get started</p>
    </div>

    <div v-else class="grid grid-cols-1 md-grid-cols-2 lg-grid-cols-3 gap-4">
      <div v-for="project in projects" :key="project.id" class="card cursor-pointer" @click="goToProject(project.id)">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-800">{{ project.name }}</h3>
          <span :class="statusBadgeClass(project.status)" class="badge">{{ project.status }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-4" style="min-height: 2.5rem;">{{ project.description || 'No description' }}</p>
        <div class="flex items-center justify-between text-sm text-gray-400">
          <span>📅 {{ project.deadline || 'No deadline' }}</span>
          <span>📝 {{ project.tasks_count || 0 }} tasks</span>
        </div>
        <div class="flex gap-2 mt-4">
          <button @click.stop="openEditModal(project)" class="btn btn-sm btn-secondary">Edit</button>
          <button @click.stop="deleteProject(project.id)" class="btn btn-sm btn-danger">Delete</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">{{ editingProject ? 'Edit Project' : 'New Project' }}</h3>
        <div v-if="formError" class="alert alert-error">{{ formError }}</div>
        <form @submit.prevent="saveProject">
          <div class="mb-4">
            <label>Project Name *</label>
            <input v-model="form.name" type="text" placeholder="Enter project name" required />
          </div>
          <div class="mb-4">
            <label>Description</label>
            <textarea v-model="form.description" rows="3" placeholder="Describe the project"></textarea>
          </div>
          <div class="mb-4">
            <label>Status</label>
            <select v-model="form.status">
              <option value="planning">Planning</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="on_hold">On Hold</option>
            </select>
          </div>
          <div class="mb-4">
            <label>Deadline</label>
            <input v-model="form.deadline" type="date" />
          </div>
          <div class="flex gap-2 justify-between">
            <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const projects = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingProject = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({ name: '', description: '', status: 'planning', deadline: '' })

const fetchProjects = async () => {
  loading.value = true
  try {
    const res = await api.get('/projects')
    projects.value = res.data.data
  } catch (e) {
    console.error('Failed to fetch projects', e)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingProject.value = null
  form.value = { name: '', description: '', status: 'planning', deadline: '' }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (project) => {
  editingProject.value = project
  form.value = {
    name: project.name,
    description: project.description || '',
    status: project.status,
    deadline: project.deadline ? project.deadline.split('T')[0] : '',
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingProject.value = null
}

const saveProject = async () => {
  saving.value = true
  formError.value = ''
  try {
    if (editingProject.value) {
      await api.put(`/projects/${editingProject.value.id}`, form.value)
    } else {
      await api.post('/projects', form.value)
    }
    closeModal()
    await fetchProjects()
  } catch (e) {
    formError.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Failed to save'
  } finally {
    saving.value = false
  }
}

const deleteProject = async (id) => {
  if (!confirm('Are you sure you want to delete this project?')) return
  try {
    await api.delete(`/projects/${id}`)
    await fetchProjects()
  } catch (e) {
    alert('Failed to delete project')
  }
}

const goToProject = (id) => router.push(`/projects/${id}`)

const statusBadgeClass = (status) => {
  const map = {
    planning: 'bg-blue-100 text-blue-700',
    active: 'bg-green-100 text-green-700',
    completed: 'bg-purple-100 text-purple-700',
    on_hold: 'bg-yellow-100 text-yellow-700',
  }
  return map[status] || 'bg-gray-100 text-gray-700'
}

onMounted(fetchProjects)
</script>
