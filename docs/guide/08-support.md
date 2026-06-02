# 8. Support — Tickets, Knowledge Base & Help Portal

**What it's for:** Knit's support desk. Customer issues become **tickets** with SLA timers
and auto-assignment; your **knowledge base** articles power a public **help portal** with
an AI chatbot so customers can help themselves.

**Where to find it:** Left sidebar → **Support** → **Tickets** / **Knowledge base**.

---

## Tickets

### Find your way around

The Tickets list has three tabs:

- **Open** — open and pending tickets.
- **Escalated** — tickets whose SLA has been breached (overdue).
- **All** — everything.

Tickets are sorted by **priority** (urgent → high → normal → low), then by SLA due time.
Each row shows a priority dot, the subject, ticket number (e.g. **T-20260602-ABCD**), the
contact, the channel (e.g. *email*), an *Escalated* badge if overdue, the SLA countdown,
status, and assignee.

### Work a ticket

Click a ticket to open it. Across the top-right are three dropdowns that update instantly:

- **Priority** — low / normal / high / urgent
- **Assignee** — Unassigned or a teammate
- **Status** — open / pending / resolved / closed

To respond:

1. Type in the reply box.
2. Leave the **Internal note** checkbox unticked to reply to the customer (button reads
   **Send reply**), or tick it to leave a team-only note (button reads **Add note**).
3. Click the button.

### The SLA card

The sidebar shows your **first-response** status (*met* or *pending*), the **due**
countdown, the ticket status, and — if it's overdue — an *"Escalated for SLA breach"*
alert. The SLA clock stops on your first public (non-internal) reply.

### AI assist

If AI is enabled, click **Summarize & suggest reply** in the AI card. Knit gives you a
one-line summary of the issue and a few ready-to-edit reply drafts; click a suggestion to
drop it into the reply box. See [AI Features](12-ai-features.md).

### Try it without real email (demo)

Click **Simulate inbound ticket**, fill in **From**, **Subject**, **Body**, and
**Priority**, and Knit creates a ticket as if an email arrived — auto-numbered, SLA timer
started, and assigned to the least-busy agent.

> **How tickets arrive for real:** an incoming support email (via your support webhook)
> creates a ticket, links it to a matching contact, starts the SLA clock based on
> priority, and routes it to an available agent — all automatically.

---

## Knowledge Base (admin)

**What it's for:** Write help articles that show up on your public help portal and feed
the AI chatbot.

### Write an article

1. Go to **Knowledge base**.
2. Fill in **Title** and **Body**.
3. Click **Publish**. The article goes live immediately (a web-friendly link/slug is
   generated for you).

The page also shows your **public portal URL** (e.g. `/help/your-workspace`) — click it to
open the customer-facing portal. Delete an article with the **✕** next to it.

---

## Public Help Portal

**What it's for:** A self-service page your customers can visit (no login) at
**/help/your-workspace**, styled with your brand colour.

Customers can:

- **Ask a question** in the *"Ask a question…"* box and click **Ask** — the AI chatbot
  answers using your published articles. (If AI is off, it shows a friendly fallback
  saying an agent will follow up.)
- **Browse articles** — click any title to expand the full text.

---

## Tips & gotchas

- **Internal note vs. reply** works just like the inbox: notes are team-only; replies go
  to the customer.
- **SLA due times depend on priority** (urgent is the tightest). Overdue tickets show up
  in the **Escalated** tab and are flagged automatically.
- **Only published articles** appear on the portal and feed the chatbot.

## Who can use it

| Action | Needs |
|---|---|
| View tickets | `tickets.view` (all roles) |
| Reply, assign, change status, AI assist | `tickets.manage` (all roles) |
| Manage knowledge-base articles | `kb.manage` (Owner, Admin, Manager) |
| Use the public help portal | Anyone — it's public |
