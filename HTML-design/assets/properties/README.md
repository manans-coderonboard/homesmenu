# Property photos — save the 12 images here

The prototype is already wired to use these real photos. **Save each pasted image
into this folder (`HTML-design/assets/properties/`) using the EXACT filename below**
(lowercase, `.jpg`). Until a file is present, that spot automatically shows a
generated SVG placeholder instead — so nothing breaks while you add them.

Order = the order the photos were provided in chat:

| # | Photo | Save as |
|---|-------|---------|
| 1 | Balcony view — wide shot, ceiling slab visible, white railing, towers ahead | `balcony-view-1.jpg` |
| 2 | Balcony view — narrower, open door frame on the right | `balcony-view-2.jpg` |
| 3 | Bathroom — grey tiles, shower + toilet + small basin | `bathroom-1.jpg` |
| 4 | Bedroom — black wardrobe, ceiling fan, glossy white floor, balcony door | `bedroom-1.jpg` |
| 5 | Store room / passage — dark stained door frame, switchboard | `store-room.jpg` |
| 6 | Aerial society view — basketball court, parking, towers (high floor) | `society-aerial.jpg` |
| 7 | Balcony view — red-and-white towers facing | `balcony-view-3.jpg` |
| 8 | Master bedroom — wooden laminate floor, attached bathroom, balcony | `master-bedroom.jpg` |
| 9 | Bathroom — beige tiles, basin, exhaust fan, window | `bathroom-2.jpg` |
| 10 | Bathroom — marble tiles, toilet + Crompton geyser | `bathroom-3.jpg` |
| 11 | Kitchen — green modular kitchen | `kitchen.jpg` |
| 12 | Balcony view — red-and-white society with flag | `balcony-view-4.jpg` |

## Where each photo shows up
- **Property cards** (home, listings, dashboards): one cover photo per listing —
  mapped in `js/data.js` → `PROP_PHOTOS`.
- **Property detail gallery** (`property-details.html`): all 12 → `js/data.js` → `GALLERY`.
- **360° virtual tour** (`virtual-visit.html`): Living Hall, Bedroom, Kitchen,
  Balcony, Bathroom → `js/data.js` → `ROOMS`.
- **Hero** (`index.html`): `balcony-view-1.jpg`.

To change any mapping, edit the `PROP_PHOTOS` / `GALLERY` / `ROOMS` constants in
`js/data.js`. To add more photos, drop them here and reference them the same way.

> Tip: any image format works if you also update the filename/extension in
> `data.js`, but keeping them as optimized `.jpg` (≈1600px wide) keeps the prototype fast.
