<template>
  <div class="project-detail-view">
    <!-- Top Navigation & Back Link -->
    <div class="mb-4">
      <router-link to="/projects" class="back-link">
        <ArrowLeft :size="16" />
        <span>Back to Projects</span>
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p class="text-secondary mt-3">Loading project workstream...</p>
    </div>

    <!-- Error / Not Found State -->
    <div v-else-if="!project" class="empty-state">
      <AlertCircle :size="32" class="text-danger mb-2" />
      <h3 class="empty-state-title">Project not found</h3>
      <p class="empty-state-desc">The requested project could not be found or you do not have permission to view it.</p>
      <router-link to="/projects" class="btn btn-primary btn-sm">Return to Projects</router-link>
    </div>

    <!-- Project Workspace -->
    <template v-else>
      <!-- Project Summary Banner -->
      <div class="card project-summary-card mb-6">
        <div class="project-summary-header">
          <div class="project-info-block">
            <div class="project-title-row">
              <h1 class="page-title">{{ project.name }}</h1>
              <StatusBadge :status="project.status" />
            </div>
            <p v-if="project.description" class="project-desc-text">
              {{ project.description }}
            </p>
          </div>

          <div class="project-actions-block">
            <button @click="openCreateTaskModal()" class="btn btn-primary btn-sm">
              <Plus :size="16" />
              <span>Add Task</span>
            </button>
          </div>
        </div>

        <div class="project-summary-footer">
          <div class="summary-metrics">
            <div class="metric-item">
              <Calendar :size="14" class="metric-icon" />
              <span>Target: <strong>{{ formatDeadline(project.deadline) }}</strong></span>
            </div>

            <div class="metric-item">
              <CheckSquare2 :size="14" class="metric-icon" />
              <span>Progress: <strong>{{ doneTasksCount }} / {{ tasks.length }}</strong> tasks completed ({{ taskPercent }}%)</span>
            </div>
          </div>

          <div class="project-progress-bar">
            <div class="progress-track">
              <div
                class="progress-fill"
                :class="{ 'progress-fill-success': taskPercent === 100 }"
                :style="{ width: `${taskPercent}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Controls & Filters -->
      <div class="task-toolbar mb-4">
        <div class="search-wrapper task-search">
          <Search :size="16" class="search-icon" />
          <input
            v-model="searchQuery"
            type="text"
            class="form-input"
            placeholder="Filter tasks by title..."
          />
        </div>

        <div class="task-filters-right">
          <select v-model="priorityFilter" class="form-select filter-select">
            <option value="all">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
      </div>

      <!-- Kanban Board -->
      <div class="kanban-board">
        <div
          v-for="col in columns"
          :key="col.key"
          class="kanban-column"
        >
          <!-- Column Header -->
          <div class="kanban-column-header">
            <div class="kanban-column-title">
              <component :is="col.icon" :size="16" class="col-icon" :style="{ color: col.iconColor }" />
              <span>{{ col.label }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="kanban-count-pill">{{ tasksByColumn(col.key).length }}</span>
              <button
                @click="openCreateTaskModal(col.key)"
                class="btn-ghost btn-xs add-col-btn"
                title="Add task to this column"
              >
                <Plus :size="14" />
              </button>
            </div>
          </div>

          <!-- Cards Stack -->
          <div class="kanban-cards-wrapper">
            <div
              v-if="tasksByColumn(col.key).length === 0"
              class="empty-column-placeholder"
            >
              <span>No tasks in {{ col.label }}</span>
            </div>

            <div
              v-for="task in tasksByColumn(col.key)"
              :key="task.id"
              class="kanban-card"
            >
              <!-- Card Top Row -->
              <div class="card-top-row">
                <span class="card-task-title">{{ task.title }}</span>
                <PriorityBadge :priority="task.priority" />
              </div>

              <!-- Card Description (if present) -->
              <p v-if="task.description" class="card-desc-text line-clamp-2">
                {{ task.description }}
              </p>

              <!-- Card Metadata -->
              <div class="card-meta-row">
                <div v-if="task.assignee" class="card-assignee" :title="`Assigned to ${task.assignee.name}`">
                  <div class="avatar avatar-sm">{{ getInitials(task.assignee.name) }}</div>
                  <span class="assignee-name">{{ task.assignee.name }}</span>
                </div>
                <div v-else class="card-assignee text-muted">
                  <span class="assignee-name">Unassigned</span>
                </div>

                <div
                  v-if="task.deadline"
                  :class="['card-deadline', { 'deadline-overdue': isOverdue(task.deadline, task.status) }]"
                >
                  <Clock :size="12" />
                  <span>{{ formatShortDate(task.deadline) }}</span>
                </div>
              </div>

              <!-- Card Quick Status & Actions Footer -->
              <div class="card-footer-actions">
                <!-- Quick Move Action -->
                <div class="quick-status-selector">
                  <select
                    :value="task.status"
                    class="form-select status-select-xs"
                    @change="quickChangeStatus(task, $event.target.value)"
                  >
                    <option value="todo">To Do</option>
                    <option value="in_progress">In Progress</option>
                    <option value="review">Review</option>
                    <option value="done">Done</option>
                  </select>
                </div>

                <div class="card-action-buttons">
                  <button @click="openEditTaskModal(task)" class="btn-ghost btn-xs" title="Edit task">
                    <Edit3 :size="13" />
                  </button>
                  <button @click="promptDeleteTask(task)" class="btn-ghost btn-xs text-danger-hover" title="Delete task">
                    <Trash2 :size="13" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Create / Edit Task Modal -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-dialog" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingTask ? 'Edit Task' : 'Create Task' }}</h3>
          <button @click="closeModal" class="btn-ghost" title="Close" aria-label="Close">
            <X :size="18" />
          </button>
        </div>

        <form @submit.prevent="saveTask">
          <div class="modal-body">
            <div v-if="formError" class="alert alert-error">
              <AlertCircle :size="16" class="flex-shrink-0" />
              <span>{{ formError }}</span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Task Title <span class="required">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                class="form-input"
                placeholder="e.g. Set up authentication middleware"
                required
              />
            </div>

            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea
                v-model="form.description"
                rows="2"
                class="form-textarea"
                placeholder="Details, acceptance criteria, or technical notes..."
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">Column / Status</label>
                <select v-model="form.status" class="form-select">
                  <option value="todo">To Do</option>
                  <option value="in_progress">In Progress</option>
                  <option value="review">Review</option>
                  <option value="done">Done</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Priority</label>
                <select v-model="form.priority" class="form-select">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">Due Date</label>
                <input
                  v-model="form.deadline"
                  type="date"
                  class="form-input"
                />
              </div>

              <div class="form-group">
                <label class="form-label">Assignee</label>
                <select v-model="form.assignee_id" class="form-select">
                  <option value="">Unassigned</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-outline" :disabled="saving">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>{{ editingTask ? 'Save Changes' : 'Create Task' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Task Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Delete Task"
      :message="`Are you sure you want to delete '${taskToDelete?.title}'? This action cannot be undone.`"
      confirm-text="Delete Task"
      confirm-variant="danger"
      :loading="deleting"
      @confirm="executeDeleteTask"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import { useToastStore } from '../stores/toast'
import StatusBadge from '../components/StatusBadge.vue'
import PriorityBadge from '../components/PriorityBadge.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import {
  ArrowLeft,
  Plus,
  Search,
  Calendar,
  CheckSquare2,
  Clock,
  Edit3,
  Trash2,
  X,
  AlertCircle,
  CircleDot,
  PlayCircle,
  Eye,
  CheckCircle2
} from 'lucide-vue-next'

