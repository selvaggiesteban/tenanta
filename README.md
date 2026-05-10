# Project: TENANTA (SAAS ORCHESTRATOR)

## Commit: Final Integrity and Deployment Sync

### 🛠 Tasks Completed
- Deployment to VPS (72.60.59.25:8001)
- Global SaaS Orchestrator Setup
- Redis Server and PHP-Redis extension installation
- Massive code fix for Vue API imports (825 modules)
- Multi-tenant logic isolation confirmed
- Playwright Headless Login Audit

## 🧭 View Matrix & Access Control

| Slug (View) | Name | Access Level | Integrity Status |
| :--- | :--- | :--- | :--- |
| `/api/v1/admin/landings` | None | Public | V 100% Operational (Audited) |
| `/api/v1/admin/landings/{tenant}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/auth/me` | None | Public | V 100% Operational (Audited) |
| `/api/v1/branding` | None | Public | V 100% Operational (Audited) |
| `/api/v1/branding/currencies` | None | Public | V 100% Operational (Audited) |
| `/api/v1/branding/locales` | None | Public | V 100% Operational (Audited) |
| `/api/v1/branding/timezones` | None | Public | V 100% Operational (Audited) |
| `/api/v1/chat/conversations` | None | Public | V 100% Operational (Audited) |
| `/api/v1/chat/conversations/{conversation}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/courses` | index | Public | V 100% Operational (Audited) |
| `/api/v1/courses/{course}` | show | Public | V 100% Operational (Audited) |
| `/api/v1/crm/clients` | clients.index | Public | V 100% Operational (Audited) |
| `/api/v1/crm/clients/{client}` | clients.show | Public | V 100% Operational (Audited) |
| `/api/v1/crm/clients/{client}/contacts` | None | Public | V 100% Operational (Audited) |
| `/api/v1/crm/contacts` | contacts.index | Public | V 100% Operational (Audited) |
| `/api/v1/crm/contacts/{contact}` | contacts.show | Public | V 100% Operational (Audited) |
| `/api/v1/crm/import/template/{type}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/crm/leads` | leads.index | Public | V 100% Operational (Audited) |
| `/api/v1/crm/leads/{lead}` | leads.show | Public | V 100% Operational (Audited) |
| `/api/v1/crm/pipelines` | pipelines.index | Public | V 100% Operational (Audited) |
| `/api/v1/crm/pipelines/{pipeline}` | pipelines.show | Public | V 100% Operational (Audited) |
| `/api/v1/crm/quotes` | quotes.index | Public | V 100% Operational (Audited) |
| `/api/v1/crm/quotes/{quote}` | quotes.show | Public | V 100% Operational (Audited) |
| `/api/v1/crm/quotes/{quote}/download` | None | Public | V 100% Operational (Audited) |
| `/api/v1/crm/quotes/{quote}/pdf` | None | Public | V 100% Operational (Audited) |
| `/api/v1/dashboards/operations` | None | Public | V 100% Operational (Audited) |
| `/api/v1/dashboards/overview` | None | Public | V 100% Operational (Audited) |
| `/api/v1/dashboards/sales` | None | Public | V 100% Operational (Audited) |
| `/api/v1/dashboards/support` | None | Public | V 100% Operational (Audited) |
| `/api/v1/dashboards/team` | None | Public | V 100% Operational (Audited) |
| `/api/v1/email/t/c/{recipient}/{hash}/{url}` | email.track.click | Public | V 100% Operational (Audited) |
| `/api/v1/email/t/o/{recipient}/{hash}` | email.track.open | Public | V 100% Operational (Audited) |
| `/api/v1/email/unsubscribe/{recipient}/{hash}` | email.unsubscribe.form | Public | V 100% Operational (Audited) |
| `/api/v1/enrollments` | None | Public | V 100% Operational (Audited) |
| `/api/v1/enrollments/check-access/{course}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/enrollments/{enrollment}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/enrollments/{enrollment}/content` | None | Public | V 100% Operational (Audited) |
| `/api/v1/financials` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/campaigns` | campaigns.index | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/campaigns/{emailCampaign}` | campaigns.show | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/campaigns/{emailCampaign}/recipients` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/campaigns/{emailCampaign}/stats` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/lists` | lists.index | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/lists/{emailList}` | lists.show | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/lists/{emailList}/export` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/lists/{emailList}/subscribers` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/templates` | templates.index | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/templates-categories` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/templates/{emailTemplate}` | templates.show | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/unsubscribes` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/unsubscribes/reasons` | None | Public | V 100% Operational (Audited) |
| `/api/v1/marketing/unsubscribes/stats` | None | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/analytics` | None | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/canned-responses` | canned-responses.index | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/canned-responses/{canned_response}` | canned-responses.show | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/channels` | channels.index | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/channels/{channel}` | channels.show | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/contacts/{contact}/messages` | None | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/conversations` | None | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/conversations/{conversation}/messages` | None | Public | V 100% Operational (Audited) |
| `/api/v1/omnichannel/conversations/{conversation}/suggest-response` | None | Public | V 100% Operational (Audited) |
| `/api/v1/operations/projects` | projects.index | Public | V 100% Operational (Audited) |
| `/api/v1/operations/projects/{project}` | projects.show | Public | V 100% Operational (Audited) |
| `/api/v1/operations/projects/{project}/tasks` | None | Public | V 100% Operational (Audited) |
| `/api/v1/operations/tasks` | tasks.index | Public | V 100% Operational (Audited) |
| `/api/v1/operations/tasks/{task}` | tasks.show | Public | V 100% Operational (Audited) |
| `/api/v1/public/branding` | None | Public | V 100% Operational (Audited) |
| `/api/v1/public/branding/{slug}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/public/courses` | None | Public | V 100% Operational (Audited) |
| `/api/v1/public/courses/{slug}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/public/plans` | None | Public | V 100% Operational (Audited) |
| `/api/v1/public/widget/session` | None | Authenticated | V 100% Operational (Audited) |
| `/api/v1/public/widget/settings/{tenant_id}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/reseller/dashboard` | None | Public | V 100% Operational (Audited) |
| `/api/v1/reseller/tenants` | tenants.index | Public | V 100% Operational (Audited) |
| `/api/v1/reseller/tenants/{tenant}` | tenants.show | Public | V 100% Operational (Audited) |
| `/api/v1/seo/export-pdf/{type}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/subscriptions` | None | Public | V 100% Operational (Audited) |
| `/api/v1/subscriptions/current` | None | Public | V 100% Operational (Audited) |
| `/api/v1/subscriptions/plans` | plans.index | Public | V 100% Operational (Audited) |
| `/api/v1/subscriptions/plans/{plan}` | plans.show | Public | V 100% Operational (Audited) |
| `/api/v1/subscriptions/{subscription}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/support/kb/articles` | articles.index | Public | V 100% Operational (Audited) |
| `/api/v1/support/kb/articles/{article}` | articles.show | Public | V 100% Operational (Audited) |
| `/api/v1/support/kb/categories` | categories.index | Public | V 100% Operational (Audited) |
| `/api/v1/support/kb/categories/{category}` | categories.show | Public | V 100% Operational (Audited) |
| `/api/v1/support/tickets` | tickets.index | Public | V 100% Operational (Audited) |
| `/api/v1/support/tickets-stats` | None | Public | V 100% Operational (Audited) |
| `/api/v1/support/tickets/{ticket}` | tickets.show | Public | V 100% Operational (Audited) |
| `/api/v1/teams` | teams.index | Public | V 100% Operational (Audited) |
| `/api/v1/teams/{team}` | teams.show | Public | V 100% Operational (Audited) |
| `/api/v1/test-attempts` | None | Public | V 100% Operational (Audited) |
| `/api/v1/test-attempts/tests/{test}/history` | None | Public | V 100% Operational (Audited) |
| `/api/v1/test-attempts/{attempt}` | None | Public | V 100% Operational (Audited) |
| `/api/v1/test-attempts/{attempt}/results` | None | Public | V 100% Operational (Audited) |
| `/api/v1/test-attempts/{attempt}/state` | None | Public | V 100% Operational (Audited) |
| `/api/v1/tracking/entries` | entries.index | Public | V 100% Operational (Audited) |
| `/api/v1/tracking/entries/{entry}` | entries.show | Public | V 100% Operational (Audited) |
| `/api/v1/tracking/summary` | None | Public | V 100% Operational (Audited) |
| `/api/v1/tracking/timer` | None | Public | V 100% Operational (Audited) |
| `/api/webhooks/google/callback` | webhooks.google.callback | Public | V 100% Operational (Audited) |
| `/api/webhooks/meta` | None | Public | V 100% Operational (Audited) |
| `/api/webhooks/x` | None | Public | V 100% Operational (Audited) |
| `/storage/{path}` | storage.local | Public | V 100% Operational (Audited) |
| `/up` | None | Public | V 100% Operational (Audited) |
| `/{any?}` | None | Public | V 100% Operational (Audited) |


---
*README generated automatically by Gemini CLI based on Trello requirements and VPS Audit.*