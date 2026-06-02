# 17. Developer — Webhooks

**What it's for:** Send your own systems a signed HTTP message ("webhook") whenever
something happens in Knit — a contact is created, a deal is won, an invoice is paid, and so
on. This is how you connect Knit to other tools.

**Where to find it:** Left sidebar → **Developer** (Settings → Webhooks).

> This is a technical feature aimed at developers. If that's not you, you can safely skip
> this page.

---

## Register an endpoint

1. Go to **Developer**.
2. Under **Add endpoint**, fill in:

   | Field | Notes |
   |---|---|
   | **Payload URL** | The HTTPS URL in your system that will receive the events |
   | **Events** | Tick the events you want (at least one) |

3. Click **Add endpoint**.

### Events you can subscribe to

Grouped for convenience (use **Select all** / **Clear all** to bulk-toggle):

- **Contacts:** `contact.created`, `contact.updated`, `contact.deleted`
- **Companies:** `company.created`, `company.updated`
- **Leads:** `lead.created`, `lead.updated`, `lead.converted`
- **Deals:** `deal.created`, `deal.updated`, `deal.won`, `deal.lost`
- **Sales:** `quote.accepted`, `invoice.paid`
- **Support:** `ticket.created`
- **Productivity:** `task.completed`, `project.created`

### Save your signing secret

When you add an endpoint, Knit shows a **signing secret** (like `whsec_…`) **once**. Copy
it now and store it safely — it's never shown again. You use it to verify that incoming
webhooks really came from Knit (every request includes an `X-Knit-Signature` HMAC header
computed from this secret).

---

## Test an endpoint

Each endpoint has a **Send test** button. Click it to queue a sample `ping` event so you
can confirm your system receives and validates it.

---

## See what was delivered

The **Recent deliveries** section lists the last 20 attempts across all endpoints, each
showing **OK** or **Failed**, the event, the URL, the HTTP status code, and when it
happened — so you can debug quickly.

Remove an endpoint with **Delete**.

---

## Tips & gotchas

- **The signing secret is shown only once.** If you lose it, delete the endpoint and create
  a new one.
- **Always verify the signature** on your side using the secret before trusting a payload.
- **Pick only the events you need** to keep traffic tidy.

## Who can use it

| Action | Needs |
|---|---|
| View endpoints & deliveries | `integrations.view` (Owner, Admin) |
| Add, test, or delete endpoints | `integrations.manage` (Owner, Admin) |
