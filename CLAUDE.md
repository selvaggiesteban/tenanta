# Tenanta - Multi-Tenant SaaS Platform

## Project Overview

**Tenanta** es una plataforma SaaS multi-tenant que combina:
- **CRM** (crm.lanuscomputacion.com): Gestión de clientes, proyectos, time tracking, soporte
- **Chat AI** (chat.lanuscomputacion.com): Asistente inteligente con herramientas que interactúa con el CRM

### Stack Tecnológico
- **Backend:** Laravel 11 + PHP 8.3 + MySQL 8 + Redis
- **Frontend:** Vue 3 + Vuetify 3 + TypeScript (Sneat Template)
- **Real-time:** Laravel Reverb (WebSockets)
- **AI:** Multi-provider (Claude, GPT, Gemini)
- **Hosting:** Hostinger VPS

### Decisiones Arquitectónicas
- Arquitectura Monolito (Laravel + Vue SPA)
- Single DB multi-tenancy con `tenant_id` + global scopes
- JWT Authentication
- RBAC con Spatie Permissions

---

## Development Commands

```bash
# Full development environment (server, queue, logs, vite)
composer dev

# Frontend only
npm run dev          # Vite development server
npm run build        # Production build
npm run typecheck    # TypeScript checking
npm run lint         # ESLint with auto-fix

# Backend
php artisan serve    # Laravel server
php artisan queue:work redis  # Queue worker
php artisan reverb:start      # WebSocket server

# Testing
php artisan test     # Run all tests
npm run test         # Frontend tests
```

---

## Project Structure

```
tenanta/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── Auth/           # Login, Register, Password
│   │   ├── CRM/            # Clients, Contacts, Leads, Quotes, Pipeline
│   │   ├── Operations/     # Projects, Tasks
│   │   ├── Tracking/       # TimeEntries, Timers
│   │   ├── Support/        # Tickets, KnowledgeBase
│   │   ├── Chat/           # Sessions, Messages, Tools
│   │   ├── Dashboard/      # 6 dashboards
│   │   └── Admin/          # SuperAdmin, Tenants, Plans
│   ├── Models/             # ~25 modelos Eloquent
│   ├── Services/
│   │   ├── AI/             # Providers (Claude, OpenAI, Gemini)
│   │   ├── Tools/          # Herramientas del chat
│   │   └── Billing/        # MercadoPago, PayPal
│   ├── Jobs/               # Async jobs
│   └── Traits/BelongsToTenant.php
├── resources/js/           # Vue 3 SPA
│   ├── pages/
│   ├── stores/             # Pinia
│   └── composables/
├── docs/plans/             # Documentación de diseño
└── config/
    ├── ai.php              # Config proveedores AI
    └── tenancy.php         # Config multi-tenant
```

---

## Key Resources

### Documentation
- **Full Design:** `docs/plans/2026-02-13-tenanta-full-design.md`
- **Product Spec:** `plan/plan2/product-overview.md`
- **CRM Frontend Spec:** `crm.lanuscomputacion.com/frontend.md`
- **Chat Frontend Spec:** `chat.lanuscomputacion.com/frontend.md`

