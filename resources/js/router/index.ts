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

  // Courses (Public)
  {
    path: '/courses',
    name: 'course-catalog',
    component: () => import('@/pages/courses/CourseCatalogPage.vue'),
    meta: { layout: 'default' },
  },
  {
    path: '/courses/:slug',
    name: 'course-detail',
    component: () => import('@/pages/courses/CourseDetailPage.vue'),
    meta: { layout: 'default' },
  },

  // Courses (Authenticated)
  {
    path: '/my-courses',
    name: 'my-courses',
    component: () => import('@/pages/courses/MyCoursesPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/learn/:enrollmentId',
    name: 'course-player',
    component: () => import('@/pages/courses/CoursePlayerPage.vue'),
    meta: { requiresAuth: true, layout: 'blank' },
  },

  // Subscriptions
  {
    path: '/plans',
    name: 'subscription-plans',
    component: () => import('@/pages/courses/SubscriptionPlansPage.vue'),
    meta: { layout: 'default' },
  },
  {
    path: '/my-subscription',
    name: 'my-subscription',
    component: () => import('@/pages/courses/MySubscriptionPage.vue'),
    meta: { requiresAuth: true },
  },

  // Marketing
  {
    path: '/marketing/campaigns',
    name: 'marketing-campaigns',
    component: () => import('@/pages/marketing/CampaignsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/campaigns/create',
    name: 'marketing-campaign-create',
    component: () => import('@/pages/marketing/CampaignFormPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/campaigns/:id/edit',
    name: 'marketing-campaign-edit',
    component: () => import('@/pages/marketing/CampaignFormPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/campaigns/:id/stats',
    name: 'marketing-campaign-stats',
    component: () => import('@/pages/marketing/CampaignStatsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/templates',
    name: 'marketing-templates',
    component: () => import('@/pages/marketing/TemplatesPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/templates/create',
    name: 'marketing-template-create',
    component: () => import('@/pages/marketing/TemplateFormPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/templates/:id/edit',
    name: 'marketing-template-edit',
    component: () => import('@/pages/marketing/TemplateFormPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/lists',
    name: 'marketing-lists',
    component: () => import('@/pages/marketing/ListsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/lists/create',
    name: 'marketing-list-create',
    component: () => import('@/pages/marketing/ListFormPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/lists/:id',
    name: 'marketing-list-detail',
    component: () => import('@/pages/marketing/ListDetailPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/lists/:id/edit',
    name: 'marketing-list-edit',
    component: () => import('@/pages/marketing/ListFormPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/marketing/unsubscribes',
    name: 'marketing-unsubscribes',
    component: () => import('@/pages/marketing/UnsubscribesPage.vue'),
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
