# 🚀 Tenanta: The AI-Driven Orchestrator SaaS

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3.5-4FC08D?logo=vue.js)]()
[![WebSockets](https://img.shields.io/badge/Real--time-Reverb-blue?logo=laravel)]()
[![Infrastructure](https://img.shields.io/badge/Proxy-Caddy--SSL-white?logo=caddy)]()
[![AI-Agnostic](https://img.shields.io/badge/AI-Strategy--Pattern-darkblue?logo=google-gemini)]()

Tenanta is a high-performance **Multi-Tenant SaaS Ecosystem** designed to be the central "Orchestrator Brain" for enterprise-level Digital Marketing, CRM, and LMS operations. It combines deep AI integration, omnichannel communication, and a robust automation engine into a single, secure environment.

---

## 💎 The Tenanta Ecosystem

Tenanta goes beyond a standard CRM; it is a hub that orchestrates multiple "Satellite" integrations and internal microservices.

### 🧠 1. Headless AI & Agentic RAG
*   **Agnostic AI Strategy**: Built with a provider-independent architecture (Strategy Pattern). Easily switch between **Gemini, OpenAI, or Claude**.
*   **BYOK (Bring Your Own Key)**: Scalable financial model where tenants provide their own API keys to drive intensive AI workloads.
*   **Unified Agentic RAG**: An internal AI overlay capable of querying both **Isolated Vector Databases (PDFs)** and **Structured CRM Data (Leads, Deals)** to provide context-aware answers.
*   **Human-in-the-Loop**: AI-generated responses for WordPress comments and Chatbots that remain "Pending Review" until approved by the tenant.

### 🛰️ 2. WordPress Satellite Network (WP-Connect)
Tenanta acts as the "Brain" for multiple WordPress installations via its proprietary **WP-Connect** protocol:
*   **SEO Audit Polling**: Automated crawler that audits tenant sites daily, reporting technical gaps (Meta, H1, Links) directly to the Tenanta Dashboard.
*   **Real-time WP Chatbot**: Low-latency conversational widget powered by **Laravel Reverb**.
*   **Auto-Update Sync**: All satellite plugins are automatically updated across all WordPress sites via our internal GitHub-based distribution engine.

### 🌐 3. CMS & Website Creator
*   **Dynamic Landing Pages**: A refactored generator that serves static-optimized landing pages through **Internal Hosting**.
*   **Caddy Auto-SSL**: Seamless **Custom Domain Mapping** for every tenant, with automatic Let's Encrypt certificates managed by a Caddy reverse proxy.
*   **Block-Based Blog**: An internal institutional blog that stores articles in **Structured JSON format** (Block Editor style), optimized for AI-driven bulk generation.

### ✉️ 4. Marketing & Growth Automation
*   **Email Warm-up Engine**: A persistent **Redis State Machine** that handles complex warm-up cycles (1-10 min) rotating through multiple SMTP accounts.
*   **Automated Bounce Handling**: Real-time processing of bounce and unsubscribe events to maintain 99% list hygiene.
*   **Omnichannel Inbox**: Unified dashboard for WhatsApp Cloud API, Messenger, Telegram, SMS (Twilio), and Google Business Messages.

---

## 🏗️ Technical Architecture

*   **Backend**: Laravel 11 + PHP 8.3 (Service-Oriented Architecture).
*   **Frontend**: Vue 3.5 (Composition API) + Vite + Vuetify 3 (Sneat).
*   **Infrastructure**: Caddy (Reverse Proxy) + Redis (State Management) + MySQL (Multi-tenant isolation).
*   **Communications**: Laravel Reverb (WebSockets) + Twilio + Meta APIs.
*   **Financials**: **Accountly Integration** (Microservice-driven) for real-time financial BI dashboards.

---

## 🚀 Deployment & Installation

### Prerequisites
* Docker & Docker Compose
* PHP 8.3+
* Node.js 20+

### Quick Start
1.  **Clone the Repo**: `git clone https://github.com/selvaggiesteban/tenanta.git`
2.  **Environment Setup**: `cp .env.example .env`
3.  **Spin up Containers**: `docker-compose up -d`
4.  **Install Dependencies**:
    ```bash
    composer install
    npm install && npm run build
    ```
5.  **Initialize Database**: `php artisan migrate --seed`

---

## 📅 Development Status (Master Plan)

Tenanta is developed following a rigorous **Master Architecture Plan**. Every route, controller, and middleware is pre-planned to ensure zero improvisation.

*   [x] **Core CRM & LMS**: Fully Functional.
*   [x] **Omnichannel Integration**: Live (WhatsApp, Meta, Telegram).
*   [ ] **WP-Connect Satellites**: In Development.
*   [ ] **Website Creator (Caddy/Auto-SSL)**: In Development.
*   [ ] **AI Content Engine (Bulk JSON)**: In Development.

---

## 📄 License
Tenanta is a proprietary SaaS platform developed by **Selvaggi Consultores**. All rights reserved.

---
*Building the future of Digital Business Orchestration.*