### Legacy Code (to migrate)
- **tenanta_old/**: 17 controllers implementados (migrar a nueva estructura)
- **sneat-vuetify-vuejs-laravel-admin-template-free-main/**: Template base

---

## Implementation Phases

| Fase | Objetivo | Estado |
|------|----------|--------|
| 1. Fundación | Backend core, auth, multi-tenancy | ✅ Completado |
| 2. CRM | Clients, Leads, Quotes, Pipeline | ✅ Completado |
| 3. Operations | Projects, Tasks, Time Tracking | ✅ Completado |
| 4. Frontend | Vue SPA, Dashboards, Real-time | ✅ Completado |
| 5. Chat AI | AI Tools, Multi-provider, Streaming | ✅ Completado |

### Implemented Components

**Backend (Laravel 11):**
- ✅ Multi-tenancy with BelongsToTenant trait and global scopes
- ✅ JWT Authentication (login, register, refresh)
- ✅ Team management
- ✅ Client CRUD with contacts
- ✅ Lead management with conversion to client
- ✅ Quote system with items and totals
- ✅ Pipeline/Stages for Kanban
- ✅ CSV Import with duplicate detection
- ✅ Project management with members
- ✅ Task workflow (pending → in_progress → review → approved)
- ✅ Time tracking with timer and entries
- ✅ AI Chat with multi-provider support (Claude, OpenAI, Gemini)
- ✅ Conversation/Message persistence
- ✅ AI Tools (search clients, search leads, create tasks, dashboard stats)
- ✅ Streaming responses with SSE

**Frontend (Vue 3 + Vuetify 3):**
- ✅ Auth pages (Login, Register)
- ✅ Dashboard with stats
- ✅ Clients list and detail
- ✅ Leads table and Kanban view
- ✅ Quotes list
- ✅ Projects list and detail
- ✅ Tasks list
- ✅ Time tracking with timer widget
- ✅ Chat AI with streaming, conversations sidebar, tool results display
- ✅ Settings page

---

## Code Style Rules

### TypeScript/JavaScript
- No semicolons
- 2-space indentation
- camelCase for variables and functions

### Vue Components
- PascalCase for component names in templates
- Use `@images` instead of `@/assets/images`
- Only mdi icons allowed (no tabler icons)

### Example
```vue
<template>
  <VCard>
    <VIcon icon="mdi-home" />
  </VCard>
</template>

<script setup lang="ts">
const userName = ref('')
</script>
```

### PHP/Laravel
- PSR-12 standard
- Type hints everywhere
- Return types on all methods
- Form Requests for validation
- API Resources for responses

---

## API Structure

### Base URL
- Production: `https://crm.lanuscomputacion.com/api/v1`
- Local: `http://localhost:8000/api/v1`

### Main Endpoints
```
/auth/*           # Authentication
/crm/*            # CRM module (clients, leads, quotes, pipelines)
/operations/*     # Projects and tasks
/tracking/*       # Time entries and timers
/support/*        # Tickets and knowledge base
/chat/*           # AI chat sessions
/dashboards/*     # Analytics dashboards
/admin/*          # Super admin functions
```

---

## Roles & Permissions

| Role | Access |
|------|--------|
| super_admin | Global platform admin |
| admin | Full tenant access |
| manager | CRM, Projects, Team view, Reports |
| member | Own tasks and time tracking only |

---

## Chat AI Tools

El chat puede ejecutar estas herramientas:

### CRM Tools
- `search_clients` - Buscar clientes por nombre, email, teléfono
- `search_leads` - Buscar leads por etapa, fuente
- `get_client_details` - Ver detalles completos de un cliente
- `get_lead_details` - Ver detalles completos de un lead
- `search_quotes` - Buscar cotizaciones

### Operations Tools
- `list_tasks` - Listar tareas con filtros
- `create_task` - Crear nuevas tareas

### Analytics Tools
- `get_dashboard_stats` - Obtener estadísticas del negocio

### AI Providers
- **Claude** (Anthropic) - Default provider
- **GPT-4** (OpenAI) - Alternative
- **Gemini** (Google) - Alternative

### Tool Configuration
Tools can be enabled/disabled in `config/ai.php`:
```php
'tools' => [
    'enabled' => true,
    'available' => [
        'search_clients',
        'search_leads',
        'get_client_details',
        'get_lead_details',
        'list_tasks',
        'create_task',
        'get_dashboard_stats',
        'search_quotes',
    ],
],
```

---

## Deployment (Hostinger VPS)

### Subdomains
- `crm.lanuscomputacion.com` - Main CRM app
- `chat.lanuscomputacion.com` - Chat interface
- `ws.lanuscomputacion.com` - WebSocket server

### Key Services
- Nginx (reverse proxy + static)
- PHP 8.3-FPM
- MySQL 8.0
- Redis (cache + queues)
- Supervisor (queue workers + reverb)

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://crm.lanuscomputacion.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenanta
DB_USERNAME=tenanta_user
DB_PASSWORD=secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

AI_PROVIDER=claude
ANTHROPIC_API_KEY=sk-ant-...
OPENAI_API_KEY=sk-...

BROADCAST_DRIVER=reverb
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Deployment Steps

```bash
# 1. Connect to VPS
ssh root@your-vps-ip

# 2. Install dependencies (Ubuntu 22.04)
apt update && apt upgrade -y
apt install nginx mysql-server redis-server supervisor -y

# 3. Install PHP 8.3
add-apt-repository ppa:ondrej/php
apt install php8.3-fpm php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip -y

# 4. Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 5. Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install nodejs -y

# 6. Clone project
cd /var/www
git clone your-repo.git tenanta
cd tenanta

# 7. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 8. Configure environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
# Edit .env with production values

# 9. Run migrations
php artisan migrate --force

# 10. Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 11. Configure Nginx (see below)
# 12. Configure Supervisor (see below)
# 13. Enable SSL with Certbot
apt install certbot python3-certbot-nginx -y
certbot --nginx -d crm.lanuscomputacion.com
```

### Nginx Configuration

Create `/etc/nginx/sites-available/tenanta`:

```nginx
server {
    listen 80;
    server_name crm.lanuscomputacion.com;
    root /var/www/tenanta/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Supervisor Configuration

Create `/etc/supervisor/conf.d/tenanta.conf`:

```ini
[program:tenanta-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tenanta/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/tenanta/storage/logs/queue.log
stopwaitsecs=3600

[program:tenanta-reverb]
command=php /var/www/tenanta/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/tenanta/storage/logs/reverb.log
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start all
```

---

## Getting Started

1. Clone repo and install dependencies:
```bash
composer install
npm install
```

2. Copy environment and generate keys:
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

3. Run migrations and seeders:
```bash
php artisan migrate --seed
```

4. Start development:
```bash
composer dev
```

---

## Important Notes

- All tenant-scoped models MUST use `BelongsToTenant` trait
- Never expose `tenant_id` in API responses
- Use Form Requests for all input validation
- Use API Resources for all JSON responses
- WebSocket events broadcast to tenant-specific channels
- AI tool execution is logged in audit trail
