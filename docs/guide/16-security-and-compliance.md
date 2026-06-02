# 16. Security & Compliance

**What it's for:** Protect your account and your workspace — turn on two-factor
authentication, restrict access by IP, review sign-ins, read the audit log, and handle
GDPR data requests.

**Where to find it:** Left sidebar → **Security** (Settings → Security), plus the **Audit
log** tab and GDPR buttons on a contact's profile.

---

## Two-factor authentication (2FA) — for your own account

Anyone can secure their own login:

1. Go to **Security**. Under *Two-Factor Authentication* you'll see **Disabled**.
2. Click **Enable 2FA**.
3. A **QR code** and a set of **recovery codes** appear. Scan the QR code with an
   authenticator app (Google Authenticator, Microsoft Authenticator, etc.) and **save the
   recovery codes somewhere safe** — they get you in if you lose your phone.
4. Enter the 6-digit code from the app and click **Confirm**.

Done — you'll enter a code from the app each time you log in. To turn it off later, click
**Disable**.

### The login code screen

When 2FA is on, after your password you'll see the *Two-factor authentication* screen.
Enter your 6-digit app code and click **Verify**. Lost your phone? Click **Use a recovery
code** and enter one of your saved codes instead.

---

## Workspace policy — for Owners/Admins

Scroll to **Workspace policy** (only visible if you can manage security):

### Require 2FA for everyone

Tick **Require two-factor authentication for all members** and **Save policy**. From then
on, any member without 2FA is sent to set it up before they can use the workspace.

### IP allow-list

Restrict access to specific networks. In the **IP allow-list** box, enter **one IP or CIDR
range per line**, for example:

```
203.0.113.4
10.0.0.0/8
```

Leave it empty to allow all. The page shows **your current IP** to help.

> **Lockout guard:** if you set a non-empty allow-list that *doesn't* include your own
> current IP, Knit refuses to save it (*"The allow-list must include your current IP …"*).
> This stops you from accidentally locking yourself out. Anyone whose IP isn't on the list
> is blocked.

---

## Recent sign-ins

The **Recent sign-ins** section lists the devices and IPs that accessed your account, with
timestamps — a quick way to spot anything you don't recognise.

---

## Audit log

**Where:** Settings → **Audit log** (Owner/Admin).

A read-only history of every change to **contacts, deals, and leads** — what changed, who
did it, their IP, and when. Events are colour-coded: created (green), updated (blue),
deleted (red), restored (orange). It shows the latest 100 entries for your workspace.

---

## GDPR — export or erase a contact

**Where:** open a [contact](02-contacts-and-companies.md) → **Data & privacy** card
(needs compliance permission).

- **Export JSON** — download everything about the contact (profile, company, activities)
  as a file, for data-portability requests.
- **Erase** — anonymise the contact's personal data (name becomes *Redacted Contact*;
  email and phone cleared) while keeping the record for your audit trail. **This is
  permanent and can't be undone.**

---

## Tips & gotchas

- **2FA is per person; requiring it is per workspace.** You enroll your own; an Owner/Admin
  can force everyone.
- **The IP lockout guard is your friend** — it won't let you save a list that would lock
  you out.
- **Erase is irreversible.** Export first if you might need the data.

## Who can use it

| Action | Needs |
|---|---|
| Enable your own 2FA / see your sign-ins | Any member |
| Set the workspace security policy | `security.manage` (Owner, Admin) |
| View the audit log | `audit.view` (Owner, Admin) |
| Export / erase a contact | `compliance.manage` (Owner, Admin) |
