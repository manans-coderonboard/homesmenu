# Refer & Earn — Program Logic

The referral program is HomesMenu's signature feature and primary growth loop.
It must be present across the app (homepage spotlight, property cards/detail, a
dedicated page, dashboards, profile wallet). Prototype: `refer-earn.html`.

## How it works (user-facing)
1. Every user is issued a unique **referral code** at signup (e.g. `ABCD1234`),
   stored on `Users.referralCode`.
2. User shares the code/link via **WhatsApp, Facebook, Telegram**, or copy-link.
3. A friend signs up with that code → a `Referrals` row is created linking
   `referralUserId` (sharer) ↔ `buyerUserId` (friend).
4. As the friend progresses (signs up → site visit → negotiating → deal closed),
   the referral advances through **reward tiers**; rewards post to the sharer's
   wallet as `Transactions`.
5. User views **Total Earnings** and can **Withdraw Money** to bank/UPI (payout
   target: within 24h).

## Reward tiers (prototype values — make configurable server-side)
| Trigger (`Referrals.stage`) | Reward (`Referrals.reward`) |
|-----------------------------|------------------------------|
| Friend **signs up** | ₹500 |
| Friend completes a **site visit** | ₹5,000 |
| Friend **closes a deal** (purchase) | ₹25,000 (max) |

Rewards are cumulative-by-milestone; `status` moves `pending → in_progress → paid`.
A referral is always tied to a specific `propertyId`.

## Wallet & withdrawal
- Wallet balance = Σ completed credits − Σ completed debits in `Transactions`.
- Withdrawal creates a debit `Transaction` (`type: withdrawal`, `status: pending →
  completed`) against a linked bank/UPI method.
- Surfaces: `refer-earn.html` (full), `dashboard-buyer.html` (mini), `profile.html`
  → Wallet & Payouts tab.

## Where it appears in the UI
- **Homepage** (`index.html`): dedicated "Refer & Earn" spotlight section + nav link.
- **Property card & detail**: "Refer this property, earn ₹25,000" with code + share.
- **Refer & Earn page**: code, share buttons, reward tiers, KPI wallet stats,
  withdraw form, referral history table (from `HM_DATA.REFERRALS`).
- **Dashboards**: a refer-&-earn mini-banner; referral count as a KPI.

## Integrity / anti-abuse (build-time considerations)
- One reward per genuine, distinct referred user; prevent self-referral
  (`referralUserId != buyerUserId`) and duplicate-device/circular referrals.
- Rewards should only finalise on **verified** milestones (e.g. dealer-confirmed
  site visit, registered deal) to avoid fraud — gate `status: paid` behind
  verification.
- Make tier amounts, caps and eligibility server-configurable, not hardcoded.

Related: [[04-data-model]], [[01-information-architecture]].
