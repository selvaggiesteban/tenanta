<div align="center">

# 🏢 Tenanta

### Multi-Tenant SaaS Platform for Business Management

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Vuetify](https://img.shields.io/badge/Vuetify-3-1867C0?style=for-the-badge&logo=vuetify&logoColor=white)](https://vuetifyjs.com)

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

**Tenanta** is a powerful multi-tenant SaaS platform that combines CRM, project management, time tracking, AI-powered chat assistant, and support ticketing into a single, elegant solution.

[Features](#-features) •
[Installation](#-installation) •
[Documentation](#-documentation) •
[Tech Stack](#-tech-stack) •
[Screenshots](#-screenshots) •
[Contributing](#-contributing)

</div>

---

## ✨ Features

### 🎯 CRM Module
- **Client Management** - Complete CRUD with contacts, notes, and activity history
- **Lead Pipeline** - Kanban-style board with drag-and-drop stage management
- **Quotes & Proposals** - Professional PDF generation with automatic calculations
- **CSV Import** - Bulk import with duplicate detection and field mapping

### 📊 Project Management
- **Projects** - Track projects with team members and role assignments
- **Tasks** - Full workflow system (pending → in_progress → review → approved)
- **Dependencies** - Task dependencies with circular reference prevention
- **Time Tracking** - Built-in timer with detailed time entries and reports

### 🤖 AI Chat Assistant
- **Multi-Provider Support** - Claude (Anthropic), GPT-4 (OpenAI), Gemini (Google)
- **Smart Tools** - Search clients, create tasks, get stats using natural language
- **Streaming Responses** - Real-time SSE streaming for instant feedback
- **Conversation History** - Persistent chat sessions with full context

### 🎫 Support System
- **Ticket Management** - Priority levels, SLA tracking, assignments
- **Knowledge Base** - Categorized articles with search and feedback
- **Email Notifications** - Automated emails for tickets, tasks, and quotes

### 📈 Analytics Dashboards
- **Overview** - Key metrics at a glance
- **Sales** - Revenue, pipeline value, conversion rates
- **Operations** - Project status, task completion trends
- **Team Performance** - Time logged, tasks completed by user
- **Support** - Ticket metrics, response times

### 🔐 Multi-Tenancy & Security
- **Single Database** - Efficient tenant isolation with global scopes
- **JWT Authentication** - Secure token-based authentication
- **Role-Based Access** - Super Admin, Admin, Manager, Member roles
- **Data Isolation** - Complete tenant data separation

---

## 🛠 Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 11.x | PHP Framework |
| **PHP** | 8.3+ | Server Language |
| **MySQL** | 8.0+ | Database |
| **Redis** | 7.x | Cache, Queues, Sessions |
| **Laravel Reverb** | Latest | WebSocket Server |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3.x | JavaScript Framework |
| **TypeScript** | 5.x | Type Safety |
| **Vuetify** | 3.x | Material Design Components |
| **Pinia** | 2.x | State Management |
| **Vite** | 5.x | Build Tool |

### AI Providers
| Provider | Models |
|----------|--------|
| **Anthropic** | Claude Sonnet 4, Claude Opus |
| **OpenAI** | GPT-4o, GPT-4 Turbo |
| **Google** | Gemini 1.5 Pro |

---

## 📦 Installation

### Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 20+
- MySQL 8.0+
- Redis 7.x

### Quick Start

```bash
# Clone the repository
git clone https://github.com/yourusername/tenanta.git
cd tenanta

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
composer dev
```

### Environment Configuration

```env
# Application
APP_NAME=Tenanta
APP_ENV=local
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenanta
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# AI Provider (choose one)
AI_PROVIDER=claude
ANTHROPIC_API_KEY=your-api-key

# WebSockets
REVERB_APP_ID=tenanta
REVERB_APP_KEY=tenanta-key
REVERB_APP_SECRET=tenanta-secret
```

---

## 🚀 Development

### Available Commands

```bash
# Start all services (server, queue, logs, vite)
composer dev

# Frontend only
npm run dev          # Development server
npm run build        # Production build
npm run typecheck    # TypeScript checking
npm run lint         # ESLint with auto-fix

# Backend
php artisan serve              # Laravel server
php artisan queue:work redis   # Queue worker
php artisan reverb:start       # WebSocket server

# Testing
php artisan test     # Run PHP tests
npm run test         # Run frontend tests
```

### Project Structure

```
tenanta/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── Auth/           # Authentication
│   │   ├── CRM/            # Clients, Leads, Quotes
│   │   ├── Operations/     # Projects, Tasks
│   │   ├── Tracking/       # Time entries
│   │   ├── Support/        # Tickets, Knowledge Base
│   │   ├── Chat/           # AI Chat
│   │   └── Dashboard/      # Analytics
│   ├── Models/             # Eloquent models
│   ├── Services/
│   │   └── AI/             # AI providers & tools
│   └── Events/             # WebSocket events
├── resources/
│   ├── js/
│   │   ├── pages/          # Vue pages
│   │   ├── stores/         # Pinia stores
│   │   ├── composables/    # Vue composables
│   │   └── layouts/        # App layouts
│   └── views/
│       ├── pdf/            # PDF templates
│       └── emails/         # Email templates
└── docs/
    └── plans/              # Documentation
```

---

## 📖 Documentation

### API Endpoints

#### Authentication
```
POST   /api/v1/auth/login          # Login
POST   /api/v1/auth/register       # Register tenant
GET    /api/v1/auth/me             # Current user
POST   /api/v1/auth/logout         # Logout
POST   /api/v1/auth/refresh        # Refresh token
```

#### CRM
```
GET    /api/v1/crm/clients         # List clients
POST   /api/v1/crm/clients         # Create client
GET    /api/v1/crm/leads           # List leads
PATCH  /api/v1/crm/leads/{id}/move-stage  # Move lead
GET    /api/v1/crm/quotes/{id}/pdf # Download quote PDF
```

#### Operations
```
GET    /api/v1/operations/projects # List projects
GET    /api/v1/operations/tasks    # List tasks
PATCH  /api/v1/operations/tasks/{id}/start   # Start task
PATCH  /api/v1/operations/tasks/{id}/submit  # Submit for review
```

#### Chat AI
```
GET    /api/v1/chat/conversations  # List conversations
POST   /api/v1/chat/conversations/{id}/stream  # Stream message
```

#### Dashboards
```
GET    /api/v1/dashboards/overview    # Overview stats
GET    /api/v1/dashboards/sales       # Sales metrics
GET    /api/v1/dashboards/operations  # Operations metrics
GET    /api/v1/dashboards/team        # Team performance
GET    /api/v1/dashboards/support     # Support metrics
```

### AI Tools

The AI assistant can execute these tools:

| Tool | Description |
|------|-------------|
| `search_clients` | Search clients by name, email, phone |
| `search_leads` | Search leads by stage, source |
| `get_client_details` | Get full client information |
| `get_lead_details` | Get full lead information |
| `list_tasks` | List tasks with filters |
| `create_task` | Create new tasks |
| `get_dashboard_stats` | Get business statistics |
| `search_quotes` | Search quotes |

---

## 🖼 Screenshots

<div align="center">

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Kanban Pipeline
![Kanban](docs/screenshots/kanban.png)

### AI Chat
![Chat](docs/screenshots/chat.png)

### Time Tracking
![Time Tracking](docs/screenshots/time-tracking.png)

</div>

---

## 🚢 Deployment

### Production Requirements

- Ubuntu 22.04 LTS (recommended)
- Nginx
- PHP 8.3-FPM
- MySQL 8.0
- Redis
- Supervisor
- SSL Certificate (Let's Encrypt)

### Quick Deploy

```bash
# Install dependencies
apt update && apt install -y nginx mysql-server redis-server supervisor

# Clone and setup
cd /var/www
git clone https://github.com/yourusername/tenanta.git
cd tenanta

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Run migrations
php artisan migrate --force

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Configure Nginx and Supervisor (see docs)
```

See [CLAUDE.md](CLAUDE.md) for detailed deployment instructions.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Style

- **PHP**: PSR-12 standard
- **TypeScript**: No semicolons, 2-space indentation
- **Vue**: PascalCase components, Composition API

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Vuetify](https://vuetifyjs.com) - Material Design Component Framework
- [Anthropic](https://anthropic.com) - Claude AI

---

<div align="center">

## 👨‍💻 Author

**Esteban Selvaggi**

[![Website](https://img.shields.io/badge/Website-selvaggiesteban.dev-4285F4?style=for-the-badge&logo=google-chrome&logoColor=white)](https://selvaggiesteban.dev/)
[![GitHub](https://img.shields.io/badge/GitHub-selvaggiesteban-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/selvaggiesteban)

---

<sub>Built with ❤️ in Argentina</sub>

</div>
