# 19. Branding & Workspace Settings

**What it's for:** Make Knit look like *your* company — set the workspace name, brand
colour, and logo — and flip the master switch for AI features. Changes apply live across
the whole workspace.

**Where to find it:** Left sidebar → **Settings** (Settings → Branding).

---

## Change your branding

1. Go to **Settings → Branding**.
2. Update any of:

   | Field | Notes |
   |---|---|
   | **Workspace name** | Your company/workspace name (shown around the app and on PDFs) |
   | **Brand color** | A hex colour like `#4F46E5` — pick with the colour picker or type it. A live preview shows immediately. |
   | **Logo** | An image file, up to 2 MB |
   | **Enable AI features (Gemini)** | The master on/off switch for all AI features |

3. Click **Save settings**.

Your brand colour flows through the whole app — buttons, links, tags, and your customer-
facing pages (quotes, the help portal, public forms, report PDFs).

---

## The AI switch

The **Enable AI features (Gemini) for this workspace** checkbox is the single control for
all AI in Knit (lead scoring, deal insight, ticket assist, meeting summaries). Turn it on
to use those features; turn it off and they quietly stop offering suggestions. See
[AI Features](12-ai-features.md).

---

## Dark mode

Separately from branding, there's a **dark-mode toggle** in the top bar. It's a personal
preference saved on your own device, so it doesn't affect teammates.

---

## Tips & gotchas

- **Changes are live** — no publish step. Save and it's applied everywhere.
- **Brand colour must be a valid hex** (e.g. `#1A73E8`); the live preview confirms it.
- **AI also needs a server-side key.** If the toggle is on but AI does nothing, ask your
  deployment admin to confirm the Gemini API key is configured.

## Who can use it

| Action | Needs |
|---|---|
| View branding settings | Any member can view |
| Save branding / toggle AI | `branding.update` (Owner, Admin) |
