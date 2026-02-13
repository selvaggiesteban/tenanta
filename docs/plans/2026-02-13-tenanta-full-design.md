# Tenanta - Documento de Diseño Completo

**Fecha:** 2026-02-13
**Estado:** APROBADO
**Versión:** 1.0

---

## 1. Resumen Ejecutivo

Tenanta es una plataforma SaaS multi-tenant que combina:
- **CRM** (crm.lanuscomputacion.com): Gestión de clientes, proyectos, time tracking
- **Chat AI** (chat.lanuscomputacion.com): Asistente con herramientas que interactúa con el CRM

### Decisiones Clave Aprobadas

| Aspecto | Decisión |
|---------|----------|
| Arquitectura | Monolito Laravel + Vue SPA |
| Código base | Migrar tenanta_old |
| Multi-tenancy | Single DB con tenant_id |
| Real-time | WebSockets (Laravel Reverb) |
| AI Provider | Configurable (Claude, GPT, Gemini) |
| Pagos | Estructura para MercadoPago + PayPal |
| Hosting | Hostinger VPS KVM 2+ |

---

## 2. Arquitectura Técnica

### 2.1 Stack Tecnológico

```
FRONTEND
├── Vue 3.4+ (Composition API)
├── Vuetify 3 (Sneat Template)
├── TypeScript
├── Pinia (State Management)
├── Vue Router
└── Vite

BACKEND
├── Laravel 11
├── PHP 8.3+
├── MySQL 8.0
├── Redis (Cache + Queues)
├── Laravel Reverb (WebSockets)
└── JWT Authentication

AI LAYER
├── Anthropic Claude API
├── OpenAI GPT API
└── Google Gemini API

INFRASTRUCTURE
├── Hostinger VPS (Ubuntu 22.04)
├── Nginx
├── Supervisor
├── Let's Encrypt SSL
└── Git-based deployments
```

### 2.2 Estructura de Directorios

