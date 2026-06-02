# 15. Billing & Plans

**What it's for:** Manage your workspace's subscription — see your current plan, switch
plans, apply a coupon, cancel, and download invoices.

**Where to find it:** Left sidebar → **Billing** (Settings → Billing).

---

## Your current plan

The top card shows your **plan name**, **status** (active / trialing / past due /
canceled), your **renewal** (or trial-end) date, and your **seats** used vs. allowed
(e.g. *2/5*, or *Unlimited*).

---

## Change your plan

1. In the **Plans** section, find the plan you want. Each card shows its **price**,
   **seat limit**, and **features**.
2. Click **Choose {plan}** (or **Switch to {plan}**).
3. (Optional) Enter a **Coupon code** before subscribing — invalid or expired codes are
   rejected with a clear message.

Your current plan is highlighted and tagged **Current**.

---

## Cancel

If you have billing permission, the current-plan card shows a **Cancel subscription**
button. Cancelling stops the renewal.

---

## Invoices

The **Invoices** section lists every invoice (newest first) with its number, status
(paid / open / void), issue date, and total. Click an invoice number to **download it as a
PDF**.

---

## Tips & gotchas

- **Seats gate invitations.** If your plan's seats are full, you can't invite new members
  until you upgrade — Knit will point you here. See [Team & Roles](14-team-and-roles.md).
- **Feature availability** can depend on your plan (the features list on each plan card
  shows what's included).
- **Plans are pre-configured.** You choose from the available plans; you don't create them
  in the app.

## Who can use it

| Action | Needs |
|---|---|
| View billing & download invoices | `billing.view` (Owner, Admin) |
| Subscribe / change plan / cancel | `billing.manage` (Owner, Admin) |
