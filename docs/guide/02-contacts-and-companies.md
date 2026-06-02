# 2. Contacts & Companies

**What it's for:** Contacts are the *people* you do business with. Companies are the
*organisations* they belong to. Together they're the foundation of your CRM — almost
everything else (deals, tickets, emails) links back to a contact.

**Where to find it:** Left sidebar → **Sales** → **Contacts** / **Companies**.

---

## Contacts

### Add a contact

1. Go to **Contacts**.
2. Click **New contact** (top-right).
3. Fill in the form:

   | Field | Notes | Required |
   |---|---|---|
   | **First name** | The person's first name | Yes |
   | **Last name** | | No |
   | **Email** | Must be unique in your workspace — Knit blocks duplicates | No |
   | **Phone** | | No |
   | **Job title** | | No |
   | **Company** | Pick from your existing companies (or leave as "—") | No |
   | *Custom fields* | Any extra fields your workspace has added appear here | No |

4. Click **Create contact**. The list refreshes with your new person.

> **Tip:** Email is optional, but if you *do* enter one it can't match an existing
> contact. This stops accidental duplicates.

### Open a contact's profile

Click any row in the contacts list. The profile page shows:

- **Left:** their photo/initials, name, job title, company, tags, and details (email,
  phone, lifecycle stage, source, owner, custom fields).
- **Deals card:** every deal linked to this person, with stage, status, and amount.
- **Timeline:** a running history of notes, calls, emails, meetings, and system events.

### Add a note to the timeline

1. On the profile, find the **timeline** card.
2. Type in the **"Add a note…"** box.
3. Click **Add**. Your note appears instantly at the top of the timeline.

### Summarise a meeting with AI

If your workspace has AI enabled, you can paste a meeting transcript and let Knit write
the summary and create follow-up tasks for you.

1. On the contact profile, click **Summarize meeting (AI)** in the timeline header.
2. Paste your transcript (up to 20,000 characters).
3. Click **Summarize**.

Knit adds a *meeting* entry to the timeline with the summary, and **automatically creates
a task for each action item** (due in 2 days, assigned to you). You'll see a message like
*"Meeting summarized — 3 task(s) created from action items."* Full details in
[AI Features](12-ai-features.md).

### Export or erase a contact (privacy / GDPR)

If you have compliance permission, the profile shows a **Data & privacy** card:

- **Export JSON** — downloads everything Knit knows about this person as a file
  (great for GDPR data-portability requests).
- **Erase** — permanently anonymises the person's personal info (name becomes
  "Redacted Contact", email/phone cleared) while keeping the record for audit purposes.
  **This can't be undone.**

See [Security & Compliance](16-security-and-compliance.md) for the full walkthrough.

---

## Companies

### Add a company

1. Go to **Companies**.
2. Click **New company** (top-right).
3. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Name** | The organisation's name | Yes |
   | **Domain** | e.g. `acme.com` | No |
   | **Industry** | e.g. *Software* | No |

4. Click **Create company**.

### Reading the companies list

The table shows each company with its **domain**, **industry**, number of **contacts**,
and a **health** bar (0–100). Health is colour-coded — green (healthy, 70+), orange
(40–69), red (below 40) — and is calculated automatically from the relationship.

> **Note:** Companies are managed from this list view; there isn't a separate company
> detail page. To see a person inside a company, open the **contact**.

---

## Tips & gotchas

- **Duplicate emails are blocked** on contacts — a safety net against double entry.
- **Health scores are automatic.** You don't set them by hand.
- **Use the search box** (left of the list) to filter as you type.

## Who can use it

| Action | Needs |
|---|---|
| View contacts/companies | `contacts.view` / `companies.view` (all roles can view contacts) |
| Add/edit contacts | `contacts.manage` (all roles) |
| Add/edit companies | `companies.manage` (Owner, Admin, Manager) |
| Export / erase a contact | `compliance.manage` (Owner, Admin) |

If a button isn't showing, your role likely doesn't include it — see [Team & Roles](14-team-and-roles.md).
