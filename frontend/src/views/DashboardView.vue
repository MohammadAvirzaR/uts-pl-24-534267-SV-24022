<template>
  <div class="dashboard-container">
    <!-- Page Header & Welcome Banner -->
    <div class="page-header-row mb-6">
      <div>
        <h1 class="page-title">Executive Overview</h1>
        <p class="page-subtitle">
          Real-time health, workstream velocity, and critical items requiring attention.
        </p>
      </div>

      <div class="header-actions">
        <router-link to="/projects" class="btn btn-primary btn-sm">
          <FolderPlus :size="16" />
          <span>New Project</span>
        </router-link>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p class="text-secondary mt-3">Compiling workspace metrics...</p>
    </div>

    <template v-else>
      <!-- Key Performance Metrics Grid -->
      <div class="stats-grid">
        <!-- Metric 1: Total Projects -->
        <div class="stat-card">
          <div class="stat-card-header">
            <span class="stat-label">Total Projects</span>
            <div class="stat-icon-wrapper">
              <FolderKanban :size="18" />
            </div>
          </div>
          <div class="stat-value">{{ totalProjects }}</div>
          <div class="stat-helper">
            <span class="text-success font-medium">{{ activeProjects }} active</span>
            <span class="text-muted"> · {{ completedProjects }} completed</span>
          </div>
        </div>

        <!-- Metric 2: Workstream Tasks -->
        <div class="stat-card">
          <div class="stat-card-header">
            <span class="stat-label">Tasks Completion</span>
            <div class="stat-icon-wrapper">
              <CheckCircle2 :size="18" />
            </div>
          </div>
          <div class="stat-value">{{ taskCompletionRate }}%</div>
          <div class="stat-helper">
            <span class="font-medium">{{ doneTasks }} of {{ totalTasks }}</span>
            <span class="text-muted"> tasks completed</span>
          </div>
          <div class="progress-track mt-2">
            <div
              class="progress-fill"
              :class="{ 'progress-fill-success': taskCompletionRate === 100 }"
              :style="{ width: `${taskCompletionRate}%` }"
            ></div>
          </div>
        </div>

        <!-- Metric 3: Active Workflows -->
        <div class="stat-card">
          <div class="stat-card-header">
            <span class="stat-label">In Execution</span>
            <div class="stat-icon-wrapper">
              <Activity :size="18" />
            </div>
          </div>
          <div class="stat-value">{{ inProgressAndReviewCount }}</div>
          <div class="stat-helper">
            <span>{{ inProgressTasks }} in progress · {{ reviewTasks }} in review</span>
          </div>
        </div>

        <!-- Metric 4: Attention Required -->
        <div class="stat-card">
          <div class="stat-card-header">
            <span class="stat-label">Attention Needed</span>
            <div class="stat-icon-wrapper" :class="{ 'stat-icon-alert': overdueTasks.length > 0 }">
              <AlertCircle :size="18" />
            </div>
          </div>
          <div class="stat-value" :class="{ 'text-danger': overdueTasks.length > 0 }">
            {{ overdueTasks.length }}
          </div>
          <div class="stat-helper">
            <span v-if="overdueTasks.length > 0" class="text-danger font-medium">
              {{ overdueTasks.length }} past deadline
            </span>
            <span v-else class="text-success font-medium">All deadlines on track</span>
          </div>
        </div>
      </div>

      <!-- Main Workspace Sections (2 columns) -->
      <div class="dashboard-columns">
        <!-- Left: Project Status Overview -->
        <div class="dashboard-section card">
          <div class="card-header">
            <div>
              <h2 class="section-title">Active Projects</h2>
              <p class="text-muted" style="font-size: 0.75rem;">Status and task volumes</p>
            </div>
            <router-link to="/projects" class="btn btn-ghost btn-xs">
              View All <ChevronRight :size="14" />
            </router-link>
          </div>

          <div class="card-body" style="padding: 0;">
            <div v-if="projects.length === 0" class="empty-state" style="border: none;">
              <FolderKanban :size="32" class="text-muted mb-2" />
              <p class="empty-state-title">No projects initialized</p>
              <p class="empty-state-desc">Create your first project to begin tracking workstreams.</p>
              <router-link to="/projects" class="btn btn-primary btn-sm">Create Project</router-link>
            </div>

            <div v-else class="project-list">
              <div
                v-for="proj in projects.slice(0, 5)"
                :key="proj.id"
                class="project-row"
                @click="$router.push(`/projects/${proj.id}`)"
              >
                <div class="project-row-main">
                  <div class="project-row-title">{{ proj.name }}</div>
                  <div class="project-row-meta">
                    <span v-if="proj.deadline">
                      📅 {{ formatDate(proj.deadline) }}
                    </span>
                    <span v-else>No deadline set</span>
                    <span>·</span>
                    <span>{{ proj.tasks_count || 0 }} tasks</span>
                  </div>
                </div>

                <div class="project-row-status">
                  <StatusBadge :status="proj.status" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Urgent & High Priority Items -->
        <div class="dashboard-section card">
          <div class="card-header">
            <div>
              <h2 class="section-title">Critical & Priority Items</h2>
              <p class="text-muted" style="font-size: 0.75rem;">Immediate tasks requiring supervision</p>
            </div>
            <router-link to="/tasks" class="btn btn-ghost btn-xs">
              All Tasks <ChevronRight :size="14" />
            </router-link>
          </div>

          <div class="card-body" style="padding: 0;">
            <div v-if="criticalAndUrgentTasks.length === 0" class="empty-state" style="border: none;">
              <CheckCircle2 :size="32" class="text-muted mb-2" />
              <p class="empty-state-title">Clear Horizon</p>
              <p class="empty-state-desc">No overdue or high priority items pending at this time.</p>
            </div>

            <div v-else class="task-list">
              <div
                v-for="task in criticalAndUrgentTasks"
                :key="task.id"
                class="task-row"
                @click="$router.push(`/projects/${task.project_id}`)"
              >
                <div class="task-row-content">
                  <div class="task-row-title-bar">
                    <span class="task-row-title">{{ task.title }}</span>
                    <PriorityBadge :priority="task.priority" />
                  </div>

                  <div class="task-row-meta">
                    <span class="task-project-tag">
                      📁 {{ task.project?.name || 'Project' }}
                    </span>
                    <span v-if="task.assignee" class="task-assignee-tag">
                      👤 {{ task.assignee.name }}
                    </span>
                    <span
                      v-if="task.deadline"
                      :class="['task-deadline-tag', { 'deadline-overdue': isOverdue(task.deadline, task.status) }]"
                    >
                      🕒 {{ isOverdue(task.deadline, task.status) ? 'Overdue: ' : 'Due: ' }}{{ formatDate(task.deadline) }}
                    </span>
                  </div>
                </div>

                <div class="task-row-action">
                  <StatusBadge :status="task.status" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import StatusBadge from '../components/StatusBadge.vue'
