# 5. Quotes (CPQ)

**What it's for:** A quote is a formal, itemised proposal you can send to a customer and
export as a branded PDF. Quotes usually hang off a deal, and accepting one updates that
deal's value.

**Where to find it:** Left sidebar → **Sales** → **Quotes** — or click **Create quote**
from inside a [deal](04-deals-and-pipeline.md).

---

## Create a quote

1. Go to **Quotes** and click **New quote** (or click **Create quote** on a deal, which
   links the two automatically).
2. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Currency** | USD, EUR, GBP, or INR | Yes (defaults to USD) |
   | **Tax rate** | A percentage applied to the subtotal | No |

3. Click **Create quote**. You land on the quote, which now has a number like
   **Q-20260602-0001** and a *draft* status.

---

## Add line items

On the quote page, use the **Add line item** card:

1. Optionally pick a product **From catalog** (this pre-fills the price), or choose
   **— custom —** to type your own.
2. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Item name** | What you're charging for | Yes |
   | **Qty** | Quantity (at least 1) | Yes |
   | **Unit price** | Price per unit | Yes |
   | **Discount %** | Optional per-line discount | No |

3. Click **Add item**. It appears in the table and the totals update.

The **Summary** panel shows your **Subtotal**, **Tax** (using the rate you set), and
**Total**, all recalculated automatically. Remove a line with the **✕**.

---

## Move a quote through its lifecycle

A quote's status flows **draft → sent → accepted** (or *declined*). The buttons you see
depend on the current status:

- While **draft** → **Mark sent** (you've sent it to the customer).
- While **sent** → **Mark accepted** (the customer said yes).
- Any time → **Download PDF** to get a branded PDF copy.

> **Accepting matters:** marking a quote *accepted* automatically updates the linked
> deal's amount and currency to match the quote. This keeps your pipeline numbers honest.

---

## Reading the quotes list

The list shows each quote's **number**, item **count**, **currency**, **total**, and
**status** (draft / sent / accepted / declined, colour-coded). Click a row to open it.

---

## Tips & gotchas

- **Numbers are generated for you** in the form `Q-YYYYMMDD-####` — you don't name quotes.
- **Catalogue prices pre-fill but can be overridden** — change the unit price after
  picking a product if you need to.
- **The PDF carries your branding** (workspace name and brand colour) — set those under
  [Branding](19-branding-and-workspace.md).

## Who can use it

| Action | Needs |
|---|---|
| View quotes / download PDF | `quotes.view` (all roles) |
| Create quotes, add items, change status | `quotes.manage` (all roles) |
