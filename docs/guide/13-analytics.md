# 13. Analytics — Dashboard & Reports

Two ways to see your numbers: the **Dashboard** (a live at-a-glance summary) and
**Reports** (filtered tables you can export).

**Where to find it:** Left sidebar → **Dashboard** and **Reports** (top of the sidebar).

---

## Dashboard

**What it's for:** Your workspace's live pulse. It updates by itself as deals change — no
refresh needed.

### What's on it

- **KPI cards** across the top:
  - **Open deals** — how many are in your pipeline.
  - **Pipeline value** — the total value of open deals.
  - **Won this month** — count, revenue, and win rate.
  - **Open tickets** — current support load.
- **Pipeline by stage** — a bar for each stage showing how many deals sit there.
- **Top reps leaderboard** — your best performers by won revenue (top 5).

> **Live updates:** when anyone moves a deal (e.g. to Won), the dashboard refreshes itself
> in real time.

> **Leaderboard is manager-only:** the *Top reps* widget only shows to Owners, Admins, and
> Managers. Agents see the rest of the dashboard without it.

---

## Reports

**What it's for:** Build a filtered list of **Deals** or **Leads** and export it as CSV,
Excel, or PDF.

### Build a report

1. Go to **Reports**.
2. Choose what to report on:

   | Filter | Options |
   |---|---|
   | **Report** | *Deals* or *Leads* |
   | **Owner** | A specific teammate, or *Anyone* |
   | **Status** | Deals: open / won / lost · Leads: new / working / qualified / unqualified |
   | **From** / **To** | A created-date range |

3. Click **Run report**. Summary cards and a data table update on the page.

### Export it

Click **CSV**, **Excel**, or **PDF**. The download uses your current filters, and the
filename includes the date. The PDF carries your workspace name and brand colour.

---

## Tips & gotchas

- **Exports respect your filters** — change the filters and export again to get a different
  slice.
- **Reports cap at 5,000 rows** — narrow your date range or status if you have more.
- **Dashboard vs. Reports:** the dashboard is for a quick live glance; reports are for
  pulling specific, exportable data.

## Who can use it

| Action | Needs |
|---|---|
| View the dashboard | All roles (leaderboard: Owner/Admin/Manager only) |
| View reports | `analytics.view` (all roles) |
| Export reports | `reports.export` (all roles) |
