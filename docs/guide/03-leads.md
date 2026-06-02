# 3. Leads

**What it's for:** A lead is a potential customer you haven't qualified yet. The Leads
screen is where new interest lands — from a web form or entered by hand — so you can
score it, work it, and convert the good ones into contacts and deals.

**Where to find it:** Left sidebar → **Sales** → **Leads**.

---

## The three ways a lead is created

1. **Someone fills in your public capture form** (no login needed) — see below.
2. **You add one by hand** with the *Capture lead* button.
3. **A marketing form submission** — covered in [Marketing](09-marketing.md).

Every new lead also **fires your `lead.created` automations** (if you've built any) — see
[Automation](11-automation.md).

---

## Your public lead-capture form

Every workspace has a shareable, public form at **/f/your-workspace-slug**. Anyone can
fill it in without logging in, and it's styled with your brand colour.

- On the **Leads** page, a banner shows the form's URL with a **Copy link** button.
- The form asks for **Name** (required), **Email** (required), **Phone**, and **Message**.
- A submission creates a new lead with status *new*, a starting score of 40, source
  *"Capture form"*, and the message saved on the lead.

> **Use it anywhere:** put the link on your website, in an email signature, or in a social
> bio. Each submission shows up instantly in your Leads list.

---

## Add a lead by hand

1. Go to **Leads**.
2. Click **Capture lead** (top-right).
3. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Name** | | Yes |
   | **Email** | Must be unique in your workspace | Yes |
   | **Phone** | | No |
   | **Source** | Where it came from, e.g. *Website* | No |

4. Click **Capture**. The lead appears with status *new*.

---

## Reading the leads list

Each row shows the lead's **name**, **email**, **source**, **score**, and **status**.

- **Score** is 0–100 and colour-coded: green (60+), orange (30–59), grey (below 30).
- **Status** is one of: *new*, *working*, *qualified*, *unqualified*. Once converted, the
  row shows a *converted* tag instead.

---

## Score a lead with AI

If AI is enabled for your workspace, Knit can rate how likely a lead is to convert.

1. On a lead's row, click **Score with AI**.
2. Knit analyses the lead and updates its **score** (0–100), saving a short list of
   reasons behind the number.

This helps you prioritise who to call first. See [AI Features](12-ai-features.md).

---

## Convert a lead

When a lead is worth pursuing, convert it into a real contact (and a deal).

1. On the lead's row, click **Convert**.
2. Knit creates a **Contact** from the lead and, if you have a default pipeline, also
   creates a **Deal** named *"{lead name} — Opportunity"* in the first stage.
3. You're taken straight to the new contact's profile.

> **What if there's no pipeline?** The contact is still created; just no deal is made.
> The lead is then marked *converted* and won't show a Convert button anymore.

---

## Tips & gotchas

- **The public form and manual capture both feed the same list** — and both can kick off
  your `lead.created` workflows.
- **Scoring doesn't change the status** — it only updates the number and its reasons.
- **Converting is one-way.** A converted lead becomes a contact; you work it from there.

## Who can use it

| Action | Needs |
|---|---|
| View leads | `leads.view` (all roles) |
| Capture / score a lead | `leads.manage` (all roles) |
| Convert a lead | `leads.convert` (all roles) |

The public capture form needs no permission — it's open to the world by design.
