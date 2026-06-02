# 12. AI Features

**What it's for:** Knit has AI (powered by Google Gemini) sprinkled into the places it
helps most — scoring leads, coaching deals, drafting support replies, and summarising
meetings. There's no separate "AI page"; the features live where you already work.

---

## Turning AI on or off

AI is controlled **per workspace** with a single switch:

1. Go to **Settings → Branding**.
2. Tick (or untick) **Enable AI features (Gemini) for this workspace**.
3. Click **Save settings**.

> **If AI is off** (or a call ever fails), nothing breaks — each feature simply returns a
> safe fallback message and you carry on. Results are also briefly cached, so re-running
> the same thing is instant.

---

## The four AI features

### 1. Lead scoring
**Where:** [Leads](03-leads.md) list → **Score with AI** on a lead's row.
**What you get:** a 0–100 score for how likely the lead is to convert, plus a few short
reasons. The score updates on the lead so you can prioritise.

### 2. Deal insight (next action & risk)
**Where:** open a [deal](04-deals-and-pipeline.md) → **Next action & risk**.
**What you get:** the single best next action with a one-line rationale, and a risk rating
(low / medium / high) with the factors behind it.

### 3. Ticket assist
**Where:** open a [ticket](08-support.md) → **Summarize & suggest reply**.
**What you get:** a one-line summary of the issue plus a few ready-to-edit reply drafts.
Click a draft to drop it into your reply box.

### 4. Meeting summary
**Where:** open a [contact](02-contacts-and-companies.md) → **Summarize meeting (AI)**.
**What you do:** paste a meeting transcript (up to 20,000 characters) and click
**Summarize**.
**What you get:** a meeting summary added to the contact's timeline, **and a follow-up
task created for each action item** (due in 2 days, assigned to you).

---

## Tips & gotchas

- **AI is a workspace-wide switch** — there's no per-feature toggle. If a teammate can't
  use AI, check the Branding setting.
- **Results appear right where you clicked** (a flash message or an updated field); they
  aren't a permanent report.
- **It fails gracefully.** Rate limits or outages just return a friendly fallback — your
  work is never blocked.
- **Setup note for admins:** AI also needs a Gemini API key configured on the server. If
  the toggle is on but nothing happens, ask whoever manages your deployment to confirm the
  key is set.

## Who can use it

AI buttons appear inside features you already have access to — so lead scoring needs
`leads.manage`, ticket assist needs `tickets.manage`, and so on. See the relevant feature
guide and [Team & Roles](14-team-and-roles.md).