```
tenanta/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── Auth/
│   │   │       │   ├── LoginController.php
│   │   │       │   ├── RegisterController.php
│   │   │       │   └── PasswordResetController.php
│   │   │       ├── CRM/
│   │   │       │   ├── ClientController.php
│   │   │       │   ├── ContactController.php
│   │   │       │   ├── LeadController.php
│   │   │       │   ├── QuoteController.php
│   │   │       │   └── PipelineController.php
│   │   │       ├── Operations/
│   │   │       │   ├── ProjectController.php
│   │   │       │   └── TaskController.php
│   │   │       ├── Tracking/
│   │   │       │   ├── TimerController.php
│   │   │       │   ├── TimeEntryController.php
│   │   │       │   └── OvertimeController.php
│   │   │       ├── Support/
│   │   │       │   ├── TicketController.php
│   │   │       │   └── KnowledgeBaseController.php
│   │   │       ├── Chat/
│   │   │       │   ├── SessionController.php
│   │   │       │   ├── MessageController.php
│   │   │       │   └── ToolController.php
│   │   │       ├── Dashboard/
│   │   │       │   ├── FinancialController.php
│   │   │       │   ├── SalesController.php
│   │   │       │   ├── MarketingController.php
│   │   │       │   ├── OperationalController.php
│   │   │       │   ├── TeamPerformanceController.php
│   │   │       │   └── ExecutiveController.php
│   │   │       └── Admin/
│   │   │           ├── TenantController.php
│   │   │           ├── PlanController.php
│   │   │           └── SuperAdminController.php
│   │   ├── Middleware/
│   │   │   ├── TenantMiddleware.php
│   │   │   ├── SuperAdminMiddleware.php
│   │   │   └── JwtMiddleware.php
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   │   ├── Tenant.php
│   │   ├── User.php
│   │   ├── Team.php
│   │   ├── Client.php
│   │   ├── Contact.php
│   │   ├── Lead.php
│   │   ├── Quote.php
│   │   ├── QuoteItem.php
│   │   ├── Pipeline.php
│   │   ├── PipelineStage.php
│   │   ├── Project.php
│   │   ├── Task.php
│   │   ├── TimeEntry.php
│   │   ├── Timer.php
│   │   ├── Ticket.php
│   │   ├── TicketReply.php
│   │   ├── KbCategory.php
│   │   ├── KbArticle.php
│   │   ├── ChatSession.php
│   │   ├── ChatMessage.php
│   │   ├── Plan.php
│   │   ├── Subscription.php
│   │   └── AuditLog.php
│   ├── Services/
│   │   ├── AI/
│   │   │   ├── AIProviderInterface.php
│   │   │   ├── AIProviderFactory.php
│   │   │   ├── ClaudeProvider.php
│   │   │   ├── OpenAIProvider.php
│   │   │   └── GeminiProvider.php
│   │   ├── Tools/
│   │   │   ├── ToolInterface.php
│   │   │   ├── ToolRegistry.php
│   │   │   ├── CRM/
│   │   │   │   ├── SearchClientsTool.php
│   │   │   │   ├── CreateTaskTool.php
│   │   │   │   └── ListTasksTool.php
│   │   │   ├── External/
│   │   │   │   ├── SendEmailTool.php
│   │   │   │   ├── CalendarTool.php
│   │   │   │   └── WebSearchTool.php
│   │   │   └── System/
│   │   │       ├── RunReportTool.php
│   │   │       └── ExportDataTool.php
│   │   └── Billing/
│   │       ├── PaymentProviderInterface.php
│   │       ├── MercadoPagoProvider.php
│   │       └── PayPalProvider.php
│   ├── Jobs/
│   │   ├── ProcessAIRequest.php
│   │   ├── SendNotification.php
│   │   └── GenerateReport.php
│   ├── Events/
│   │   ├── TaskUpdated.php
│   │   ├── TicketReplied.php
│   │   └── ChatMessageReceived.php
│   ├── Traits/
│   │   └── BelongsToTenant.php
│   └── Policies/
├── resources/
│   └── js/
│       ├── App.vue
│       ├── router/
│       │   └── index.ts
│       ├── stores/
│       │   ├── auth.store.ts
│       │   ├── crm.store.ts
│       │   ├── operations.store.ts
│       │   ├── tracking.store.ts
│       │   └── chat.store.ts
│       ├── pages/
│       │   ├── auth/
│       │   │   ├── Login.vue
│       │   │   └── Register.vue
│       │   ├── dashboards/
│       │   │   ├── Financial.vue
│       │   │   ├── Sales.vue
│       │   │   ├── Marketing.vue
│       │   │   ├── Operational.vue
│       │   │   ├── TeamPerformance.vue
│       │   │   └── Executive.vue
│       │   ├── crm/
│       │   │   ├── clients/
│       │   │   ├── leads/
│       │   │   ├── quotes/
│       │   │   └── pipeline/
│       │   ├── operations/
│       │   │   ├── projects/
│       │   │   └── tasks/
│       │   ├── tracking/
│       │   │   ├── TimeEntries.vue
│       │   │   └── PersonalDashboard.vue
│       │   ├── support/
│       │   │   ├── tickets/
│       │   │   └── knowledge-base/
│       │   ├── chat/
│       │   │   └── ChatInterface.vue
│       │   ├── team/
│       │   │   ├── Users.vue
│       │   │   └── Teams.vue
│       │   └── settings/
│       │       ├── Branding.vue
│       │       ├── Billing.vue
│       │       └── Profile.vue
│       ├── components/
│       │   ├── layout/
│       │   │   ├── NavBar.vue
│       │   │   ├── SideBar.vue
│       │   │   └── TimerWidget.vue
│       │   ├── common/
│       │   └── charts/
│       └── composables/
│           ├── useAuth.ts
│           ├── useWebSocket.ts
│           └── useTimer.ts
├── routes/
│   ├── api.php
│   ├── web.php
│   └── channels.php
├── database/
│   ├── migrations/
│   └── seeders/
├── config/
│   ├── ai.php
│   ├── tenancy.php
│   └── billing.php
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 3. Base de Datos

### 3.1 Diagrama ER Principal

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   tenants   │────<│    users    │────<│    teams    │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │
       │                   │                   │
       ▼                   ▼                   │
┌─────────────┐     ┌─────────────┐           │
│   clients   │────<│  contacts   │           │
└─────────────┘     └─────────────┘           │
       │                                       │
       ├──────────────────────────────────────┘
       │
       ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  projects   │────<│    tasks    │────<│time_entries │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│   quotes    │     │   tickets   │
└─────────────┘     └─────────────┘
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│quote_items  │     │ticket_replies│
└─────────────┘     └─────────────┘
```

### 3.2 Tablas Principales

