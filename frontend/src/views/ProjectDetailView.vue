<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div v-if="loading" class="text-center py-8 text-gray-500">Loading project...</div>

    <template v-else-if="project">
      <!-- Project Header -->
      <div class="flex items-center gap-4 mb-6">
        <button @click="$router.push('/projects')" class="btn btn-secondary btn-sm">← Back</button>
        <div>
          <h2 class="text-2xl font-bold text-gray-800">{{ project.name }}</h2>
          <p class="text-sm text-gray-500">{{ project.description || 'No description' }}</p>
        </div>
        <span :class="statusBadgeClass(project.status)" class="badge">{{ project.status }}</span>
      </div>

      <!-- Tasks Section -->
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-gray-700">Tasks</h3>
        <button @click="openCreateModal" class="btn btn-primary btn-sm">+ Add Task</button>
      </div>

      <div v-if="tasks.length === 0" class="text-center py-8 text-gray-500">
        <p>No tasks yet. Click "Add Task" to create one.</p>
      </div>

      <div v-else class="flex gap-4" style="overflow-x: auto;">
        <!-- Kanban columns -->
        <div v-for="col in columns" :key="col.key" class="bg-gray-100 rounded-lg p-3" style="min-width: 250px; flex: 1;">
          <h4 class="font-semibold text-sm text-gray-600 mb-3">{{ col.label }} ({{ tasksByStatus(col.key).length }})</h4>
          <div class="flex gap-2" style="flex-direction: column;">
            <div v-for="task in tasksByStatus(col.key)" :key="task.id" class="card p-3" style="margin-bottom: 0.5rem;">
              <div class="flex items-center justify-between mb-1">
                <span class="font-medium text-sm">{{ task.title }}</span>
                <span :class="priorityBadgeClass(task.priority)" class="badge">{{ task.priority }}</span>
              </div>
              <p class="text-sm text-gray-400" style="font-size: 0.75rem;">{{ task.description || '' }}</p>
              <div class="text-sm text-gray-400 mt-2" style="font-size: 0.7rem;">
                <span v-if="task.assignee">👤 {{ task.assignee.name }}</span>
                <span v-if="task.deadline"> · 📅 {{ task.deadline }}</span>
              </div>
              <div class="flex gap-2 mt-2">
                <button @click="openEditModal(task)" class="btn btn-sm btn-secondary">Edit</button>
                <button @click="deleteTask(task.id)" class="btn btn-sm btn-danger">Del</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Task Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">{{ editingTask ? 'Edit Task' : 'New Task' }}</h3>
        <div v-if="formError" class="alert alert-error">{{ formError }}</div>
        <form @submit.prevent="saveTask">
          <div class="mb-4">
            <label>Title *</label>
            <input v-model="form.title" type="text" placeholder="Task title" required />
          </div>
          <div class="mb-4">
            <label>Description</label>
            <textarea v-model="form.description" rows="2" placeholder="Describe the task"></textarea>
          </div>
          <div class="flex gap-4 mb-4">
            <div style="flex:1;">
              <label>Status</label>
              <select v-model="form.status">
                <option value="todo">Todo</option>
                <option value="in_progress">In Progress</option>
                <option value="review">Review</option>
                <option value="done">Done</option>
              </select>
            </div>
            <div style="flex:1;">
              <label>Priority</label>
              <select v-model="form.priority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
          </div>
          <div class="mb-4">
            <label>Deadline</label>
            <input v-model="form.deadline" type="date" />
          </div>
          <div class="mb-4">
            <label>Assignee</label>
            <select v-model="form.assignee_id">
              <option value="">Unassigned</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
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
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const projectId = computed(() => route.params.id)

const project = ref(null)
const tasks = ref([])
const users = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingTask = ref(null)
const saving = ref(false)
const formError = ref('')

const columns = [
  { key: 'todo', label: '📋 Todo' },
  { key: 'in_progress', label: '🔧 In Progress' },
  { key: 'review', label: '👀 Review' },
  { key: 'done', label: '✅ Done' },
]

const form = ref({ title: '', description: '', status: 'todo', priority: 'medium', deadline: '', assignee_id: '' })

const tasksByStatus = (status) => tasks.value.filter(t => t.status === status)

const fetchProject = async () => {
  loading.value = true
  try {
    const res = await api.get(`/projects/${projectId.value}`)
    project.value = res.data.data
    tasks.value = res.data.data.tasks || []
  } catch (e) {
    console.error('Failed to fetch project', e)
  } finally {
    loading.value = false
  }
}

const fetchTasks = async () => {
  try {
    const res = await api.get('/tasks', { params: { project_id: projectId.value } })
    tasks.value = res.data.data
  } catch (e) {
    console.error(e)
  }
}

const openCreateModal = () => {
  editingTask.value = null
  form.value = { title: '', description: '', status: 'todo', priority: 'medium', deadline: '', assignee_id: '' }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (task) => {
  editingTask.value = task
  form.value = {
    title: task.title,
    description: task.description || '',
    status: task.status,
    priority: task.priority,
    deadline: task.deadline ? task.deadline.split('T')[0] : '',
    assignee_id: task.assignee_id || '',
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingTask.value = null
}

const saveTask = async () => {
  saving.value = true
  formError.value = ''
  const data = {
    ...form.value,
    project_id: projectId.value,
    assignee_id: form.value.assignee_id || null,
  }
  try {
    if (editingTask.value) {
      await api.put(`/tasks/${editingTask.value.id}`, data)
    } else {
      await api.post('/tasks', data)
    }
    closeModal()
    await fetchTasks()
  } catch (e) {
    formError.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Failed to save'
  } finally {
    saving.value = false
  }
}

const deleteTask = async (id) => {
  if (!confirm('Delete this task?')) return
  try {
    await api.delete(`/tasks/${id}`)
    await fetchTasks()
  } catch (e) {
    alert('Failed to delete task')
  }
}

const statusBadgeClass = (status) => {
  const map = { planning: 'bg-blue-100 text-blue-700', active: 'bg-green-100 text-green-700', completed: 'bg-purple-100 text-purple-700', on_hold: 'bg-yellow-100 text-yellow-700' }
  return map[status] || 'bg-gray-100 text-gray-700'
}

const priorityBadgeClass = (priority) => {
  const map = { low: 'bg-green-100 text-green-700', medium: 'bg-blue-100 text-blue-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }
  return map[priority] || 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
  await fetchProject()
  // Fetch users list for assignee dropdown
  try {
    const res = await api.get('/user')
    // For simplicity, we'll use current user. In production, you'd have a /users endpoint
    users.value = [res.data]
  } catch (e) { /* ignore */ }
})
</script>
