<template>
  <div class="tasks-view">
    <!-- View Header -->
    <div class="view-header mb-6">
      <div>
        <h1 class="page-title">All Tasks</h1>
        <p class="page-subtitle">Global supervision across all projects, assignees, and deadlines.</p>
      </div>
    </div>

    <!-- Metrics Summary Strip -->
    <div class="stats-grid mb-6">
      <div class="stat-card" style="padding: var(--space-4);">
        <div class="stat-label">Total Workstream Items</div>
        <div class="stat-value" style="font-size: 1.5rem;">{{ tasks.length }}</div>
      </div>
      <div class="stat-card" style="padding: var(--space-4);">
        <div class="stat-label">In Execution</div>
        <div class="stat-value text-info" style="font-size: 1.5rem;">{{ inProgressCount }}</div>
      </div>
      <div class="stat-card" style="padding: var(--space-4);">
        <div class="stat-label">Completed</div>
        <div class="stat-value text-success" style="font-size: 1.5rem;">{{ completedCount }}</div>
      </div>
      <div class="stat-card" style="padding: var(--space-4);">
        <div class="stat-label">Overdue / Action Needed</div>
        <div class="stat-value" :class="overdueCount > 0 ? 'text-danger' : 'text-success'" style="font-size: 1.5rem;">
          {{ overdueCount }}
        </div>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="toolbar mb-4">
      <div class="search-wrapper task-search">
        <Search :size="16" class="search-icon" />
        <input
          v-model="searchQuery"
          type="text"
          class="form-input"
          placeholder="Search task title..."
        />
      </div>

      <div class="filters-group">
        <!-- Filter by Project -->
        <select v-model="selectedProject" class="form-select filter-select">
          <option value="all">All Projects</option>
          <option v-for="p in uniqueProjects" :key="p.id" :value="p.id">
            {{ p.name }}
          </option>
        </select>

        <!-- Filter by Status -->
        <select v-model="selectedStatus" class="form-select filter-select">
          <option value="all">All Statuses</option>
          <option value="todo">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="review">Review</option>
          <option value="done">Done</option>
        </select>

        <!-- Filter by Priority -->
        <select v-model="selectedPriority" class="form-select filter-select">
          <option value="all">All Priorities</option>
          <option value="critical">Critical</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="low">Low</option>
        </select>

        <button
          v-if="searchQuery || selectedProject !== 'all' || selectedStatus !== 'all' || selectedPriority !== 'all'"
          @click="resetFilters"
          class="btn btn-ghost btn-sm"
          title="Reset filters"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p class="text-secondary mt-3">Loading tasks catalog...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredTasks.length === 0" class="empty-state">
      <div class="empty-state-icon">
        <CheckSquare2 :size="24" />
      </div>
      <h3 class="empty-state-title">No matching tasks found</h3>
      <p class="empty-state-desc">
        {{
          searchQuery || selectedProject !== 'all' || selectedStatus !== 'all' || selectedPriority !== 'all'
            ? 'Adjust your query or active filters to find the task you are looking for.'
            : 'No tasks exist in the workspace yet. Tasks can be created from within individual projects.'
        }}
      </p>
      <button
        v-if="searchQuery || selectedProject !== 'all' || selectedStatus !== 'all' || selectedPriority !== 'all'"
        @click="resetFilters"
        class="btn btn-outline btn-sm"
      >
        Clear Filters
      </button>
      <router-link v-else to="/projects" class="btn btn-primary btn-sm">
        Go to Projects
      </router-link>
    </div>

    <!-- Tasks Table -->
    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 32%;">Task</th>
            <th style="width: 20%;">Project</th>
            <th style="width: 14%;">Status</th>
            <th style="width: 12%;">Priority</th>
            <th style="width: 12%;">Assignee</th>
            <th style="width: 10%;">Deadline</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="task in filteredTasks"
            :key="task.id"
            class="table-row-interactive"
            @click="goToProject(task.project_id)"
          >
            <!-- Title & Description -->
            <td>
              <div class="task-title-cell">
                <span class="font-medium text-primary">{{ task.title }}</span>
                <span v-if="task.description" class="task-desc-cell line-clamp-2">
                  {{ task.description }}
                </span>
              </div>
            </td>

            <!-- Project -->
            <td>
              <span class="project-tag" :title="task.project?.name">
                <FolderKanban :size="13" class="project-tag-icon" />
                <span class="truncate">{{ task.project?.name || 'Project' }}</span>
              </span>
            </td>

            <!-- Status -->
            <td>
              <StatusBadge :status="task.status" />
            </td>

            <!-- Priority -->
            <td>
              <PriorityBadge :priority="task.priority" />
            </td>

            <!-- Assignee -->
            <td>
              <div v-if="task.assignee" class="assignee-cell" :title="task.assignee.name">
                <div class="avatar avatar-sm">{{ getInitials(task.assignee.name) }}</div>
                <span class="truncate">{{ task.assignee.name }}</span>
              </div>
              <span v-else class="text-muted" style="font-size: 0.75rem;">Unassigned</span>
            </td>

            <!-- Deadline -->
            <td>
              <span
                v-if="task.deadline"
                :class="['deadline-cell', { 'text-danger font-medium': isOverdue(task.deadline, task.status) }]"
              >
                {{ formatShortDate(task.deadline) }}
              </span>
              <span v-else class="text-muted" style="font-size: 0.75rem;">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import StatusBadge from '../components/StatusBadge.vue'