#### tenants
```sql
CREATE TABLE tenants (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    logo_url VARCHAR(500),
    primary_color VARCHAR(7) DEFAULT '#673DE6',
    plan_id BIGINT UNSIGNED,
    trial_ends_at TIMESTAMP,
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500),
    role ENUM('super_admin', 'admin', 'manager', 'member') DEFAULT 'member',
    contracted_hours DECIMAL(4,2) DEFAULT 8.00,
    billable_rate DECIMAL(10,2) DEFAULT 0.00,
    timezone VARCHAR(50) DEFAULT 'America/Argentina/Buenos_Aires',
    email_verified_at TIMESTAMP,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    UNIQUE KEY unique_email_tenant (email, tenant_id)
);
```

#### clients
```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    vat_number VARCHAR(50),
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    notes TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status)
);
```

#### projects
```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('planning', 'in_progress', 'completed', 'on_hold', 'cancelled') DEFAULT 'planning',
    start_date DATE,
    due_date DATE,
    budget DECIMAL(12,2),
    is_billable BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_tenant_client (tenant_id, client_id),
    INDEX idx_status (status)
);
```

#### tasks
```sql
CREATE TABLE tasks (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    parent_task_id BIGINT UNSIGNED,
    assigned_to BIGINT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo', 'in_progress', 'pending_review', 'completed', 'cancelled') DEFAULT 'todo',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    due_date DATE,
    estimated_hours DECIMAL(6,2),
    is_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_project_status (project_id, status),
    INDEX idx_assigned (assigned_to)
);
```

#### time_entries
```sql
CREATE TABLE time_entries (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED,
    task_id BIGINT UNSIGNED,
    started_at TIMESTAMP NOT NULL,
    stopped_at TIMESTAMP,
    duration_minutes INT UNSIGNED,
    is_billable BOOLEAN DEFAULT FALSE,
    is_overtime BOOLEAN DEFAULT FALSE,
    overtime_authorized_by BIGINT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user_date (user_id, started_at),
    INDEX idx_project (project_id)
);
```

