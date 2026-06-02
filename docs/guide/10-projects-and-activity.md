# 10. Projects & Activity Feed

Two collaboration tools: **Projects** (kanban boards for delivery work, with subtasks,
time tracking, and file sharing) and the **Activity Feed** (a live stream of everything
your team is doing).

**Where to find it:** Left sidebar → **Collaborate** → **Projects** / **Activity feed**.

---

## Projects

**What it's for:** Organise delivery work on a kanban board. A project can stand alone, or
hang off a won [deal](04-deals-and-pipeline.md) and inherit that deal's customer — exactly
how delivery work follows a sale.

### Create a project

1. Go to **Projects** and click **New project**.
2. Fill in:

   | Field | Notes | Required |
   |---|---|---|
   | **Name** | e.g. *Q3 Onboarding revamp* | Yes |
   | **Linked deal** | *No deal — internal project*, or pick a deal to inherit its company & contact | No |
   | **Description** | | No |

3. Click **Create**.

> **Tip:** You can also click **Start project** directly on a deal — it pre-links the deal
> and copies its company and contact. (Linking copies those once at creation; later
> changes to the deal don't re-sync.)

### The board

Each project opens to a three-column kanban: **To do**, **In progress**, **Done**. The
header shows total **time logged** and clickable chips for the linked deal, company, and
contact.

### Add tasks and subtasks

- **Add a task:** type a title in the **New task** box (optionally pick an assignee) and
  click **Add task**. It appears in *To do*.
- **Add a subtask:** click **+ Subtask** on a task card, type a title, and confirm.
- **Move work:** drag a task card between columns. The position saves automatically.

### Log time

1. Click **+ Time** on a task card.
2. Enter **Minutes** (default 30) and an optional **Note**.
3. Click **Log**. The time shows on the card and rolls up into the project total.

### Share files

- Click **Upload file** at the bottom of a project (max 10 MB per file).
- Files are listed with their size; click one to download. Downloads are secured so only
  this project's files can be fetched.

---

## Activity Feed

**What it's for:** A single, newest-first stream of everything happening across your
workspace — notes, calls, emails, meetings, and system events — so the whole team stays in
the loop.

Each entry shows who did it (or *System*), a colour-coded type tag, which record it relates
to, when, and a short preview. It shows the latest ~80 events. There's nothing to set up —
just read it.

> **Note:** the feed is a read-only view. Activities are created automatically as your team
> works (adding notes, moving deals, summarising meetings, and so on).

---

## Tips & gotchas

- **Task statuses are the three columns** — To do, In progress, Done. Progress % on the
  card list is simply done ÷ total.
- **Time format:** under an hour shows as minutes (e.g. *45m*); an hour or more shows as
  hours (e.g. *2.5h*).
- **Project ↔ deal link is a one-time copy** of company/contact at creation.

## Who can use it

| Action | Needs |
|---|---|
| View projects & activity feed | `projects.view` (all roles); the feed is open to any member |
| Create projects, add tasks, log time, upload files | `projects.manage` (all roles) |
