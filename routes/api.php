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
use App\Http\Controllers\Api\Omnichannel\WebhookOrchestratorController;
use App\Http\Controllers\Api\Admin\ResellerController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\PublicInquiryController;
use App\Http\Controllers\Api\CRM\TeamController;

/*
|--------------------------------------------------------------------------
| Webhook & Callback Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks')->group(function () {
    // Meta (WhatsApp/Messenger)
    Route::match(['get', 'post'], 'meta', [WebhookOrchestratorController::class, 'handleMeta'])
        ->middleware('meta.signature');

    // Telegram
    Route::post('telegram/{token}', [WebhookOrchestratorController::class, 'handleTelegram']);

    // Twilio (SMS)
    Route::post('twilio', [WebhookOrchestratorController::class, 'handleTwilio']);

    // Google Business Messages
    Route::post('google-business', [WebhookOrchestratorController::class, 'handleGoogleBusinessMessages']);

    // X (Twitter)
    Route::match(['get', 'post'], 'x', [WebhookOrchestratorController::class, 'handleX']);

    // Google OAuth Callback (GBM & Email Hub)
    Route::get('google/callback', [WebhookOrchestratorController::class, 'handleGoogleOAuthCallback'])
        ->name('webhooks.google.callback');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
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
        Route::get('branding', [PublicController::class, 'branding']);
        Route::get('branding/{slug}', [BrandingController::class, 'show']);
        Route::get('courses', [PublicController::class, 'courses']);
        Route::get('courses/{slug}', [CourseController::class, 'showBySlug']);
        Route::get('plans', [PublicController::class, 'plans']);
        Route::post('inquiry', [PublicInquiryController::class, 'store']);

        // Widget Routes
        Route::get('widget/settings/{tenant_id}', [\App\Http\Controllers\Api\Omnichannel\WidgetController::class, 'settings']);
        Route::post('widget/init', [\App\Http\Controllers\Api\Omnichannel\WidgetController::class, 'init']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('widget/message', [\App\Http\Controllers\Api\Omnichannel\WidgetController::class, 'sendMessage']);
            Route::get('widget/session', [\App\Http\Controllers\Api\Omnichannel\WidgetController::class, 'getSession']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Email Tracking Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('t/o/{recipient}/{hash}', [EmailTrackingController::class, 'trackOpen'])->name('track.open');
        Route::get('t/c/{recipient}/{hash}/{url}', [EmailTrackingController::class, 'trackClick'])->name('track.click');
        Route::get('unsubscribe/{recipient}/{hash}', [EmailTrackingController::class, 'unsubscribeForm'])->name('unsubscribe.form');
        Route::post('unsubscribe/{recipient}/{hash}', [EmailTrackingController::class, 'unsubscribe'])->name('unsubscribe');
        Route::post('webhook/{provider}', [EmailTrackingController::class, 'webhook'])->name('webhook');
    });

    /*
    |--------------------------------------------------------------------------
    | Auth Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

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

        Route::apiResource('teams', TeamController::class);
        Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
        Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

        Route::prefix('crm')->group(function () {
            Route::apiResource('clients', ClientController::class);
            Route::get('clients/{client}/contacts', [ClientController::class, 'contacts']);
            Route::apiResource('contacts', ContactController::class);
            Route::patch('contacts/{contact}/make-primary', [ContactController::class, 'makePrimary']);
            Route::apiResource('leads', LeadController::class);
            Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
            Route::patch('leads/{lead}/move-stage', [LeadController::class, 'moveStage']);
            Route::apiResource('quotes', QuoteController::class);
            Route::patch('quotes/{quote}/send', [QuoteController::class, 'send']);
            Route::patch('quotes/{quote}/accept', [QuoteController::class, 'accept']);
            Route::patch('quotes/{quote}/reject', [QuoteController::class, 'reject']);
            Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate']);
            Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf']);
            Route::get('quotes/{quote}/download', [QuoteController::class, 'download']);
            Route::apiResource('pipelines', PipelineController::class);
            Route::patch('pipelines/{pipeline}/make-default', [PipelineController::class, 'makeDefault']);
            Route::post('pipelines/{pipeline}/stages', [PipelineController::class, 'storeStage']);
            Route::put('pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'updateStage']);
            Route::delete('pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'destroyStage']);
            Route::patch('pipelines/{pipeline}/stages/reorder', [PipelineController::class, 'reorderStages']);
            Route::post('import/preview', [ImportController::class, 'preview']);
            Route::post('import', [ImportController::class, 'import']);
            Route::get('import/template/{type}', [ImportController::class, 'template']);
        });

        Route::prefix('operations')->group(function () {
            Route::apiResource('projects', ProjectController::class);
            Route::get('projects/{project}/tasks', [ProjectController::class, 'tasks']);
            Route::patch('projects/{project}/complete', [ProjectController::class, 'complete']);
            Route::patch('projects/{project}/reopen', [ProjectController::class, 'reopen']);
            Route::post('projects/{project}/members', [ProjectController::class, 'addMember']);
            Route::put('projects/{project}/members/{user}', [ProjectController::class, 'updateMember']);
            Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);
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

        Route::prefix('tracking')->group(function () {
            Route::get('timer', [TimerController::class, 'current']);
            Route::post('timer/start', [TimerController::class, 'start']);
            Route::post('timer/stop', [TimerController::class, 'stop']);
            Route::post('timer/cancel', [TimerController::class, 'cancel']);
            Route::patch('timer', [TimerController::class, 'update']);
            Route::apiResource('entries', TimeEntryController::class);
            Route::get('summary', [TimeEntryController::class, 'summary']);
        });

        Route::prefix('support')->group(function () {
            Route::apiResource('tickets', TicketController::class);
            Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply']);
            Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign']);
            Route::patch('tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
            Route::patch('tickets/{ticket}/close', [TicketController::class, 'close']);
            Route::patch('tickets/{ticket}/reopen', [TicketController::class, 'reopen']);
            Route::get('tickets-stats', [TicketController::class, 'stats']);
            Route::apiResource('kb/categories', KbCategoryController::class);
            Route::apiResource('kb/articles', KbArticleController::class);
            Route::patch('kb/articles/{kbArticle}/publish', [KbArticleController::class, 'publish']);
            Route::patch('kb/articles/{kbArticle}/archive', [KbArticleController::class, 'archive']);
            Route::post('kb/articles/{kbArticle}/feedback', [KbArticleController::class, 'feedback']);
        });

        Route::prefix('courses')->middleware(\App\Http\Middleware\LMSAccessMiddleware::class)->group(function () {
            Route::apiResource('/', CourseController::class)->parameters(['' => 'course']);
            Route::post('{course}/publish', [CourseController::class, 'publish']);
        });

        Route::prefix('chat')->group(function () {
            Route::get('conversations', [ChatController::class, 'index']);
            Route::post('conversations', [ChatController::class, 'store']);
            Route::get('conversations/{conversation}', [ChatController::class, 'show']);
            Route::delete('conversations/{conversation}', [ChatController::class, 'destroy']);
            Route::post('conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
            Route::post('conversations/{conversation}/stream', [ChatController::class, 'streamMessage']);
        });

        Route::prefix('enrollments')->group(function () {
            Route::get('/', [EnrollmentController::class, 'index']);
            Route::post('/', [EnrollmentController::class, 'store']);
            Route::get('{enrollment}', [EnrollmentController::class, 'show']);
            Route::delete('{enrollment}', [EnrollmentController::class, 'unenroll']);
            Route::get('{enrollment}/content', [EnrollmentController::class, 'courseContent']);
            Route::post('{enrollment}/topics/{topic}/complete', [EnrollmentController::class, 'markTopicCompleted']);
            Route::post('{enrollment}/topics/{topic}/progress', [EnrollmentController::class, 'updateTopicProgress']);
            Route::get('check-access/{course}', [EnrollmentController::class, 'checkAccess']);
        });

        Route::prefix('subscriptions')->group(function () {
            Route::apiResource('plans', SubscriptionPlanController::class);
            Route::post('plans/{plan}/toggle-active', [SubscriptionPlanController::class, 'toggleActive']);
            Route::get('/', [SubscriptionController::class, 'index']);
            Route::post('/', [SubscriptionController::class, 'store']);
            Route::get('current', [SubscriptionController::class, 'current']);
            Route::get('{subscription}', [SubscriptionController::class, 'show']);
            Route::post('{subscription}/cancel', [SubscriptionController::class, 'cancel']);
            Route::post('{subscription}/reactivate', [SubscriptionController::class, 'reactivate']);
            Route::post('{subscription}/change-plan', [SubscriptionController::class, 'changePlan']);
        });

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

        Route::prefix('dashboards')->group(function () {
            Route::get('overview', [DashboardController::class, 'overview']);
            Route::get('sales', [DashboardController::class, 'sales']);
            Route::get('operations', [DashboardController::class, 'operations']);
            Route::get('team', [DashboardController::class, 'team']);
            Route::get('support', [DashboardController::class, 'support']);
        });

        Route::prefix('financials')->group(function () {
            Route::get('/', [DashboardController::class, 'financials']);
            Route::post('upload', [\App\Http\Controllers\Api\Finance\FinanceUploadController::class, 'upload']);
        });

        Route::prefix('seo')->group(function () {
            Route::get('export-pdf/{type}', [DashboardController::class, 'exportPdf']);
        });

        Route::prefix('omnichannel')->group(function () {
            Route::apiResource('channels', \App\Http\Controllers\Api\Omnichannel\ChannelController::class);
            Route::post('channels/{channel}/toggle-active', [\App\Http\Controllers\Api\Omnichannel\ChannelController::class, 'toggleActive']);
            Route::get('analytics', [UnifiedInboxController::class, 'analytics']);
            Route::get('conversations', [UnifiedInboxController::class, 'index']);
            Route::get('conversations/{conversation}/messages', [UnifiedInboxController::class, 'messages']);
            Route::post('conversations/{conversation}/link-contact', [UnifiedInboxController::class, 'linkContact']);
            Route::post('conversations/{conversation}/assign-agent', [UnifiedInboxController::class, 'assignAgent']);
            Route::get('conversations/{conversation}/suggest-response', [UnifiedInboxController::class, 'suggestResponse']);
            Route::post('emit-typing', [UnifiedInboxController::class, 'emitTyping']);
            Route::apiResource('canned-responses', \App\Http\Controllers\Api\Omnichannel\CannedResponseController::class);
            Route::get('contacts/{contact}/messages', [\App\Http\Controllers\Api\Omnichannel\ContactMessagesController::class, 'index']);
            Route::post('send', [UnifiedInboxController::class, 'sendMessage']);
        });

        Route::prefix('reseller')->middleware(\App\Http\Middleware\ResellerAccessMiddleware::class)->group(function () {
            Route::get('dashboard', [ResellerController::class, 'dashboard']);
            Route::apiResource('tenants', \App\Http\Controllers\Api\Admin\ResellerTenantController::class);
        });

        Route::prefix('branding')->group(function () {
            Route::get('/', [BrandingController::class, 'index']);
            Route::put('/', [BrandingController::class, 'update']);
            Route::get('/locales', [BrandingController::class, 'locales']);
            Route::get('/timezones', [BrandingController::class, 'timezones']);
            Route::get('/currencies', [BrandingController::class, 'currencies']);
        });

        Route::prefix('marketing')->group(function () {
            Route::apiResource('templates', EmailTemplateController::class)->parameters(['templates' => 'emailTemplate']);
            Route::post('templates/{emailTemplate}/duplicate', [EmailTemplateController::class, 'duplicate']);
            Route::post('templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview']);
            Route::get('templates-categories', [EmailTemplateController::class, 'categories']);
            Route::apiResource('campaigns', EmailCampaignController::class)->parameters(['campaigns' => 'emailCampaign']);
            Route::post('campaigns/{emailCampaign}/duplicate', [EmailCampaignController::class, 'duplicate']);
            Route::get('campaigns/{emailCampaign}/recipients', [EmailCampaignController::class, 'recipients']);
            Route::post('campaigns/{emailCampaign}/recipients', [EmailCampaignController::class, 'addRecipients']);
            Route::delete('campaigns/{emailCampaign}/recipients/{email}', [EmailCampaignController::class, 'removeRecipient']);
            Route::post('campaigns/{emailCampaign}/schedule', [EmailCampaignController::class, 'schedule']);
            Route::post('campaigns/{emailCampaign}/cancel-schedule', [EmailCampaignController::class, 'cancelSchedule']);
            Route::post('campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send']);
            Route::get('campaigns/{emailCampaign}/stats', [EmailCampaignController::class, 'stats']);
            Route::post('campaigns/{emailCampaign}/preview', [EmailCampaignController::class, 'preview']);
            Route::apiResource('lists', EmailListController::class)->parameters(['lists' => 'emailList']);
            Route::get('lists/{emailList}/subscribers', [EmailListController::class, 'subscribers']);
            Route::post('lists/{emailList}/subscribers', [EmailListController::class, 'addSubscribers']);
            Route::post('lists/{emailList}/import', [EmailListController::class, 'importSubscribers']);
            Route::delete('lists/{emailList}/subscribers/{subscriber}', [EmailListController::class, 'removeSubscriber']);
            Route::post('lists/{emailList}/sync', [EmailListController::class, 'syncDynamic']);
            Route::get('lists/{emailList}/export', [EmailListController::class, 'export']);
            Route::get('unsubscribes', [EmailUnsubscribeController::class, 'index']);
            Route::post('unsubscribes/resubscribe', [EmailUnsubscribeController::class, 'resubscribe']);
            Route::get('unsubscribes/reasons', [EmailUnsubscribeController::class, 'reasons']);
            Route::get('unsubscribes/stats', [EmailUnsubscribeController::class, 'stats']);
        });

        Route::prefix('payments')->group(function () {
            Route::post('checkout', [\App\Http\Controllers\Api\PaymentController::class, 'createCheckoutSession']);
            Route::post('webhook', [\App\Http\Controllers\Api\PaymentController::class, 'webhook'])
                ->name('payments.webhook')
                ->withoutMiddleware([\App\Http\Middleware\TenantMiddleware::class])
                ->middleware(['mp.signature']);
        });
    });

    Route::middleware(['tenant', 'super_admin'])->prefix('admin')->group(function () {
        Route::post('import/preview', [\App\Http\Controllers\Api\Admin\TenantImportController::class, 'uploadAndPreview']);
        Route::post('import/process', [\App\Http\Controllers\Api\Admin\TenantImportController::class, 'process']);
        Route::get('landings', [\App\Http\Controllers\Api\Admin\LandingController::class, 'index']);
        Route::get('landings/{tenant}', [\App\Http\Controllers\Api\Admin\LandingController::class, 'show']);
        Route::put('landings/{tenant}', [\App\Http\Controllers\Api\Admin\LandingController::class, 'update']);
        Route::post('landings/{tenant}/regenerate', [\App\Http\Controllers\Api\Admin\LandingController::class, 'regenerate']);
        Route::delete('landings/{tenant}', [\App\Http\Controllers\Api\Admin\LandingController::class, 'destroy']);
    });
});
