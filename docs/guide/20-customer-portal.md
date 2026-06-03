# 20. Customer Portal

**What it's for:** Give your customers their own secure login to a branded
self-service portal where they can see their deals, quotes, projects and support
tickets — and open new tickets — without emailing your team.

**Where you manage it:** open any **Contact** → the **Customer portal** card (right side).

> Customers do **not** sign in where your staff do. They use a separate portal at
> **`/portal`** with their own email + password — they can never see your internal
> data, other customers' data, or your staff app.

---

## Invite a customer

1. Open the **Contact** you want to give access to (they must have an **email**).
2. In the **Customer portal** card, click **Enable portal**.
3. Knit **emails them an activation link** to set their password. The link is also
   shown on the card so you can copy and share it yourself if needed.
4. The status shows **Invited** until they set a password, then **Active**.

> **Email not arriving?** Email uses your workspace's mail settings. If you haven't
> added your own SMTP yet, it goes through Knit's default server — set up your own
> under **Settings → Email** (see [Branding & Workspace](19-branding-and-workspace.md)).
> You can always copy the activation link from the card and send it manually.

---

## What the customer can do

Once activated, the customer signs in at **`/portal`** and gets:

- **Overview** — a dashboard of their open tickets, deals and recent activity.
- **Deals** — a read-only view of their deals and current stage/status.
- **Quotes** — view quotes, **download the PDF**, and **accept or decline** online.
  Accepting updates the linked deal automatically.
- **Projects** — delivery progress: a progress bar and tasks grouped *To do /
  In progress / Done*.
- **Tickets** — open new support tickets and reply to existing ones. (They only
  ever see public replies — your **internal notes stay private**.)
- **Profile** — update their own name, phone and job title.

---

## Managing access

From the same **Customer portal** card:

- **Reset password link** — sends the customer a fresh set-password link (use this
  if they're locked out). Customers can also reset it themselves via **"Forgot
  password?"** on the portal login.
- **Disable** — instantly revokes portal access. Their records are untouched; you
  can re-enable anytime.

---

## Tips & gotchas

- **Strict isolation:** a customer only ever sees records linked to *them*. There's
  no way for one customer to see another's tickets, deals or quotes.
- **Tickets opened in the portal** flow into your normal **Support** queue with an
  SLA and auto-assignment, exactly like email tickets — see [Support](08-support.md).
- **Accepting a quote** in the portal marks it accepted and syncs the deal's amount,
  the same as if your team did it — see [Quotes](05-quotes.md).
- **Invoices are intentionally not shown** in the portal (those are your workspace's
  own subscription billing, not customer-facing).

## Who can use it

| Action | Needs |
|---|---|
| Invite / reset / disable portal access | `contacts.manage` (all roles) |
| Configure workspace email (SMTP) | `settings.update` (Owner, Admin) |