import PriorityBadge from '../components/PriorityBadge.vue'
import {
  Search,
  CheckSquare2,
  FolderKanban
} from 'lucide-vue-next'

const router = useRouter()

const tasks = ref([])
const loading = ref(true)

const searchQuery = ref('')
const selectedProject = ref('all')
const selectedStatus = ref('all')
const selectedPriority = ref('all')

const fetchTasks = async () => {
  loading.value = true
  try {
    const res = await api.get('/tasks')
    tasks.value = res.data.data || []
  } catch (err) {
    console.error('Failed to fetch tasks', err)
  } finally {
    loading.value = false
  }
}

const inProgressCount = computed(() => {
  return tasks.value.filter((t) => t.status === 'in_progress' || t.status === 'review').length
})

const completedCount = computed(() => {
  return tasks.value.filter((t) => t.status === 'done').length
})

const isOverdue = (deadline, status) => {
  if (!deadline || status === 'done') return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return new Date(deadline) < today
}

const overdueCount = computed(() => {
  return tasks.value.filter((t) => isOverdue(t.deadline, t.status)).length
})

const uniqueProjects = computed(() => {
  const map = new Map()
  for (const t of tasks.value) {
    if (t.project && !map.has(t.project.id)) {
      map.set(t.project.id, t.project)
    }
  }
  return Array.from(map.values())
})

const filteredTasks = computed(() => {
  let list = [...tasks.value]

  if (selectedProject.value !== 'all') {
    list = list.filter((t) => t.project_id === Number(selectedProject.value))
  }

  if (selectedStatus.value !== 'all') {
    list = list.filter((t) => t.status === selectedStatus.value)
  }

  if (selectedPriority.value !== 'all') {
    list = list.filter((t) => t.priority === selectedPriority.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(
      (t) =>
        t.title.toLowerCase().includes(q) ||
        (t.description && t.description.toLowerCase().includes(q))
    )
  }

  return list
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedProject.value = 'all'
  selectedStatus.value = 'all'
  selectedPriority.value = 'all'
}

const goToProject = (projectId) => {
  if (projectId) {
    router.push(`/projects/${projectId}`)
  }
}

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return parts[0].slice(0, 2).toUpperCase()
}

const formatShortDate = (val) => {
  if (!val) return '—'
  const dateStr = val.split('T')[0]
  const [, month, day] = dateStr.split('-')
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  return `${day} ${months[parseInt(month, 10) - 1]}`
}

onMounted(fetchTasks)
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

.task-search {
  max-width: 280px;
  flex: 1;
}

.filters-group {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  flex-wrap: wrap;
  margin-left: auto;
}

.filter-select {
  height: 36px;
  font-size: 0.8125rem;
  padding: 0 0.75rem;
}

.table-row-interactive {
  cursor: pointer;
}

.task-title-cell {
  display: flex;
  flex-direction: column;
}

.task-desc-cell {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-top: 2px;
}

.project-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background-color: var(--bg-subtle);
  color: var(--text-primary);
  font-size: 0.75rem;
  font-weight: 500;
  padding: 3px 8px;
  border-radius: var(--radius-xs);
  max-width: 180px;
}

.project-tag-icon {
  color: var(--accent-primary);
  flex-shrink: 0;
}

.assignee-cell {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8125rem;
}

.deadline-cell {
  font-size: 0.8125rem;
  white-space: nowrap;
}

.text-info { color: var(--info-dot); }
.text-success { color: var(--success-dot); }
.text-danger { color: var(--danger-dot); }

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
