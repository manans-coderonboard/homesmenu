# Tech Notes & Next Steps

## What the prototype is (and isn't)
`../HTML-design/` is a **static, front-end-only prototype**: hand-written HTML +
one CSS design system + vanilla JS. It is the **visual and interaction
specification** for the real product, NOT production code.

- **No backend, no build step, no framework.** Open any `.html` directly in a
  browser — it works fully offline.
- **All data is mocked** in `js/data.js` (`window.HM_DATA`). Swap these for real API
  calls when building.
- **All images are generated inline SVGs** (`propImg`, `avatarImg`) so nothing
  depends on a CDN. Replace with real property photos / a CDN later.
- Interactions are visual only (no real auth, payments, OTP, chat, or 360 engine).

## When turning this into a real app
Preserve the design system (`css/main.css`) and the page structures — they are the
agreed UX. Recommended approach:

- **Frontend:** port pages to a component framework (React/Next.js or Vue/Nuxt).
  Each prototype page → a route; repeated chrome (nav, footer, sidebar) → shared
  layout components. Keep the CSS tokens/classes (or translate to Tailwind/CSS
  modules using the same token values from `main.css :root`).
- **Auth:** mobile-number + OTP primary (matches `Users.mobile`); role chosen
  post-login (Buyer/Seller/Dealer). Social login optional.
- **Backend/API:** REST or GraphQL over the entities in [[04-data-model]]
  (Users, Properties, Referrals, Transactions, Leads). Postgres is a good default.
- **Search:** server-side filtering for the `properties.html` filters (city, type,
  BHK, budget, status, amenities, verified, has-virtual-tour) — consider a search
  index (Meilisearch/Elastic) at scale.
- **Virtual tours:** integrate a real 360°/panorama viewer (e.g. Pannellum, Matterport
  embed) for `virtualTourLink`; the prototype only swaps static room images.
- **Refer & Earn:** implement tiered reward engine + wallet + payouts (see
  [[05-referral-program]]); gate payouts behind verified milestones; add fraud checks.
- **Payments/payouts:** integrate a gateway + bank/UPI payout (e.g. Razorpay) for
  withdrawals; ledger via the `Transactions` table.
- **Realtime:** chat (buyer↔dealer) and lead notifications via websockets/push.
- **Maps:** Google Maps / Mapbox for property location & nearby (prototype uses a
  placeholder box).

## Guardrails for future edits to the prototype
- Reuse existing classes from `css/main.css`; avoid large new `<style>` blocks.
- Keep nav/footer/sidebar markup identical across pages (copy from `index.html` /
  existing dashboards) so chrome stays consistent.
- Keep it offline-safe: never add remote image/script URLs except the Google Fonts
  link already used.
- Public/marketing pages stay animated & "AI-modern"; app dashboards stay clean and
  dense.

## Market reference
Positioned like **99acres.com** and **magicbricks.com** (Indian property portals),
differentiated by the integrated **Refer & Earn** cash-reward program.

Related: [[00-overview]], [[02-design-system]], [[04-data-model]].