#### timers (estado activo de timer)
```sql
CREATE TABLE timers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED,
    task_id BIGINT UNSIGNED,
    started_at TIMESTAMP NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### chat_sessions
```sql
CREATE TABLE chat_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255),
    ai_provider VARCHAR(50) DEFAULT 'claude',
    model VARCHAR(100),
    context JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user (user_id)
);
```

#### chat_messages
```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    role ENUM('user', 'assistant', 'system', 'tool') NOT NULL,
    content TEXT NOT NULL,
    tool_calls JSON,
    tool_results JSON,
    tokens_used INT UNSIGNED,
    created_at TIMESTAMP,
    INDEX idx_session (session_id)
);
```

---

## 4. APIs Detalladas

### 4.1 Autenticación

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `POST /api/v1/auth/register` | POST | Registro de nuevo tenant + admin |
| `POST /api/v1/auth/login` | POST | Login, retorna JWT |
| `POST /api/v1/auth/logout` | POST | Logout, invalida token |
| `POST /api/v1/auth/refresh` | POST | Refresh JWT token |
| `POST /api/v1/auth/forgot-password` | POST | Solicitar reset password |
| `POST /api/v1/auth/reset-password` | POST | Resetear password |
| `GET /api/v1/auth/me` | GET | Usuario actual |

### 4.2 CRM

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `GET /api/v1/crm/clients` | GET | Listar clientes (paginado) |
| `POST /api/v1/crm/clients` | POST | Crear cliente |
| `GET /api/v1/crm/clients/{id}` | GET | Detalle cliente |
| `PUT /api/v1/crm/clients/{id}` | PUT | Actualizar cliente |
| `DELETE /api/v1/crm/clients/{id}` | DELETE | Soft delete cliente |
| `GET /api/v1/crm/clients/{id}/contacts` | GET | Contactos del cliente |
| `POST /api/v1/crm/clients/{id}/contacts` | POST | Crear contacto |
| `GET /api/v1/crm/leads` | GET | Listar leads |
| `POST /api/v1/crm/leads` | POST | Crear lead |
| `POST /api/v1/crm/leads/{id}/convert` | POST | Convertir lead a cliente |
| `GET /api/v1/crm/quotes` | GET | Listar presupuestos |
| `POST /api/v1/crm/quotes` | POST | Crear presupuesto |
| `PATCH /api/v1/crm/quotes/{id}/send` | PATCH | Marcar como enviado |
| `PATCH /api/v1/crm/quotes/{id}/accept` | PATCH | Marcar como aceptado |
| `GET /api/v1/crm/pipelines` | GET | Listar pipelines |
| `POST /api/v1/crm/pipelines/stages/reorder` | POST | Reordenar stages |
| `POST /api/v1/crm/import/csv` | POST | Importar contactos CSV |

### 4.3 Operations

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `GET /api/v1/operations/projects` | GET | Listar proyectos |
| `POST /api/v1/operations/projects` | POST | Crear proyecto |
| `GET /api/v1/operations/projects/{id}` | GET | Detalle proyecto |
| `PUT /api/v1/operations/projects/{id}` | PUT | Actualizar proyecto |
| `GET /api/v1/operations/projects/{id}/tasks` | GET | Tareas del proyecto |
| `POST /api/v1/operations/projects/{id}/tasks` | POST | Crear tarea |
| `POST /api/v1/operations/projects/{id}/members` | POST | Asignar miembros |
| `GET /api/v1/operations/tasks/{id}` | GET | Detalle tarea |
| `PUT /api/v1/operations/tasks/{id}` | PUT | Actualizar tarea |
| `PATCH /api/v1/operations/tasks/{id}/submit` | PATCH | Enviar a revisión |
| `PATCH /api/v1/operations/tasks/{id}/approve` | PATCH | Aprobar tarea |
| `PATCH /api/v1/operations/tasks/{id}/reject` | PATCH | Rechazar tarea |

### 4.4 Time Tracking

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `GET /api/v1/tracking/timer` | GET | Timer actual del usuario |
| `POST /api/v1/tracking/timer/start` | POST | Iniciar timer |
| `POST /api/v1/tracking/timer/stop` | POST | Detener timer |
| `POST /api/v1/tracking/timer/cancel` | POST | Cancelar timer |
| `GET /api/v1/tracking/entries` | GET | Listar time entries |
| `POST /api/v1/tracking/overtime/authorize` | POST | Autorizar horas extras |
| `GET /api/v1/tracking/dashboard/personal` | GET | Dashboard personal |
| `GET /api/v1/tracking/dashboard/team` | GET | Dashboard equipo |

### 4.5 Chat AI

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `GET /api/v1/chat/sessions` | GET | Listar sesiones |
| `POST /api/v1/chat/sessions` | POST | Crear sesión |
| `GET /api/v1/chat/sessions/{id}` | GET | Obtener sesión con mensajes |
| `DELETE /api/v1/chat/sessions/{id}` | DELETE | Eliminar sesión |
| `POST /api/v1/chat/sessions/{id}/messages` | POST | Enviar mensaje |
| `POST /api/v1/chat/sessions/{id}/stream` | POST | Mensaje con streaming |
| `GET /api/v1/chat/tools` | GET | Listar herramientas disponibles |
| `POST /api/v1/chat/upload` | POST | Subir archivo |

### 4.6 Dashboards

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `GET /api/v1/dashboards/financial` | GET | Dashboard financiero |
| `GET /api/v1/dashboards/sales` | GET | Dashboard ventas |
| `GET /api/v1/dashboards/marketing` | GET | Dashboard marketing |
| `GET /api/v1/dashboards/operational` | GET | Dashboard operacional |
| `GET /api/v1/dashboards/team` | GET | Dashboard equipo |
| `GET /api/v1/dashboards/executive` | GET | Dashboard ejecutivo |

---

## 5. Sistema de Herramientas AI

### 5.1 Herramientas CRM

```php
// SearchClientsTool
[
    'name' => 'search_clients',
    'description' => 'Buscar clientes por nombre, email o VAT',
    'parameters' => [
        'query' => ['type' => 'string', 'required' => true],
        'status' => ['type' => 'string', 'enum' => ['active', 'inactive', 'all']],
        'limit' => ['type' => 'integer', 'default' => 10]
    ],
    'permissions' => ['crm.clients.view']
]

// CreateTaskTool
[
    'name' => 'create_task',
    'description' => 'Crear una nueva tarea en un proyecto',
    'parameters' => [
        'project_id' => ['type' => 'integer', 'required' => true],
        'title' => ['type' => 'string', 'required' => true],
        'description' => ['type' => 'string'],
        'assigned_to' => ['type' => 'integer'],
        'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'urgent']],
        'due_date' => ['type' => 'string', 'format' => 'date']
    ],
    'permissions' => ['operations.tasks.create']
]

