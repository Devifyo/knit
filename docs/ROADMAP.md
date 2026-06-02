# Roadmap — Knit CRM

Build **one phase at a time**. After each phase: migrations + full
Pest/Pint/PHPStan suite green, commit on `phase/N-*`, summarize, **wait for
approval**. Never leave the repo broken.

Legend: ✅ done · 🔜 next · ⬜ not started

---

## ✅ Phase 0 — Foundation
Laravel 13 + Inertia + Vue 3 + Vite + Tailwind v4; all Composer/NPM packages
installed; Redis/Horizon/Reverb wired; Pint + PHPStan L6 + Pest + GitHub Actions
CI; UI component library + layouts + demo Inertia page; docs (`CLAUDE.md`,
`ARCHITECTURE.md`, `ROADMAP.md`, `DATA_MODEL.md`). Full Docker stack.

**Acceptance:** app boots via nginx, Vite HMR works, Reverb + Horizon run, demo
components render, CI green. ✔

## ✅ Phase 1 — Tenancy, Auth, RBAC, White-Label
Single-DB scoped tenancy (`stancl/tenancy`), tenant resolution by custom domain →
subdomain slug → authenticated-user fallback (`ResolveTenant` middleware),
signup/onboarding that provisions a workspace (roles/permissions + owner user +
subdomain), Fortify auth + 2FA (Inertia auth pages), `spatie/laravel-permission`
with **teams scoped by `tenant_id`** (Owner/Admin/Manager/Agent) + **field-level
permissions**, audit (`owen-it/laravel-auditing`) + activity log
(`spatie/laravel-activitylog`), white-label branding (name/color/logo) applied
live via shared Inertia props + CSS vars, real-time notifications
(`tenant.{id}.notifications` → Reverb → toast).

**Acceptance — all met (17 Pest tests):** cross-tenant isolation (index returns
only own rows; direct ID access 404s); a non-owner (Agent) is denied
`members.view`; custom domain + subdomain resolve the right tenant; theme changes
apply live. Pint + PHPStan L6 + Pest all green.

Deferred to their phases (tables don't exist yet): default pipelines (Phase 2),
ticket statuses (Phase 5), email templates (Phase 6). SSO/OAuth providers scaffolded
behind Fortify but real provider wiring lands with the integrations work (Phase 10).

## ✅ Phase 2 — Core CRM

Full core data model (Company, Contact, Lead, Pipeline/Stage, Deal, Account,
Activity, Tag, CustomFieldDefinition) — all tenant-scoped via BelongsToTenant.
Contacts (list + search + detail with polymorphic timeline + notes + email dedupe
+ custom fields), Companies (health), Leads (capture + scoring stub + **convert →
contact + deal** via LeadConversionService), Deals (default pipeline + **drag-drop
kanban with live Reverb sync** via DealStageChanged), Accounts (health + renewals),
Dashboard KPIs. Premium design system (`docs/DESIGN.md`): Geist type, Zinc palette,
white-label accent, rebuilt UI library + app shell.

**Acceptance — all met (6 new Pest tests, 23 total):** lead → convert into contact
+ deal; kanban move persists + broadcasts `DealStageChanged`; a tenant custom field
appears on the contacts screen; per-entity isolation + cross-tenant 404;
duplicate-email rejection. Pint + PHPStan L6 green.

Deferred to later phases: product catalog / CPQ on deals (Phase 3); saved filters +
bulk actions + global ⌘K search wiring (palette shell exists); multi-owner pivot +
social enrichment hook (single owner_id for now).

## ✅ Phase 3 — Sales Automation
Workflow engine (`App\Modules\Automation`): per-tenant workflows with a trigger +
ordered steps (wait, send_email, create_task, update_field, add_tag, assign_owner,
webhook, condition/branch). Triggers fire on record events (lead/contact/deal
created); runs persist in `workflow_runs` + `workflow_run_steps`, are **idempotent**
(each step recorded once), **queued** via `RunWorkflowJob`, and **delayed** waits
re-dispatch a continuation (Redis/Horizon honors the delay; sync runs inline).
Tasks/reminders. CPQ: product catalog, Quotes + line items (per-line discount,
quote-level tax, **multi-currency**), `PricingService` (integer minor units), and
**branded PDF export** via dompdf.

**Acceptance — both met (6 new Pest tests, 29 total):** "new lead → wait 1 day →
send email → if still new, create task" runs end-to-end on the queue (and stops at
the branch when the condition fails); a quote renders to a PDF with correct
subtotal/discount/tax/total in the chosen currency. Pint + PHPStan L6 green.

