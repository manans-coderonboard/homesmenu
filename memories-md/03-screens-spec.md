# Screen Specifications (from wireframes)

Each screen below maps a hand-drawn wireframe to the prototype page and lists the
elements that must exist. Use this when implementing the real screens.

## Screen 1 — Home (wireframe pages 1 & 3)
- Header: app logo (house icon) + "HomesMenu".
- Primary entry points (icon list): **Search Property, Buy Property, Sell Property,
  Virtual Property/Visit, Refer & Earn, Profile**.
- **Featured Properties** section below.
- Prototype: the public `index.html` expands this into a full animated marketing
  home; the in-app equivalent is `dashboard-buyer.html`.

## Screen 2 — Property Details (wireframe page 4)
- **Property Photo** (gallery), **Property Name**, **Location**, **Price**, **Area**.
- Action row: **Virtual Tour, Call Dealer, Chat, Refer & Earn, Save**.
- Prototype: `property-details.html` (adds: key-facts grid, amenities, location/map,
  EMI calculator, dealer card, similar properties).

## Screen 3 — Virtual Visit (wireframe page 5)
- **360° Property Tour** with **Rotate view** across rooms: **Bedroom, Kitchen,
  Hall, Balcony**.
- **Virtual Tour** playback.
- **Schedule Site Visit** (CTA).
- Prototype: `virtual-visit.html` (room `.room-pill` switcher on `.viewer`; schedule
  form with date + time slots; video walkthrough / floor-plan tabs).

## Screen 4 — Refer & Earn (wireframe page 6)
- **Your Referral Code** (e.g. `ABCD1234`).
- **Share Link**: WhatsApp, Facebook, Telegram.
- **Total Earning** (₹ amount).
- **Withdraw Money**.
- Prototype: `refer-earn.html` (adds reward tiers, KPI wallet stats, referral history
  table, how-it-works). Full logic in [[05-referral-program]].

## Screen 5 — Seller Dashboard (wireframe page 7)
- **Add Property, My Property, Leads Received, Property Status, Sold Properties**.
- Prototype: `dashboard-seller.html` + `add-property.html`.

## Screen 6 — Dealer Dashboard (wireframe page 8)
- **Total Listing, Customer Leads, Deals Closed, Commission Earned, Add New Property**.
- Prototype: `dashboard-dealer.html` (adds a CSS performance chart + deals table).

## Login & Role selection (wireframe page 2)
- LOGIN → **Choose Role (Buyer / Seller / Dealer)** → Home.
- Prototype: `login.html` (phone/OTP or email login, signup with `.role-grid` role
  selection; role routes to the matching dashboard).

## Notes for implementation
- "Buy Property" / "Sell Property" from the home grid map to the listings flow and
  the seller flow respectively (not separate marketplaces).
- "Virtual Property" (page 3) == Virtual Visit (page 5) — same feature.
- Every property card and detail page should surface a **Refer & Earn** affordance —
  referrals are tied to a specific property (see Referrals table in
  [[04-data-model]]).

Related: [[01-information-architecture]], [[02-design-system]].
