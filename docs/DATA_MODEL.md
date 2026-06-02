# Data Model — Knit CRM

This is the **spec** for the core CRM entities (implemented in Phase 2). Every
tenant-owned table carries an indexed `tenant_id` FK and uses the
`BelongsToTenant` trait. All tables are `utf8mb4`, timestamps in UTC. Money is
stored as integer **minor units** + a currency code (never floats).

> Status: specification only. Migrations/factories/seeders are written in Phase 2.

---

## Contact
| Field | Type | Notes |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | indexed |
| first_name, last_name | string | |
| email | string | unique per tenant; dedupe on insert |
| phone | string | dedupe on insert |
| job_title | string | nullable |
| company_id | FK → companies | nullable |
| lifecycle_stage | enum | subscriber/lead/MQL/SQL/customer/evangelist |
| source | string | |
| social_profiles | json | |
| custom_fields | json | per-tenant field values (see Custom Fields) |
| timestamps, soft deletes | | |

Relations: `owner_ids` many-to-many → users (pivot `contact_user`); `tags`
(polymorphic); `activities` (polymorphic timeline).

## Company
id, tenant_id, name, domain, industry, size, annual_revenue (minor units),
address (json), `parent_company_id` (self FK, parent-child), health_score
(int 0–100), `owner_ids` (pivot), tags (polymorphic), custom_fields (json),
timestamps, soft deletes.

## Lead
id, tenant_id, name, email, phone, source, status (enum:
new/working/qualified/unqualified), score (int), assigned_user_id (FK), pipeline_id
(nurture, FK), converted_to_contact_id (nullable FK), conversion data (json),
custom_fields (json). **Duplicate detection on email/phone before insert.**
Emits `LeadConverted` on conversion.

## Pipeline & Stage
- **Pipeline:** id, tenant_id, name, type (`deal` | `lead_nurture`). Multiple per
  tenant.
- **Stage:** id, pipeline_id (FK), name, order (int), probability (int %),
  type (enum: open/won/lost).

## Deal
id, tenant_id, name, pipeline_id (FK), stage_id (FK), amount (minor units) +
currency, probability (from stage, overridable), expected_close_date, contact_id
(FK), company_id (FK), owner_id (FK), status (open/won/lost), custom_fields (json),
timestamps. `products` pivot (`deal_product`: qty, unit_price minor, discount).
Emits `DealStageChanged` (broadcast to the pipeline kanban channel).

## Account (enterprise wrapper over Company)
id, tenant_id, company_id (FK), health_score (int 0–100). `contracts` hasMany
(value minor units, start/end dates). `renewals` (date + status). Upsell/
cross-sell opportunities. Churn + health scored via `GeminiService` (Phase 7).

## Activity / Timeline (polymorphic)
id, tenant_id, type (note/call/email/meeting/task/system), `subject_type` +
`subject_id` (morphs to Contact/Lead/Deal/Account/Ticket), body, due_at,
completed_at, user_id (FK), timestamps. The unified timeline reads from this one
table.

## Custom Fields
- **CustomFieldDefinition:** id, tenant_id, entity (contact/company/lead/deal/…),
  key, label, type (text/number/date/select/multiselect/boolean/…), options (json),
  required (bool). Field-level permissions reference these (Phase 1).
- **Values:** stored on each entity's `custom_fields` json column (EAV table is an
  option if querying by custom field becomes necessary).

## Tags (polymorphic)
id, tenant_id, name, color. Pivot `taggables` (`tag_id`, `taggable_type`,
`taggable_id`).

---

## Cross-cutting

- **Users & Roles:** `spatie/laravel-permission` (Phase 1) — custom roles, team
  hierarchy, field-level permissions.
- **Audit:** `owen-it/laravel-auditing` (record-level) + `spatie/laravel-activitylog`
  (activity feed).
- **Media:** `spatie/laravel-medialibrary` for attachments/files.
- **ai_outputs:** entity_type, entity_id, type, prompt_hash, response (json),
  tokens (int), created_at — audit + cache for all `GeminiService` results.
