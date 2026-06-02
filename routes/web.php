<?php

declare(strict_types=1);

use App\Http\Controllers\GuideController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SearchController;
use App\Modules\Accounts\Http\Controllers\AccountController;
use App\Modules\Admin\Http\Controllers\AuditController;
use App\Modules\Admin\Http\Controllers\BrandingController;
use App\Modules\Admin\Http\Controllers\ComplianceController;
use App\Modules\Admin\Http\Controllers\InvitationController;
use App\Modules\Admin\Http\Controllers\MemberController;
use App\Modules\Admin\Http\Controllers\SecurityController;
use App\Modules\AI\Http\Controllers\MeetingController;
use App\Modules\Analytics\Http\Controllers\ActivityFeedController;
use App\Modules\Analytics\Http\Controllers\DashboardController;
use App\Modules\Analytics\Http\Controllers\ReportController;
use App\Modules\Automation\Http\Controllers\TaskController;
use App\Modules\Automation\Http\Controllers\WorkflowController;
use App\Modules\Billing\Http\Controllers\BillingController;
use App\Modules\Billing\Http\Controllers\InvoiceController;
use App\Modules\Communication\Http\Controllers\ChatController;
use App\Modules\Communication\Http\Controllers\InboundWebhookController;
use App\Modules\Communication\Http\Controllers\InboxController;
use App\Modules\Contacts\Http\Controllers\CompanyController;
use App\Modules\Contacts\Http\Controllers\ContactController;
use App\Modules\Deals\Http\Controllers\DealController;
use App\Modules\Deals\Http\Controllers\QuoteController;
use App\Modules\Industry\Http\Controllers\ModuleController;
use App\Modules\Industry\Http\Controllers\ModuleRecordController;
use App\Modules\Integrations\Http\Controllers\WebhookEndpointController;
use App\Modules\Leads\Http\Controllers\LeadCaptureController;
use App\Modules\Leads\Http\Controllers\LeadController;
use App\Modules\Marketing\Http\Controllers\CampaignController;
use App\Modules\Marketing\Http\Controllers\FormController;
use App\Modules\Marketing\Http\Controllers\FormPublicController;
use App\Modules\Marketing\Http\Controllers\TrackingController;
use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Support\Http\Controllers\HelpController;
use App\Modules\Support\Http\Controllers\KbController;
use App\Modules\Support\Http\Controllers\SupportWebhookController;
use App\Modules\Support\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

// Public, per-workspace lead capture form (no auth). Feeds the automation engine.
Route::get('/f/{slug}', [LeadCaptureController::class, 'show'])->name('lead-capture.show');
Route::post('/f/{slug}', [LeadCaptureController::class, 'submit'])->name('lead-capture.submit');

// Public invitation acceptance (no auth — the invitee may have no account yet).
Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invite/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

// Public inbound-email webhook (mail provider posts here). Threads into the inbox.
Route::post('/webhooks/mail/{slug}', InboundWebhookController::class)->name('webhooks.mail');

// Public support intake webhook + self-service help portal (KB + AI chatbot).
Route::post('/webhooks/support/{slug}', SupportWebhookController::class)->name('webhooks.support');
Route::get('/help/{slug}', [HelpController::class, 'show'])->name('help.show');
Route::post('/help/{slug}/ask', [HelpController::class, 'ask'])->name('help.ask');

// Public marketing: landing-page forms + email open/click tracking.
Route::get('/forms/{slug}', [FormPublicController::class, 'show'])->name('forms.public');
Route::post('/forms/{slug}', [FormPublicController::class, 'submit'])->name('forms.submit');
Route::get('/track/open/{token}', [TrackingController::class, 'open'])->name('track.open');
Route::get('/track/click/{token}', [TrackingController::class, 'click'])->name('track.click');

