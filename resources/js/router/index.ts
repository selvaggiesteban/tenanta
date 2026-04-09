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

  // Public Frontend
  {
    path: '/',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@/pages/public/LandingPage.vue')
      },
      {
        path: 'l/:slug',
        name: 'landing-page',
        component: () => import('@/pages/public/LandingPage.vue')
      },
      {
        path: 'pricing',
        name: 'pricing',
        component: () => import('@/pages/public/PricingPage.vue')
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('@/pages/public/ContactPage.vue')
      }
    ]
  },
  {
    path: '/app',
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
  {
    path: '/test/:id',
    component: () => import('@/pages/courses/TestPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/test/:id/result/:attemptId',
    component: () => import('@/pages/courses/TestResultPage.vue'),
    meta: { requiresAuth: true }
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

  // CRM & Intelligence Dashboards
  {
    path: '/finanzas/dizteku',
    name: 'dashboard-financial',
    component: () => import('@/pages/crm/DiztekuDashboard.vue'),
    meta: { requiresAuth: true, title: 'Dizteku - Salud Financiera | Tenanta', description: 'Visualiza la rentabilidad real de tu negocio.' }
  },
  {
    path: '/crm/piblo',
    name: 'dashboard-sales',
    component: () => import('@/pages/crm/PibloDashboard.vue'),
    meta: { requiresAuth: true, title: 'Piblo - Ventas | Tenanta', description: 'Pipeline y cumplimiento de metas comerciales.' }
  },
  {
    path: '/crm/cmo',
    name: 'dashboard-marketing',
    component: () => import('@/pages/crm/CMODashboard.vue'),
    meta: { requiresAuth: true, title: 'CMO Intelligence | Tenanta', description: 'ROI y métricas avanzadas de marketing.' }
  },
  {
    path: '/productividad/mi-panel',
    name: 'dashboard-productivity',
    component: () => import('@/pages/operations/MyDeskTime.vue'),
    meta: { requiresAuth: true, title: 'Mi Productividad | Tenanta', description: 'Control de tiempo y eficiencia operativa.' }
  },
  {
    path: '/productividad/equipos',
    name: 'dashboard-teams',
    component: () => import('@/pages/crm/TeamsPerformanceDashboard.vue'),
    meta: { requiresAuth: true, title: 'Ranking de Equipos | Tenanta', description: 'Rendimiento y productividad por departamento.' }
  },
  {
    path: '/productividad/comparativo',
    name: 'dashboard-comparison',
    component: () => import('@/pages/crm/ReportsComparisonDashboard.vue'),
    meta: { requiresAuth: true, title: 'Comparativo de Informes | Tenanta', description: 'Análisis de crecimiento operativo mensual.' }
  },
  {
    path: '/crm/bi-exports',
    name: 'dashboard-bi',
    component: () => import('@/pages/crm/BIExportsDashboard.vue'),
    meta: { requiresAuth: true, title: 'Exportaciones BI | Tenanta', description: 'Generación de plantillas de datos externas.' }
  },
  {
    path: '/admin/importar',
    name: 'admin-import',
    component: () => import('@/pages/admin/TenantImportPage.vue'),
    meta: { requiresAuth: true, title: 'Importador Masivo | Tenanta', description: 'Carga dinámica de bases de datos externas.' }
  },
  {
    path: '/reseller/tablero',
    name: 'reseller-dashboard',
    component: () => import('@/pages/admin/ResellerDashboard.vue'),
    meta: { requiresAuth: true, title: 'Panel Distribuidor | Tenanta', description: 'Gestión de red marca blanca.' }
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

router.afterEach((to) => {
  const defaultTitle = 'Tenanta - Fábrica de Crecimiento Digital'
  const defaultDesc = 'Plataforma SaaS líder en automatización de presencia web, CRM y LMS para el mercado latinoamericano.'
  
  document.title = (to.meta.title as string) || defaultTitle
  
  const metaDescription = document.querySelector('meta[name="description"]')
  if (metaDescription) {
    metaDescription.setAttribute('content', (to.meta.description as string) || defaultDesc)
  }
})

export default router
