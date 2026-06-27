# Data Model

Based on the wireframe **Database Structure** (page 9), expanded into a workable
schema. The prototype mirrors these entities in `../HTML-design/js/data.js`
(`PROPERTIES`, `LEADS`, `REFERRALS`, `TRANSACTIONS`). Field names below use the
wireframe's intent; add the "recommended extra" fields when building the backend.

## Entities & relationships
```
Users 1───∞ Properties        (a Seller/Dealer owns many properties: Properties.sellerId)
Users 1───∞ Referrals (as referrer: referralUserId)
Users 1───∞ Referrals (as referred buyer: buyerUserId)
Properties 1───∞ Referrals    (a referral is for a specific property)
Users 1───∞ Transactions      (wallet credits/debits)
Properties 1───∞ Leads        (enquiries on a property)
```

## Users  (wireframe: User Id, Name, Mobile, Role)
| Field | Type | Notes |
|-------|------|-------|
| `userId` | PK / uuid | |
| `name` | string | |
| `mobile` | string | primary login identifier (OTP) |
| `role` | enum | `buyer` \| `seller` \| `dealer` |
| `email` | string | recommended |
| `referralCode` | string | unique, e.g. `ABCD1234` — every user has one |
| `referredBy` | fk Users | who referred this user (nullable) |
| `walletBalance` | decimal | derived from Transactions |
| `isVerified` / `kycStatus` | bool/enum | recommended |
| `city`, `avatarUrl`, `createdAt` | | recommended |

## Properties  (wireframe: Property ID, Seller ID, Price, Location, Image, Virtual Tour Link)
| Field | Type | Notes |
|-------|------|-------|
| `propertyId` | PK | |
| `sellerId` | fk Users | owner (seller or dealer) |
| `price` | decimal | store numeric; display as ₹ Lakh/Cr |
| `location` | string/object | city + locality + address; geo lat/lng recommended |
| `image` / `images[]` | url[] | gallery |
| `virtualTourLink` | url | 360°/video tour |
| `title`, `propertyType` | | Apartment/Villa/Builder Floor/Plot/Penthouse (recommended) |
| `bedrooms`, `bathrooms`, `areaSqft` | int | recommended |
| `listingType` | enum | `sale` \| `rent` |
| `status` | enum | `active` \| `under_offer` \| `sold` \| `draft` |
| `constructionStatus` | enum | ready / under-construction / new-launch |
| `amenities[]`, `isVerified` (RERA), `views`, `createdAt` | | recommended |

## Referrals  (wireframe: Referral ID, Referral User ID, Buyer User ID, Property ID, Reward)
| Field | Type | Notes |
|-------|------|-------|
| `referralId` | PK | |
| `referralUserId` | fk Users | the **referrer** (who shared the code) |
| `buyerUserId` | fk Users | the **referred** friend |
| `propertyId` | fk Properties | property the referral relates to |
| `reward` | decimal | amount earned |
| `stage` | enum | `signed_up` \| `site_visit` \| `negotiating` \| `deal_closed` (drives tiered reward) |
| `status` | enum | `pending` \| `in_progress` \| `paid` |
| `createdAt` | | |

## Transactions  (wireframe: User Id, Amount, Status)
| Field | Type | Notes |
|-------|------|-------|
| `transactionId` | PK | |
| `userId` | fk Users | wallet owner |
| `amount` | decimal | + credit / − debit |
| `status` | enum | `pending` \| `completed` \| `failed` |
| `type` | enum | `referral_reward` \| `withdrawal` \| `bonus` (recommended) |
| `description`, `method` (bank/UPI), `createdAt` | | recommended |

## Supporting entity (in prototype, recommended for backend)
**Leads** — enquiries shown on Seller/Dealer dashboards:
`leadId, propertyId, buyerUserId, dealerUserId, source (search/refer_&_earn/virtual_tour),
status (new/contacted/site_visit/negotiating/closed), createdAt`.

## Derived/computed values surfaced in UI
- Wallet balance, total earnings, pending vs paid = aggregations over Transactions/Referrals.
- Dealer "Commission earned", "Deals closed" = aggregations over closed Leads/Properties.
- Property "Featured" flag drives the homepage featured grid.

Related: [[05-referral-program]], [[00-overview]].
