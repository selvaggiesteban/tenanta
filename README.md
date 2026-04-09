# 🚀 Tenanta - Multi-Tenant ERP, CRM & LMS Solution

Tenanta is a comprehensive, multi-tenant enterprise resource planning (ERP) platform designed to streamline business operations. It integrates CRM, Project Management, Learning Management (LMS), Support Ticketing, and AI-driven automation into a single, cohesive ecosystem.

---

## 🌟 Key Features

### 🏢 Multi-Tenancy Core
- **Isolation:** Strict data separation between tenants using `BelongsToTenant` traits.
- **Branding:** Customizable themes, logos, and branding per tenant.
- **Subscription Management:** Built-in support for different plans and features.

### 🤝 CRM & Sales
- **Leads & Pipelines:** Manage sales stages, track conversions, and move leads through custom pipelines.
- **Client Management:** Centralized database for clients and contacts.
- **Quotes & Invoicing:** Generate professional PDF quotes and manage items.

### 📅 Project & Task Management
- **Projects:** Organise work into projects with dedicated members.
- **Task Tracking:** Kanban-style task management with status updates and assignments.
- **Time Tracking:** Log hours spent on tasks for accurate billing and productivity analysis.

### 🎓 Learning Management System (LMS)
- **Course Builder:** Create courses with blocks, topics, and structured content.
- **Enrollments:** Manage student progress and subscriptions to courses.
- **Testing:** Integrated test engine with questions, options, and attempt tracking.

### 🎫 Support & Knowledge Base
- **Ticketing System:** Handle customer inquiries with a robust reply system.
- **Knowledge Base:** Categorized articles to help users find answers quickly.

### 🤖 AI Integration
- **AI Service Provider:** Extensible architecture for AI-powered features.
- **Accountly Transcriber:** Python-based transcription service for processing audio/video content.

---

## 🛠 Tech Stack

- **Backend:** [Laravel 11](https://laravel.com/) (PHP 8.2+)
- **Frontend:** [Vue 3](https://vuejs.org/) with [Vuetify 3](https://vuetifyjs.com/) & [TypeScript](https://www.typescriptlang.org/)
- **State Management:** [Pinia](https://pinia.vuejs.org/)
- **Authentication:** [JWT (JSON Web Tokens)](https://jwt.io/)
- **Permissions:** [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Database:** MySQL/PostgreSQL (Production) | SQLite (Development)
- **Caching:** Redis
- **Asset Bundling:** Vite

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Node.js 20+
- Composer
- MySQL or SQLite

### Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd Tenanta
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Frontend dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Database Setup:**
   - Create a database and update your `.env` file.
   - Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

6. **Development Server:**
   ```bash
   composer dev
   ```
   *This starts Laravel, Vite, and the Queue worker simultaneously.*

---

## 📂 Project Structure

- `app/Models/`: Eloquent models with multi-tenant logic.
- `app/Services/AI/`: AI integration logic.
- `resources/js/`: Vue 3 frontend application.
- `database/migrations/`: Database schema definitions.
- `scripts/`: Python utility scripts (e.g., transcription).

---

## 🔒 Security & Privacy

This project follows strict security standards:
- **Environment Isolation:** Credentials are kept in `.env` and never committed.
- **Data Protection:** Tenant data is strictly partitioned.
- **Authentication:** Secure JWT-based stateless authentication.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).
