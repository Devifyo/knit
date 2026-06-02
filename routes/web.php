<?php

declare(strict_types=1);

use App\Http\Controllers\NoteController;
use App\Modules\Accounts\Http\Controllers\AccountController;
use App\Modules\Admin\Http\Controllers\BrandingController;
use App\Modules\Admin\Http\Controllers\InvitationController;
use App\Modules\Admin\Http\Controllers\MemberController;
use App\Modules\Analytics\Http\Controllers\DashboardController;
use App\Modules\Automation\Http\Controllers\TaskController;
use App\Modules\Automation\Http\Controllers\WorkflowController;
use App\Modules\Contacts\Http\Controllers\CompanyController;
use App\Modules\Contacts\Http\Controllers\ContactController;
use App\Modules\Deals\Http\Controllers\DealController;
use App\Modules\Deals\Http\Controllers\QuoteController;
use App\Modules\Leads\Http\Controllers\LeadCaptureController;
use App\Modules\Leads\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'phase' => 'Phase 2 — Core CRM',
        'laravelVersion' => app()->version(),
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Public, per-workspace lead capture form (no auth). Feeds the automation engine.
Route::get('/f/{slug}', [LeadCaptureController::class, 'show'])->name('lead-capture.show');
Route::post('/f/{slug}', [LeadCaptureController::class, 'submit'])->name('lead-capture.submit');

// Public invitation acceptance (no auth — the invitee may have no account yet).
Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invite/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contacts
    Route::middleware('permission:contacts.view')->group(function () {
        Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    });
    Route::middleware('permission:contacts.manage')->group(function () {
        Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
        Route::post('/contacts/{contact}/notes', [ContactController::class, 'addNote'])->name('contacts.notes');
    });

    // Companies
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('permission:companies.view')->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->middleware('permission:companies.manage')->name('companies.store');

    // Leads
    Route::get('/leads', [LeadController::class, 'index'])->middleware('permission:leads.view')->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->middleware('permission:leads.manage')->name('leads.store');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->middleware('permission:leads.convert')->name('leads.convert');

    // Deals
    Route::get('/deals', [DealController::class, 'index'])->middleware('permission:deals.view')->name('deals.index');
    Route::get('/deals/{deal}', [DealController::class, 'show'])->middleware('permission:deals.view')->name('deals.show');
    Route::post('/deals', [DealController::class, 'store'])->middleware('permission:deals.manage')->name('deals.store');
    Route::patch('/deals/{deal}/move', [DealController::class, 'move'])->middleware('permission:deals.manage')->name('deals.move');
    Route::post('/deals/{deal}/products', [DealController::class, 'addProduct'])->middleware('permission:deals.manage')->name('deals.products.add');
    Route::delete('/deals/{deal}/products/{pivotId}', [DealController::class, 'removeProduct'])->middleware('permission:deals.manage')->name('deals.products.remove');

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->middleware('permission:accounts.view')->name('accounts.index');

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

    Route::get('/current-workspace', fn () => response()->json([
        'tenant_id' => tenant('id'),
        'name' => tenant('name'),
    ]))->name('current-workspace');
});
