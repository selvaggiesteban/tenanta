# Tenanta - Tests Pendientes

> Este documento lista todos los tests que deben escribirse en una sesión futura.

---

## Phase 1: Foundation

### 1.1 BelongsToTenant Trait Tests
**File:** `tests/Unit/Traits/BelongsToTenantTest.php`

```php
- test_model_has_tenant_relationship()
- test_global_scope_filters_by_current_tenant()
- test_creating_model_automatically_sets_tenant_id()
- test_can_query_without_tenant_scope()
```

### 1.2 TenantMiddleware Tests
**File:** `tests/Feature/Middleware/TenantMiddlewareTest.php`

```php
- test_middleware_sets_current_tenant_from_jwt()
- test_unauthenticated_request_returns_401()
- test_request_with_invalid_tenant_returns_404()
```

### 1.3 Auth Tests
**File:** `tests/Feature/Auth/LoginTest.php`

```php
- test_user_can_login_with_valid_credentials()
- test_login_fails_with_invalid_credentials()
- test_login_updates_last_login_timestamp()
```

**File:** `tests/Feature/Auth/RegisterTest.php`

```php
- test_new_tenant_can_register()
- test_registration_creates_tenant_and_admin_user()
- test_registration_requires_valid_data()
- test_duplicate_email_fails()
```

### 1.4 Team Tests
**File:** `tests/Feature/TeamTest.php`

```php
- test_can_list_teams()
- test_can_create_team()
- test_can_update_team()
- test_can_delete_team()
- test_can_add_member_to_team()
- test_can_remove_member_from_team()
- test_teams_are_scoped_to_tenant()
```

---

## Phase 2: CRM

### 2.1 Client Tests
**File:** `tests/Feature/CRM/ClientTest.php`

```php
- test_can_list_clients()
- test_can_create_client()
- test_can_update_client()
- test_can_delete_client()
- test_can_restore_deleted_client()
- test_clients_are_scoped_to_tenant()
```

### 2.2 Contact Tests
**File:** `tests/Feature/CRM/ContactTest.php`

```php
- test_can_list_contacts_for_client()
- test_can_create_contact()
- test_can_update_contact()
- test_can_delete_contact()
```

### 2.3 Lead Tests
**File:** `tests/Feature/CRM/LeadTest.php`

```php
- test_can_list_leads()
- test_can_create_lead()
- test_can_update_lead()
- test_can_delete_lead()
- test_can_convert_lead_to_client()
- test_conversion_creates_client_and_contact()
- test_conversion_maintains_history_link()
```

### 2.4 Quote Tests
**File:** `tests/Feature/CRM/QuoteTest.php`

```php
- test_can_list_quotes()
- test_can_create_quote_with_items()
- test_can_update_quote()
- test_can_send_quote()
- test_can_accept_quote()
- test_can_reject_quote()
- test_quote_calculates_totals()
```

### 2.5 Pipeline Tests
**File:** `tests/Feature/CRM/PipelineTest.php`

```php
- test_can_list_pipelines()
- test_can_create_pipeline_with_stages()
- test_can_reorder_stages()
- test_can_move_lead_between_stages()
```

### 2.6 CSV Import Tests
**File:** `tests/Feature/CRM/ImportTest.php`

```php
- test_can_import_csv()
- test_detects_duplicates_by_email()
- test_can_skip_duplicates()
- test_can_update_duplicates()
- test_validates_csv_format()
```

---

## Phase 3: Operations

### 3.1 Project Tests
**File:** `tests/Feature/Operations/ProjectTest.php`

```php
- test_can_list_projects()
- test_can_create_project()
- test_can_update_project()
- test_can_delete_project()
- test_can_assign_members_to_project()
- test_completing_project_cancels_open_tasks()
```

### 3.2 Task Tests
**File:** `tests/Feature/Operations/TaskTest.php`

```php
- test_can_list_tasks()
- test_can_create_task()
- test_can_update_task()
- test_can_delete_task()
- test_can_submit_task_for_review()
- test_manager_can_approve_task()
- test_manager_can_reject_task_with_comment()
- test_rejected_task_is_unlocked()
- test_approved_task_logs_completion()
```

### 3.3 Task Dependencies Tests
**File:** `tests/Feature/Operations/TaskDependencyTest.php`

```php
- test_can_add_dependency()
- test_prevents_circular_dependencies()
- test_uncompleting_parent_reverts_children()
```

### 3.4 Timer Tests
**File:** `tests/Feature/Tracking/TimerTest.php`

```php
- test_can_start_timer()
- test_can_stop_timer()
- test_can_cancel_timer()
- test_timer_persists_across_sessions()
- test_only_one_timer_per_user()
```

### 3.5 TimeEntry Tests
**File:** `tests/Feature/Tracking/TimeEntryTest.php`

```php
- test_stopping_timer_creates_entry()
- test_entry_calculates_duration()
- test_entry_is_immutable_for_members()
- test_admin_can_modify_with_reason()
- test_modification_creates_audit_log()
```

### 3.6 Overtime Tests
**File:** `tests/Feature/Tracking/OvertimeTest.php`

```php
- test_manager_can_authorize_overtime()
- test_overtime_requires_preauthorization()
- test_alerts_when_approaching_contracted_hours()
```

---

## Phase 4: Frontend

### 4.1 Component Tests (Vitest)
**File:** `tests/js/components/TimerWidget.spec.ts`

```typescript
- displays current timer state
- can start timer
- can stop timer
- shows elapsed time
- persists across page refresh
```

### 4.2 Store Tests
**File:** `tests/js/stores/auth.spec.ts`

