(function () {
  'use strict';

  if (typeof avtodealerAdmin === 'undefined') return;

  var app = document.getElementById('avto-cms-app');
  if (!app) return;

  var view = 'list';
  var activeTab = 'header';
  var activeLang = (avtodealerAdmin.lang || 'ru').toLowerCase();
  var languages = Array.isArray(avtodealerAdmin.languages) && avtodealerAdmin.languages.length
    ? avtodealerAdmin.languages
    : ['ru', 'uz', 'en'];
  var dirty = false;
  var busy = false;
  var currentData = null;
  var searchQ = '';

  var LANG_META = {
    ru: { label: 'Русский', flag: '🇷🇺' },
    uz: { label: "Oʻzbekcha", flag: '🇺🇿' },
    en: { label: 'English', flag: '🇬🇧' },
  };

  var RESOURCES = [
    { id: 'header', title: 'Header' },
    { id: 'hero', title: 'Hero — Bosh sahifa' },
    { id: 'hero-tank300', title: 'Hero — TANK 300' },
    { id: 'hero-tank500', title: 'Hero — TANK 500' },
    { id: 'catalog', title: 'Catalog' },
    { id: 'models', title: 'Models' },
    { id: 'configs', title: 'Configs' },
    { id: 'tradein', title: 'Trade-in' },
    { id: 'credit', title: 'Credit' },
    { id: 'corporate', title: 'Corporate' },
    { id: 'contact', title: 'Contact' },
    { id: 'footer', title: 'Footer' },
    { id: 'modal', title: 'Modal' },
    { id: 'leads', title: 'Leads' },
  ];

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/"/g, '&quot;');
  }

  function flag(lang) {
    return (LANG_META[lang] && LANG_META[lang].flag) || '🏳️';
  }

  function langLabel(lang) {
    return (LANG_META[lang] && LANG_META[lang].label) || lang.toUpperCase();
  }

  function buildApiUrl(path) {
    var clean = String(path || '').replace(/^\//, '');
    if (avtodealerAdmin.useQuery) {
      var url = new URL(avtodealerAdmin.siteUrl || '/', window.location.origin);
      var route = (avtodealerAdmin.namespace || '/avtodealer/v1/') + clean;
      url.searchParams.set('rest_route', route.replace(/\/$/, '') || '/avtodealer/v1');
      if (clean && clean !== 'leads') url.searchParams.set('lang', activeLang);
      return url.toString();
    }
    var base = (avtodealerAdmin.restUrl || '').replace(/\/?$/, '/') + clean;
    if (clean === 'leads') return base;
    return base + (base.indexOf('?') >= 0 ? '&' : '?') + 'lang=' + encodeURIComponent(activeLang);
  }

  async function api(path, method, body) {
    var opts = {
      method: method || 'GET',
      headers: {
        'X-WP-Nonce': avtodealerAdmin.nonce,
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
    };
    if (body !== undefined && body !== null) opts.body = JSON.stringify(body);
    var res = await fetch(buildApiUrl(path), opts);
    var data = await res.json().catch(function () {
      return {};
    });
    if (!res.ok) throw new Error(data.message || data.code || 'HTTP ' + res.status);
    return data;
  }

  function notice(msg, isError) {
    var el = document.getElementById('avto-flash');
    if (!el) return;
    el.className =
      'mb-4 border-l-4 bg-white px-3 py-2 shadow-sm ' +
      (isError ? 'border-[#d63638] text-[#d63638]' : 'border-[#00a32a] text-[#1d2327]');
    el.textContent = msg;
    el.hidden = false;
    clearTimeout(notice._t);
    notice._t = setTimeout(function () {
      el.hidden = true;
    }, 3200);
  }

  function btn(label, extra, attrs) {
    return (
      '<button type="button" ' +
      (attrs || '') +
      ' class="inline-flex h-7 items-center rounded-sm border border-[#2271b1] bg-white px-2.5 text-[13px] text-[#2271b1] hover:bg-[#f0f6fc] ' +
      (extra || '') +
      '">' +
      label +
      '</button>'
    );
  }

  function btnPrimary(label, attrs) {
    return (
      '<button type="button" ' +
      (attrs || '') +
      ' class="inline-flex h-8 items-center rounded-sm border border-[#2271b1] bg-[#2271b1] px-3 text-[13px] font-medium text-white hover:bg-[#135e96]">' +
      label +
      '</button>'
    );
  }

  function select(id, optionsHtml, extraClass) {
    return (
      '<select id="' +
      id +
      '" class="h-7 rounded-sm border border-[#8c8f94] bg-white px-2 text-[13px] text-[#2c3338] ' +
      (extraClass || '') +
      '">' +
      optionsHtml +
      '</select>'
    );
  }

  function field(name, label, value, opts) {
    opts = opts || {};
    var type = opts.type || 'text';
    var wide = opts.wide;
    var val = value == null ? '' : String(value);
    var id = 'f-' + name.replace(/[^a-z0-9_-]/gi, '-');
    var input =
      type === 'textarea'
        ? '<textarea id="' +
          id +
          '" name="' +
          esc(name) +
          '" rows="3" class="mt-1 w-full rounded-sm border border-[#8c8f94] px-2 py-1.5 text-[13px] shadow-sm focus:border-[#2271b1] focus:outline-none">' +
          esc(val) +
          '</textarea>'
        : '<input id="' +
          id +
          '" name="' +
          esc(name) +
          '" type="' +
          type +
          '" value="' +
          esc(val) +
          '" class="mt-1 w-full rounded-sm border border-[#8c8f94] px-2 py-1.5 text-[13px] shadow-sm focus:border-[#2271b1] focus:outline-none">';
    return (
      '<div class="' +
      (wide ? 'col-span-full' : '') +
      '" data-field-label="' +
      esc((label + ' ' + name).toLowerCase()) +
      '">' +
      '<label for="' +
      id +
      '" class="block text-[13px] font-semibold text-[#1d2327]">' +
      esc(label) +
      '</label>' +
      input +
      '</div>'
    );
  }

  function group(title, fieldsHtml, open) {
    return (
      '<details class="mb-3 overflow-hidden rounded-sm border border-[#c3c4c7] bg-white" ' +
      (open === false ? '' : 'open') +
      '>' +
      '<summary class="cursor-pointer list-none border-b border-[#c3c4c7] bg-[#f6f7f7] px-3 py-2 text-[13px] font-semibold text-[#1d2327] [&::-webkit-details-marker]:hidden">' +
      esc(title) +
      '</summary>' +
      '<div class="grid grid-cols-1 gap-3 p-3 md:grid-cols-2">' +
      fieldsHtml +
      '</div></details>'
    );
  }

  function mediaRow(title, imgUrl, btnId, imgId) {
    return (
      '<div class="mb-3 flex items-start gap-3 rounded-sm border border-dashed border-[#c3c4c7] bg-[#f6f7f7] p-3">' +
      (imgUrl
        ? '<img id="' +
          esc(imgId) +
          '" src="' +
          esc(imgUrl) +
          '" alt="" class="h-16 max-w-[120px] object-contain">'
        : '<span id="' + esc(imgId) + '-empty" class="text-[#646970]">Rasm yo‘q</span>') +
      '<div><div class="mb-1 font-semibold">' +
      esc(title) +
      '</div>' +
      btn('Media tanlash', '', 'id="' + esc(btnId) + '"') +
      '</div></div>'
    );
  }

  function formData(form) {
    var fd = new FormData(form);
    var out = {};
    fd.forEach(function (v, k) {
      out[k] = v;
    });
    Object.keys(out).forEach(function (k) {
      if (k === 'visible_count' || /_id$/.test(k)) out[k] = parseInt(out[k], 10) || 0;
    });
    return out;
  }

  function stripMeta(d) {
    var skip = { i18n: 1, lang: 1, crud: 1, items: 1, cars: 1, models: 1 };
    var keepUrl = {
      address_url: 1,
      service_url: 1,
      testdrive_url: 1,
      phone_href: 1,
      cta_url: 1,
      offer_cta_url: 1,
      car1_url: 1,
      car2_url: 1,
      car1_cta_url: 1,
      car1_td_url: 1,
      car1_credit_url: 1,
      car1_offer_url: 1,
      car2_cta_url: 1,
      car2_td_url: 1,
      car2_credit_url: 1,
      car2_offer_url: 1,
    };
    var footerDerived = {
      brand: 1,
      subtitle: 1,
      address: 1,
      phone: 1,
      phone_href: 1,
      callback_label: 1,
      service_label: 1,
      service_url: 1,
      testdrive_label: 1,
      testdrive_url: 1,
      logo_url: 1,
    };
    var out = {};
    Object.keys(d || {}).forEach(function (k) {
      if (skip[k]) return;
      if (/_default$/.test(k)) return;
      if (/_url$/.test(k) && !keepUrl[k]) return;
      if (activeTab === 'footer' && footerDerived[k]) return;
      out[k] = d[k];
    });
    return out;
  }

  function mediaPick(inputName, imgId) {
    return function () {
      if (typeof wp === 'undefined' || !wp.media) {
        notice('Media library ishlamayapti', true);
        return;
      }
      var form = document.getElementById('avto-edit-form');
      var frame = wp.media({ title: 'Rasm tanlang', button: { text: 'Tanlash' }, multiple: false });
      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        var input = form && form.querySelector('[name="' + inputName + '"]');
        if (input) {
          input.value = attachment.id;
          dirty = true;
        }
        var img = document.getElementById(imgId);
        if (img) img.src = attachment.url;
      });
      frame.open();
    };
  }

  /* ——— LIST VIEW (WP Pages style) ——— */
  function renderList() {
    var items = RESOURCES.filter(function (r) {
      if (!searchQ) return true;
      return (r.title + ' ' + r.id).toLowerCase().indexOf(searchQ.toLowerCase()) !== -1;
    });
    var n = items.length;

    var rows = items
      .map(function (r, i) {
        var zebra = i % 2 === 1 ? 'bg-[#f6f7f7]' : 'bg-white';

        return (
          '<tr class="' +
          zebra +
          ' hover:bg-[#f0f6fc]">' +
          '<th class="w-8 border-b border-[#c3c4c7] px-2 py-2"><input type="checkbox" class="avto-row-check"></th>' +
          '<td class="border-b border-[#c3c4c7] px-2 py-2 font-semibold">' +
          '<button type="button" data-edit="' +
          esc(r.id) +
          '" data-lang="ru" class="text-left text-[#2271b1] hover:text-[#135e96]">' +
          esc(r.title) +
          '</button>' +
          '<div class="mt-1 text-[12px] font-normal text-[#646970]">' +
          '<button type="button" data-edit="' +
          esc(r.id) +
          '" data-lang="ru" class="text-[#2271b1] hover:underline">Edit</button>' +
          '</div></td>' +
          '<td class="border-b border-[#c3c4c7] px-2 py-2"><a class="text-[#2271b1] hover:underline" href="#">' +
          esc(avtodealerAdmin.userName || 'admin') +
          '</a></td>' +
          '<td class="border-b border-[#c3c4c7] px-2 py-2 text-center">' +
          '<button type="button" data-edit="' +
          esc(r.id) +
          '" data-lang="ru" class="inline-flex items-center gap-1 text-[#2271b1]" title="Русский">' +
          '<span class="text-base leading-none">🇷🇺</span>' +
          '<span class="dashicons dashicons-edit text-[16px] leading-none !w-4 !h-4"></span></button></td>' +
          '<td class="border-b border-[#c3c4c7] px-2 py-2 text-center text-[#646970]">—</td>' +
          '<td class="border-b border-[#c3c4c7] px-2 py-2 text-[12px] text-[#2c3338]"><span class="block">Published</span><span class="text-[#646970]">' +
          esc(new Date().toISOString().slice(0, 10).replace(/-/g, '/')) +
          '</span></td></tr>'
        );
      })
      .join('');

    app.innerHTML =
      '<div id="avto-flash" hidden></div>' +
      '<h1 class="mb-2 text-[23px] font-normal leading-tight text-[#1d2327]">Avtodealer Content</h1>' +
      '<div class="mb-2 flex flex-wrap items-center justify-between gap-3">' +
      '<ul class="m-0 flex list-none gap-0 p-0 text-[13px]">' +
      '<li><a class="font-semibold text-[#1d2327] no-underline" href="#">All <span class="text-[#646970]">(' +
      n +
      ')</span></a></li>' +
      '<li class="px-1 text-[#c3c4c7]">|</li>' +
      '<li><a class="text-[#2271b1] no-underline hover:underline" href="#">Published <span class="text-[#646970]">(' +
      n +
      ')</span></a></li></ul>' +
      '<div class="flex items-center gap-1.5">' +
      '<input id="avto-search" type="search" value="' +
      esc(searchQ) +
      '" placeholder="Search pages" class="h-7 w-[180px] rounded-sm border border-[#8c8f94] px-2 text-[13px]">' +
      btn('Search Pages', '', 'id="avto-search-btn"') +
      '</div></div>' +
      '<div class="mb-2 flex flex-wrap items-center justify-between gap-2">' +
      '<div class="flex flex-wrap items-center gap-1.5">' +
      select('avto-bulk', '<option>Bulk actions</option><option value="edit">Edit</option>') +
      btn('Apply', '', 'id="avto-bulk-apply"') +
      select('avto-dates', '<option>All dates</option>') +
      btn('Filter', '', 'id="avto-filter-btn"') +
      '</div>' +
      '<div class="text-[13px] text-[#646970]">' +
      n +
      ' items</div></div>' +
      '<div class="overflow-x-auto border border-[#c3c4c7] bg-white shadow-sm">' +
      '<table class="w-full border-collapse text-left">' +
      '<thead><tr class="bg-white">' +
      '<td class="w-8 border-b border-[#c3c4c7] px-2 py-2"><input type="checkbox" id="avto-check-all"></td>' +
      '<th class="border-b border-[#c3c4c7] px-2 py-2 text-[13px] font-normal">Title</th>' +
      '<th class="border-b border-[#c3c4c7] px-2 py-2 text-[13px] font-normal">Author</th>' +
      '<th class="border-b border-[#c3c4c7] px-2 py-2 text-center text-[13px] font-normal" title="Русский"><span class="text-base">🇷🇺</span></th>' +
      '<th class="border-b border-[#c3c4c7] px-2 py-2 text-center text-[13px] font-normal" title="Comments"><span class="dashicons dashicons-admin-comments text-[#646970]"></span></th>' +
      '<th class="border-b border-[#c3c4c7] px-2 py-2 text-[13px] font-normal">Date</th>' +
      '</tr></thead><tbody>' +
      rows +
      '</tbody></table></div>' +
      '<div class="mt-2 flex flex-wrap items-center justify-between gap-2">' +
      '<div class="flex flex-wrap items-center gap-1.5">' +
      select('avto-bulk2', '<option>Bulk actions</option>') +
      btn('Apply') +
      '</div>' +
      '<div class="text-[13px] text-[#646970]">' +
      n +
      ' items</div></div>';

    bindList();
  }

  function bindList() {
    document.querySelectorAll('[data-edit]').forEach(function (el) {
      el.addEventListener('click', function () {
        openEdit(el.getAttribute('data-edit'), el.getAttribute('data-lang') || activeLang);
      });
    });
    var searchBtn = document.getElementById('avto-search-btn');
    var searchInput = document.getElementById('avto-search');
    if (searchBtn && searchInput) {
      searchBtn.addEventListener('click', function () {
        searchQ = searchInput.value || '';
        renderList();
      });
      searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          searchQ = searchInput.value || '';
          renderList();
        }
      });
    }
    var checkAll = document.getElementById('avto-check-all');
    if (checkAll) {
      checkAll.addEventListener('change', function () {
        document.querySelectorAll('.avto-row-check').forEach(function (c) {
          c.checked = checkAll.checked;
        });
      });
    }
  }

  /* ——— EDIT VIEW ——— */
  async function openEdit(tab, lang) {
    if (dirty && !window.confirm('Saqlanmagan o‘zgarishlar bor. Davom?')) return;
    activeTab = tab;
    activeLang = lang;
    view = 'edit';
    dirty = false;
    app.innerHTML = '<p class="text-[#646970]">Yuklanmoqda…</p>';
    try {
      currentData = await api(activeTab === 'leads' ? 'leads' : activeTab, 'GET');
      if (currentData && currentData.lang) activeLang = currentData.lang;
      renderEdit();
    } catch (err) {
      notice(err.message, true);
      view = 'list';
      renderList();
      notice(err.message, true);
    }
  }

  function publishBox() {
    var now = new Date();
    var dateStr =
      now.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
      ' at ' +
      now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
    return (
      '<div class="mb-4 overflow-hidden rounded-sm border border-[#c3c4c7] bg-white shadow-sm">' +
      '<div class="flex items-center justify-between border-b border-[#c3c4c7] bg-white px-3 py-2">' +
      '<h2 class="m-0 text-[14px] font-semibold text-[#1d2327]">Publish</h2>' +
      '<span class="flex gap-1 text-[#646970]"><span class="dashicons dashicons-arrow-up-alt2 !text-[16px]"></span><span class="dashicons dashicons-arrow-down-alt2 !text-[16px]"></span><span class="dashicons dashicons-arrow-up !text-[16px]"></span></span></div>' +
      '<div class="space-y-3 p-3">' +
      '<div class="flex justify-end">' +
      btn('Preview Changes', '', 'id="avto-preview"') +
      '</div>' +
      '<div class="flex items-start gap-2 text-[13px]"><span class="dashicons dashicons-post-status mt-0.5 text-[#646970]"></span><div>Status: <strong>Published</strong> <button type="button" class="text-[#2271b1] hover:underline">Edit</button></div></div>' +
      '<div class="flex items-start gap-2 text-[13px]"><span class="dashicons dashicons-visibility mt-0.5 text-[#646970]"></span><div>Visibility: <strong>Public</strong> <button type="button" class="text-[#2271b1] hover:underline">Edit</button></div></div>' +
      '<div class="flex items-start gap-2 text-[13px]"><span class="dashicons dashicons-calendar-alt mt-0.5 text-[#646970]"></span><div>Published on: <strong>' +
      esc(dateStr) +
      '</strong> <button type="button" class="text-[#2271b1] hover:underline">Edit</button></div></div>' +
      '</div>' +
      '<div class="flex items-center justify-between border-t border-[#c3c4c7] bg-[#f6f7f7] px-3 py-2.5">' +
      '<button type="button" id="avto-trash" class="text-[13px] text-[#b32d2e] underline hover:text-[#8a2424]">Move to Trash</button>' +
      btnPrimary('Update', 'id="avto-update"') +
      '</div></div>'
    );
  }

  function languagesBox() {
    var otherLangs = languages.filter(function (l) {
      return l !== activeLang;
    });
    var langOpts = languages
      .map(function (l) {
        return (
          '<option value="' +
          l +
          '"' +
          (l === activeLang ? ' selected' : '') +
          '>' +
          esc(langLabel(l)) +
          '</option>'
        );
      })
      .join('');

    var translations = otherLangs
      .map(function (l) {
        return (
          '<div class="mb-2 flex items-center gap-2 last:mb-0">' +
          '<span class="text-base leading-none" aria-hidden="true">' +
          flag(l) +
          '</span>' +
          '<button type="button" data-switch-lang="' +
          esc(l) +
          '" class="text-[#2271b1] hover:text-[#135e96]" title="Edit translation">' +
          '<span class="dashicons dashicons-edit !text-[16px] !w-4 !h-4"></span></button>' +
          '<input type="text" readonly value="' +
          esc(activeTab.toUpperCase() + '-' + l.toUpperCase()) +
          '" class="h-7 flex-1 rounded-sm border border-[#8c8f94] px-2 text-[13px] text-[#2c3338]">' +
          '</div>'
        );
      })
      .join('');

    return (
      '<div class="overflow-hidden rounded-sm border border-[#c3c4c7] bg-white shadow-sm">' +
      '<div class="flex items-center justify-between border-b border-[#c3c4c7] bg-white px-3 py-2">' +
      '<h2 class="m-0 text-[14px] font-semibold text-[#1d2327]">Languages</h2>' +
      '<span class="flex gap-1 text-[#646970]"><span class="dashicons dashicons-arrow-up-alt2 !text-[16px]"></span><span class="dashicons dashicons-arrow-down-alt2 !text-[16px]"></span><span class="dashicons dashicons-arrow-up !text-[16px]"></span></span></div>' +
      '<div class="space-y-4 p-3">' +
      '<div>' +
      '<div class="mb-1 text-[13px] font-semibold">Language</div>' +
      '<div class="flex items-center gap-2">' +
      '<span class="text-base leading-none" aria-hidden="true">' +
      flag(activeLang) +
      '</span>' +
      '<select id="avto-lang-select" class="h-8 flex-1 rounded-sm border border-[#8c8f94] bg-white px-2 text-[13px]">' +
      langOpts +
      '</select></div></div>' +
      '<div>' +
      '<div class="mb-1 text-[13px] font-semibold">Translations</div>' +
      (translations ||
        '<p class="m-0 text-[13px] text-[#646970]">Boshqa tillar yo‘q</p>') +
      '</div></div></div>'
    );
  }

  function fieldsForTab(d) {
    d = d || {};
    if (activeTab === 'header') {
      return (
        group(
          'Kontakt',
          field('address', 'Manzil', d.address, { wide: true }) +
            field('address_url', 'Manzil URL', d.address_url) +
            field('phone', 'Telefon', d.phone) +
            field('phone_href', 'Tel link', d.phone_href) +
            field('status', 'Status', d.status) +
            field('hours', 'Ish vaqti', d.hours || '') +
            field('callback_label', 'Tugma', d.callback_label) +
            field('brand', 'Brend', d.brand) +
            field('subtitle', 'Subtitle', d.subtitle) +
            field('logo_id', 'Logo ID', d.logo_id, { type: 'number' })
        ) + mediaRow('Logo', d.logo_url, 'pick-logo', 'img-logo')
      );
    }
    if (activeTab === 'hero' || activeTab === 'hero-tank300' || activeTab === 'hero-tank500') {
      return (
        group(
          'Hero matn',
          field('eyebrow', 'Eyebrow', d.eyebrow) +
            field('title', 'Title', d.title) +
            field('subtitle', 'Subtitle', d.subtitle, { wide: true }) +
            field('cta_label', 'CTA', d.cta_label) +
            field('cta_url', 'CTA URL', d.cta_url) +
            field('image_id', 'Desktop ID', d.image_id, { type: 'number' }) +
            field('image_mobile_id', 'Mobile ID', d.image_mobile_id, { type: 'number' })
        ) +
        mediaRow('Desktop', d.image_url, 'pick-hero', 'img-hero') +
        mediaRow('Mobile', d.image_mobile_url, 'pick-hero-m', 'img-hero-m')
      );
    }
    if (activeTab === 'catalog') {
      return (
        group(
          'Catalog',
          field('offer_title', 'Offer title', d.offer_title, { wide: true }) +
            field('offer_cta_label', 'Offer CTA', d.offer_cta_label) +
            field('promo_title', 'Promo', d.promo_title, { wide: true, type: 'textarea' }) +
            field('car1_title', 'Car 1 title', d.car1_title) +
            field('car1_label', 'Car 1 label', d.car1_label || d.car1_title || '') +
            field('car2_title', 'Car 2 title', d.car2_title) +
            field('car2_label', 'Car 2 label', d.car2_label || d.car2_title || '') +
            field('car1_image_id', 'Car 1 image ID', d.car1_image_id, { type: 'number' }) +
            field('car2_image_id', 'Car 2 image ID', d.car2_image_id, { type: 'number' })
        ) +
        mediaRow('Car 1', d.car1_image_url, 'pick-c1', 'img-c1') +
        mediaRow('Car 2', d.car2_image_url, 'pick-c2', 'img-c2')
      );
    }
    if (activeTab === 'footer') {
      return group(
        'Footer',
        field('disclaimer_title', 'Disclaimer', d.disclaimer_title || '') +
          field('legal1', 'Legal 1', d.legal1, { wide: true, type: 'textarea' }) +
          field('legal2', 'Legal 2', d.legal2, { wide: true, type: 'textarea' }) +
          field('copy_note', 'Copy note', d.copy_note, { wide: true }) +
          field('link_legal', 'Huquqiy link', d.link_legal || '') +
          field('link_promo', 'Aksiya link', d.link_promo || '')
      );
    }
    if (activeTab === 'modal') {
      return group(
        'Modal',
        field('title', 'Title', d.title, { wide: true }) +
          field('subtitle', 'Subtitle', d.subtitle, { wide: true, type: 'textarea' }) +
          field('model_label', 'Model label', d.model_label) +
          field('model_options', 'Models', d.model_options, { wide: true, type: 'textarea' }) +
          field('phone_label', 'Phone label', d.phone_label) +
          field('button', 'Button', d.button) +
          field('consent', 'Consent', d.consent, { wide: true, type: 'textarea' })
      );
    }
    if (activeTab === 'leads') {
      var items = Array.isArray(d.items) ? d.items : [];
      var rows = items
        .map(function (item) {
          return (
            '<tr class="odd:bg-white even:bg-[#f6f7f7]"><td class="border-b border-[#c3c4c7] px-2 py-2">' +
            esc(item.phone || '—') +
            '</td><td class="border-b border-[#c3c4c7] px-2 py-2">' +
            esc(item.model || '—') +
            '</td><td class="border-b border-[#c3c4c7] px-2 py-2">' +
            esc(item.created_at || '—') +
            '</td></tr>'
          );
        })
        .join('');
      return (
        '<div class="overflow-hidden rounded-sm border border-[#c3c4c7] bg-white"><table class="w-full text-left"><thead><tr><th class="border-b px-2 py-2">Telefon</th><th class="border-b px-2 py-2">Model</th><th class="border-b px-2 py-2">Vaqt</th></tr></thead><tbody>' +
        (rows || '<tr><td class="px-2 py-4" colspan="3">Bo‘sh</td></tr>') +
        '</tbody></table></div>'
      );
    }
    // generic fallback for other tabs — show common fields if present
    var keys = Object.keys(stripMeta(d)).filter(function (k) {
      return typeof d[k] !== 'object';
    });
    var html = keys
      .slice(0, 40)
      .map(function (k) {
        var isLong = String(d[k] || '').length > 80 || /text|note|legal|perks|options|consent|subtitle|title|promo/i.test(k);
        return field(k, k, d[k], { wide: true, type: isLong ? 'textarea' : 'text' });
      })
      .join('');
    return group('Maydonlar', html || '<p class="text-[#646970]">Maydonlar yo‘q</p>');
  }

  function renderEdit() {
    var res = RESOURCES.find(function (r) {
      return r.id === activeTab;
    });
    var title = (res && res.title) || activeTab;
    var isLeads = activeTab === 'leads';

    app.innerHTML =
      '<div id="avto-flash" hidden></div>' +
      '<div class="mb-3 flex flex-wrap items-center gap-2">' +
      '<button type="button" id="avto-back" class="text-[13px] text-[#2271b1] hover:underline">← Barcha kontent</button>' +
      '<span class="text-[#c3c4c7]">|</span>' +
      '<h1 class="m-0 text-[23px] font-normal text-[#1d2327]">Edit Content</h1>' +
      '<span class="rounded-sm bg-[#dcdcde] px-1.5 py-0.5 text-[11px] uppercase text-[#2c3338]">' +
      esc(activeLang) +
      '</span></div>' +
      '<div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_280px]">' +
      '<div>' +
      '<div class="mb-3 rounded-sm border border-[#c3c4c7] bg-white p-3 shadow-sm">' +
      '<input type="text" readonly value="' +
      esc(title) +
      '" class="w-full border-0 border-b border-[#c3c4c7] bg-transparent pb-2 text-[22px] font-normal text-[#1d2327] outline-none">' +
      '</div>' +
      (isLeads
        ? fieldsForTab(currentData)
        : '<form id="avto-edit-form">' + fieldsForTab(currentData) + '</form>') +
      '</div>' +
      '<div class="lg:sticky lg:top-8 lg:self-start">' +
      publishBox() +
      languagesBox() +
      '</div></div>';

    bindEdit();
  }

  function bindEdit() {
    var form = document.getElementById('avto-edit-form');
    if (form) {
      form.addEventListener('input', function () {
        dirty = true;
      });
      form.addEventListener('change', function () {
        dirty = true;
      });
    }

    document.getElementById('avto-back').addEventListener('click', function () {
      if (dirty && !window.confirm('Saqlanmagan o‘zgarishlar bor. Chiqasizmi?')) return;
      dirty = false;
      view = 'list';
      renderList();
    });

    var preview = document.getElementById('avto-preview');
    if (preview) {
      preview.addEventListener('click', function () {
        window.open(avtodealerAdmin.siteUrl || '/', '_blank');
      });
    }

    var update = document.getElementById('avto-update');
    if (update) {
      update.addEventListener('click', async function () {
        if (activeTab === 'leads') return;
        if (busy) return;
        var f = document.getElementById('avto-edit-form');
        if (!f) return;
        busy = true;
        update.disabled = true;
        try {
          currentData = await api(activeTab, 'PUT', formData(f));
          dirty = false;
          notice('Updated · ' + activeLang.toUpperCase());
          renderEdit();
        } catch (err) {
          notice(err.message, true);
        } finally {
          busy = false;
        }
      });
    }

    var trash = document.getElementById('avto-trash');
    if (trash) {
      trash.addEventListener('click', async function () {
        if (activeTab === 'leads') return;
        if (!window.confirm('Defaultga qaytarilsinmi?')) return;
        try {
          currentData = await api(activeTab, 'DELETE');
          dirty = false;
          notice('Defaultga qaytarildi');
          renderEdit();
        } catch (err) {
          notice(err.message, true);
        }
      });
    }

    var langSelect = document.getElementById('avto-lang-select');
    if (langSelect) {
      langSelect.addEventListener('change', function () {
        openEdit(activeTab, langSelect.value);
      });
    }

    document.querySelectorAll('[data-switch-lang]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openEdit(activeTab, btn.getAttribute('data-switch-lang'));
      });
    });

    // media picks
    var picks = [
      ['pick-logo', 'logo_id', 'img-logo'],
      ['pick-hero', 'image_id', 'img-hero'],
      ['pick-hero-m', 'image_mobile_id', 'img-hero-m'],
      ['pick-c1', 'car1_image_id', 'img-c1'],
      ['pick-c2', 'car2_image_id', 'img-c2'],
    ];
    picks.forEach(function (row) {
      var el = document.getElementById(row[0]);
      if (el) el.addEventListener('click', mediaPick(row[1], row[2]));
    });
  }

  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && (e.key || '').toLowerCase() === 's' && view === 'edit') {
      e.preventDefault();
      var u = document.getElementById('avto-update');
      if (u) u.click();
    }
  });

  window.addEventListener('beforeunload', function (e) {
    if (!dirty) return;
    e.preventDefault();
    e.returnValue = '';
  });

  renderList();
})();
