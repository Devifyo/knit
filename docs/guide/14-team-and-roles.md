# 14. Team & Roles

**What it's for:** Invite teammates into your workspace and control what each one can see
and do. This is the page that decides which buttons and menus everyone gets.

**Where to find it:** Left sidebar → **Members**.

---

## The four roles

Every member has one of four roles. From most to least access:

| Role | In short |
|---|---|
| **Owner** | Full control of everything, including managing roles. (The person who signed up.) |
| **Admin** | Everything except managing roles. |
| **Manager** | Full CRM, marketing, automation, and projects. No billing, security, audit, or compliance. |
| **Agent** | Day-to-day CRM work: contacts, leads, deals, tasks, quotes, inbox, chat, projects. No admin areas. |

### What each role can do

| Area | Owner | Admin | Manager | Agent |
|---|:--:|:--:|:--:|:--:|
| Contacts, Leads, Deals, Tasks, Quotes | ✅ | ✅ | ✅ | ✅ |
| Inbox, Team chat, Tickets | ✅ | ✅ | ✅ | ✅ |
| Projects, Analytics, Reports | ✅ | ✅ | ✅ | ✅ |
| Companies (create/edit) | ✅ | ✅ | ✅ | — |
| Workflows (automation) | ✅ | ✅ | ✅ | — |
| Knowledge base | ✅ | ✅ | ✅ | — |
| Marketing (campaigns/forms) | ✅ | ✅ | ✅ | — |
| Members & invitations | ✅ | ✅ | — | — |
| Branding / Settings | ✅ | ✅ | — | — |
| Billing | ✅ | ✅ | — | — |
| Security policy, Audit log, GDPR | ✅ | ✅ | — | — |
| Webhooks (Developer) | ✅ | ✅ | — | — |
| Install/uninstall Industry modules | ✅ | ✅ | — | — |
| Manage roles | ✅ | — | — | — |

> Owners bypass every permission check by design. If you're unsure why someone can't see a
> feature, match it to this table — it's almost always their role.

---

## Invite a teammate

1. Go to **Members** and click **Invite member**.
2. Fill in:

   | Field | Notes |
   |---|---|
   | **Email** | Must not already belong to a member of this workspace |
   | **Role** | Owner, Admin, Manager, or Agent |

3. Click **Create invitation**.

Knit creates an invite link (valid for **7 days**). Copy it with the **Copy** button and
send it to your teammate however you like. They open the link, set a name and password,
and join with the role you chose — see [Getting Started](01-getting-started.md#joining-a-workspace-by-invitation).

> **Seat limits:** if your plan has a seat limit and it's full, you'll see *"Your plan
> includes N seats. Upgrade in Billing to add more."* See [Billing](15-billing.md).

---

## Manage members & invites

The **Members** page lists everyone with their email and role tags. Pending invitations
are listed separately with their link, role, and expiry — you can **Copy** a link again or
**Revoke** an invite that's no longer needed.

---

## Tips & gotchas

- **Roles are fixed** (Owner/Admin/Manager/Agent) and assigned per person; there's no
  custom-role builder in the UI.
- **Invites expire in 7 days.** Revoke and re-send if one lapses.
- **An email can only be a member once** per workspace.

## Who can use it

| Action | Needs |
|---|---|
| View members | `members.view` (Owner, Admin, Manager) |
| Invite / revoke | `members.invite` (Owner, Admin) |