```typescript
- login stores token
- logout clears token
- refresh updates token
```

**File:** `tests/js/stores/crm.spec.ts`

```typescript
- fetchClients populates state
- createClient adds to list
- updateClient updates in list
```

---

## Phase 5: Chat AI

### 5.1 Conversation Model Tests
**File:** `tests/Unit/Models/ConversationTest.php`

```php
- test_conversation_belongs_to_user()
- test_conversation_belongs_to_tenant()
- test_conversation_has_many_messages()
- test_get_messages_for_ai_formats_correctly()
- test_add_message_updates_token_counts()
- test_add_message_updates_last_message_at()
- test_generate_title_from_first_user_message()
- test_get_total_tokens_calculation()
```

### 5.2 Message Model Tests
**File:** `tests/Unit/Models/MessageTest.php`

```php
- test_message_belongs_to_conversation()
- test_is_user_returns_correct_boolean()
- test_is_assistant_returns_correct_boolean()
- test_has_tool_calls_returns_correct_boolean()
- test_is_tool_result_returns_correct_boolean()
- test_tool_calls_cast_as_array()
- test_tool_results_cast_as_array()
```

### 5.3 ChatController Tests
**File:** `tests/Feature/Chat/ChatControllerTest.php`

```php
- test_can_list_conversations()
- test_can_create_conversation()
- test_can_view_conversation_with_messages()
- test_can_delete_conversation()
- test_cannot_view_other_users_conversation()
- test_cannot_delete_other_users_conversation()
- test_conversations_scoped_to_tenant()
- test_send_message_creates_user_and_assistant_messages()
- test_send_message_generates_conversation_title()
- test_stream_message_returns_sse_format()
```

### 5.4 AI Provider Tests
**File:** `tests/Unit/Services/AI/AnthropicProviderTest.php`

```php
- test_builds_payload_correctly()
- test_formats_messages_correctly()
- test_formats_tools_correctly()
- test_parses_response_correctly()
- test_parses_tool_calls_from_response()
- test_handles_api_errors()
- test_chat_sends_request_to_api()
- test_stream_chat_yields_chunks()
```

**File:** `tests/Unit/Services/AI/OpenAIProviderTest.php`

```php
- test_builds_payload_with_system_message()
- test_formats_messages_correctly()
- test_formats_tools_as_functions()
- test_parses_response_correctly()
- test_parses_tool_calls_from_response()
- test_handles_api_errors()
```

**File:** `tests/Unit/Services/AI/GoogleProviderTest.php`

```php
- test_builds_payload_with_contents()
- test_formats_messages_as_user_model_roles()
- test_formats_tools_as_function_declarations()
- test_parses_response_correctly()
- test_parses_function_calls_from_response()
- test_handles_api_errors()
```

### 5.5 AI Manager Tests
**File:** `tests/Unit/Services/AI/AIManagerTest.php`

```php
- test_returns_default_provider()
- test_returns_specific_provider()
- test_creates_anthropic_provider_for_claude()
- test_creates_openai_provider()
- test_creates_google_provider_for_gemini()
- test_throws_exception_for_unknown_provider()
- test_caches_provider_instances()
- test_can_change_default_provider()
```

### 5.6 Tool Definitions Tests
**File:** `tests/Unit/Services/AI/Tools/ToolDefinitionsTest.php`

```php
- test_all_returns_all_tool_definitions()
- test_enabled_returns_only_configured_tools()
- test_each_tool_has_required_fields()
- test_search_clients_tool_schema()
- test_search_leads_tool_schema()
- test_create_task_tool_schema()
- test_get_dashboard_stats_tool_schema()
```

### 5.7 Tool Executor Tests
**File:** `tests/Unit/Services/AI/Tools/ToolExecutorTest.php`

```php
- test_execute_unknown_tool_returns_error()
- test_search_clients_executes_correctly()
- test_search_clients_filters_by_status()
- test_search_clients_respects_tenant_scope()
- test_search_leads_executes_correctly()
- test_search_leads_filters_by_stage()
- test_get_client_details_returns_full_info()
- test_get_client_details_returns_error_for_missing()
- test_get_lead_details_returns_full_info()
- test_list_tasks_filters_correctly()
- test_create_task_creates_and_returns_task()
- test_get_dashboard_stats_returns_all_metrics()
- test_get_dashboard_stats_respects_period()
- test_search_quotes_executes_correctly()
```

### 5.8 Conversation Policy Tests
**File:** `tests/Unit/Policies/ConversationPolicyTest.php`

```php
- test_user_can_view_own_conversation()
- test_user_cannot_view_other_users_conversation()
- test_user_cannot_view_other_tenants_conversation()
- test_user_can_update_own_conversation()
- test_user_can_delete_own_conversation()
```

### 5.9 Integration Tests
**File:** `tests/Feature/Chat/AIIntegrationTest.php`

```php
- test_full_conversation_flow()
- test_tool_execution_in_conversation()
- test_multiple_tool_calls_handled()
- test_conversation_persists_history()
- test_token_counting_accumulates()
```

### 5.10 Ticket Tests (Future)
**File:** `tests/Feature/Support/TicketTest.php`

```php
- test_can_create_ticket()
- test_can_reply_to_ticket()
- test_can_resolve_ticket()
- test_tracks_sla_deadline()
```

---

## Test Execution Commands

```bash
# Run all PHP tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run with coverage
php artisan test --coverage

# Run frontend tests
npm run test

# Run specific frontend test
npm run test -- TimerWidget
```

---

*Document created: 2026-02-13*
*To be executed in a dedicated testing session*
