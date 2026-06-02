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

## 🔜 Phase 1 — Tenancy, Auth, RBAC, White-Label
Tenant resolution (subdomain + custom domain), signup/onboarding, workspace
provisioning (default roles, pipelines, ticket statuses, email templates, owner
user), Fortify auth, 2FA, SSO (OAuth), `spatie/laravel-permission` with custom
roles + team hierarchy + **field-level permissions**, audit + activity logs,
white-label (branding, logo, theme colors, custom domain, email branding), tenant
settings, real-time notifications channel.

**Acceptance:** cross-tenant isolation test passes; a non-owner role is denied
restricted actions; custom domain resolves to the right tenant; theme changes
apply live.

## ⬜ Phase 2 — Core CRM
Contacts, Companies, Leads, Pipelines/Stages, Deals (drag-drop kanban with live
Reverb updates), Accounts (parent-child, health, contracts, renewals). Custom
fields, tags/segmentation, polymorphic timeline, dedupe, global search, saved
filters, bulk actions. See `DATA_MODEL.md`.

**Acceptance:** lead → qualify → convert into contact + deal; kanban move syncs to
a second browser via Reverb; a custom field added in settings appears on forms.

## ⬜ Phase 3 — Sales Automation
Workflow engine, tasks/reminders, calendar sync (Google/Outlook adapters), meeting
scheduling, follow-up sequences/cadences. Quote/Proposal builder (templates,
dynamic pricing, **PDF**, e-signature adapter, approvals). CPQ (product catalog,
pricing rules, discounts, subscriptions, taxes, multi-currency).

**Acceptance:** "new lead → wait 1 day → email → if no reply, create task" runs on
the queue; a proposal renders to PDF with correct totals/tax/currency.

## ⬜ Phase 4 — Communication & Inbox
Shared inbox, unified timeline, email threading/tracking/read receipts, internal
comments/mentions, team chat (Reverb presence). VoIP/video behind adapters.

## ⬜ Phase 5 — Customer Support
Ticketing (SLA timers, priority routing, auto-assignment, escalation), omnichannel
intake via `ChannelAdapter` (email, chat, WhatsApp, FB/IG, voice), helpdesk (KB,
FAQ, forums, portal, AI chatbot).

**Acceptance:** inbound email creates a ticket, SLA timer starts, routing assigns
an agent, breach triggers escalation.

## ⬜ Phase 6 — Marketing Automation
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
