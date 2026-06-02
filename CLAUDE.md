# CLAUDE.md — Knit CRM

Production-grade, multi-tenant SaaS CRM (HubSpot / Zoho / Freshsales competitor).
Built as a **modular monolith**. This file is the source of truth for how the
codebase is organized and how to work in it. Keep it current.

---

## Stack

| Layer | Choice |
|---|---|
| Backend | Laravel 13, PHP 8.4 (modular monolith) |
| Frontend | Vue 3 (`<script setup>`) + Inertia.js + Vite |
| State | Pinia |
| Styling | TailwindCSS v4 + in-house UI library (`resources/js/Components/ui`) |
| Real-time | Laravel Reverb (WebSockets) over Redis |
| Queue/Cache | Redis + Laravel Horizon |
| DB | MySQL (latest) — utf8mb4 |
| Auth | Fortify + Sanctum (2FA, SSO) — wired in Phase 1 |
| AI | Google Gemini via `App\Services\AI\GeminiService` (the ONLY AI entry point) |
| Search | Laravel Scout (database driver; Meilisearch-ready) |
| Multi-tenancy | `stancl/tenancy` (single-DB, scoped) |

Everything runs in Docker. There is **no host PHP/Composer/MySQL** — all PHP
tooling runs inside the `knit_app` image.

---

## Run commands (Docker)

```bash
# Bring the whole stack up (app, horizon, scheduler, reverb, vite, nginx, db, redis, pma)
docker compose up -d

# One-off artisan / composer / pest inside the app image:
docker compose exec app php artisan <cmd>
docker compose exec app composer <cmd>
docker compose exec app php artisan test          # Pest
docker compose exec app composer lint             # Pint (fix)
docker compose exec app composer analyse          # PHPStan level 6
docker compose exec app composer ci               # lint:test + analyse + test

# Rebuild the app image after Dockerfile/extension changes:
docker compose build app
```

### Endpoints (local)

| Service | URL |
|---|---|
| App (nginx) | http://localhost:8100 |
| Vite HMR | http://localhost:5173 |
| Reverb WS | ws://localhost:8102 |
| phpMyAdmin | http://localhost:8101 |
| MySQL (host) | 127.0.0.1:33062 |

> If a class isn't found after adding files, run
> `docker compose exec app composer dump-autoload`.
> Composer requires that touch service providers must be followed by
> `php artisan package:discover` (we install with `--no-scripts` in CI-less flows).

---

## Where things live

```
app/
  Modules/<Module>/            # one folder per bounded context (see below)
    Models/ Services/ Policies/
    Http/{Controllers,Requests,Resources}/
    Events/ Listeners/ Jobs/ Tests/
  Services/AI/GeminiService.php # the single AI gateway
  Support/Tenancy/              # BelongsToTenant trait + TenantScope
  Http/Middleware/HandleInertiaRequests.php
  Providers/                    # AppServiceProvider, HorizonServiceProvider
resources/js/
  Components/ui/                # UI library (Button, Card, Modal, DataTable, Kanban, CommandPalette…)
  Layouts/                      # AppLayout, AuthLayout, SettingsLayout
  Pages/                        # Inertia pages (mirror modules)
  Stores/                       # Pinia stores
  Composables/                  # useEcho, useTenant, usePermissions, useTable
routes/
  web.php  channels.php  console.php
docs/                           # ARCHITECTURE.md, ROADMAP.md, DATA_MODEL.md, modules/
```

