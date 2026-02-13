import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // Auth routes (blank layout)
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/LoginPage.vue'),
    meta: { layout: 'blank', requiresGuest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/RegisterPage.vue'),
    meta: { layout: 'blank', requiresGuest: true },
  },

  // App routes (default layout)
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/pages/DashboardPage.vue'),
    meta: { requiresAuth: true },
  },

  // CRM
  {
    path: '/crm/clients',
    name: 'clients',
    component: () => import('@/pages/crm/ClientsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/crm/clients/:id',
    name: 'client-detail',
    component: () => import('@/pages/crm/ClientDetailPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/crm/leads',
    name: 'leads',
    component: () => import('@/pages/crm/LeadsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/crm/kanban',
    name: 'kanban',
    component: () => import('@/pages/crm/KanbanPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/crm/quotes',
    name: 'quotes',
    component: () => import('@/pages/crm/QuotesPage.vue'),
    meta: { requiresAuth: true },
  },

  // Operations
  {
    path: '/projects',
    name: 'projects',
    component: () => import('@/pages/operations/ProjectsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/projects/:id',
    name: 'project-detail',
    component: () => import('@/pages/operations/ProjectDetailPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tasks',
    name: 'tasks',
    component: () => import('@/pages/operations/TasksPage.vue'),
    meta: { requiresAuth: true },
  },

  // Time Tracking
  {
    path: '/time',
    name: 'time-tracking',
    component: () => import('@/pages/tracking/TimeTrackingPage.vue'),
    meta: { requiresAuth: true },
  },

  // Chat AI
  {
    path: '/chat',
    name: 'chat',
    component: () => import('@/pages/chat/ChatPage.vue'),
    meta: { requiresAuth: true },
  },

  // Support
  {
    path: '/support/tickets',
    name: 'tickets',
    component: () => import('@/pages/support/TicketsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/support/kb',
    name: 'knowledge-base',
    component: () => import('@/pages/support/KnowledgeBasePage.vue'),
    meta: { requiresAuth: true },
  },

  // Settings
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/pages/SettingsPage.vue'),
    meta: { requiresAuth: true },
  },

  // 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFoundPage.vue'),
    meta: { layout: 'blank' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
