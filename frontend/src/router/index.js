import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    redirect: '/projects',
  },
  {
    path: '/projects',
    name: 'Projects',
    component: () => import('../views/ProjectsView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/projects/:id',
    name: 'ProjectDetail',
    component: () => import('../views/ProjectDetailView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tasks',
    name: 'Tasks',
    component: () => import('../views/TasksView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/projects',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/projects')
  } else {
    next()
  }
})

export default router