Route::middleware(['auth', 'tenant', 'ip.allow', '2fa.enforce'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Analytics — custom report builder + exports
    Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:analytics.view')->name('reports.index');
    Route::get('/reports/export/{format}', [ReportController::class, 'export'])->middleware('permission:reports.export')->name('reports.export');

    // Collaboration — team activity feed (any workspace member)
    Route::get('/feed', [ActivityFeedController::class, 'index'])->name('feed.index');

    // In-app user guide (how-to docs, any workspace member)
    Route::get('/guide', [GuideController::class, 'index'])->name('guide.index');
    Route::get('/guide/{slug}', [GuideController::class, 'show'])->name('guide.show');

    // Global ⌘K record search (returns JSON; tenant-scoped, view-permission filtered)
    Route::get('/search', SearchController::class)->name('search');

    // Projects & tasks (kanban, subtasks, time tracking, file sharing)
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/files/{media}', [ProjectController::class, 'downloadFile'])->name('projects.files.download');
    });
    Route::middleware('permission:projects.manage')->group(function () {
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::post('/projects/{project}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
        Route::patch('/project-tasks/{task}/move', [ProjectController::class, 'moveTask'])->name('projects.tasks.move');
        Route::post('/project-tasks/{task}/time', [ProjectController::class, 'logTime'])->name('projects.tasks.time');
        Route::post('/projects/{project}/files', [ProjectController::class, 'uploadFile'])->name('projects.files.upload');
    });

    // Contacts
    Route::middleware('permission:contacts.view')->group(function () {
        Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    });
    Route::middleware('permission:contacts.manage')->group(function () {
        Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
        Route::post('/contacts/{contact}/notes', [ContactController::class, 'addNote'])->name('contacts.notes');
        Route::post('/contacts/{contact}/meeting', [MeetingController::class, 'summarize'])->name('contacts.meeting');
    });

    // Companies
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('permission:companies.view')->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->middleware('permission:companies.manage')->name('companies.store');

    // Leads
    Route::get('/leads', [LeadController::class, 'index'])->middleware('permission:leads.view')->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->middleware('permission:leads.manage')->name('leads.store');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->middleware('permission:leads.convert')->name('leads.convert');
    Route::post('/leads/{lead}/score', [LeadController::class, 'score'])->middleware('permission:leads.manage')->name('leads.score');

    // Deals
    Route::get('/deals', [DealController::class, 'index'])->middleware('permission:deals.view')->name('deals.index');
    Route::get('/deals/{deal}', [DealController::class, 'show'])->middleware('permission:deals.view')->name('deals.show');
    Route::post('/deals', [DealController::class, 'store'])->middleware('permission:deals.manage')->name('deals.store');
    Route::patch('/deals/{deal}/move', [DealController::class, 'move'])->middleware('permission:deals.manage')->name('deals.move');
    Route::post('/deals/{deal}/insight', [DealController::class, 'insight'])->middleware('permission:deals.view')->name('deals.insight');
    Route::post('/deals/{deal}/products', [DealController::class, 'addProduct'])->middleware('permission:deals.manage')->name('deals.products.add');
    Route::delete('/deals/{deal}/products/{pivotId}', [DealController::class, 'removeProduct'])->middleware('permission:deals.manage')->name('deals.products.remove');

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->middleware('permission:accounts.view')->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->middleware('permission:accounts.manage')->name('accounts.store');

    // Shared inbox
    Route::middleware('permission:inbox.view')->group(function () {
        Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
        Route::get('/inbox/{conversation}', [InboxController::class, 'show'])->name('inbox.show');
    });
    Route::middleware('permission:inbox.manage')->group(function () {
        Route::post('/inbox/{conversation}/reply', [InboxController::class, 'reply'])->name('inbox.reply');
        Route::post('/inbox/{conversation}/note', [InboxController::class, 'note'])->name('inbox.note');
        Route::patch('/inbox/{conversation}/assign', [InboxController::class, 'assign'])->name('inbox.assign');
        Route::patch('/inbox/{conversation}/status', [InboxController::class, 'status'])->name('inbox.status');
        Route::post('/inbox/simulate', [InboxController::class, 'simulate'])->name('inbox.simulate');
    });

    // Team chat
    Route::get('/chat', [ChatController::class, 'index'])->middleware('permission:chat.use')->name('chat.index');
    Route::post('/chat', [ChatController::class, 'send'])->middleware('permission:chat.use')->name('chat.send');

    // Support — tickets
    Route::middleware('permission:tickets.view')->group(function () {
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    });
    Route::middleware('permission:tickets.manage')->group(function () {
        Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/assist', [TicketController::class, 'assist'])->name('tickets.assist');
        Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
        Route::post('/tickets/simulate', [TicketController::class, 'simulate'])->name('tickets.simulate');
    });

    // Helpdesk knowledge base (admin)
    Route::middleware('permission:kb.manage')->group(function () {
        Route::get('/kb', [KbController::class, 'index'])->name('kb.index');
        Route::post('/kb', [KbController::class, 'store'])->name('kb.store');
        Route::delete('/kb/{article}', [KbController::class, 'destroy'])->name('kb.destroy');
    });

    // Marketing — campaigns + forms
    Route::middleware('permission:marketing.view')->group(function () {
        Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
        Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
        Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
    });
    Route::middleware('permission:marketing.manage')->group(function () {
        Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::post('/campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
        Route::post('/forms', [FormController::class, 'store'])->name('forms.store');
    });

    // Automation — workflows
    Route::get('/workflows', [WorkflowController::class, 'index'])->middleware('permission:workflows.view')->name('workflows.index');
    Route::middleware('permission:workflows.manage')->group(function () {
        Route::get('/workflows/create', [WorkflowController::class, 'create'])->name('workflows.create');
        Route::post('/workflows', [WorkflowController::class, 'store'])->name('workflows.store');
        Route::get('/workflows/{workflow}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
        Route::get('/workflows/{workflow}/runs', [WorkflowController::class, 'runs'])->name('workflows.runs');
        Route::post('/workflows/{workflow}/test', [WorkflowController::class, 'testRun'])->name('workflows.test');
        Route::put('/workflows/{workflow}', [WorkflowController::class, 'update'])->name('workflows.update');
        Route::patch('/workflows/{workflow}/toggle', [WorkflowController::class, 'toggle'])->name('workflows.toggle');
        Route::delete('/workflows/{workflow}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');
    });

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('permission:tasks.view')->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('permission:tasks.manage')->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->middleware('permission:tasks.manage')->name('tasks.toggle');

    // Quotes / CPQ
    Route::get('/quotes', [QuoteController::class, 'index'])->middleware('permission:quotes.view')->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->middleware('permission:quotes.view')->name('quotes.show');
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->middleware('permission:quotes.view')->name('quotes.pdf');
    Route::post('/quotes', [QuoteController::class, 'store'])->middleware('permission:quotes.manage')->name('quotes.store');
    Route::post('/quotes/{quote}/items', [QuoteController::class, 'addItem'])->middleware('permission:quotes.manage')->name('quotes.items');
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'status'])->middleware('permission:quotes.manage')->name('quotes.status');

    // Notes (Phase 1 demo resource)
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Workspace admin
    Route::get('/members', [MemberController::class, 'index'])->middleware('permission:members.view')->name('members.index');
    Route::post('/members/invite', [MemberController::class, 'invite'])->middleware('permission:members.invite')->name('members.invite');
    Route::delete('/invitations/{invitation}', [MemberController::class, 'revokeInvite'])->middleware('permission:members.invite')->name('invitations.revoke');
    Route::get('/settings/branding', [BrandingController::class, 'edit'])->name('settings.branding');
    Route::put('/settings/branding', [BrandingController::class, 'update'])->middleware('permission:branding.update')->name('settings.branding.update');

    // Billing & subscriptions (account-level)
    Route::get('/settings/billing', [BillingController::class, 'index'])->middleware('permission:billing.view')->name('billing.index');
    Route::get('/settings/billing/invoices/{invoice}/pdf', [InvoiceController::class, 'download'])->middleware('permission:billing.view')->name('billing.invoices.pdf');
    Route::post('/settings/billing/subscribe', [BillingController::class, 'subscribe'])->middleware('permission:billing.manage')->name('billing.subscribe');
    Route::post('/settings/billing/cancel', [BillingController::class, 'cancel'])->middleware('permission:billing.manage')->name('billing.cancel');

    // Security — 2FA enrolment + sessions (any user); policy edit (Owner/Admin)
    Route::get('/settings/security', [SecurityController::class, 'edit'])->name('security.edit');
    Route::put('/settings/security', [SecurityController::class, 'update'])->middleware('permission:security.manage')->name('security.update');

    // Compliance — audit trail + GDPR data-subject tooling
    Route::get('/settings/audit', [AuditController::class, 'index'])->middleware('permission:audit.view')->name('audit.index');
    Route::middleware('permission:compliance.manage')->group(function () {
        Route::get('/contacts/{contact}/export', [ComplianceController::class, 'export'])->name('contacts.export');
        Route::post('/contacts/{contact}/erase', [ComplianceController::class, 'erase'])->name('contacts.erase');
    });

    // Industry modules — marketplace (install/uninstall) + generic record CRUD
    Route::get('/settings/modules', [ModuleController::class, 'index'])->middleware('permission:modules.view')->name('modules.index');
    Route::post('/settings/modules/{key}/toggle', [ModuleController::class, 'toggle'])->middleware('permission:modules.manage')->name('modules.toggle');
    Route::middleware('permission:modules.view')->group(function () {
        Route::get('/m/{module}/{entity}', [ModuleRecordController::class, 'index'])->name('modules.records.index');
    });
    Route::middleware('permission:modules.use')->group(function () {
        Route::post('/m/{module}/{entity}', [ModuleRecordController::class, 'store'])->name('modules.records.store');
        Route::delete('/m/{module}/{entity}/{record}', [ModuleRecordController::class, 'destroy'])->name('modules.records.destroy');
    });

    // Integrations — outbound webhooks (developer platform)
    Route::get('/settings/webhooks', [WebhookEndpointController::class, 'index'])->middleware('permission:integrations.view')->name('webhooks.index');
    Route::middleware('permission:integrations.manage')->group(function () {
        Route::post('/settings/webhooks', [WebhookEndpointController::class, 'store'])->name('webhooks.store');
        Route::delete('/settings/webhooks/{endpoint}', [WebhookEndpointController::class, 'destroy'])->name('webhooks.destroy');
        Route::post('/settings/webhooks/{endpoint}/ping', [WebhookEndpointController::class, 'ping'])->name('webhooks.ping');
    });

    Route::get('/current-workspace', fn () => response()->json([
        'tenant_id' => tenant('id'),
        'name' => tenant('name'),
    ]))->name('current-workspace');
});
