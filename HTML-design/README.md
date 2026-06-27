# HomesMenu — HTML Prototype

A modern, animated, fully-offline front-end prototype for **HomesMenu**, an
Indian real-estate marketplace (99acres / MagicBricks class) with a built-in
**Refer & Earn** rewards program. Static HTML + one CSS design system + vanilla JS.
No backend, no build step, no dependencies.

## Run it
Just open `index.html` in a browser. (Some browsers block ES module / fetch over the
`file://` protocol — these pages use plain scripts so direct open works, but if
anything looks off, serve the folder over HTTP instead:)

```bash
# from this folder
python -m http.server 8000
# then visit http://localhost:8000/index.html
```

## Pages
| File | What it is |
|------|------------|
| `index.html` | Animated marketing landing page (hero, featured, refer-&-earn, CTA) |
| `properties.html` | Search results / listings with filters & pagination |
| `property-details.html` | Single property: gallery, amenities, dealer, EMI, refer |
| `virtual-visit.html` | 360° virtual tour + schedule site visit |
| `refer-earn.html` | Referral code, share, wallet, reward tiers, withdraw, history |
| `login.html` | Login / Sign up + role selection (Buyer / Seller / Dealer) |
| `dashboard-buyer.html` | Buyer dashboard |
| `dashboard-seller.html` | Seller dashboard (listings, leads, status, sold) |
| `dashboard-dealer.html` | Dealer dashboard (KPIs, performance chart, deals) |
| `add-property.html` | List-a-property form with live preview |
| `profile.html` | Profile / settings / wallet |

## Structure
```
css/main.css   → the entire design system (tokens + components). Reuse its classes.
js/data.js     → window.HM_DATA mock data + propImg()/avatarImg() inline-SVG helpers
js/app.js      → shared behaviours: scroll-reveal, counters, tabs, chips, 360 viewer,
                 property-card rendering ([data-props]), save hearts, copy, toasts
```

All images are generated inline SVGs, so the prototype renders with **zero network**
(only Google Fonts are fetched, and it degrades gracefully without them).

## Spec
Full product/design/data specification lives in **`../memories-md/`** — start with
`../memories-md/README.md`. That is the source of truth for building the real app.