const route = useRoute()
const toast = useToastStore()
const projectId = computed(() => route.params.id)

const project = ref(null)
const tasks = ref([])
const users = ref([])
const loading = ref(true)

const searchQuery = ref('')
const priorityFilter = ref('all')

const showModal = ref(false)
const editingTask = ref(null)
const saving = ref(false)
const formError = ref('')

const showDeleteModal = ref(false)
const taskToDelete = ref(null)
const deleting = ref(false)

const columns = [
  { key: 'todo', label: 'To Do', icon: CircleDot, iconColor: '#868E96' },
  { key: 'in_progress', label: 'In Progress', icon: PlayCircle, iconColor: '#3B82F6' },
  { key: 'review', label: 'Review', icon: Eye, iconColor: '#F59E0B' },
  { key: 'done', label: 'Done', icon: CheckCircle2, iconColor: '#10B981' }
]

const form = ref({
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  deadline: '',
  assignee_id: ''
})

const fetchProjectDetails = async () => {
  loading.value = true
  try {
    const res = await api.get(`/projects/${projectId.value}`)
    project.value = res.data.data
    tasks.value = res.data.data.tasks || []
  } catch (err) {
    console.error('Failed to load project details', err)
    toast.error('Failed to load project details')
  } finally {
    loading.value = false
  }
}