// ListMyTasksTool
[
    'name' => 'list_my_tasks',
    'description' => 'Listar mis tareas asignadas',
    'parameters' => [
        'status' => ['type' => 'string', 'enum' => ['todo', 'in_progress', 'pending_review', 'all']],
        'project_id' => ['type' => 'integer']
    ],
    'permissions' => ['operations.tasks.view']
]
```

### 5.2 Herramientas Externas

```php
// SendEmailTool
[
    'name' => 'send_email',
    'description' => 'Enviar un email',
    'parameters' => [
        'to' => ['type' => 'string', 'required' => true],
        'subject' => ['type' => 'string', 'required' => true],
        'body' => ['type' => 'string', 'required' => true],
        'cc' => ['type' => 'array']
    ],
    'permissions' => ['email.send'],
    'requires_confirmation' => true
]

// CalendarTool
[
    'name' => 'get_calendar_events',
    'description' => 'Obtener eventos del calendario',
    'parameters' => [
        'start_date' => ['type' => 'string', 'format' => 'date', 'required' => true],
        'end_date' => ['type' => 'string', 'format' => 'date', 'required' => true]
    ],
    'permissions' => ['calendar.view']
]

// WebSearchTool
[
    'name' => 'search_web',
    'description' => 'Buscar información en la web',
    'parameters' => [
        'query' => ['type' => 'string', 'required' => true],
        'num_results' => ['type' => 'integer', 'default' => 5]
    ],
    'permissions' => ['tools.web_search']
]
```

### 5.3 Herramientas Sistema

```php
// RunReportTool
[
    'name' => 'run_report',
    'description' => 'Generar un reporte',
    'parameters' => [
        'type' => ['type' => 'string', 'enum' => ['time_tracking', 'sales', 'projects'], 'required' => true],
        'date_from' => ['type' => 'string', 'format' => 'date'],
        'date_to' => ['type' => 'string', 'format' => 'date'],
        'filters' => ['type' => 'object']
    ],
    'permissions' => ['reports.generate']
]

// ExportDataTool
[
    'name' => 'export_data',
    'description' => 'Exportar datos a CSV o PDF',
    'parameters' => [
        'entity' => ['type' => 'string', 'enum' => ['clients', 'projects', 'tasks', 'time_entries'], 'required' => true],
        'format' => ['type' => 'string', 'enum' => ['csv', 'pdf'], 'required' => true],
        'filters' => ['type' => 'object']
    ],
    'permissions' => ['data.export']
]
```

---

## 6. Roles y Permisos

### 6.1 Roles Predefinidos

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **super_admin** | Administrador global de la plataforma | Todo |
| **admin** | Administrador del tenant | Todo dentro del tenant |
| **manager** | Gerente de proyectos/equipos | CRM, Projects, Team view, Reports |
| **member** | Miembro del equipo | Solo sus tareas y time tracking |

### 6.2 Permisos por Módulo

```php
// CRM
'crm.clients.view', 'crm.clients.create', 'crm.clients.update', 'crm.clients.delete'
'crm.leads.view', 'crm.leads.create', 'crm.leads.convert'
'crm.quotes.view', 'crm.quotes.create', 'crm.quotes.send'

// Operations
'operations.projects.view', 'operations.projects.create', 'operations.projects.manage'
'operations.tasks.view', 'operations.tasks.create', 'operations.tasks.approve'

// Time Tracking
'tracking.timer.use', 'tracking.entries.view_own', 'tracking.entries.view_team'
'tracking.overtime.authorize'

// Support
'support.tickets.view', 'support.tickets.create', 'support.tickets.manage'
'support.kb.view', 'support.kb.manage'

