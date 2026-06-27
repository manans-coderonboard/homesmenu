# HomesMenu — Specification (memories-md)

These docs are the **persistent knowledge base** for any coding agent or developer
working on **HomesMenu**, a modern Indian real-estate marketplace
(99acres / magicbricks class) with a built-in **Refer & Earn** rewards program.
They describe what the app is, how it's structured, and how the HTML prototype in
`../HTML-design/` maps to a real build.

## Read in this order
1. **[00-overview.md](00-overview.md)** — what the product is, roles, pillars, status.
2. **[01-information-architecture.md](01-information-architecture.md)** — app flow, sitemap, navigation, user journeys.
3. **[02-design-system.md](02-design-system.md)** — the shared CSS/JS, component classes, conventions, offline rule.
4. **[03-screens-spec.md](03-screens-spec.md)** — each wireframe screen → prototype page + required elements.
5. **[04-data-model.md](04-data-model.md)** — Users / Properties / Referrals / Transactions / Leads schema.
6. **[05-referral-program.md](05-referral-program.md)** — Refer & Earn tiers, wallet, payouts, anti-abuse.
7. **[06-tech-and-next-steps.md](06-tech-and-next-steps.md)** — prototype vs production, recommended stack, guardrails.

## TL;DR for a coding agent
- The UI is already designed: reuse `../HTML-design/css/main.css` classes; don't
  invent a new visual language.
- Three roles: **Buyer / Seller / Dealer**, chosen after login.
- Everything ties back to four core entities + Leads; the **referral** is the hook.
- Prototype is **static, mocked, offline-safe** — replace `js/data.js` with a real
  API and the inline-SVG images with real photos when productionising.
