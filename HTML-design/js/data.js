/* =====================================================================
   HOMESMENU — Mock data
   Shared sample data used to render property cards, dashboards, tables.
   In production these map to the Properties / Users / Referrals /
   Transactions tables (see memories-md/04-data-model.md).
   ===================================================================== */

// Inline SVG placeholder generator so the design works fully offline
// (no external image hosts). Produces a soft gradient "photo".
function propImg(seed, label) {
  const palettes = [
    ['#2f8451', '#1f9d63'], ['#1f6b3d', '#57b07f'], ['#1f9d63', '#0e7a4d'],
    ['#c98a2e', '#2f8451'], ['#3a8f5c', '#1f6b3d'], ['#1f9d63', '#5cc08a'],
    ['#246b46', '#3fae73'], ['#17794d', '#2f8451']
  ];
  const [a, b] = palettes[seed % palettes.length];
  const svg = `
  <svg xmlns='http://www.w3.org/2000/svg' width='800' height='560' viewBox='0 0 800 560'>
    <defs>
      <linearGradient id='g${seed}' x1='0' y1='0' x2='1' y2='1'>
        <stop offset='0' stop-color='${a}'/><stop offset='1' stop-color='${b}'/>
      </linearGradient>
    </defs>
    <rect width='800' height='560' fill='url(#g${seed})'/>
    <g fill='rgba(255,255,255,.16)'>
      <rect x='90' y='300' width='180' height='180' rx='8'/>
      <rect x='300' y='240' width='210' height='240' rx='8'/>
      <rect x='540' y='320' width='170' height='160' rx='8'/>
      <polygon points='300,240 405,150 510,240'/>
      <polygon points='90,300 180,230 270,300'/>
    </g>
    <g fill='rgba(255,255,255,.28)'>
      <rect x='130' y='350' width='34' height='34' rx='4'/>
      <rect x='196' y='350' width='34' height='34' rx='4'/>
      <rect x='340' y='300' width='40' height='40' rx='4'/>
      <rect x='430' y='300' width='40' height='40' rx='4'/>
      <rect x='340' y='370' width='40' height='40' rx='4'/>
      <rect x='430' y='370' width='40' height='40' rx='4'/>
      <rect x='580' y='370' width='34' height='34' rx='4'/>
    </g>
    <text x='40' y='70' fill='rgba(255,255,255,.9)' font-family='Inter,sans-serif' font-size='30' font-weight='700'>${label || ''}</text>
  </svg>`;
  return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}