// Admin
'admin.users.manage', 'admin.teams.manage', 'admin.settings.manage'
'admin.billing.manage', 'admin.impersonate'
```

---

## 7. Fases de Implementación

### FASE 1: Fundación (Backend Core)

**Objetivo:** API funcional con autenticación y multi-tenancy

**Tareas:**
1. Setup Laravel 11 limpio con estructura de directorios
2. Migrar trait BelongsToTenant y middleware de tenanta_old
3. Implementar JWT authentication
4. Implementar RBAC con Spatie Permissions
5. Crear migraciones: tenants, users, teams
6. APIs: Auth, Tenants, Users, Teams
7. Tests unitarios para auth y tenancy
8. Setup Redis y Queue workers

**Entregables:**
- [ ] Login/Register funcional
- [ ] Multi-tenancy con global scopes
- [ ] CRUD de usuarios y equipos
- [ ] Tests pasando

### FASE 2: CRM Completo

**Objetivo:** Módulo CRM backend completo

**Tareas:**
1. Migraciones: clients, contacts, leads, quotes, pipelines
2. Migrar controllers de tenanta_old: Client, Contact, Lead, Quote, Pipeline
3. Implementar conversión Lead → Client
4. Implementar import CSV con detección de duplicados
5. APIs de Pipeline con reordenamiento
6. Form Requests y Resources
7. Tests de integración CRM

**Entregables:**
- [ ] CRUD completo de clientes y contactos
- [ ] Gestión de leads con conversión
- [ ] Presupuestos con items
- [ ] Pipeline Kanban funcional
- [ ] Import CSV

### FASE 3: Operations + Time Tracking

**Objetivo:** Gestión de proyectos y seguimiento de tiempo

**Tareas:**
1. Migraciones: projects, tasks, time_entries, timers
2. Migrar y mejorar ProjectController, TaskController
3. Implementar workflow de aprobación de tareas
4. Timer server-side con persistencia
5. Sistema de horas extras con autorización
6. APIs de dashboards personal y equipo
7. Notificaciones para flujos de aprobación

**Entregables:**
- [ ] CRUD proyectos y tareas
- [ ] Timer que sobrevive cierre de browser
- [ ] Workflow submit → review → approve/reject
- [ ] Autorización de horas extras
- [ ] Dashboard de productividad

### FASE 4: Frontend Vue + Dashboards

**Objetivo:** SPA funcional con todas las vistas

**Tareas:**
1. Setup Vue 3 + Vuetify 3 basado en Sneat
2. Configurar Vue Router con guards de auth
3. Implementar stores Pinia para cada módulo
4. Vistas de autenticación
5. Layout principal con navbar y sidebar
6. Timer widget global
7. Vistas CRM: clientes, leads, quotes, pipeline
8. Vistas Operations: proyectos, tareas
9. Vistas Time Tracking
10. 6 Dashboards con gráficos
11. Configurar Laravel Reverb para WebSockets
12. Integrar real-time updates

**Entregables:**
- [ ] Login/Register UI
- [ ] Dashboard landing
- [ ] CRM completo con Kanban
- [ ] Gestión de proyectos y tareas
- [ ] Time tracking con timer global
- [ ] 6 dashboards funcionales
- [ ] Real-time updates

### FASE 5: Chat AI + Production

**Objetivo:** Chat con herramientas y deployment

**Tareas:**
1. Migraciones: chat_sessions, chat_messages
2. Implementar AIProviderInterface y providers
3. Implementar Tool system con registry
4. Todas las herramientas definidas
5. UI del chat con markdown y streaming
6. Sistema de tickets y knowledge base
7. Estructura de pagos (MercadoPago, PayPal)
8. Setup VPS Hostinger
9. Configurar Nginx, SSL, Supervisor
10. Script de deployment automatizado
11. Monitoreo y logs

**Entregables:**
- [ ] Chat AI funcional con tools
- [ ] Tickets y KB
- [ ] Sistema de pagos preparado
- [ ] Deployment automatizado
- [ ] Sistema en producción

---

## 8. Configuración de Deployment

### 8.1 Requisitos de Servidor

- **VPS:** Hostinger KVM 2+
- **OS:** Ubuntu 22.04 LTS
- **CPU:** 2+ vCPU
- **RAM:** 8+ GB
- **Disco:** 100+ GB SSD

### 8.2 Software Stack

- PHP 8.3-fpm
- MySQL 8.0
- Redis 7
- Nginx
- Supervisor
- Node.js 20 LTS
- Certbot (Let's Encrypt)

### 8.3 Subdominios

| Subdominio | Propósito |
|------------|-----------|
| crm.lanuscomputacion.com | Aplicación CRM principal |
| chat.lanuscomputacion.com | Interfaz de Chat AI |
| ws.lanuscomputacion.com | WebSocket server |
| api.lanuscomputacion.com | API (opcional, puede usar crm/api) |

### 8.4 Variables de Entorno Críticas

```env
# App
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_DATABASE=tenanta

# Cache & Queues
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# WebSockets
BROADCAST_DRIVER=reverb

# AI (configurar según proveedor activo)
AI_PROVIDER=claude
ANTHROPIC_API_KEY=sk-ant-...

# Payments (cuando estén listos)
MERCADOPAGO_ACCESS_TOKEN=...
```

---

## 9. Próximos Pasos

1. **Crear repositorio Git** para el proyecto unificado
2. **Comenzar FASE 1** según el plan detallado
3. **Configurar CI/CD** básico (GitHub Actions)
4. **Contratar VPS Hostinger** cuando esté listo para staging

---

*Documento generado durante sesión de brainstorming - 2026-02-13*
