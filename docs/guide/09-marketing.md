# 9. Marketing — Campaigns & Forms

**What it's for:** Reach your contacts and leads with **email campaigns** (with open/click
tracking and A/B subject testing), and capture new interest with **public forms** that
turn submissions into leads and drop them into a nurture sequence.

**Where to find it:** Left sidebar → **Marketing** → **Campaigns** / **Forms**.

---

## Campaigns

### Create a campaign

1. Go to **Campaigns** and click **New campaign**.
2. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Campaign name** | Internal label | Yes |
   | **Subject (A)** | The main subject line | Yes |
   | **Subject B (optional A/B)** | A second subject to test; if set, Knit alternates A/B across recipients | No |
   | **Body (HTML)** | The email body; supports placeholders like `{{name}}` | Yes |
   | **CTA label** | Button text (default *"Learn more"*) | No |
   | **CTA URL** | Where the button links (tracked for clicks); leave blank for no button | No |
   | **Audience** | *All contacts* or *All leads* | Yes |

3. Click **Create**. The campaign is saved as a **draft**.

### Send it

1. Open the campaign.
2. Click **Send campaign**.

The status moves from *draft* → *sending* → *sent* as Knit emails each recipient. Every
email gets an invisible tracking pixel and a click-tracked CTA link.

### See how it performed

The campaign page shows live **Sent**, **Opens** (with %), and **Clicks** (with %). If you
ran an A/B test, you'll also see open rates per subject variant.

---

## How tracking works (plain English)

- **Opens:** each email contains a tiny invisible image. When the recipient's email app
  loads it, Knit records an open (counted once per person).
- **Clicks:** the CTA link goes through Knit first, which records the click and then
  forwards the person to the real destination — instantly and invisibly.
- A **click also counts as an open**, and repeats aren't double-counted, so your numbers
  stay clean.

---

## Forms (lead capture)

**What it's for:** Public landing-page forms. Every submission becomes a **lead**, and can
be enrolled automatically into a nurture **workflow**.

### Build a form

1. Go to **Forms** and click **New form**.
2. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Form name** | e.g. *Q1 Lead Capture* | Yes |
   | **Nurture sequence** | Pick a [workflow](11-automation.md) to auto-enrol submissions, or leave blank | No |

   Every form automatically collects **Name** (required), **Email** (required), and
   **Phone**.

3. Click **Create form**.

### Share it

Each form card shows its **public URL** (e.g. `/forms/q1-lead-capture-a3x2`) with a
**Copy** button. Paste that link on your site, in ads, or in emails. When someone submits:

- A **lead** is created (or matched by email if they already exist), with source
  *"Form: {name}"*.
- The submission is recorded against the form (and the count goes up).
- If you set a **nurture sequence**, the lead is enrolled into that workflow.

---

## Tips & gotchas

- **Audience is a snapshot of a list type:** *All contacts* emails your contacts; *All
  leads* emails your leads.
- **A/B testing:** fill in Subject B and Knit splits recipients between the two subjects so
  you can see which performs better.
- **The form's default fields can't be changed from the UI** — they're always Name, Email,
  Phone.
- **No duplicate leads:** a submission with an email that already exists links to that
  existing lead instead of creating a new one.

## Who can use it

| Action | Needs |
|---|---|
| View campaigns and forms | `marketing.view` (Owner, Admin, Manager) |
| Create/send campaigns, create forms | `marketing.manage` (Owner, Admin, Manager) |
| Submit a public form | Anyone — it's public |
