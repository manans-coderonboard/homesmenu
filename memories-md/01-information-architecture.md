# Information Architecture & App Flow

## App flow (from wireframe page 2)
```
LOGIN
  └─> Choose Role (Buyer / Seller / Dealer)
        └─> Home Screen
              ├─ Buy Property
              ├─ Sell Property
              ├─ Virtual Visit
              ├─ Refer & Earn
              └─ My Profile
```
Login → role selection → role-specific home/dashboard. Role determines which
navigation and dashboard the user lands on.

## Sitemap (prototype pages in `../HTML-design/`)
Two "shells" share the same design system:

### A. Public / content shell — top glass nav + footer
| Page | File | Purpose |
|------|------|---------|
| Landing | `index.html` | Marketing home: hero search, featured, refer&earn spotlight, how-it-works, CTA. The animated "AI-modern" showpiece. |
| Listings | `properties.html` | 99acres-style search results: filter sidebar + sort bar + property grid + pagination. |
| Property detail | `property-details.html` | Single property: gallery, key facts, amenities, dealer card, EMI calc, refer box, similar. |
| Virtual visit | `virtual-visit.html` | 360° viewer (room switch), schedule site visit, video walkthrough/floor plan tabs. |
| Refer & Earn | `refer-earn.html` | Referral code, share, wallet, reward tiers, withdraw, referral history. |
| Login / Signup | `login.html` | Split auth screen + **role selection** (Buyer/Seller/Dealer). Minimal chrome. |

### B. App shell — left sidebar + topbar (no public footer)
| Page | File | Role | Purpose |
|------|------|------|---------|
| Buyer dashboard | `dashboard-buyer.html` | Buyer | Saved, recommended, upcoming visits, referral mini, wallet. |
| Seller dashboard | `dashboard-seller.html` | Seller | KPIs, My Properties table, Leads received, status, sold. |
| Dealer dashboard | `dashboard-dealer.html` | Dealer | KPIs, performance chart, customer leads, deals closed, commission. |
| Add property | `add-property.html` | Seller/Dealer | Multi-step listing form + live preview. |
| Profile | `profile.html` | All | Personal info, security, notifications, wallet & payouts. |

## Primary navigation
**Public top nav:** Buy · Rent · Sell · Virtual Tours · Refer & Earn · Dealer Zone +
`Log in` / `Get Started`. Collapses to a mobile drawer below 860px.

**App sidebar (role-specific Workspace section + shared Account section):**
- Buyer: Dashboard · Search · Saved · Site Visits · Messages
- Seller: Dashboard · Add Property · My Properties · Leads · Messages
- Dealer: Dashboard · Add Property · Listings · Customer Leads · Deals
- Account (all): Refer & Earn · My Profile · Log out

## Key user journeys
1. **Buyer:** Landing → search → results → property detail → virtual tour →
   schedule visit / call dealer → (later) refer a friend → earn.
2. **Seller:** Login (Seller) → seller dashboard → Add Property → receive leads →
   update status → mark sold.
3. **Dealer:** Login (Dealer) → dealer dashboard → manage listings & leads →
   close deals → track commission.
4. **Referral:** Any user → Refer & Earn → copy/share code → friend signs up &
   transacts → reward credited → withdraw. See [[05-referral-program]].

Related: [[00-overview]], [[03-screens-spec]].
