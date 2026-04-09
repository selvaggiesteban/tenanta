<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

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
use App\Http\Controllers\Api\BrandingController;
use App\Http\Controllers\Api\Courses\CourseController;
use App\Http\Controllers\Api\Courses\CourseBlockController;
use App\Http\Controllers\Api\Courses\CourseTopicController;
use App\Http\Controllers\Api\Courses\EnrollmentController;
use App\Http\Controllers\Api\Courses\SubscriptionPlanController;
use App\Http\Controllers\Api\Courses\SubscriptionController;
use App\Http\Controllers\Api\Courses\CourseTestController;
use App\Http\Controllers\Api\Courses\TestAttemptController;
use App\Http\Controllers\Api\Marketing\EmailTemplateController;
use App\Http\Controllers\Api\Marketing\EmailCampaignController;
use App\Http\Controllers\Api\Marketing\EmailListController;
use App\Http\Controllers\Api\Marketing\EmailTrackingController;
use App\Http\Controllers\Api\Marketing\EmailUnsubscribeController;
use App\Http\Controllers\Api\Omnichannel\UnifiedInboxController;
use App\Http\Controllers\Api\Admin\ResellerController;

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
    | Public Routes (no authentication required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('public')->group(function () {
        // Branding for current session (if any)
        Route::get('branding', [PublicController::class, 'branding']);

        // Branding by tenant slug (for public pages)
        Route::get('branding/{slug}', [BrandingController::class, 'show']);

        // Public course catalog
        Route::get('courses', [PublicController::class, 'courses']);
        Route::get('courses/{slug}', [CourseController::class, 'showBySlug']);

        // Public subscription plans
        Route::get('plans', [PublicController::class, 'plans']);

        // Public Inquiries
        Route::post('inquiry', [PublicInquiryController::class, 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | Email Tracking Routes (no authentication - accessed via email links)
    |--------------------------------------------------------------------------
    */
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('t/o/{recipient}/{hash}', [EmailTrackingController::class, 'trackOpen'])
            ->name('track.open');
        Route::get('t/c/{recipient}/{hash}/{url}', [EmailTrackingController::class, 'trackClick'])
            ->name('track.click');
        Route::get('unsubscribe/{recipient}/{hash}', [EmailTrackingController::class, 'unsubscribeForm'])
            ->name('unsubscribe.form');
        Route::post('unsubscribe/{recipient}/{hash}', [EmailTrackingController::class, 'unsubscribe'])
            ->name('unsubscribe');
        Route::post('webhook/{provider}', [EmailTrackingController::class, 'webhook'])
            ->name('webhook');
    });

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
        Route::prefix('crm')->middleware(\App\Http\Middleware\CRMReadAccessMiddleware::class)->group(function () {
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
            // ... resto de rutas de tareas
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
        | Support Routes (Tickets)
        |--------------------------------------------------------------------------
        */
        Route::prefix('support')->middleware(\App\Http\Middleware\TicketAccessMiddleware::class)->group(function () {
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
        });

        /*
        |--------------------------------------------------------------------------
        | LMS (Courses) Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('courses')->middleware(\App\Http\Middleware\LMSAccessMiddleware::class)->group(function () {
            // Courses CRUD (admin/manager/teacher/member read)
            Route::apiResource('/', CourseController::class)->parameters(['' => 'course']);
            Route::post('{course}/publish', [CourseController::class, 'publish']);
            // ... resto de rutas de cursos
        });

        /*
        |--------------------------------------------------------------------------
        | Enrollments Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('enrollments')->group(function () {
            Route::get('/', [EnrollmentController::class, 'index']);
            Route::post('/', [EnrollmentController::class, 'store']);
            Route::get('{enrollment}', [EnrollmentController::class, 'show']);
            Route::delete('{enrollment}', [EnrollmentController::class, 'unenroll']);
            Route::get('{enrollment}/content', [EnrollmentController::class, 'courseContent']);
            Route::post('{enrollment}/topics/{topic}/complete', [EnrollmentController::class, 'markTopicCompleted']);
            Route::post('{enrollment}/topics/{topic}/progress', [EnrollmentController::class, 'updateTopicProgress']);

            // Check course access
            Route::get('check-access/{course}', [EnrollmentController::class, 'checkAccess']);
        });

        /*
        |--------------------------------------------------------------------------
        | Subscriptions Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('subscriptions')->group(function () {
            // Subscription Plans (admin)
            Route::apiResource('plans', SubscriptionPlanController::class);
            Route::post('plans/{plan}/toggle-active', [SubscriptionPlanController::class, 'toggleActive']);

            // User Subscriptions
            Route::get('/', [SubscriptionController::class, 'index']);
            Route::post('/', [SubscriptionController::class, 'store']);
            Route::get('current', [SubscriptionController::class, 'current']);
            Route::get('{subscription}', [SubscriptionController::class, 'show']);
            Route::post('{subscription}/cancel', [SubscriptionController::class, 'cancel']);
            Route::post('{subscription}/reactivate', [SubscriptionController::class, 'reactivate']);
            Route::post('{subscription}/change-plan', [SubscriptionController::class, 'changePlan']);
        });

        /*
        |--------------------------------------------------------------------------
        | Test Attempts Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('test-attempts')->group(function () {
            Route::get('/', [TestAttemptController::class, 'index']);
            Route::post('tests/{test}/start', [TestAttemptController::class, 'start']);
            Route::get('tests/{test}/history', [TestAttemptController::class, 'history']);
            Route::get('{attempt}', [TestAttemptController::class, 'show']);
            Route::get('{attempt}/state', [TestAttemptController::class, 'state']);
            Route::post('{attempt}/save', [TestAttemptController::class, 'saveProgress']);
            Route::post('{attempt}/submit', [TestAttemptController::class, 'submit']);
            Route::get('{attempt}/results', [TestAttemptController::class, 'results']);
        });

        /*
        |--------------------------------------------------------------------------
        | Dashboard & Intelligence Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('dashboards')->group(function () {
            Route::get('overview', [DashboardController::class, 'overview']);
            Route::get('sales', [DashboardController::class, 'sales']);
            Route::get('operations', [DashboardController::class, 'operations']);
            Route::get('team', [DashboardController::class, 'team']);
            Route::get('support', [DashboardController::class, 'support']);
        Route::prefix('financials')->group(function () {
            Route::get('/', [DashboardController::class, 'financials']);
            Route::post('upload', [\App\Http\Controllers\Api\Finance\FinanceUploadController::class, 'upload']);
        });

        Route::prefix('seo')->group(function () {
            Route::get('export-pdf/{type}', [DashboardController::class, 'exportPdf']);
        });

        /*
        |--------------------------------------------------------------------------
        | Omnichannel & Reseller Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('omnichannel')->group(function () {
            Route::get('messages', [UnifiedInboxController::class, 'index']);
            Route::post('send', [UnifiedInboxController::class, 'sendMessage']);
        });

        Route::prefix('reseller')->middleware(\App\Http\Middleware\ResellerAccessMiddleware::class)->group(function () {
            Route::get('dashboard', [ResellerController::class, 'dashboard']);
            Route::apiResource('tenants', \App\Http\Controllers\Api\Admin\ResellerTenantController::class);
        });

        /*
        |--------------------------------------------------------------------------
        | Branding Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('branding')->group(function () {
            Route::get('/', [BrandingController::class, 'index']);
            Route::put('/', [BrandingController::class, 'update']);
            Route::get('/locales', [BrandingController::class, 'locales']);
            Route::get('/timezones', [BrandingController::class, 'timezones']);
            Route::get('/currencies', [BrandingController::class, 'currencies']);
        });

        /*
        |--------------------------------------------------------------------------
        | Settings Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('settings')->group(function () {
            // Route::get('profile', [Settings\ProfileController::class, 'show']);
            // Route::put('profile', [Settings\ProfileController::class, 'update']);
        });

        /*
        |--------------------------------------------------------------------------
        | Marketing Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('marketing')->group(function () {
            // Email Templates
            Route::apiResource('templates', EmailTemplateController::class)
                ->parameters(['templates' => 'emailTemplate']);
            Route::post('templates/{emailTemplate}/duplicate', [EmailTemplateController::class, 'duplicate']);
            Route::post('templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview']);
            Route::get('templates-categories', [EmailTemplateController::class, 'categories']);

            // Email Campaigns
            Route::apiResource('campaigns', EmailCampaignController::class)
                ->parameters(['campaigns' => 'emailCampaign']);
            Route::post('campaigns/{emailCampaign}/duplicate', [EmailCampaignController::class, 'duplicate']);
            Route::get('campaigns/{emailCampaign}/recipients', [EmailCampaignController::class, 'recipients']);
            Route::post('campaigns/{emailCampaign}/recipients', [EmailCampaignController::class, 'addRecipients']);
            Route::delete('campaigns/{emailCampaign}/recipients/{email}', [EmailCampaignController::class, 'removeRecipient']);
            Route::post('campaigns/{emailCampaign}/schedule', [EmailCampaignController::class, 'schedule']);
            Route::post('campaigns/{emailCampaign}/cancel-schedule', [EmailCampaignController::class, 'cancelSchedule']);
            Route::post('campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send']);
            Route::get('campaigns/{emailCampaign}/stats', [EmailCampaignController::class, 'stats']);
            Route::post('campaigns/{emailCampaign}/preview', [EmailCampaignController::class, 'preview']);

            // Email Lists
            Route::apiResource('lists', EmailListController::class)
                ->parameters(['lists' => 'emailList']);
            Route::get('lists/{emailList}/subscribers', [EmailListController::class, 'subscribers']);
            Route::post('lists/{emailList}/subscribers', [EmailListController::class, 'addSubscribers']);
            Route::post('lists/{emailList}/import', [EmailListController::class, 'importSubscribers']);
            Route::delete('lists/{emailList}/subscribers/{subscriber}', [EmailListController::class, 'removeSubscriber']);
            Route::post('lists/{emailList}/sync', [EmailListController::class, 'syncDynamic']);
            Route::get('lists/{emailList}/export', [EmailListController::class, 'export']);

            // Unsubscribes
            Route::get('unsubscribes', [EmailUnsubscribeController::class, 'index']);
            Route::post('unsubscribes/resubscribe', [EmailUnsubscribeController::class, 'resubscribe']);
            Route::get('unsubscribes/reasons', [EmailUnsubscribeController::class, 'reasons']);
            Route::get('unsubscribes/stats', [EmailUnsubscribeController::class, 'stats']);
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('payments')->group(function () {
            Route::post('checkout', [\App\Http\Controllers\Api\PaymentController::class, 'createCheckoutSession']);
            Route::post('webhook', [\App\Http\Controllers\Api\PaymentController::class, 'webhook'])
                ->name('payments.webhook')
                ->withoutMiddleware([\App\Http\Middleware\TenantMiddleware::class]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Super Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['tenant', 'super_admin'])->prefix('admin')->group(function () {
        // Tenant Dynamic Import
        Route::post('import/preview', [\App\Http\Controllers\Api\Admin\TenantImportController::class, 'uploadAndPreview']);
        Route::post('import/process', [\App\Http\Controllers\Api\Admin\TenantImportController::class, 'process']);

        // Landings Management
        Route::get('landings', [\App\Http\Controllers\Api\Admin\LandingController::class, 'index']);
        Route::get('landings/{tenant}', [\App\Http\Controllers\Api\Admin\LandingController::class, 'show']);
        Route::put('landings/{tenant}', [\App\Http\Controllers\Api\Admin\LandingController::class, 'update']);
        Route::post('landings/{tenant}/regenerate', [\App\Http\Controllers\Api\Admin\LandingController::class, 'regenerate']);
        Route::delete('landings/{tenant}', [\App\Http\Controllers\Api\Admin\LandingController::class, 'destroy']);
    });
});
