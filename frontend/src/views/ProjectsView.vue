<template>
  <div class="projects-view">
    <!-- View Header -->
    <div class="view-header mb-6">
      <div>
        <h1 class="page-title">Projects</h1>
        <p class="page-subtitle">Oversee active workstreams, delivery timelines, and project health.</p>
      </div>

      <button @click="openCreateModal" class="btn btn-primary">
        <Plus :size="16" />
        <span>New Project</span>
      </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="toolbar mb-6">
      <div class="search-wrapper filter-search">
        <Search :size="16" class="search-icon" />
        <input
          v-model="searchQuery"
          type="text"
          class="form-input"
          placeholder="Search projects..."
        />
      </div>

      <div class="filter-tabs">
        <button
          v-for="tab in filterTabs"
          :key="tab.value"
          :class="['filter-tab-btn', { active: selectedStatus === tab.value }]"
          @click="selectedStatus = tab.value"
        >
          <span>{{ tab.label }}</span>
          <span class="filter-count">{{ getCountForStatus(tab.value) }}</span>
        </button>
      </div>

      <div class="filter-sort">
        <select v-model="sortBy" class="form-select sort-select">
          <option value="newest">Recently Created</option>
          <option value="deadline">Upcoming Deadline</option>
          <option value="name">Alphabetical (A-Z)</option>
          <option value="tasks">Most Tasks</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p class="text-secondary mt-3">Loading projects...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredProjects.length === 0" class="empty-state">
      <div class="empty-state-icon">
        <FolderKanban :size="24" />
      </div>
      <h3 class="empty-state-title">
        {{ searchQuery || selectedStatus !== 'all' ? 'No matching projects found' : 'No projects created yet' }}
      </h3>
      <p class="empty-state-desc">
        {{
          searchQuery || selectedStatus !== 'all'
            ? 'Try adjusting your search terms or filter selection to find what you are looking for.'
            : 'Initialize your first workspace project to start assigning and managing tasks.'
        }}
      </p>
      <button v-if="!searchQuery && selectedStatus === 'all'" @click="openCreateModal" class="btn btn-primary btn-sm">
        <Plus :size="16" />
        <span>Create Project</span>
      </button>
      <button v-else @click="resetFilters" class="btn btn-outline btn-sm">
        Reset Filters
      </button>
    </div>

    <!-- Project Cards Grid -->
    <div v-else class="grid grid-cols-1 md-grid-cols-2 lg-grid-cols-3 gap-4">
      <div
        v-for="project in filteredProjects"
        :key="project.id"
        class="card card-interactive project-card"
        @click="goToProject(project.id)"
      >
        <div class="project-card-header">
          <div class="project-title-area">
            <h3 class="card-title">{{ project.name }}</h3>
          </div>
          <StatusBadge :status="project.status" />
        </div>

        <p class="project-description line-clamp-2">
          {{ project.description || 'No description provided for this workstream.' }}
        </p>

        <!-- Project Meta & Progress -->
        <div class="project-card-footer">
          <div class="project-meta-item">
            <Calendar :size="14" class="meta-icon" />
            <span :class="{ 'text-danger font-medium': isOverdue(project.deadline, project.status) }">
              {{ formatDeadline(project.deadline) }}
            </span>
          </div>

          <div class="project-meta-item">
            <CheckSquare2 :size="14" class="meta-icon" />
            <span>{{ project.tasks_count || 0 }} tasks</span>
          </div>
        </div>

        <!-- Card Actions Toolbar -->
        <div class="project-actions-row" @click.stop>
          <button @click="openEditModal(project)" class="btn btn-ghost btn-xs action-btn" title="Edit project">
            <Edit3 :size="14" />
            <span>Edit</span>
          </button>
          <button @click="promptDeleteProject(project)" class="btn btn-ghost btn-xs action-btn text-danger-hover" title="Delete project">
            <Trash2 :size="14" />
            <span>Delete</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Create / Edit Project Modal -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-dialog" role="dialog" aria-modal="true">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingProject ? 'Edit Project' : 'Create New Project' }}</h3>
          <button @click="closeModal" class="btn-ghost" title="Close" aria-label="Close">
            <X :size="18" />
          </button>
        </div>

        <form @submit.prevent="saveProject">
          <div class="modal-body">
            <div v-if="formError" class="alert alert-error">
              <AlertCircle :size="16" class="flex-shrink-0" />
              <span>{{ formError }}</span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Project Name <span class="required">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                class="form-input"
                placeholder="e.g. Core System Migration"
                required
              />
            </div>

            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="form-textarea"
                placeholder="Briefly state the objective and scope of this project..."
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">Status</label>
                <select v-model="form.status" class="form-select">
                  <option value="planning">Planning</option>
                  <option value="active">Active</option>
                  <option value="completed">Completed</option>
                  <option value="on_hold">On Hold</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Target Deadline</label>
                <input
                  v-model="form.deadline"
                  type="date"
                  class="form-input"
                />
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-outline" :disabled="saving">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>{{ editingProject ? 'Save Changes' : 'Create Project' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Delete Project"
      :message="`Are you sure you want to delete '${projectToDelete?.name}'? All associated tasks will also be removed. This action cannot be undone.`"
      confirm-text="Delete Project"
      confirm-variant="danger"
      :loading="deleting"
      @confirm="executeDeleteProject"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useToastStore } from '../stores/toast'
import StatusBadge from '../components/StatusBadge.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import {
  Plus,
  Search,
  FolderKanban,
  Calendar,
  CheckSquare2,
  Edit3,
  Trash2,
  X,
  AlertCircle
} from 'lucide-vue-next'

const router = useRouter()
const toast = useToastStore()

const projects = ref([])
const loading = ref(true)
const searchQuery = ref('')
const selectedStatus = ref('all')
const sortBy = ref('newest')

