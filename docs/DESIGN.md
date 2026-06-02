# Design System: Knit CRM

A calibrated, product-grade design language for a multi-tenant CRM. The target is
the quiet confidence of **Linear / Stripe / Vercel**: a near-monochrome neutral
canvas, one tenant-controlled accent, generous breathing room, hairline structure
over heavy borders, and motion you feel more than see. This is a **software UI**,
not a marketing site — density is "daily-app balanced" (≈5), variance is restrained
(symmetry is fine in a tool), motion is subtle (≈3).

White-label: the accent is the CSS variable `--brand` (set per tenant). Every accent
usage must read correctly for any hue the tenant picks, so the accent is used
**flat** (fills, 1px rings, text) — never as a glow or gradient.

## 1. Atmosphere
Clinical but warm. A bright Zinc canvas, crisp hairlines, ink-black text used
sparingly for hierarchy. Surfaces float on diffuse, low-opacity shadows tinted to
the canvas — never hard drop shadows. Interactions are tactile and quick (120–180ms),
with a gentle spring on the few things that move (kanban cards, toasts, menus).

## 2. Color Palette & Roles
Neutral base is **Zinc** (one family — no warm/cool drift).

- **Canvas** `#FAFAFA` (zinc-50) — app background
- **Surface** `#FFFFFF` — cards, panels, table rows
- **Surface Sunken** `#F4F4F5` (zinc-100) — table headers, inset wells, hover
- **Ink** `#18181B` (zinc-900) — primary text, headings
- **Ink Soft** `#3F3F46` (zinc-700) — body text
- **Muted** `#71717A` (zinc-500) — secondary text, metadata, icons
- **Faint** `#A1A1AA` (zinc-400) — placeholders, disabled
- **Hairline** `#E4E4E7` (zinc-200) — borders, dividers (1px)
- **Hairline Soft** `#F0F0F1` — internal dividers
- **Brand** `var(--brand)` — single accent: primary buttons, active nav, focus rings, links. Default `#4f46e5`.
- **Brand Wash** `color-mix(in srgb, var(--brand) 10%, white)` — selected rows, active-nav background, badge fills
- Semantic (used only for status): **Positive** `#16a34a`, **Warning** `#d97706`, **Critical** `#dc2626`, **Info** `#2563eb` — always as `*-soft` tinted chips, never large fills.

Never: pure black `#000`, neon/outer-glow shadows, gradient text, more than one accent.

## 3. Typography
- **Family:** `Geist` (UI) + `Geist Mono` (numbers, IDs, timestamps, money). **Inter is banned.**
- **Headings:** weight 600, tracking `-0.02em`. Hierarchy comes from weight + color, not size jumps.
- **Body:** 14px / 1.5, Ink Soft. Long text capped at ~68ch.
- **Labels/eyebrows:** 12px, weight 500, Muted, tracking `0.01em` (NOT all-caps spammy `LABEL // 2025`).
- **Money & metrics:** Geist Mono, tabular-nums, Ink.
- Type scale (rem): `xs .75 · sm .8125 · base .875 · md 1 · lg 1.125 · xl 1.375 · 2xl 1.75`.

## 4. Space, Radius, Elevation
- **Spacing** on a 4px grid; default rhythm 16/24. Page padding 24–32px. Card padding 20–24px.
- **Radius:** controls `8px`, cards/panels `12px`, pills/avatars full. One step smaller for nested elements.
- **Elevation** (tinted, soft, never harsh):
  - `e1` cards: `0 1px 2px rgb(24 24 27 / .04), 0 1px 3px rgb(24 24 27 / .06)`
  - `e2` dropdowns/popovers: `0 4px 12px rgb(24 24 27 / .08), 0 2px 4px rgb(24 24 27 / .04)`
  - `e3` modals: `0 16px 48px rgb(24 24 27 / .18)`
  - Prefer **1px hairline + e1** over heavy shadow. High-density tables use hairlines only.

## 5. Components
- **Button** — height 36px (sm 30, lg 42), radius 8, weight 500, no glow.
  - *Primary:* Brand fill, white text; hover darkens ~6%; active `translateY(1px)`.
  - *Secondary:* Surface + hairline; hover Surface Sunken.
  - *Ghost:* transparent; hover Surface Sunken.
  - *Danger:* Critical fill. Focus: 2px Brand ring at 35% + 2px offset.
- **Input** — 36px, Surface, 1px Hairline, radius 8; focus = Brand border + 3px Brand-wash ring. Label above (12px Muted), error below (Critical). No floating labels.
- **Card** — Surface, 1px Hairline, radius 12, `e1`. Header: title 14px/600 Ink + optional 12px Muted subtitle, hairline-soft divider. Use cards only where elevation = hierarchy; otherwise hairline sections.
- **Table** — header row Surface Sunken, 12px Muted labels, tabular-nums for numeric columns; rows 52px, hairline-soft separators, hover Surface Sunken, selected Brand Wash. Right-align numbers/money. Sticky header. Skeleton shimmer (not spinners) for loading; composed empty states with one CTA.
- **Kanban** — columns are Surface-Sunken wells (radius 12, no border); column header = stage name + count pill + tiny brand progress bar (stage probability). Cards: Surface, hairline, radius 10, `e1`; on drag, lift to `e2` + 2° tilt + spring. Card shows deal name (14/600), company (12 Muted), amount (Geist Mono), owner avatar. Live moves from other users animate in.
- **Badge/Tag** — pill, 12px/500, soft tinted fill + matching text (e.g. Positive: `#16a34a` text on 12% wash). Brand badges use Brand Wash.
- **Avatar** — circle, Brand Wash bg + Brand text initials, or image; sizes 24/32/40. Stacks overlap −8px with ring.
- **Shell** — fixed 248px sidebar: wordmark + tenant logo, grouped nav with 20px icons, active item = Brand Wash bg + Brand text + 2px left brand bar. Topbar 60px: page title left, global search (⌘K) center-left, notifications + avatar menu right. Content max-width 1400px, 24–32px padding.
- **Modal** — center, max-w 520 (lg 720), radius 16, `e3`, backdrop `zinc-900/40` + 2px blur; spring scale-in from .97.

## 6. Motion
- Default transition 150ms `cubic-bezier(.2,.7,.3,1)`. Spring (stiffness ~220, damping ~26) for kanban drag, modal/menu enter, toast.
- Stagger list/table reveals ~24ms/row (cap ~12). Animate **only** `transform`/`opacity`. Skeleton shimmer for loads. Respect `prefers-reduced-motion`.
- One restrained perpetual loop allowed: skeleton shimmer + a soft pulse on "live"/syncing indicators. No bouncing chevrons.

## 7. Anti-patterns (banned)
No emojis in the product UI. No Inter. No pure black. No neon/outer-glow or gradient text. No more than one accent. No 3-equal-card rows (use asymmetric or table). No fabricated metrics/round numbers in empty states — use real data or `—`. No `LABEL // YEAR` eyebrows. No AI clichés ("Elevate/Seamless/Unleash"). No circular spinners (skeletons instead). No generic placeholder names in real data. No `h-screen` (use `min-h-[100dvh]`). Tap targets ≥ 44px; columns collapse to single below 768px.
