<?php

declare(strict_types=1);

use App\Http\Controllers\NoteController;
use App\Modules\Admin\Http\Controllers\BrandingController;
use App\Modules\Admin\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'phase' => 'Phase 1 — Tenancy, Auth, RBAC, White-Label',
        'laravelVersion' => app()->version(),
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

/*
| Authenticated workspace routes. `tenant` resolves the active workspace
| (custom domain → subdomain → authenticated user) and pins RBAC to it.
*/
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::get('/members', [MemberController::class, 'index'])
        ->middleware('permission:members.view')
        ->name('members.index');

    Route::get('/settings/branding', [BrandingController::class, 'edit'])->name('settings.branding');
    Route::put('/settings/branding', [BrandingController::class, 'update'])
        ->middleware('permission:branding.update')
        ->name('settings.branding.update');

    // Lightweight endpoint used by tenancy-resolution tests.
    Route::get('/current-workspace', fn () => response()->json([
        'tenant_id' => tenant('id'),
        'name' => tenant('name'),
    ]))->name('current-workspace');
});
