/* =====================================================================
   HOMESMENU — Shared interactions
   Scroll reveal, animated counters, nav behaviour, property-card
   rendering, save toggle, share/copy, toasts, mobile drawer, tabs.
   Vanilla JS, no dependencies. Safe to include on every page.
   ===================================================================== */
(function () {
  'use strict';
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

  /* ---- Image fallback: if a real photo is missing, swap to the generated
     SVG placeholder so nothing ever shows a broken image. Any <img> can use:
     <img src="assets/properties/x.jpg" data-seed="0" data-label="Bedroom"
          onerror="hmImgFallback(this)">  ---- */
  window.hmImgFallback = function (img) {
    if (!img || img.dataset.fellBack) return;
    img.dataset.fellBack = '1';
    img.onerror = null;
    if (window.HM_DATA) img.src = window.HM_DATA.propImg(parseInt(img.dataset.seed, 10) || 0, img.dataset.label || '');
  };

  /* ---- Sticky nav shadow ---- */
  const nav = $('.nav');
  if (nav) {
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---- Scroll reveal ---- */
  const revealEls = $$('.reveal');
  if (revealEls.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('in'));
  }

  /* ---- Animated counters ---- */
  const counters = $$('[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    const cio = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        const el = e.target;
        const target = parseFloat(el.dataset.count);
        const dur = 1400, t0 = performance.now();
        const prefix = el.dataset.prefix || '';
        const suffix = el.dataset.suffix || '';
        const dec = (el.dataset.dec | 0);
        const step = (now) => {
          const p = Math.min((now - t0) / dur, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          const val = target * eased;
          el.textContent = prefix + val.toLocaleString('en-IN', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix;
          if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        cio.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach((el) => cio.observe(el));
  }

  /* ---- Mobile drawer ---- */
  const drawer = $('.drawer');
  const openDrawer = () => drawer && drawer.classList.add('open');
  const closeDrawer = () => drawer && drawer.classList.remove('open');
  $$('.nav-toggle').forEach((b) => b.addEventListener('click', openDrawer));
  if (drawer) {
    $('.drawer-bg', drawer)?.addEventListener('click', closeDrawer);
    $$('[data-drawer-close]', drawer).forEach((b) => b.addEventListener('click', closeDrawer));
  }

  /* ---- Generic tab groups: [data-tabs] containing [data-tab] + panels [data-panel] ---- */
  $$('[data-tabs]').forEach((group) => {
    const tabs = $$('[data-tab]', group);
    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const key = tab.dataset.tab;
        tabs.forEach((t) => t.classList.toggle('active', t === tab));
        $$('[data-panel]', group).forEach((p) => p.classList.toggle('hidden', p.dataset.panel !== key));
      });
    });
  });

  /* ---- Chip / pill single-or-multi select ---- */
  $$('[data-chip-group]').forEach((group) => {
    const multi = group.hasAttribute('data-multi');
    $$('.chip', group).forEach((chip) => {
      chip.addEventListener('click', () => {
        if (multi) chip.classList.toggle('active');
        else $$('.chip', group).forEach((c) => c.classList.toggle('active', c === chip));
      });
    });
  });

  /* ---- Toggle switches ---- */
  $$('.switch').forEach((sw) => sw.addEventListener('click', () => sw.classList.toggle('on')));

  /* ---- Save / favourite hearts (delegated) ---- */
  document.addEventListener('click', (e) => {
    const save = e.target.closest('.prop-save');
    if (save) {
      e.preventDefault();
      save.classList.toggle('active');
      toast(save.classList.contains('active') ? 'Saved to your shortlist' : 'Removed from shortlist');
    }
  });

  /* ---- Copy to clipboard ([data-copy]) ---- */
  $$('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const text = btn.dataset.copy;
      const done = () => toast('Copied: ' + text);
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(done).catch(done);
      else done();
    });
  });

  /* ---- Toast ---- */
  let toastEl, toastTimer;
  function toast(msg) {
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.className = 'toast';
      toastEl.innerHTML = '<span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="msg"></span>';
      document.body.appendChild(toastEl);
    }
    $('.msg', toastEl).textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2600);
  }
  window.hmToast = toast;

  /* ---- Render featured property cards into [data-props] containers ---- */
  function renderProps() {
    const D = window.HM_DATA;
    if (!D) return;
    $$('[data-props]').forEach((host) => {
      const limit = parseInt(host.dataset.props, 10) || D.PROPERTIES.length;
      const onlyFeatured = host.hasAttribute('data-featured');
      const list = (onlyFeatured ? D.PROPERTIES.filter((p) => p.featured) : D.PROPERTIES).slice(0, limit);
      host.innerHTML = list.map((p, i) => propCard(p, i)).join('');
    });
  }
  function propCard(p, i) {
    const D = window.HM_DATA;
    const delay = 'reveal-d' + (Math.min(i % 4 + 1, 5));
    return `
    <a href="property-details.html" class="prop-card reveal ${delay}">
      <div class="prop-media">
        <img src="${D.coverImg(p)}" data-seed="${p.seed}" data-label="${p.type}" onerror="hmImgFallback(this)" alt="${p.title} in ${p.loc}" loading="lazy">
        <div class="prop-badges">
          <span class="badge ${p.badge.c}">${p.badge.t}</span>
          ${p.verified && p.badge.t !== 'Verified' ? '<span class="badge badge-glass"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Verified</span>' : ''}
        </div>
        <button class="prop-save" aria-label="Save property"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg></button>
        ${p.tour ? '<span class="prop-tour"><span class="pulse"></span>360° Tour</span>' : ''}
      </div>
      <div class="prop-body">
        <div class="prop-price">${p.price} <small>· ${p.ppsf}</small></div>
        <div class="prop-title">${p.title} <span class="muted" style="font-weight:500">· ${p.project}</span></div>
        <div class="prop-loc"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>${p.loc}</div>
        <div class="prop-meta">
          <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9V6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v3"/><path d="M2 11h20v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z"/><path d="M6 11V9m12 2V9"/></svg>${p.beds} Beds</span>
          <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4z"/><path d="M6 12V6a2 2 0 0 1 2-2 2 2 0 0 1 2 2"/></svg>${p.baths} Baths</span>
          <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3z"/><path d="M9 3v18M3 9h18"/></svg>${p.area} sq.ft</span>
        </div>
      </div>
      <div class="prop-foot">
        <div class="dealer-mini"><span class="av">${p.dealerInit}</span><div><div style="font-weight:600">${p.dealer}</div><div class="muted" style="font-size:.74rem">Verified Dealer</div></div></div>
        <span class="btn btn-soft btn-sm">View</span>
      </div>
    </a>`;
  }
  renderProps();

  // Re-run reveal observer for freshly rendered cards
  if ('IntersectionObserver' in window) {
    const io2 = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io2.unobserve(e.target); } });
    }, { threshold: 0.1 });
    $$('[data-props] .reveal').forEach((el) => io2.observe(el));
  }

  /* ---- Year stamp ---- */
  $$('[data-year]').forEach((el) => { el.textContent = '2026'; });

  /* ---- Simple lightbox for gallery / 360 ([data-room] switches viewer img) ---- */
  $$('[data-viewer]').forEach((viewer) => {
    const img = $('img', viewer);
    $$('[data-room]', viewer.closest('[data-viewer-scope]') || viewer.parentElement).forEach((pill) => {
      pill.addEventListener('click', () => {
        $$('[data-room]', pill.parentElement).forEach((p) => p.classList.toggle('active', p === pill));
        const src = pill.dataset.room;
        if (src && img) { img.style.opacity = '0'; setTimeout(() => { img.src = src; img.style.opacity = '1'; }, 180); }
      });
    });
  });

})();
