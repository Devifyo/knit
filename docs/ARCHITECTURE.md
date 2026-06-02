# Architecture — Knit CRM

## 1. Shape

A **modular monolith** on Laravel 13. Each bounded context is a self-contained
module under `app/Modules/<Module>` holding its own Models, Services, Policies,
HTTP layer (Controllers/Requests/Resources), Events, Listeners, Jobs and Tests.
Modules communicate through **Events**, never by reaching into each other's
internals. This keeps the option open to extract a module into a service later
without a rewrite.

```
Request → Middleware (tenancy + auth + Inertia) → thin Controller
        → Service (business logic) → Model (tenant-scoped)
        → Events → queued Listeners / Jobs (Horizon)
        → Broadcast (Reverb) → Vue/Inertia UI
```

- **Controllers** are ≤ ~5 lines: validate (Form Request), authorize (Policy),
  delegate to a Service, return an Inertia response or API Resource.
- **Services** own business logic and transactions.
- **Jobs** (Redis/Horizon) own slow or retryable work.
- **Events/Listeners** own cross-module side effects (`LeadConverted`,
  `DealStageChanged`, `TicketCreated`, …). Listeners are queued.

## 2. Multi-tenancy

We use `stancl/tenancy` in **single-database, scoped** mode. A `Tenant` is a
workspace.

- **Resolution:** middleware resolves the tenant by **subdomain** of
  `CENTRAL_DOMAIN` (e.g. `acme.localhost`) **and** by custom domain. Central
  (landlord) routes — signup, billing, platform admin — live on the root domain.
- **Scoping:** every tenant-owned table has an indexed `tenant_id` FK. Models use
  `App\Support\Tenancy\BelongsToTenant`, which:
  - registers a global `TenantScope` filtering by the active tenant, and
  - auto-fills `tenant_id` on create.
- **Provisioning:** creating a tenant seeds default roles, pipelines, ticket
  statuses, email templates and an owner user (Phase 1).
- **Isolation test (mandatory):** seed two tenants; authenticated as tenant A,
  every index/show endpoint returns zero tenant-B rows, and direct ID access to a
  tenant-B record returns 404.

### Migration path to DB-per-tenant (future, not implemented)

`stancl/tenancy` also supports a database-per-tenant topology. For very large
tenants we can migrate by: provisioning a dedicated database per heavy tenant,
switching that tenant's connection at resolution time, and running the existing
tenant migrations against it. Because all tenant access already goes through the
`BelongsToTenant`/`TenantScope` abstraction and tenant-aware connections, models
do not change — only the bootstrap/connection layer does. We stay single-DB
scoped until scale demands otherwise.

## 3. Real-time (Reverb)

Reverb runs as its own container (`knit_reverb`, port 6001 internal / 8102 host)
and is backed by Redis. The browser connects via Laravel Echo (`resources/js/
bootstrap.js`, `useEcho` composable). Channels (authorized in
`routes/channels.php`, tenant + permission scoped):

| Channel | Purpose |
|---|---|
| `tenant.{id}.notifications` | toasts, assignments, mentions |
| `tenant.{id}.pipeline.{pipelineId}` | live kanban card moves |
| `tenant.{id}.inbox.{userId}` | new messages / tickets |
| `tenant.{id}.dashboard` | KPI widget updates |
| `presence-tenant.{id}.chat.{channelId}` | team chat presence + typing |

## 4. Queue / Cache

Redis backs cache, sessions, and the queue. **Horizon** (`knit_horizon`
container) supervises queue workers. Delayed automation steps are scheduled
queued jobs; the `knit_scheduler` container runs `schedule:work`.

## 5. AI (Gemini)

`App\Services\AI\GeminiService` is the **single** entry point for all AI. Every
method is cache-keyed by a content hash, runs queue-backed (Phase 7), retries
with backoff, and **degrades gracefully** to a safe fallback when AI is disabled
or upstream fails. Results are persisted to `ai_outputs` for audit + caching. A
per-tenant `AI_ENABLED` flag gates the feature. No raw Gemini HTTP may exist
anywhere else.

Contract: `scoreLead, recommendNextAction, predictDealRisk, forecastRevenue,
summarizeTicket, suggestReply, analyzeSentiment, chatbotReply, generateEmail,
generateProposal, summarizeMeeting, predictChurn, scoreCustomerHealth`.

## 6. Automation engine (Phase 3)

A `Workflow` (per tenant) has a trigger, ordered steps, and an enabled flag.
Triggers: record created/updated, field changed, stage changed, form submitted,
date reached, inbound webhook, API call. Steps: send email/SMS/WhatsApp, create
task, update field, assign owner, add tag, call webhook, wait/delay, branch
(if/else over an AND/OR condition tree). Each run is persisted in `workflow_runs`
with per-step status; runs are idempotent and safe to retry. `wait` steps park a
run and resume via scheduled jobs.

## 7. Standards

PSR-12, `declare(strict_types=1)`, typed everything. Pint + PHPStan level 6 in
CI. Money = integer minor units + currency via a `Money` value object. Dates in
UTC. API envelope `{ data, meta, links }` via API Resources. Frontend: typed
Pinia stores, composables for shared logic, server data via Inertia props or
`/api/v1` for async widgets.

## 8. Infrastructure (Docker)

`docker-compose.yml` services: `app` (php-fpm + supervisor), `horizon`,
`scheduler`, `reverb`, `vite` (HMR), `nginx` (`:8100`), `db` (MySQL latest,
`:33062`), `redis`, `phpmyadmin` (`:8101`). The app image
(`php:8.4-fpm-alpine`) carries pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip,
intl, opcache and the redis extension, plus Composer.
