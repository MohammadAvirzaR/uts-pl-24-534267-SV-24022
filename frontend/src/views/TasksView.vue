<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">All Tasks</h2>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading tasks...</div>

    <div v-else-if="tasks.length === 0" class="text-center py-8 text-gray-500">
      <p class="text-lg">No tasks found</p>
      <p class="text-sm mt-2">Create tasks from inside a project</p>
    </div>

    <div v-else>
      <table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
            <th class="p-3 text-sm font-semibold text-gray-600">Title</th>
            <th class="p-3 text-sm font-semibold text-gray-600">Project</th>
            <th class="p-3 text-sm font-semibold text-gray-600">Status</th>
            <th class="p-3 text-sm font-semibold text-gray-600">Priority</th>
            <th class="p-3 text-sm font-semibold text-gray-600">Assignee</th>
            <th class="p-3 text-sm font-semibold text-gray-600">Deadline</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in tasks" :key="task.id" style="border-bottom: 1px solid #f3f4f6;" class="cursor-pointer" @click="$router.push(`/projects/${task.project_id}`)">
            <td class="p-3 font-medium">{{ task.title }}</td>
            <td class="p-3 text-sm text-gray-500">{{ task.project?.name || '-' }}</td>
            <td class="p-3">
              <span :class="statusBadgeClass(task.status)" class="badge">{{ task.status }}</span>
            </td>
            <td class="p-3">
              <span :class="priorityBadgeClass(task.priority)" class="badge">{{ task.priority }}</span>
            </td>
            <td class="p-3 text-sm text-gray-500">{{ task.assignee?.name || 'Unassigned' }}</td>
            <td class="p-3 text-sm text-gray-400">{{ task.deadline || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const tasks = ref([])
const loading = ref(true)

const fetchTasks = async () => {
  loading.value = true
  try {
    const res = await api.get('/tasks')
    tasks.value = res.data.data
  } catch (e) {
    console.error('Failed to fetch tasks', e)
  } finally {
    loading.value = false
  }
}

const statusBadgeClass = (status) => {
  const map = { todo: 'bg-blue-100 text-blue-700', in_progress: 'bg-yellow-100 text-yellow-700', review: 'bg-purple-100 text-purple-700', done: 'bg-green-100 text-green-700' }
  return map[status] || 'bg-gray-100 text-gray-700'
}

const priorityBadgeClass = (priority) => {
  const map = { low: 'bg-green-100 text-green-700', medium: 'bg-blue-100 text-blue-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }
  return map[priority] || 'bg-gray-100 text-gray-700'
}

onMounted(fetchTasks)
</script>
