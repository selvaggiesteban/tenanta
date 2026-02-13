<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\CRM\ClientController;
use App\Http\Controllers\Api\CRM\ContactController;
use App\Http\Controllers\Api\CRM\LeadController;
use App\Http\Controllers\Api\CRM\QuoteController;
use App\Http\Controllers\Api\CRM\PipelineController;
use App\Http\Controllers\Api\CRM\ImportController;
use App\Http\Controllers\Api\Operations\ProjectController;
use App\Http\Controllers\Api\Operations\TaskController;
use App\Http\Controllers\Api\Tracking\TimerController;
use App\Http\Controllers\Api\Tracking\TimeEntryController;
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\Support\TicketController;
use App\Http\Controllers\Api\Support\KbCategoryController;
use App\Http\Controllers\Api\Support\KbArticleController;
use App\Http\Controllers\Api\Dashboard\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Tenanta API v1 Routes
| Base URL: /api/v1
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        // Public routes
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        // Protected auth routes
        Route::middleware('tenant')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (require authentication + tenant)
    |--------------------------------------------------------------------------
    */
    Route::middleware('tenant')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Team Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('teams', TeamController::class);
        Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
        Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

        /*
        |--------------------------------------------------------------------------
        | CRM Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('crm')->group(function () {
            // Clients
            Route::apiResource('clients', ClientController::class);
            Route::get('clients/{client}/contacts', [ClientController::class, 'contacts']);

            // Contacts
            Route::apiResource('contacts', ContactController::class);
            Route::patch('contacts/{contact}/make-primary', [ContactController::class, 'makePrimary']);

            // Leads
            Route::apiResource('leads', LeadController::class);
            Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
            Route::patch('leads/{lead}/move-stage', [LeadController::class, 'moveStage']);

            // Quotes
            Route::apiResource('quotes', QuoteController::class);
            Route::patch('quotes/{quote}/send', [QuoteController::class, 'send']);
            Route::patch('quotes/{quote}/accept', [QuoteController::class, 'accept']);
            Route::patch('quotes/{quote}/reject', [QuoteController::class, 'reject']);
            Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate']);
            Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf']);
            Route::get('quotes/{quote}/download', [QuoteController::class, 'download']);

            // Pipelines
            Route::apiResource('pipelines', PipelineController::class);
            Route::patch('pipelines/{pipeline}/make-default', [PipelineController::class, 'makeDefault']);
            Route::post('pipelines/{pipeline}/stages', [PipelineController::class, 'storeStage']);
            Route::put('pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'updateStage']);
            Route::delete('pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'destroyStage']);
            Route::patch('pipelines/{pipeline}/stages/reorder', [PipelineController::class, 'reorderStages']);

            // Import
            Route::post('import/preview', [ImportController::class, 'preview']);
            Route::post('import', [ImportController::class, 'import']);
            Route::get('import/template/{type}', [ImportController::class, 'template']);
        });

        /*
        |--------------------------------------------------------------------------
        | Operations Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('operations')->group(function () {
            // Projects
            Route::apiResource('projects', ProjectController::class);
            Route::get('projects/{project}/tasks', [ProjectController::class, 'tasks']);
            Route::patch('projects/{project}/complete', [ProjectController::class, 'complete']);
            Route::patch('projects/{project}/reopen', [ProjectController::class, 'reopen']);
            Route::post('projects/{project}/members', [ProjectController::class, 'addMember']);
            Route::put('projects/{project}/members/{user}', [ProjectController::class, 'updateMember']);
            Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);

            // Tasks
            Route::apiResource('tasks', TaskController::class);
            Route::patch('tasks/{task}/start', [TaskController::class, 'start']);
            Route::patch('tasks/{task}/submit', [TaskController::class, 'submit']);
            Route::patch('tasks/{task}/approve', [TaskController::class, 'approve']);
            Route::patch('tasks/{task}/reject', [TaskController::class, 'reject']);
            Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);
            Route::post('tasks/reorder', [TaskController::class, 'reorder']);
            Route::post('tasks/{task}/dependencies', [TaskController::class, 'addDependency']);
            Route::delete('tasks/{task}/dependencies/{dependency}', [TaskController::class, 'removeDependency']);
        });

        /*
        |--------------------------------------------------------------------------
        | Time Tracking Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('tracking')->group(function () {
            // Timer
            Route::get('timer', [TimerController::class, 'current']);
            Route::post('timer/start', [TimerController::class, 'start']);
            Route::post('timer/stop', [TimerController::class, 'stop']);
            Route::post('timer/cancel', [TimerController::class, 'cancel']);
            Route::patch('timer', [TimerController::class, 'update']);

            // Time Entries
            Route::apiResource('entries', TimeEntryController::class);
            Route::get('summary', [TimeEntryController::class, 'summary']);
        });

        /*
        |--------------------------------------------------------------------------
        | Support Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('support')->group(function () {
            // Tickets
            Route::apiResource('tickets', TicketController::class);
            Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply']);
            Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign']);
            Route::patch('tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
            Route::patch('tickets/{ticket}/close', [TicketController::class, 'close']);
            Route::patch('tickets/{ticket}/reopen', [TicketController::class, 'reopen']);
            Route::get('tickets-stats', [TicketController::class, 'stats']);

            // Knowledge Base
            Route::apiResource('kb/categories', KbCategoryController::class);
            Route::apiResource('kb/articles', KbArticleController::class);
            Route::patch('kb/articles/{kbArticle}/publish', [KbArticleController::class, 'publish']);
            Route::patch('kb/articles/{kbArticle}/archive', [KbArticleController::class, 'archive']);
            Route::post('kb/articles/{kbArticle}/feedback', [KbArticleController::class, 'feedback']);
        });

        /*
        |--------------------------------------------------------------------------
        | Chat Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('chat')->group(function () {
            // Conversations
            Route::get('conversations', [ChatController::class, 'index']);
            Route::post('conversations', [ChatController::class, 'store']);
            Route::get('conversations/{conversation}', [ChatController::class, 'show']);
            Route::delete('conversations/{conversation}', [ChatController::class, 'destroy']);

            // Messages
            Route::post('conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
            Route::post('conversations/{conversation}/stream', [ChatController::class, 'streamMessage']);
        });

        /*
        |--------------------------------------------------------------------------
        | Dashboard Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('dashboards')->group(function () {
            Route::get('overview', [DashboardController::class, 'overview']);
            Route::get('sales', [DashboardController::class, 'sales']);
            Route::get('operations', [DashboardController::class, 'operations']);
            Route::get('team', [DashboardController::class, 'team']);
            Route::get('support', [DashboardController::class, 'support']);
        });

        /*
        |--------------------------------------------------------------------------
        | Settings Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('settings')->group(function () {
            // Route::get('branding', [Settings\BrandingController::class, 'show']);
            // Route::put('branding', [Settings\BrandingController::class, 'update']);
            // Route::get('profile', [Settings\ProfileController::class, 'show']);
            // Route::put('profile', [Settings\ProfileController::class, 'update']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Super Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['tenant', 'super_admin'])->prefix('admin')->group(function () {
        // Tenants
        // Route::apiResource('tenants', Admin\TenantController::class);
        // Route::post('tenants/{tenant}/impersonate', [Admin\TenantController::class, 'impersonate']);

        // Plans
        // Route::apiResource('plans', Admin\PlanController::class);

        // Analytics
        // Route::get('analytics', [Admin\AnalyticsController::class, 'index']);
    });
});
