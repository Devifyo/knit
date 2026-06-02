# 11. Automation — Workflows & Tasks

**What it's for:** **Workflows** do repetitive work for you automatically — send a
follow-up email, create a task, add a tag, branch on a condition — whenever a record is
created. **Tasks** are your to-do list (manual ones you add, plus ones workflows create).

**Where to find it:** Left sidebar → **Automate** → **Workflows** / **Tasks**.

---

## Workflows

### The idea in one sentence

A workflow says: *"When **this** happens (a trigger), do **these things** in order (the
steps)."*

### Triggers — what starts a workflow

A workflow fires automatically when a matching record is created:

- **Lead created**
- **Contact created**
- **Deal created**

> Triggers only fire on **new** records going forward. Existing records aren't
> retroactively run. To enrol leads from a marketing form on demand, link the form to the
> workflow instead — see [Marketing](09-marketing.md).

### Steps — what a workflow can do

Add as many steps as you like, in order. The step types are:

| Step | What it does | You set |
|---|---|---|
| **Wait / delay** | Pause before the next step | Days (and optional hours) |
| **Send email** | Email the record's contact | Subject, body, who to send to |
| **Create task** | Make a follow-up task | Title, due-in days |
| **If / else (branch)** | Continue only if a condition is true | A rule (or set of AND/OR rules) |
| **Add tag** | Tag the record | The tag |
| **Update field** | Change a field's value | Field, new value |
| **Assign owner** | Hand the record to a user | The user |
| **Call webhook** | Notify an outside system | URL, optional payload |

For an **If / else** step you build a rule like *field → operator → value*. Operators
include equals, not-equals, contains, greater/less than, and is-empty checks, combined
with **AND**/**OR**. If the condition is false, the workflow stops there.

### Build a workflow

1. Go to **Workflows** and click **New workflow**.
2. Enter a **name** and choose the **trigger** (lead/contact/deal created).
3. Tick **Enabled** so it runs once saved.
4. Click **+ Add step** for each action. Reorder with the ↑ ↓ arrows; remove with **✕**.
5. Click **Create workflow** (or **Save changes** when editing).

### Test it before going live

On the Workflows list, click **Test run**. Knit runs the workflow against your most recent
matching record and takes you to the run history so you can see each step's result — no
need to wait for a real trigger, and it works even while disabled.

### Watch it run

Click **View runs** on a workflow to see recent runs. Each run shows its overall status
(*completed, running, waiting, stopped, failed*) and a step-by-step breakdown
(*pending / done / skipped / failed*).

> **Wait steps resume on their own.** A "Wait 2 days" step pauses the run and Knit
> automatically picks it back up later — you don't do anything. Steps are also safe to
> retry: each one only runs once.

### Turn on/off or delete

Use the **toggle** on each workflow to enable/disable it. Workflows only fire while
**enabled**.

---

## Tasks

**What it's for:** A simple to-do list. You add tasks by hand, and workflows (and AI
meeting summaries) can add them too.

### Add a task

1. Go to **Tasks**.
2. Type a title in the **"Add a task…"** box.
3. Click **Add**. It's assigned to you and due tomorrow by default.

### Complete a task

Click the **checkbox** next to a task to mark it done (it gets a strikethrough). Click
again to reopen it. Each task shows its title, due date, and assignee.

> Tasks created by workflows or AI appear here alongside your manual ones, already linked
> to the record they came from.

---

## Tips & gotchas

- **Enabled = live.** A workflow that isn't enabled won't fire (but you can still test it).
- **A false condition stops the run** — that step shows as *skipped*, not failed.
- **Triggers are forward-only** — they don't run against records that already existed.

## Who can use it

| Action | Needs |
|---|---|
| View workflows | `workflows.view` (Owner, Admin, Manager) |
| Create/edit/test/toggle workflows | `workflows.manage` (Owner, Admin, Manager) |
| View tasks | `tasks.view` (all roles) |
| Add/complete tasks | `tasks.manage` (all roles) |
