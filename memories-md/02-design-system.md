# Design System & Frontend Conventions

Single source of truth: **`../HTML-design/css/main.css`**. Every page reuses its
classes — do NOT introduce a second visual language. This doc summarises it so an
agent can build new pages consistently without re-reading the whole stylesheet.

## File structure (`../HTML-design/`)
```
HTML-design/
├── index.html                 # landing (gold standard reference)
├── properties.html            # listings/search results
├── property-details.html      # single property
├── virtual-visit.html         # 360° tour + schedule visit
├── refer-earn.html            # refer & earn wallet
├── login.html                 # auth + role selection
├── dashboard-buyer.html       # buyer app dashboard
├── dashboard-seller.html      # seller app dashboard
├── dashboard-dealer.html      # dealer app dashboard
├── add-property.html          # list-a-property form
├── profile.html               # profile/settings
├── css/main.css               # the ENTIRE design system
└── js/
    ├── data.js                # window.HM_DATA mock data + propImg()/avatarImg()
    └── app.js                 # shared behaviours (reveal, counters, tabs, etc.)
```
All pages are **siblings** — use relative links (`href="properties.html"`,
`css/main.css`, `js/app.js`). Pages are standalone (no server includes); shared
chrome (nav/footer/sidebar) is repeated inline per page.

## Brand & tokens (CSS custom properties in `:root`)
- **Theme: white + forest green.** Primary brand `--brand-500: #2f8451` (forest green,
  scale 50–900; deepest action shade `--brand-600: #1f6b3d`).
- **Accent:** fresh emerald `--accent-500: #1f9d63`; secondary **warm honey/gold**
  `--pink-500: #c98a2e` (used sparingly for rewards/Featured/Hot highlights — the one
  warm pop against the greens). Dedicated `--gold` still used for stars/Featured.
- **Signature gradients:** `--grad-brand` (forest→emerald), `--grad-text` (green
  headline gradient; on the dark hero it shifts to light mint/sage), `--grad-dark`
  (deep forest near-black hero/footer/CTA bg). `--grad-warm` is gold (rewards).
- Surfaces are white with **warm green-tinted neutrals** (`--bg-soft #f4f8f4`,
  `--border #e2ebe4`). The whole palette is token-driven — changing the `:root`
  brand tokens recolors all 11 pages at once.
- **Fonts:** display = **Space Grotesk** (headings), body = **Inter**. Loaded from
  Google Fonts in each page `<head>`.
- Radii `--r-xs..--r-xl` + `--r-pill`; shadows `--sh-xs..--sh-lg`, `--sh-glow`.
- Motion: `--ease`, `--ease-spring`, durations `--t-fast/--t/--t-slow`.
- Light theme throughout, with dark gradient/aurora bands for hero & CTAs.

## Layout utilities
`.container` (max 1200px) / `.container-wide`; `.section` (vertical rhythm);
`.grid` + `.grid-2/3/4`; flex helpers `.flex .items-center .justify-between .gap-1/2/3 .wrap`;
spacing `.mt-1..4 / .mb-1..4`; `.center .muted .hidden .full`.

## Core components (class → use)
- **Buttons:** `.btn` + variant `.btn-primary` (gradient+glow), `.btn-dark`,
  `.btn-ghost`, `.btn-glass`, `.btn-outline`, `.btn-soft`, `.btn-success`; sizes
  `.btn-sm/.btn-lg`, `.btn-block`, `.btn-icon`.
- **Nav:** `.nav` (sticky glass; gets `.scrolled` shadow) · `.brand` + `.brand-logo`
  (house SVG) · `.nav-links a` (`.active`) · `.nav-actions` · `.nav-toggle` (mobile) ·
  mobile `.drawer/.drawer-panel`.
- **Hero / animated bg:** `.hero` + `.aurora span.b1..b4` (floating blurred blobs) +
  `.grid-overlay`; `.gradient-text` for headline accents; `.eyebrow` pill.
- **Search:** `.search-bar` + `.search-field` + `.search-tabs`; `.chip`/`.chip-row`
  (filter pills, `.active`).