const fetchTasks = async () => {
  try {
    const res = await api.get('/tasks', { params: { project_id: projectId.value } })
    tasks.value = res.data.data || []
  } catch (err) {
    console.error('Failed to fetch tasks', err)
  }
}

const doneTasksCount = computed(() => {
  return tasks.value.filter((t) => t.status === 'done').length
})

const taskPercent = computed(() => {
  if (tasks.value.length === 0) return 0
  return Math.round((doneTasksCount.value / tasks.value.length) * 100)
})

const filteredTasks = computed(() => {
  let list = [...tasks.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(
      (t) =>
        t.title.toLowerCase().includes(q) ||
        (t.description && t.description.toLowerCase().includes(q))
    )
  }

  if (priorityFilter.value !== 'all') {
    list = list.filter((t) => t.priority === priorityFilter.value)
  }

  return list
})

const tasksByColumn = (statusKey) => {
  return filteredTasks.value.filter((t) => t.status === statusKey)
}

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return parts[0].slice(0, 2).toUpperCase()
}

const isOverdue = (deadline, status) => {
  if (!deadline || status === 'done') return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return new Date(deadline) < today
}

const formatDeadline = (val) => {
  if (!val) return 'None'
  const dateStr = val.split('T')[0]
  const [year, month, day] = dateStr.split('-')
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  return `${day} ${months[parseInt(month, 10) - 1]} ${year}`
}

const formatShortDate = (val) => {
  if (!val) return ''
  const dateStr = val.split('T')[0]
  const [, month, day] = dateStr.split('-')
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  return `${day} ${months[parseInt(month, 10) - 1]}`
}

const openCreateTaskModal = (initialStatus = 'todo') => {
  editingTask.value = null
  form.value = {
    title: '',
    description: '',
    status: initialStatus,
    priority: 'medium',
    deadline: '',
    assignee_id: ''
  }
  formError.value = ''
  showModal.value = true
}

