# 18. Industry Modules

**What it's for:** Optional add-ons that tailor Knit to a specific industry. Turn one on
and it adds a new kind of record (e.g. *Properties* for Real Estate) plus its own sidebar
section. Turn it off and it disappears.

**Where to find it:** Left sidebar → **Modules** (Settings → Modules).

---

## What's available

| Module | Adds | Tracks |
|---|---|---|
| **Real Estate** | Properties | Address, price, bedrooms, type, status |
| **Recruitment** | Candidates | Name, role applied for, hiring stage, source |
| **Education** | Students | Name, program, year, status |
| **Healthcare** *(HIPAA-flagged)* | Patients | Name, date of birth, primary condition, status |

Each module is a "custom object" defined by a simple field schema (text, number, money,
select, date), and records can optionally link to a core [contact](02-contacts-and-companies.md).

---

## Enable a module

1. Go to **Modules**.
2. Find the module card and click **Enable**.

It now shows an *Enabled* tag, a record count, and an **Open** button — and a new
**Industry** section appears in your sidebar.

---

## Add and manage records

1. Click **Open** on an enabled module (e.g. Real Estate → *Properties*).
2. Click **Add {record}**.
3. Fill in the fields shown — these are built automatically from that module's schema, with
   required fields marked. Money fields take an amount; select fields offer a dropdown.
4. Save. Your record appears in the list. Delete records you no longer need.

---

## Disable a module

Click **Disable** on its card. The module vanishes from the sidebar and its record pages
become inaccessible (they return *not found*). **Your records aren't deleted** — re-enable
the module and they're back exactly as they were.

---

## Tips & gotchas

- **Disabled modules are invisible**, not erased — safe to toggle off and on.
- **Healthcare is HIPAA-flagged** — pair it with the workspace [audit log](16-security-and-compliance.md)
  for a compliance-style trail.
- **Records can link to a contact**, tying an industry record back to a real person in your
  CRM.

## Who can use it

| Action | Needs |
|---|---|
| Install / uninstall modules | `modules.manage` (Owner, Admin) |
| View module records | `modules.view` (all roles) |
| Add / delete records | `modules.use` (all roles) |