- **Cards:** `.card`(+`.card-pad`,`.card-hover`), `.glass`, `.feature`(+`.bento`),
  KPI `.kpi` (with `.ic-brand/.ic-accent/.ic-gold/.ic-pink/.ic-success`, `.value`,
  `.delta.up/.down`).
- **Property card:** `.prop-card` → `.prop-media img`, `.prop-badges .badge`,
  `.prop-save` (heart), `.prop-tour` (360 pulse), `.prop-body`, `.prop-price`,
  `.prop-loc`, `.prop-meta`, `.prop-foot` + `.dealer-mini`.
- **Badges/tags:** `.badge` + `.badge-brand/-accent/-gold/-pink/-success/-warn/-danger/-soft/-glass`; `.tag/.tags`.
- **Forms:** `.field`+`label`, `.input/.select/.textarea`, `.input-group` (icon),
  `.role-grid`+`.role-opt` (role selector), `.switch` (toggle).
- **App shell:** `.app` (grid sidebar+main) → `.sidebar` (`.side-link.active`,
  `.side-section`), `.topbar`, `.app-content`, `.page-title`, `.avatar`, `.icon-btn`.
- **Tables:** `.table-wrap` + `table.table` (+ `.cell-prop`).
- **Misc:** `.steps/.step` (how-it-works), `.quote` (testimonial), `.cta-band`,
  `.marquee`, `.ref-code` + `.share-btn.sb-wa/.sb-fb/.sb-tg/.sb-cp`, `.progress`,
  `.timeline/.tl-item.done/.active`, `.gallery`, `.viewer`+`.room-pill` (360),
  `.footer`, `.toast`, `.skel` (skeleton).

## Shared JS behaviours (`js/app.js`) — opt in via attributes
- `class="reveal"` (+ `reveal-d1..d5` stagger) → fades/slides in on scroll
  (IntersectionObserver). Add to major blocks for the animated feel.
- `data-count="50000"` (+ `data-prefix/-suffix/-dec`) → animated number counter.
- `data-props="N"` (+ optional `data-featured`) on a container → auto-renders N
  property cards from `HM_DATA.PROPERTIES`.
- `data-tabs` wrapper + `data-tab="key"` buttons + `data-panel="key"` panels → tabs.
- `data-chip-group` (+ `data-multi`) on a `.chip` container → single/multi select.
- `.switch` → toggles `.on`. `.prop-save` → toggles favourite + toast.
- `data-copy="TEXT"` → copy to clipboard + toast. `window.hmToast(msg)` available.
- `[data-viewer-scope]` + `.viewer[data-viewer]` + `.room-pill[data-room=URL]` → 360
  room switcher. `[data-year]` → stamps the year.

## Images / offline rule
**No remote images.** Two layers:
1. **Real property photos** live in `HTML-design/assets/properties/` (12 supplied
   photos of one flat — balconies, bedrooms, kitchen, bathrooms, society views). They
   are mapped in `js/data.js` via `PROP_PHOTOS` (card cover per listing), `GALLERY`
   (detail-page gallery), and `ROOMS` (360° tour). Helpers: `assetImg(file)` and
   `coverImg(property)`. See `assets/properties/README.md` for the photo→filename map.
2. **Generated SVG fallback.** `window.HM_DATA.propImg(seed,label)` and
   `avatarImg(initials,seed)` return inline-SVG data URIs. Every real-photo `<img>`
   carries `data-seed`, `data-label` and `onerror="hmImgFallback(this)"` (defined in
   `app.js`) so a missing file auto-swaps to the SVG placeholder — the prototype never
   shows a broken image and renders with zero network.

To add/replace photos: drop files into `assets/properties/` and reference them via
`PROP_PHOTOS` / `GALLERY` / `ROOMS` in `data.js`. Never use remote image URLs.

## Accessibility & responsive
- Real `<label>`s, `alt` text, `aria-label` on icon-only buttons, `:focus-visible`
  styles, `prefers-reduced-motion` disables animation.
- Breakpoints at 1024 / 860 / 620px already handle nav→drawer, grid collapse,
  sidebar hide. Build markup with the grid utilities so it reflows for free.

Related: [[00-overview]], [[03-screens-spec]], [[06-tech-and-next-steps]].