const openEditTaskModal = (task) => {
  editingTask.value = task
  form.value = {
    title: task.title,
    description: task.description || '',
    status: task.status,
    priority: task.priority,
    deadline: task.deadline ? task.deadline.split('T')[0] : '',
    assignee_id: task.assignee_id || ''
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
  const payload = {
    ...form.value,
    project_id: projectId.value,
    assignee_id: form.value.assignee_id ? Number(form.value.assignee_id) : null
  }
  try {
    if (editingTask.value) {
      await api.put(`/tasks/${editingTask.value.id}`, payload)
      toast.success('Task updated successfully')
    } else {
      await api.post('/tasks', payload)
      toast.success('Task created successfully')
    }
    closeModal()
    await fetchTasks()
  } catch (err) {
    const errorMsg =
      err.response?.data?.message ||
      Object.values(err.response?.data?.errors || {}).flat().join(', ') ||
      'Failed to save task'
    formError.value = errorMsg
  } finally {
    saving.value = false
  }
}

const quickChangeStatus = async (task, newStatus) => {
  if (task.status === newStatus) return
  const prevStatus = task.status
  task.status = newStatus // Optimistic UI update
  try {
    await api.put(`/tasks/${task.id}`, {
      title: task.title,
      project_id: task.project_id,
      status: newStatus
    })
    toast.success(`Task moved to ${newStatus.replace('_', ' ')}`)
    await fetchTasks()
  } catch (err) {
    task.status = prevStatus
    toast.error('Failed to change status')
  }
}

const promptDeleteTask = (task) => {
  taskToDelete.value = task
  showDeleteModal.value = true
}

const executeDeleteTask = async () => {
  if (!taskToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/tasks/${taskToDelete.value.id}`)
    toast.success('Task deleted')
    showDeleteModal.value = false
    taskToDelete.value = null
    await fetchTasks()
  } catch (err) {
    toast.error('Failed to delete task')
  } finally {
    deleting.value = false
  }
}

onMounted(async () => {
  await fetchProjectDetails()
  try {
    const userRes = await api.get('/user')
    if (userRes.data) {
      users.value = [userRes.data]
    }
  } catch (e) {
    // ignore
  }
})
</script>

<style scoped>
.back-link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary);
  transition: color 0.15s ease;
}

.back-link:hover {
  color: var(--accent-primary);
}

.project-summary-card {
  padding: var(--space-5);
}

.project-summary-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--space-4);
  flex-wrap: wrap;
}

.project-info-block {
  flex: 1;
  min-width: 0;
}

.project-title-row {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  flex-wrap: wrap;
}

.project-desc-text {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: var(--space-2);
  max-width: 800px;
  line-height: 1.45;
}

.project-summary-footer {
  margin-top: var(--space-4);
  padding-top: var(--space-3);
  border-top: 1px solid var(--border-subtle);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.summary-metrics {
  display: flex;
  align-items: center;
  gap: var(--space-5);
  font-size: 0.8125rem;
  color: var(--text-secondary);
  flex-wrap: wrap;
}

.metric-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.metric-icon {
  color: var(--text-muted);
}

.project-progress-bar {
  width: 100%;
}

.task-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  flex-wrap: wrap;
}

.task-search {
  max-width: 280px;
  flex: 1;
}

.filter-select {
  height: 36px;
  font-size: 0.8125rem;
}

.col-icon {
  flex-shrink: 0;
}

.add-col-btn {
  padding: 2px 4px;
  color: var(--text-secondary);
  border-radius: var(--radius-xs);
}

.add-col-btn:hover {
  color: var(--accent-primary);
  background-color: #E2E5F3;
}

.empty-column-placeholder {
  text-align: center;
  padding: var(--space-6) var(--space-2);
  font-size: 0.75rem;
  color: var(--text-muted);
  border: 1px dashed #D9DCEB;
  border-radius: var(--radius-sm);
}

.card-top-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--space-2);
  margin-bottom: var(--space-1);
}

.card-task-title {
  font-size: 0.84375rem;
  font-weight: 600;
  color: var(--text-primary);
  line-height: 1.35;
  flex: 1;
}

.card-desc-text {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-bottom: var(--space-2);
  line-height: 1.35;
}

.card-meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2);
  font-size: 0.75rem;
  margin-top: var(--space-2);
  padding-top: var(--space-2);
  border-top: 1px solid #F1F3FA;
}

.card-assignee {
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 0;
}

.assignee-name {
  font-size: 0.75rem;
  color: var(--text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100px;
}

.card-deadline {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.6875rem;
  color: var(--text-muted);
}

.deadline-overdue {
  color: var(--danger-dot);
  font-weight: 600;
}

.card-footer-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2);
  margin-top: var(--space-2);
  padding-top: var(--space-2);
}

.status-select-xs {
  height: 24px;
  padding: 0 4px;
  font-size: 0.6875rem;
  border-radius: var(--radius-xs);
  background-color: #FAFAFD;
  border-color: #E2E4F0;
  color: var(--text-secondary);
}

.card-action-buttons {
  display: flex;
  align-items: center;
  gap: 2px;
}

.card-action-buttons button {
  color: var(--text-muted);
  padding: 3px;
}

.card-action-buttons button:hover {
  color: var(--text-primary);
}

.text-danger-hover:hover {
  color: var(--danger-dot) !important;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-12) 0;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-subtle);
  border-top-color: var(--accent-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