import PriorityBadge from '../components/PriorityBadge.vue'
import {
  FolderKanban,
  FolderPlus,
  CheckCircle2,
  Activity,
  AlertCircle,
  ChevronRight
} from 'lucide-vue-next'

const projects = ref([])
const tasks = ref([])
const loading = ref(true)

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const [projRes, taskRes] = await Promise.all([
      api.get('/projects'),
      api.get('/tasks')
    ])
    projects.value = projRes.data.data || []
    tasks.value = taskRes.data.data || []
  } catch (err) {
    console.error('Failed to load dashboard metrics', err)
  } finally {
    loading.value = false
  }
}

// Executive Metric Computations
const totalProjects = computed(() => projects.value.length)

const activeProjects = computed(() => {
  return projects.value.filter((p) => p.status === 'active').length
})

const completedProjects = computed(() => {
  return projects.value.filter((p) => p.status === 'completed').length
})

const totalTasks = computed(() => tasks.value.length)

const doneTasks = computed(() => {
  return tasks.value.filter((t) => t.status === 'done').length
})

const taskCompletionRate = computed(() => {
  if (totalTasks.value === 0) return 0
  return Math.round((doneTasks.value / totalTasks.value) * 100)
})

const inProgressTasks = computed(() => {
  return tasks.value.filter((t) => t.status === 'in_progress').length
})

const reviewTasks = computed(() => {
  return tasks.value.filter((t) => t.status === 'review').length
})

const inProgressAndReviewCount = computed(() => {
  return inProgressTasks.value + reviewTasks.value
})

const isOverdue = (deadline, status) => {
  if (!deadline || status === 'done') return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const d = new Date(deadline)
  return d < today
}

const overdueTasks = computed(() => {
  return tasks.value.filter((t) => isOverdue(t.deadline, t.status))
})

const criticalAndUrgentTasks = computed(() => {
  // Combine overdue tasks and critical/high priority pending tasks
  const pending = tasks.value.filter((t) => t.status !== 'done')
  const prioritized = pending.filter(
    (t) => isOverdue(t.deadline, t.status) || t.priority === 'critical' || t.priority === 'high'
  )
  return prioritized.slice(0, 5)
})

const formatDate = (val) => {
  if (!val) return ''
  const dateStr = val.split('T')[0]
  const [year, month, day] = dateStr.split('-')
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  return `${day} ${months[parseInt(month, 10) - 1]} ${year}`
}

onMounted(fetchDashboardData)
</script>

<style scoped>
.page-header-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--space-4);
  flex-wrap: wrap;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: var(--space-2);
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

.text-success { color: var(--success-dot); }
.text-danger { color: var(--danger-dot); }

.stat-icon-alert {
  background-color: var(--danger-bg);
  color: var(--danger-dot);
}

.dashboard-columns {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

@media (min-width: 1024px) {
  .dashboard-columns {
    grid-template-columns: 1fr 1fr;
  }
}

.project-list, .task-list {
  display: flex;
  flex-direction: column;
}

.project-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem var(--space-5);
  border-bottom: 1px solid var(--border-subtle);
  cursor: pointer;
  transition: background-color 0.12s ease;
}

.project-row:last-child {
  border-bottom: none;
}

.project-row:hover {
  background-color: var(--bg-surface-hover);
}

.project-row-main {
  flex: 1;
  min-width: 0;
}

.project-row-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.project-row-meta {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-top: 2px;
}

.task-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  padding: 0.875rem var(--space-5);
  border-bottom: 1px solid var(--border-subtle);
  cursor: pointer;
  transition: background-color 0.12s ease;
}

.task-row:last-child {
  border-bottom: none;
}

.task-row:hover {
  background-color: var(--bg-surface-hover);
}

.task-row-content {
  flex: 1;
  min-width: 0;
}

.task-row-title-bar {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.task-row-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-row-meta {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-top: 4px;
  flex-wrap: wrap;
}

.task-project-tag, .task-assignee-tag {
  color: var(--text-secondary);
}

.task-deadline-tag {
  color: var(--text-muted);
}

.deadline-overdue {
  color: var(--danger-dot);
  font-weight: 600;
}
</style>