function avatarImg(initials, seed) {
  const palettes = ['#2f8451', '#1f9d63', '#1f6b3d', '#c98a2e', '#17794d', '#46a06c'];
  const c = palettes[(seed || 0) % palettes.length];
  const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='96' height='96'><rect width='96' height='96' fill='${c}'/><text x='50%' y='54%' fill='#fff' font-family='Inter,sans-serif' font-size='38' font-weight='700' text-anchor='middle' dominant-baseline='middle'>${initials}</text></svg>`;
  return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}

const PROPERTIES = [
  { id: 'PM1024', title: '3 BHK Luxury Apartment', project: 'Skyline Heights', loc: 'Bandra West, Mumbai', price: '₹2.45 Cr', ppsf: '₹24,500/sq.ft', area: '1240', beds: 3, baths: 3, type: 'Apartment', status: 'For Sale', tags: ['Ready to Move', 'Premium'], featured: true, tour: true, badge: { t: 'Featured', c: 'badge-gold' }, dealer: 'Anil Mehta', dealerInit: 'AM', verified: true, seed: 0 },
  { id: 'PM1025', title: '2 BHK Modern Flat', project: 'Green Valley Residency', loc: 'Whitefield, Bengaluru', price: '₹95 Lakh', ppsf: '₹9,200/sq.ft', area: '1030', beds: 2, baths: 2, type: 'Apartment', status: 'For Sale', tags: ['New Launch'], featured: true, tour: true, badge: { t: 'New', c: 'badge-accent' }, dealer: 'Priya Nair', dealerInit: 'PN', verified: true, seed: 1 },
  { id: 'PM1026', title: '4 BHK Independent Villa', project: 'Palm Grove Estate', loc: 'Gachibowli, Hyderabad', price: '₹3.80 Cr', ppsf: '₹12,800/sq.ft', area: '2950', beds: 4, baths: 4, type: 'Villa', status: 'For Sale', tags: ['Gated', 'Garden'], featured: true, tour: true, badge: { t: 'Verified', c: 'badge-success' }, dealer: 'Rohit Sharma', dealerInit: 'RS', verified: true, seed: 2 },
  { id: 'PM1027', title: '1 BHK Studio Apartment', project: 'Urban Nest', loc: 'Kharadi, Pune', price: '₹62 Lakh', ppsf: '₹8,600/sq.ft', area: '720', beds: 1, baths: 1, type: 'Apartment', status: 'For Sale', tags: ['Ready to Move'], featured: true, tour: false, badge: { t: 'Hot', c: 'badge-pink' }, dealer: 'Sneha Patil', dealerInit: 'SP', verified: false, seed: 3 },
  { id: 'PM1028', title: '3 BHK Builder Floor', project: 'Royal Residency', loc: 'Sector 57, Gurugram', price: '₹1.65 Cr', ppsf: '₹11,000/sq.ft', area: '1500', beds: 3, baths: 3, type: 'Builder Floor', status: 'For Sale', tags: ['Corner', 'Premium'], featured: true, tour: true, badge: { t: 'Featured', c: 'badge-gold' }, dealer: 'Vikram Singh', dealerInit: 'VS', verified: true, seed: 4 },
  { id: 'PM1029', title: '2 BHK Sea-View Flat', project: 'Marine Crest', loc: 'Worli, Mumbai', price: '₹4.20 Cr', ppsf: '₹38,000/sq.ft', area: '1100', beds: 2, baths: 2, type: 'Apartment', status: 'For Sale', tags: ['Sea View', 'Luxury'], featured: true, tour: true, badge: { t: 'Luxury', c: 'badge-brand' }, dealer: 'Anil Mehta', dealerInit: 'AM', verified: true, seed: 5 },
  { id: 'PM1030', title: '5 BHK Penthouse', project: 'The Crown', loc: 'Jubilee Hills, Hyderabad', price: '₹6.50 Cr', ppsf: '₹16,200/sq.ft', area: '4010', beds: 5, baths: 5, type: 'Penthouse', status: 'For Sale', tags: ['Penthouse', 'Private Pool'], featured: false, tour: true, badge: { t: 'Verified', c: 'badge-success' }, dealer: 'Rohit Sharma', dealerInit: 'RS', verified: true, seed: 6 },
  { id: 'PM1031', title: '3 BHK Lakeview Apartment', project: 'Lakeshore Towers', loc: 'Powai, Mumbai', price: '₹2.10 Cr', ppsf: '₹21,000/sq.ft', area: '1000', beds: 3, baths: 2, type: 'Apartment', status: 'For Sale', tags: ['Lake View'], featured: false, tour: false, badge: { t: 'New', c: 'badge-accent' }, dealer: 'Priya Nair', dealerInit: 'PN', verified: true, seed: 7 },
];

const CITIES = ['Mumbai', 'Bengaluru', 'Hyderabad', 'Pune', 'Gurugram', 'Delhi', 'Chennai', 'Kolkata'];
const PROP_TYPES = ['Apartment', 'Villa', 'Builder Floor', 'Penthouse', 'Plot', 'Studio'];

// Dealer / seller leads
const LEADS = [
  { name: 'Karan Malhotra', init: 'KM', prop: '3 BHK Luxury Apartment', when: '2 hours ago', status: 'New', phone: '+91 98765 43210', via: 'Refer & Earn' },
  { name: 'Divya Reddy', init: 'DR', prop: '2 BHK Modern Flat', when: '5 hours ago', status: 'Contacted', phone: '+91 91234 56780', via: 'Search' },
  { name: 'Arjun Kapoor', init: 'AK', prop: '4 BHK Independent Villa', when: 'Yesterday', status: 'Site Visit', phone: '+91 99887 76655', via: 'Virtual Tour' },
  { name: 'Meera Iyer', init: 'MI', prop: '2 BHK Sea-View Flat', when: '2 days ago', status: 'Negotiating', phone: '+91 90011 22334', via: 'Refer & Earn' },
  { name: 'Sahil Verma', init: 'SV', prop: '1 BHK Studio Apartment', when: '3 days ago', status: 'Closed', phone: '+91 98123 45678', via: 'Search' },
];

// Referral history
const REFERRALS = [
  { code: 'ABCD1234', friend: 'Karan Malhotra', prop: '3 BHK, Skyline Heights', stage: 'Deal Closed', reward: '₹25,000', status: 'Paid', date: '12 May 2026' },
  { code: 'ABCD1234', friend: 'Divya Reddy', prop: '2 BHK, Green Valley', stage: 'Site Visit Done', reward: '₹5,000', status: 'Pending', date: '28 May 2026' },
  { code: 'ABCD1234', friend: 'Meera Iyer', prop: '2 BHK, Marine Crest', stage: 'Negotiating', reward: '₹25,000', status: 'In Progress', date: '02 Jun 2026' },
  { code: 'ABCD1234', friend: 'Sahil Verma', prop: '1 BHK, Urban Nest', stage: 'Signed Up', reward: '₹500', status: 'Paid', date: '05 Jun 2026' },
];

const TRANSACTIONS = [
  { id: 'TXN9001', desc: 'Referral reward — Karan M.', amount: '+₹25,000', type: 'credit', status: 'Completed', date: '12 May 2026' },
  { id: 'TXN9002', desc: 'Withdrawal to HDFC ••4521', amount: '-₹20,000', type: 'debit', status: 'Completed', date: '15 May 2026' },
  { id: 'TXN9003', desc: 'Referral reward — Sahil V.', amount: '+₹500', type: 'credit', status: 'Completed', date: '05 Jun 2026' },
  { id: 'TXN9004', desc: 'Site-visit bonus — Divya R.', amount: '+₹5,000', type: 'credit', status: 'Pending', date: '28 May 2026' },
];

/* =====================================================================
   REAL PROPERTY PHOTOS
   Drop the 12 supplied photos into  assets/properties/  with the exact
   filenames below (see assets/properties/README.md for the photo→name
   map). Every <img> that uses these falls back to the generated SVG
   placeholder (propImg) if the file is missing, so the prototype never
   looks broken before the photos are added.
   ===================================================================== */
const PHOTO_DIR = 'assets/properties/';
function assetImg(file) { return PHOTO_DIR + file; }

// All 12 supplied photos, in the order they were provided.
const PHOTOS = [
  { file: 'balcony-view-1.jpg',  label: 'Balcony View',    type: 'Balcony'  },
  { file: 'balcony-view-2.jpg',  label: 'Balcony',         type: 'Balcony'  },
  { file: 'bathroom-1.jpg',      label: 'Bathroom',        type: 'Bathroom' },
  { file: 'bedroom-1.jpg',       label: 'Bedroom',         type: 'Bedroom'  },
  { file: 'store-room.jpg',      label: 'Store Room',      type: 'Room'     },
  { file: 'society-aerial.jpg',  label: 'Society View',    type: 'View'     },
  { file: 'balcony-view-3.jpg',  label: 'Balcony View',    type: 'Balcony'  },
  { file: 'master-bedroom.jpg',  label: 'Master Bedroom',  type: 'Bedroom'  },
  { file: 'bathroom-2.jpg',      label: 'Bathroom',        type: 'Bathroom' },
  { file: 'bathroom-3.jpg',      label: 'Bathroom',        type: 'Bathroom' },
  { file: 'kitchen.jpg',         label: 'Modular Kitchen', type: 'Kitchen'  },
  { file: 'balcony-view-4.jpg',  label: 'Balcony View',    type: 'Balcony'  },
];

// Cover photo shown on each property card (by property id).
const PROP_PHOTOS = {
  PM1024: 'master-bedroom.jpg',
  PM1025: 'kitchen.jpg',
  PM1026: 'bedroom-1.jpg',
  PM1027: 'balcony-view-2.jpg',
  PM1028: 'balcony-view-3.jpg',
  PM1029: 'balcony-view-1.jpg',
  PM1030: 'society-aerial.jpg',
  PM1031: 'balcony-view-4.jpg',
};

// Detail-page gallery for the featured property (PM1024) — ordered.
const GALLERY = [
  { file: 'master-bedroom.jpg', label: 'Master Bedroom' },
  { file: 'bedroom-1.jpg',      label: 'Bedroom' },
  { file: 'kitchen.jpg',        label: 'Modular Kitchen' },
  { file: 'balcony-view-1.jpg', label: 'Balcony View' },
  { file: 'society-aerial.jpg', label: 'Society View' },
  { file: 'bathroom-3.jpg',     label: 'Bathroom' },
  { file: 'bathroom-2.jpg',     label: 'Bathroom' },
  { file: 'bathroom-1.jpg',     label: 'Bathroom' },
  { file: 'store-room.jpg',     label: 'Store Room' },
  { file: 'balcony-view-3.jpg', label: 'Balcony' },
  { file: 'balcony-view-2.jpg', label: 'Balcony' },
  { file: 'balcony-view-4.jpg', label: 'Balcony' },
];

// 360° virtual-tour rooms (pill label -> photo).
const ROOMS = [
  { label: 'Living Hall', file: 'bedroom-1.jpg' },
  { label: 'Bedroom',     file: 'master-bedroom.jpg' },
  { label: 'Kitchen',     file: 'kitchen.jpg' },
  { label: 'Balcony',     file: 'balcony-view-1.jpg' },
  { label: 'Bathroom',    file: 'bathroom-3.jpg' },
];

// Resolve a property's cover image (real photo, else SVG placeholder).
function coverImg(p) {
  return PROP_PHOTOS[p.id] ? assetImg(PROP_PHOTOS[p.id]) : propImg(p.seed, p.type);
}

if (typeof window !== 'undefined') {
  window.HM_DATA = {
    PROPERTIES, CITIES, PROP_TYPES, LEADS, REFERRALS, TRANSACTIONS,
    propImg, avatarImg,
    PHOTO_DIR, assetImg, coverImg, PHOTOS, PROP_PHOTOS, GALLERY, ROOMS
  };
}
