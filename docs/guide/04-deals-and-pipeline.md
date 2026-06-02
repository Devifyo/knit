# 4. Deals & Pipeline

**What it's for:** A deal is a sales opportunity. The pipeline is the visual board where
deals move left-to-right through your stages (New → … → Won/Lost). This is the heart of
your sales process.

**Where to find it:** Left sidebar → **Sales** → **Deals**.

---

## The pipeline board (kanban)

The Deals page is a board with one **column per stage**. Each deal is a card showing its
name, company, amount, and the owner's avatar.

### Create a deal

1. On the Deals board, click **New deal** (top-right).
2. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Deal name** | What you're calling this opportunity | Yes |
   | **Amount** | Value in your currency (you can also let products set this — see below) | No |
   | **Stage** | Which column it starts in | Yes |

3. Click **Create deal**. The card appears in its column.

### Move a deal through the pipeline

**Just drag the card** from one column to another. That's it — the change saves
instantly.

- Drag into a **Won** stage and the deal is automatically marked *won*.
- Drag into a **Lost** stage and it's marked *lost*.
- Each stage carries a **probability**, which the deal inherits when you move it.

> **Live updates:** If a teammate moves a deal while you're watching the board, it updates
> on your screen in real time and you'll see a small *"{Deal} moved"* notification. No
> refresh needed.

---

## Inside a deal (the detail page)

Click any card to open the deal. You'll see:

### AI Insight card
Click **Next action & risk** to have AI recommend your best next move and rate the risk
of losing the deal (low / medium / high) with the reasons why. See
[AI Features](12-ai-features.md).

### Deal details
Amount, stage, probability, close date, linked contact, company, and owner.

### Products (line items)
Build the deal's value out of catalogue products instead of typing a number:

1. In the **Products** card, pick a product from the dropdown.
2. Enter **Qty** and an optional **Disc %** (discount, 0–100).
3. Click **Add**.

The **deal's amount becomes the sum of its line items** automatically. Remove a line with
the **✕** button. (The unit price is snapshotted when you add it, so later catalogue
price changes won't alter past deals.)

### Quotes
Click **Create quote** to spin up a formal quote linked to this deal. See
[Quotes](05-quotes.md). When a quote is *accepted*, it updates this deal's amount and
currency.

### Projects
Click **Start project** to create a delivery project tied to this deal — it inherits the
deal's company and contact. See [Projects & Activity](10-projects-and-activity.md).

### Activity
A timeline of everything that's happened on the deal.

---

## Tips & gotchas

- **Amount vs. products:** if you add products, they *drive* the amount — they overwrite
  any number you typed.
- **Probability comes from the stage**, not from you — move the deal and it updates.
- **Won/Lost is set by the column**, not a separate button. Dragging into a Won/Lost
  stage flips the status.
- **AI insight is shown once** after you click; it isn't stored permanently on the page.

## Who can use it

| Action | Needs |
|---|---|
| View deals | `deals.view` (all roles) |
| Create / move / edit deals, add products | `deals.manage` (all roles) |
| Get AI insight | `deals.view` (all roles, if AI is enabled) |

See [Team & Roles](14-team-and-roles.md) if a button is missing.