Deferred to integration phases: calendar sync (Google/Outlook) + meeting scheduling
+ e-signature + approval chains (adapters, Phase 10); SMS/WhatsApp steps (Phase 6);
visual step editor (template-based creation for now).

## ✅ Phase 4 — Communication & Inbox
Shared inbox (`App\Modules\Communication`): Conversation + Message models with
threading (In-Reply-To + subject fallback) and read receipts. `InboundEmailService`
threads inbound mail, links it to a matching contact, and drops an Activity on that
contact's timeline. Public inbound webhook (`/webhooks/mail/{slug}`, CSRF-exempt) +
a dev "simulate inbound" tool. Thread view with reply (outbound email), **internal
notes with @mentions** (live toast to the mentioned teammate), assign + status.
Team chat over a **Reverb presence channel** (online roster + live messages).
VoIP/video remain adapter stubs (Phase 10).

**Acceptance — met (5 new Pest tests, 43 total):** inbound email lands in the
shared inbox, a reply threads into the same conversation (not a new one), and it
appears on the related contact's timeline; webhook intake works; @mention notifies;
chat broadcasts. Pint + PHPStan L6 green.

## ✅ Phase 5 — Customer Support
Ticketing (`App\Modules\Support`): Ticket + TicketReply with priority/status, an
**SLA first-response timer** (`SlaService`), **least-loaded auto-assignment**
(`AssignmentService`), and **escalation** (`EscalationService` + `tickets:check-sla`
scheduled every minute → bumps priority, reassigns to a manager, notifies).
Omnichannel intake behind a **`ChannelAdapter`** contract (`EmailChannelAdapter`
shipped) via `TicketIntakeService` (links contact + starts SLA + routes + timelines).
Public support webhook + dev simulator. Helpdesk KB with a **public self-service
portal** (`/help/{slug}`) and an **AI chatbot** answering from KB via `GeminiService`
(graceful fallback). First public agent reply stops the SLA clock.

**Acceptance — met (6 new Pest tests, 49 total):** inbound email → ticket created,
**linked to the contact**, SLA timer started, **auto-assigned**, and an SLA breach
triggers **escalation** (verified live: the scheduled command escalated the seeded
overdue ticket to urgent). Pint + PHPStan L6 green. VoIP/video remain adapter stubs (Phase 10).

## 🔜 Phase 6 — Marketing Automation (next)
Email marketing (campaign + TipTap editor, sequences, drip, A/B, open/click
analytics), landing page/funnel/form builder, SMS & WhatsApp, social scheduling +
inbox.

## ⬜ Phase 7 — AI Layer
Wire every `GeminiService` method to real features: lead scoring, recommendations,
next-best-action, deal-risk, forecasting; ticket summaries/replies/sentiment; AI
content; predictive analytics, churn, health scoring. Caching, queueing,
rate-limit handling, per-tenant AI toggle enforced.

## ⬜ Phase 8 — Analytics & Reporting
Real-time role-based dashboards (Reverb), KPI widgets, custom report builder;
sales/marketing/support analytics; export CSV/Excel/PDF.

## ⬜ Phase 9 — Collaboration & Projects
Notes, @mentions, shared tasks/calendars, activity feeds; project management with
tasks/subtasks, time tracking, kanban, file sharing.

## ⬜ Phase 10 — Billing & Integrations
Stripe + Razorpay (recurring, trials, coupons, invoices, plan gating).
Integrations behind adapters + OAuth (Gmail, Outlook, Slack, Zoom, Stripe, PayPal,
Shopify, WooCommerce, QuickBooks, Xero). Developer platform: versioned REST +
GraphQL, signed webhooks, SDK stubs, OAuth apps, Zapier-compatible triggers.

## ⬜ Phase 11 — Security, Compliance & UX Polish
2FA enforcement, SSO, IP allow-listing, field encryption at rest, device/session
tracking. GDPR (export, erasure, consent), HIPAA mode, SOC2/ISO audit trails. UX:
fast global search, Cmd-K palette, dark mode, realtime everywhere, saved filters,
bulk actions, automation template library.

## ⬜ Phase 12 — Industry Modules (installable, optional)
Real Estate, Healthcare (HIPAA-gated), Education, Recruitment — each a
self-contained, toggleable module, not part of core.
