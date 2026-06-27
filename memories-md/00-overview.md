# HomesMenu — Product Overview

> Source of truth for any coding agent building or extending this application.
> Derived from the founder's hand-drawn wireframes (`New Doc 06-05-2026`) and the
> HTML design prototype in `../HTML-design/`.

## What it is
**HomesMenu** is a modern, mobile-first **real-estate marketplace** for the
Indian market — positioned in the same class as **99acres.com** and
**magicbricks.com**, but with a signature differentiator: a built-in
**Refer & Earn** rewards program that pays users real cash when their referrals
transact.

- "Mitra" = *friend* (Hindi). Brand promise / tagline: **"Find. Tour. Refer. Earn."**
- Candidate names considered (final = **HomesMenu**): HomesMenu, ReferEstate,
  HomeVisit, Property Link.

## The three pillars
1. **Marketplace** — search, browse and shortlist verified properties (buy / rent /
   commercial / PG).
2. **Virtual visits** — immersive 360° tours (rotate view across Bedroom, Kitchen,
   Hall, Balcony) + schedule a real site visit.
3. **Refer & Earn** — every user gets a referral code; sharing it and driving a
   transaction earns tiered cash rewards, withdrawable to bank/UPI. This is the
   growth engine and the product's main hook.

## User roles (chosen at signup, after LOGIN)
| Role | Primary jobs | Home surface |
|------|--------------|--------------|
| **Buyer** | Search, tour, shortlist, contact dealers, refer & earn | `dashboard-buyer.html` |
| **Seller** | Add/list property, track leads, property status, sold history | `dashboard-seller.html` |
| **Dealer** | Manage many listings, customer leads, deals closed, commission | `dashboard-dealer.html` |

A user picks a role right after login (`Choose Role` step in the App Flow). The
home screen and dashboards adapt to the chosen role.

## Core features (from wireframes)
- Search Property · Buy Property · Sell Property · Virtual Visit (360° tour) ·
  Refer & Earn · Dealer Zone · Featured Properties · Profile.
- Property detail: Photo, Name, Location, Price, Area + actions: Virtual Tour,
  Call Dealer, Chat, Refer & Earn, Save.
- Seller dashboard: Add property, My property, Leads received, Property status,
  Sold properties.
- Dealer dashboard: Total listings, Customer leads, Deals closed, Commission
  earned, Add new property.
- Refer & Earn: referral code, share to WhatsApp/Facebook/Telegram, total
  earnings, withdraw money.

## Design intent
The **public/marketing area must look animated and "AI-modern"**: gradient/aurora
hero, glassmorphism, scroll-reveal animations, animated counters, micro-interactions,
bento layouts. The **authenticated app/dashboards** are clean, dense and utilitarian
(sidebar + topbar shell). Both share ONE design system (`../HTML-design/css/main.css`).

## Status of this repo
- `../HTML-design/` — a **complete static HTML/CSS/JS prototype** (no backend). All
  data is mocked in `js/data.js`; all images are generated inline SVGs so it runs
  fully offline. This is the visual + interaction spec to build the real app against.
- `../memories-md/` — these specification docs.

See also: [[01-information-architecture]], [[02-design-system]], [[03-screens-spec]],
[[04-data-model]], [[05-referral-program]], [[06-tech-and-next-steps]].