const showModal = ref(false)
const editingProject = ref(null)
const saving = ref(false)
const formError = ref('')

const showDeleteModal = ref(false)
const projectToDelete = ref(null)
const deleting = ref(false)

const form = ref({
  name: '',
  description: '',
  status: 'planning',
  deadline: ''
})

const filterTabs = [
  { label: 'All', value: 'all' },
  { label: 'Active', value: 'active' },
  { label: 'Planning', value: 'planning' },
  { label: 'Completed', value: 'completed' },
  { label: 'On Hold', value: 'on_hold' }
]

const fetchProjects = async () => {
  loading.value = true
  try {
    const res = await api.get('/projects')
    projects.value = res.data.data || []
  } catch (err) {
    console.error('Failed to fetch projects', err)
    toast.error('Unable to load projects list')
  } finally {
    loading.value = false
  }
}

const getCountForStatus = (status) => {
  if (status === 'all') return projects.value.length
  return projects.value.filter((p) => p.status === status).length
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = 'all'
}

const filteredProjects = computed(() => {
  let list = [...projects.value]

  // Status Filter
  if (selectedStatus.value !== 'all') {
    list = list.filter((p) => p.status === selectedStatus.value)
  }

  // Search Filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        (p.description && p.description.toLowerCase().includes(q))
    )
  }

  // Sorting
  list.sort((a, b) => {
    if (sortBy.value === 'newest') {
      return (b.id || 0) - (a.id || 0)
    }
    if (sortBy.value === 'name') {
      return a.name.localeCompare(b.name)
    }
    if (sortBy.value === 'tasks') {
      return (b.tasks_count || 0) - (a.tasks_count || 0)
    }
    if (sortBy.value === 'deadline') {
      if (!a.deadline) return 1
      if (!b.deadline) return -1
      return new Date(a.deadline) - new Date(b.deadline)
    }
    return 0
  })

  return list
})

const isOverdue = (deadline, status) => {
  if (!deadline || status === 'completed') return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return new Date(deadline) < today
}

const formatDeadline = (val) => {
  if (!val) return 'No deadline'
  const dateStr = val.split('T')[0]
  const [year, month, day] = dateStr.split('-')
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  return `${day} ${months[parseInt(month, 10) - 1]} ${year}`
}

const goToProject = (id) => {
  router.push(`/projects/${id}`)
}

const openCreateModal = () => {
  editingProject.value = null
  form.value = {
    name: '',
    description: '',
    status: 'planning',
    deadline: ''
  }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (project) => {
  editingProject.value = project
  form.value = {
    name: project.name,
    description: project.description || '',
    status: project.status,
    deadline: project.deadline ? project.deadline.split('T')[0] : ''
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
      toast.success('Project details updated successfully')
    } else {
      await api.post('/projects', form.value)
      toast.success('Project created successfully')
    }
    closeModal()
    await fetchProjects()
  } catch (err) {
    const errorMsg =
      err.response?.data?.message ||
      Object.values(err.response?.data?.errors || {}).flat().join(', ') ||
      'Failed to save project'
    formError.value = errorMsg
  } finally {
    saving.value = false
  }
}

const promptDeleteProject = (project) => {
  projectToDelete.value = project
  showDeleteModal.value = true
}

const executeDeleteProject = async () => {
  if (!projectToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/projects/${projectToDelete.value.id}`)
    toast.success('Project deleted successfully')
    showDeleteModal.value = false
    projectToDelete.value = null
    await fetchProjects()
  } catch (err) {
    toast.error('Failed to delete project')
  } finally {
    deleting.value = false
  }
}

onMounted(fetchProjects)
</script>

<style scoped>
.view-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--space-4);
  flex-wrap: wrap;
}

.toolbar {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  flex-wrap: wrap;
}

.filter-search {
  flex: 1;
  min-width: 200px;
  max-width: 320px;
}

.filter-tabs {
  display: flex;
  align-items: center;
  background-color: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  padding: 3px;
  gap: 2px;
  overflow-x: auto;
}

.filter-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border-radius: var(--radius-xs);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary);
  transition: all 0.12s ease;
  white-space: nowrap;
}

.filter-tab-btn:hover {
  color: var(--text-primary);
  background-color: var(--bg-subtle);
}

.filter-tab-btn.active {
  background-color: var(--accent-subtle);
  color: var(--accent-primary);
  font-weight: 600;
}

.filter-count {
  font-size: 0.6875rem;
  padding: 1px 5px;
  background-color: #E6E8F3;
  border-radius: var(--radius-full);
  color: var(--text-secondary);
}

.filter-tab-btn.active .filter-count {
  background-color: #DBD7F8;
  color: var(--accent-primary);
}

.filter-sort {
  margin-left: auto;
}

.sort-select {
  height: 34px;
  padding: 0 0.75rem;
  font-size: 0.8125rem;
}

.project-card {
  display: flex;
  flex-direction: column;
  padding: var(--space-5);
  height: 100%;
}

.project-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--space-2);
  margin-bottom: var(--space-3);
}

.project-title-area {
  flex: 1;
  min-width: 0;
}

.project-description {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  line-height: 1.45;
  margin-bottom: var(--space-4);
  flex: 1;
}

.project-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: var(--space-3);
  border-top: 1px solid var(--border-subtle);
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.project-meta-item {
  display: flex;
  align-items: center;
  gap: 5px;
}

.meta-icon {
  color: var(--text-muted);
}

.project-actions-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--space-1);
  margin-top: var(--space-3);
}

.action-btn {
  color: var(--text-muted);
  padding: 3px 8px;
}

.action-btn:hover {
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

.text-danger {
  color: var(--danger-dot);
}
</style>
