# 7. Inbox & Team Chat

Two ways your team communicates: the **Shared Inbox** (with customers, by email) and
**Team Chat** (with each other, in real time).

**Where to find it:** Left sidebar → **Communicate** → **Inbox** / **Team chat**.

---

## Shared Inbox

**What it's for:** One shared mailbox the whole team can see. Customer emails land here as
**conversations**; anyone can reply, leave private notes for colleagues, assign owners,
and track status.

### Find your way around

At the top of the inbox are three tabs:

- **Open** — active conversations.
- **Assigned to me** — the ones you own.
- **All** — everything.

Each conversation in the list shows its subject, the sender, an unread badge, a status tag
(open / closed / snoozed), the assignee, and how long ago the last message arrived.

### Read and reply to a customer

1. Click a conversation to open it. Opening it marks incoming messages as **read**.
2. At the bottom, make sure **Reply** mode is selected.
3. Type your message (the box says *"Type a reply to the customer…"*).
4. Click **Send reply**. It's emailed to the customer right away.

### Leave an internal note (team-only)

Notes are for your colleagues — the customer never sees them.

1. At the bottom, switch to **Internal note** mode.
2. Type your note. You can **@mention** a teammate to notify them.
3. Click **Add note**. It appears in the thread with an *"internal note"* badge.

### Assign and close

- **Assign:** use the assignee dropdown in the header to hand the conversation to a
  teammate (or leave it Unassigned).
- **Status:** click **Close** when it's resolved (and **Reopen** if it comes back).

### Try it without a real mailbox (demo)

Click **Simulate inbound email** to create a fake incoming email. Fill in **From**,
**Subject**, and **Body**, and Knit threads it in exactly as a real one would — handy for
testing and demos. (Real email arrives automatically once your mail provider is wired up.)

> **How threading works:** replies to an existing email are matched to the right
> conversation automatically (by email headers, falling back to a matching subject).
> If the sender matches a known contact, the conversation is linked to them and added to
> that contact's timeline.

---

## Team Chat

**What it's for:** Quick, real-time messaging for your whole team — like a group channel.
It is **not** customer-facing.

### Send a message

1. Go to **Team chat**.
2. Type in the box (*"Message your team…"*).
3. Press Enter or click **Send**. Your message shows immediately.

### Who's around

The header shows **avatars of everyone currently online** and a live count (e.g.
*"3 online"*). People appear and disappear as they open and leave the chat. The last 50
messages load when you arrive.

---

## Tips & gotchas

- **Reply vs. internal note:** a *reply* is emailed to the customer; an *internal note*
  stays inside Knit for your team. Watch which mode you're in before sending.
- **Chat isn't saved to contacts** — it's ephemeral team talk, separate from the inbox.
- **@mentions** in inbox notes notify the person you tag.

## Who can use it

| Action | Needs |
|---|---|
| View the inbox | `inbox.view` (all roles) |
| Reply, note, assign, change status | `inbox.manage` (all roles) |
| Use team chat | `chat.use` (all roles) |