Modules: `Contacts, Leads, Deals, Accounts, Automation, Marketing, Support,
Communication, AI, Analytics, Billing, Admin, Integrations`.
The `App\` PSR-4 root maps to `app/`, so `App\Modules\Contacts\Models\Contact`
resolves to `app/Modules/Contacts/Models/Contact.php` with no extra config.

---

## Non-negotiable conventions

1. **Tenant isolation is sacred.** Every tenant-owned model uses
   `App\Support\Tenancy\BelongsToTenant` (global `TenantScope` + auto-fill
   `tenant_id`). Never write a query that can cross tenants. Prove it with an
   isolation test (seed 2 tenants; tenant A must see 0 of tenant B's rows; direct
   ID access to B returns 404).
2. **Every business model ships with:** migration + factory + seeder + policy +
   Form Request + feature test. No exceptions.
3. **Thin controllers** (≤ ~5 lines/action). Logic → Service classes. Slow work →
   queued Jobs (Horizon). Cross-module side effects → Events/Listeners (queued).
4. **All AI calls go through `GeminiService`.** No raw Gemini HTTP anywhere else.
5. **Money** = integer minor units + currency code via a `Money` value object.
   Never float math for money.
6. **Dates** stored UTC; tenant has a timezone; format in the presentation layer.
7. PSR-12, `declare(strict_types=1)`, typed properties/returns. Pint + PHPStan L6
   must pass. API responses via API Resources with `{ data, meta, links }`.
8. Real-time channels are authorized per-tenant + per-user in `routes/channels.php`.

**Definition of done:** code + migration + factory + seeder + policy + validation
+ passing tests + demo data visible in UI + docs updated.

---

## Build process

Build **one phase at a time** (see `docs/ROADMAP.md`). After each phase: run
migrations + the full Pest/Pint/PHPStan suite, commit on a `phase/N-*` branch,
summarize, and **wait for approval** before the next phase. Never leave the repo
broken.

## Tenancy & RBAC (Phase 1)

- **Single-DB scoped tenancy** (`stancl/tenancy`): initializing a tenant only sets
  the `tenant()` context (no DB/cache/queue switching — `tenancy.bootstrappers` is
  empty). Row isolation = `App\Support\Tenancy\TenantScope` via `BelongsToTenant`.
- **Resolution** (`App\Http\Middleware\ResolveTenant`, alias `tenant`): custom
  domain (full host in `domains`) → subdomain slug → authenticated user's
  `tenant_id`. It runs *before* `SubstituteBindings` (priority list) so scoped
  route bindings 404 across tenants, and pins the spatie permissions team.
- **RBAC**: `spatie/laravel-permission` with **teams keyed by `tenant_id`**. Roles:
  Owner/Admin/Manager/Agent (`App\Modules\Admin\Services\Rbac`). Owners bypass all
  gates (`Gate::before`). Field-level perms via `FieldPermissionService`.
- **Signup** provisions a workspace via `WorkspaceProvisioner` (tenant + subdomain
  + roles/permissions + owner user). Auth is **Fortify** (login/register/reset/2FA)
  with Inertia pages under `resources/js/Pages/Auth`.

### Demo data (after `php artisan migrate:fresh --seed`)
- `owner@acme.test` / `password` — Owner of *Acme Inc.*
- `agent@acme.test` / `password` — Agent of *Acme Inc.* (limited perms)
- `owner@globex.test` / `password` — Owner of *Globex*

### Tests
Run against a dedicated **MySQL `knit_test`** database (not sqlite — for schema
parity). Config comes from `.env.testing` (loaded because `APP_ENV=testing`). The
PHP containers do **not** use Docker `env_file`; Laravel reads `.env` / `.env.testing`
directly, so the test env isn't clobbered by container env vars.

## Design system (Phase 2)

`docs/DESIGN.md` is the source of truth: **Geist** UI font + Geist Mono for
numbers, near-monochrome **Zinc** palette, one white-label accent (`--brand`),
hairline structure, soft tinted elevation, restrained motion. Tokens live in
`resources/css/app.css` via Tailwind v4 `@theme` — use semantic utilities
(`bg-canvas`, `text-ink`/`text-muted`, `border-hairline`, `shadow-e1`, `brand-wash`,
`.nums` for money/metrics). The UI library in `resources/js/Components/ui` is the
only place to build primitives; new screens compose those.

## Domain models (Phase 2)

Core CRM Eloquent models live in `app/Models` (one tightly-coupled graph): Company,
Contact, Lead, Pipeline, Stage, Deal, Account, Activity, Tag, CustomFieldDefinition
(+ Note, FieldPermission, Tenant, User). **Controllers/services/policies live in
`app/Modules/<Module>`** (e.g. `App\Modules\Leads\Services\LeadConversionService`,
`App\Modules\Deals\Http\Controllers\DealController`). Every tenant-owned model
`implements TenantOwned` + `use BelongsToTenant`. Money = integer minor units +
currency (`Deal::formattedAmount()`). Real-time: `DealStageChanged` →
`tenant.{id}.pipeline.{pipelineId}`; `NoteCreated` → `tenant.{id}.notifications`.

## Automation engine (Phase 3)

`App\Modules\Automation`: `WorkflowEngine::trigger($event, $model)` (tenant-guarded)
starts a `WorkflowRun` per matching enabled workflow and queues `RunWorkflowJob`.
The job initializes the run's tenant, executes ordered `WorkflowStep`s, and is
**idempotent** (each step logged once in `workflow_run_steps`, skipped on retry) and
**resumable** (`wait` parks the run + re-dispatches a delayed continuation). Step
types: wait, send_email, create_task, update_field, add_tag, assign_owner, webhook,
condition (branch via `ConditionEvaluator` — AND/OR rule tree; false stops the run).
Triggers are registered in `AppServiceProvider` on model `created` events. CPQ:
`PricingService` does all money math in integer minor units; quote PDFs render via
`resources/views/pdf/quote.blade.php` (dompdf).

## Communication (Phase 4)

`App\Modules\Communication`: shared-inbox `Conversation` + `Message` (threading via
In-Reply-To then subject fallback; internal notes via `is_internal`; read receipts).
`InboundEmailService` threads inbound mail, links the sender to a `Contact`, and
writes an `Activity` onto that contact's timeline. Public webhook
`POST /webhooks/mail/{slug}` (CSRF-exempt via `webhooks/*`). Broadcast events:
`NewInboundMessage` → `tenant.{id}.inbox`; `UserMentioned` → notifications;
`ChatMessageSent` → presence `tenant.{id}.chat` (team chat online roster).

## Support (Phase 5)

`App\Modules\Support`: `Ticket` + `TicketReply` + `KbArticle`. Intake is
channel-agnostic — implement `Contracts\ChannelAdapter` per channel
(`EmailChannelAdapter` shipped) and feed `TicketIntakeService::fromChannel()`
(links contact, starts SLA via `SlaService`, routes via `AssignmentService`,
timelines it). `EscalationService::escalateBreached()` is run by the
`tickets:check-sla` command (scheduled every minute in `routes/console.php`,
registered in `bootstrap/app.php` withCommands) — it loops tenants and escalates
SLA-breached tickets idempotently. Public: `POST /webhooks/support/{slug}` intake
and the self-service portal `/help/{slug}` (KB + AI chatbot via `GeminiService`).

**Current status:** Phases 0–5 complete (49 Pest tests green). Phase 6 (Marketing
Automation) is next and not yet started.
