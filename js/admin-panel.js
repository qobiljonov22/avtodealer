(function () {
  'use strict';

  if (typeof avtodealerAdmin === 'undefined') return;

  var panel = document.getElementById('avto-cms-panel');
  var toastEl = document.getElementById('avto-cms-toast');
  var activeTab = 'header';
  var activeLang = (avtodealerAdmin.lang || 'ru').toLowerCase();
  var languages = Array.isArray(avtodealerAdmin.languages) && avtodealerAdmin.languages.length
    ? avtodealerAdmin.languages
    : ['ru', 'uz', 'en'];
  var dirty = false;
  var busy = false;
  var saveMethod = 'PUT';

  var ui = {
    tab:
      'rounded-xl border border-white/10 bg-[#14191f] px-3.5 py-2.5 text-[13px] font-semibold text-[#8b95a5] transition hover:border-white/20 hover:text-white',
    tabOn:
      'rounded-xl border border-transparent bg-gradient-to-br from-[#ff6a00] to-[#ff9549] px-3.5 py-2.5 text-[13px] font-semibold text-[#111] shadow-[0_8px_24px_rgba(255,106,0,0.28)]',
    lang:
      'min-w-[46px] rounded-xl border border-white/10 bg-[#14191f] px-3 py-2.5 text-center text-[13px] font-semibold uppercase text-[#8b95a5] transition hover:border-white/20 hover:text-white',
    langOn:
      'min-w-[46px] rounded-xl border border-[#ff9549]/50 bg-[#222a33] px-3 py-2.5 text-center text-[13px] font-semibold uppercase text-white',
    btn:
      'cursor-pointer rounded-xl border border-white/10 bg-[#1a222b] px-3.5 py-2.5 text-[13px] font-bold text-white transition hover:-translate-y-px disabled:cursor-wait disabled:opacity-55',
    btnPrimary:
      'cursor-pointer rounded-xl border border-transparent bg-gradient-to-br from-[#ff6a00] to-[#ff9549] px-3.5 py-2.5 text-[13px] font-bold text-[#111] transition hover:-translate-y-px disabled:cursor-wait disabled:opacity-55',
    btnGhost:
      'cursor-pointer rounded-xl border border-white/10 bg-transparent px-3.5 py-2.5 text-[13px] font-bold text-white transition hover:-translate-y-px disabled:cursor-wait disabled:opacity-55',
    btnDanger:
      'cursor-pointer rounded-xl border border-[#ff5c5c]/40 bg-[#ff5c5c]/12 px-3.5 py-2.5 text-[13px] font-bold text-[#ffd0d0] transition hover:-translate-y-px disabled:cursor-wait disabled:opacity-55',
    input:
      'w-full rounded-xl border border-white/10 bg-[#0a0d11] px-3 py-2.5 text-[13px] text-white outline-none transition focus:border-[#ff6a00]/55 focus:shadow-[0_0_0_3px_rgba(255,106,0,0.15)]',
  };

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/"/g, '&quot;');
  }

  function toast(text, type) {
    if (!toastEl) return;
    toastEl.className =
      'fixed bottom-6 right-6 z-[100000] min-w-[240px] max-w-[360px] rounded-[14px] border bg-[#151b22] px-4 py-3.5 text-white shadow-[0_20px_50px_rgba(0,0,0,0.4)] transition duration-250 translate-y-0 opacity-100 ' +
      (type === 'error' ? 'border-[#ff5c5c]/45' : 'border-[#3ddc84]/45');
    toastEl.textContent = text;
    clearTimeout(toast._t);
    toast._t = setTimeout(function () {
      toastEl.className =
        'pointer-events-none fixed bottom-6 right-6 z-[100000] min-w-[240px] max-w-[360px] translate-y-5 rounded-[14px] border border-white/10 bg-[#151b22] px-4 py-3.5 text-white opacity-0 shadow-[0_20px_50px_rgba(0,0,0,0.4)] transition duration-250';
    }, 3200);
  }

  function buildApiUrl(path) {
    var clean = String(path || '').replace(/^\//, '');
    if (avtodealerAdmin.useQuery) {
      var url = new URL(avtodealerAdmin.siteUrl || '/', window.location.origin);
      var route = (avtodealerAdmin.namespace || '/avtodealer/v1/') + clean;
      url.searchParams.set('rest_route', route.replace(/\/$/, '') || '/avtodealer/v1');
      if (clean) url.searchParams.set('lang', activeLang);
      return url.toString();
    }
    var base = (avtodealerAdmin.restUrl || '').replace(/\/?$/, '/') + clean;
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

  function field(name, label, value, opts) {
    opts = opts || {};
    var type = opts.type || 'text';
    var wide = opts.wide ? ' md:col-span-2' : '';
    var val = value == null ? '' : String(value);
    var input =
      type === 'textarea'
        ? '<textarea class="' + ui.input + '" name="' + esc(name) + '" rows="3">' + esc(val) + '</textarea>'
        : '<input class="' + ui.input + '" type="' + type + '" name="' + esc(name) + '" value="' + esc(val) + '">';
    return (
      '<p class="m-0' +
      wide +
      '" data-field-label="' +
      esc((label + ' ' + name).toLowerCase()) +
      '">' +
      '<label class="mb-1.5 block text-xs font-semibold text-[#8b95a5]">' +
      esc(label) +
      '</label>' +
      input +
      '</p>'
    );
  }

  function group(title, hint, fieldsHtml, open) {
    var collapsed = open === false;
    return (
      '<section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.015]' +
      (collapsed ? ' is-collapsed' : '') +
      '">' +
      '<button type="button" class="flex w-full items-center justify-between gap-3 border-0 bg-transparent px-4 py-3.5 text-left text-white" data-group-toggle>' +
      '<div class="text-[13px] font-bold uppercase tracking-wide">' +
      esc(title) +
      '</div>' +
      '<span class="text-xs text-[#8b95a5]">' +
      esc(hint || 'ochish / yopish') +
      '</span></button>' +
      '<div class="grid grid-cols-1 gap-3 px-4 pb-4 md:grid-cols-2' +
      (collapsed ? ' hidden' : '') +
      '" data-group-body>' +
      fieldsHtml +
      '</div></section>'
    );
  }

  function formData(form) {
    var fd = new FormData(form);
    var out = {};
    fd.forEach(function (v, k) {
      out[k] = v;
    });
    Object.keys(out).forEach(function (k) {
      if (k === 'visible_count' || /_id$/.test(k)) {
        out[k] = parseInt(out[k], 10) || 0;
      }
    });
    return out;
  }

  function mediaCard(id, title, imgUrl, btnId, imgId) {
    return (
      '<div class="flex items-center gap-3.5 rounded-2xl border border-dashed border-[#ff9549]/35 bg-gradient-to-br from-[#171d24] to-[#10151a] p-3.5" data-preview="' +
      esc(id) +
      '">' +
      (imgUrl
        ? '<img class="h-16 w-auto max-w-[120px] object-contain" src="' + esc(imgUrl) + '" alt="" id="' + esc(imgId) + '">'
        : '<strong class="text-[#8b95a5]">Rasm yo‘q</strong>') +
      '<div class="flex min-w-0 flex-col gap-2">' +
      '<strong class="text-[13px] text-white">' +
      esc(title) +
      '</strong>' +
      '<button type="button" class="' +
      ui.btnGhost +
      '" id="' +
      esc(btnId) +
      '">Media tanlash</button></div></div>'
    );
  }

  function mediaPick(inputName, imgId, previewSel, title) {
    return function () {
      if (typeof wp === 'undefined' || !wp.media) {
        toast('Media library ishlamayapti', 'error');
        return;
      }
      var form = document.getElementById('avto-resource-form');
      var frame = wp.media({ title: title || 'Rasm tanlang', button: { text: 'Tanlash' }, multiple: false });
      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        var input = form.querySelector('[name="' + inputName + '"]');
        if (input) {
          input.value = attachment.id;
          markDirty();
        }
        var img = document.getElementById(imgId);
        if (img) img.src = attachment.url;
        else {
          var box = document.querySelector(previewSel);
          if (box) {
            box.insertAdjacentHTML(
              'afterbegin',
              '<img class="h-16 w-auto max-w-[120px] object-contain" src="' +
                esc(attachment.url) +
                '" alt="" id="' +
                esc(imgId) +
                '">'
            );
          }
        }
      });
      frame.open();
    };
  }

  function completeness(form) {
    if (!form) return 0;
    var inputs = form.querySelectorAll('input, textarea');
    var total = 0;
    var filled = 0;
    inputs.forEach(function (el) {
      if (el.type === 'hidden') return;
      total += 1;
      if (String(el.value || '').trim() !== '') filled += 1;
    });
    return total ? Math.round((filled / total) * 100) : 0;
  }

  function updateMeter() {
    var form = document.getElementById('avto-resource-form');
    var pct = completeness(form);
    var bar = document.getElementById('avto-meter-bar');
    var label = document.getElementById('avto-meter-label');
    if (bar) bar.style.width = pct + '%';
    if (label) label.textContent = pct + '% tayyor';
  }

  function updateLivePreview() {
    var form = document.getElementById('avto-resource-form');
    if (!form) return;
    var get = function (name) {
      var el = form.querySelector('[name="' + name + '"]');
      return el ? String(el.value || '').trim() : '';
    };
    var title = document.getElementById('avto-live-title');
    var sub = document.getElementById('avto-live-sub');
    if (activeTab === 'header') {
      if (title) title.textContent = get('brand') || 'Brand';
      if (sub) sub.textContent = (get('subtitle') || 'Subtitle') + ' · ' + (get('phone') || '');
    } else if (activeTab === 'hero' || activeTab === 'hero-tank300' || activeTab === 'hero-tank500') {
      if (title) title.textContent = get('title') || 'Hero';
      if (sub) sub.textContent = get('subtitle') || get('eyebrow') || '';
    } else if (activeTab === 'catalog') {
      if (title) title.textContent = get('car1_title') || 'Catalog';
      if (sub) sub.textContent = get('offer_title') || get('promo_title') || '';
    } else if (activeTab === 'models') {
      if (title) title.textContent = get('car1_title') || 'Models';
      if (sub) sub.textContent = (get('car1_title') || '') + ' · ' + (get('car2_title') || '');
    } else if (activeTab === 'configs') {
      if (title) title.textContent = get('title') || 'Configs';
      if (sub) sub.textContent = get('trim1_title') || '';
    } else if (activeTab === 'footer') {
      if (title) title.textContent = 'Footer';
      if (sub) sub.textContent = get('copy_note') || '';
    } else {
      if (title) title.textContent = get('title') || activeTab;
      if (sub) sub.textContent = get('text') || get('subtitle') || get('cta_label') || '';
    }
  }

  function insightHtml(d) {
    var cloneOptions = languages
      .filter(function (l) {
        return l !== activeLang;
      })
      .map(function (l) {
        return '<option value="' + esc(l) + '">' + esc(l.toUpperCase()) + '</option>';
      })
      .join('');

    var resources = Array.isArray(avtodealerAdmin.resources) ? avtodealerAdmin.resources : [];
    var apiList = resources
      .map(function (r) {
        return (
          '<button type="button" class="rounded-lg border border-white/10 bg-[#0a0d11] px-2 py-1 font-mono text-[10px] text-[#c7d0dc] transition hover:border-[#ff9549]/50 hover:text-white" data-tab="' +
          esc(r) +
          '">' +
          esc(r) +
          '</button>'
        );
      })
      .join('');

    return (
      '<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">' +
      '<div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">' +
      '<div class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#8b95a5]">Live preview</div>' +
      '<div id="avto-live-title" class="mt-2 text-lg font-bold text-white">' +
      esc(d.brand || d.title || d.car1_title || activeTab) +
      '</div>' +
      '<div id="avto-live-sub" class="mt-1 truncate text-sm text-[#8b95a5]">' +
      esc(d.subtitle || d.eyebrow || d.offer_title || d.car2_title || '') +
      '</div></div>' +
      '<div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">' +
      '<div class="flex items-center justify-between gap-2">' +
      '<div class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#8b95a5]">To‘liqlik</div>' +
      '<div id="avto-meter-label" class="text-sm font-bold text-[#ff9549]">0%</div></div>' +
      '<div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">' +
      '<div id="avto-meter-bar" class="h-full w-0 rounded-full bg-gradient-to-r from-[#ff6a00] to-[#ff9549] transition-all duration-300"></div></div>' +
      '<p class="mt-2 text-xs text-[#8b95a5]">Bo‘sh maydonlar kamaysa, foiz oshadi</p></div>' +
      '<div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">' +
      '<div class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#8b95a5]">Tezkor amallar</div>' +
      '<div class="mt-3 flex flex-wrap gap-2">' +
      '<a class="inline-flex items-center rounded-xl border border-white/10 px-3 py-2 text-xs font-bold text-white no-underline hover:border-[#ff9549]/50" href="' +
      esc(avtodealerAdmin.siteUrl || '/') +
      '" target="_blank" rel="noopener">Sayt ↗</a>' +
      '<button type="button" class="' +
      ui.btnGhost +
      ' !px-3 !py-2 !text-xs" id="avto-clone-run">Tilni ko‘chir</button>' +
      '<select id="avto-clone-from" class="rounded-xl border border-white/10 bg-[#0a0d11] px-2 py-2 text-xs text-white">' +
      cloneOptions +
      '</select></div>' +
      '<p class="mt-2 text-xs text-[#8b95a5]">Boshqa tildan joriy tilga kontent</p></div></div>' +
      '<div class="mb-4 rounded-2xl border border-white/10 bg-white/[0.02] p-4">' +
      '<div class="flex flex-wrap items-center justify-between gap-2">' +
      '<div class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#8b95a5]">API resources · GET POST PUT DELETE</div>' +
      '<code class="font-mono text-[11px] text-[#8b95a5]">/avtodealer/v1/{resource}?lang=' +
      esc(activeLang) +
      '</code></div>' +
      '<div class="mt-3 flex flex-wrap gap-1.5">' +
      apiList +
      '<button type="button" class="rounded-lg border border-[#3ddc84]/30 bg-[#3ddc84]/10 px-2 py-1 font-mono text-[10px] text-[#3ddc84]" data-tab="leads">leads</button>' +
      '</div></div>'
    );
  }

  function navHtml() {
    var tabs = [
      { id: 'header', label: 'Header' },
      { id: 'hero', label: 'Hero' },
      { id: 'hero-tank300', label: 'Hero TANK 300' },
      { id: 'hero-tank500', label: 'Hero TANK 500' },
      { id: 'catalog', label: 'Catalog' },
      { id: 'models', label: 'Models' },
      { id: 'configs', label: 'Configs' },
      { id: 'tradein', label: 'Trade-in' },
      { id: 'credit', label: 'Credit' },
      { id: 'corporate', label: 'Corporate' },
      { id: 'contact', label: 'Contact' },
      { id: 'footer', label: 'Footer' },
      { id: 'modal', label: 'Modal' },
      { id: 'leads', label: 'Leads' },
    ];
    return (
      '<div class="mb-4 flex flex-wrap items-center justify-between gap-3">' +
      '<div class="flex flex-wrap gap-2">' +
      tabs
        .map(function (t) {
          return (
            '<button type="button" class="' +
            (t.id === activeTab ? ui.tabOn : ui.tab) +
            '" data-tab="' +
            t.id +
            '">' +
            t.label +
            '</button>'
          );
        })
        .join('') +
      '</div>' +
      '<div class="flex flex-wrap items-center gap-2">' +
      '<span class="mr-1 text-[11px] font-bold uppercase tracking-[0.12em] text-[#8b95a5]">Til</span>' +
      languages
        .map(function (l) {
          return (
            '<button type="button" class="' +
            (l === activeLang ? ui.langOn : ui.lang) +
            '" data-lang="' +
            l +
            '">' +
            l +
            '</button>'
          );
        })
        .join('') +
      '</div></div>'
    );
  }

  function actionsHtml() {
    return (
      '<div class="sticky bottom-3 z-[5] mx-3 mb-3 flex flex-wrap gap-2 rounded-2xl border border-white/10 bg-[#0a0d11]/92 p-3 shadow-[0_16px_40px_rgba(0,0,0,0.35)] backdrop-blur">' +
      '<button type="button" class="' +
      ui.btnGhost +
      '" id="avto-get">GET</button>' +
      '<button type="submit" form="avto-resource-form" class="' +
      ui.btnPrimary +
      '" data-method="PUT" id="avto-put">Saqlash (PUT)</button>' +
      '<button type="submit" form="avto-resource-form" class="' +
      ui.btn +
      '" data-method="POST">POST</button>' +
      '<button type="button" class="' +
      ui.btnDanger +
      '" id="avto-delete">Reset</button>' +
      '<span class="ml-auto self-center text-xs text-[#8b95a5]">Ctrl/⌘ + S · <span id="avto-dirty" class="font-bold text-[#ff9549]" hidden>Saqlanmagan</span></span></div>'
    );
  }

  function shell(title, bodyHtml, mediaHtml, data) {
    var endpoint = '/avtodealer/v1/' + activeTab + '?lang=' + activeLang;
    return (
      navHtml() +
      insightHtml(data || {}) +
      '<div class="animate-[avtoIn_.35s_ease] overflow-hidden rounded-[18px] border border-white/10 bg-gradient-to-b from-[#14191f] to-[#10151b]">' +
      '<div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 bg-white/[0.02] px-5 py-4">' +
      '<div>' +
      '<div class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#ff9549]">Studio block</div>' +
      '<h2 class="m-0 mt-1 text-lg font-bold text-white">' +
      esc(title) +
      ' · ' +
      esc(activeLang.toUpperCase()) +
      '</h2></div>' +
      '<div class="flex gap-1.5">' +
      '<span class="rounded-lg bg-[#5cb8ff] px-2 py-1 text-[10px] font-extrabold tracking-wide text-[#0b0d10]">GET</span>' +
      '<span class="rounded-lg bg-[#3ddc84] px-2 py-1 text-[10px] font-extrabold tracking-wide text-[#0b0d10]">POST</span>' +
      '<span class="rounded-lg bg-[#f5c542] px-2 py-1 text-[10px] font-extrabold tracking-wide text-[#0b0d10]">PUT</span>' +
      '<span class="rounded-lg bg-[#ff5c5c] px-2 py-1 text-[10px] font-extrabold tracking-wide text-white">DELETE</span>' +
      '</div></div>' +
      '<div class="flex flex-wrap items-center justify-between gap-2.5 border-b border-white/10 px-5 py-3.5">' +
      '<div class="flex min-w-0 items-center gap-2">' +
      '<code id="avto-endpoint-code" class="inline-block max-w-[min(560px,70vw)] overflow-hidden text-ellipsis whitespace-nowrap rounded-[10px] border border-white/10 bg-[#0a0d11] px-2.5 py-2 font-mono text-xs text-[#c7d0dc]">' +
      esc(endpoint) +
      '</code>' +
      '<button type="button" class="' +
      ui.btnGhost +
      '" id="avto-copy-endpoint">Copy</button></div>' +
      '<input id="avto-field-search" class="min-w-[180px] max-w-[280px] flex-1 rounded-xl border border-white/10 bg-[#0a0d11] px-3 py-2.5 text-[13px] text-white outline-none focus:border-[#ff6a00]/55 focus:shadow-[0_0_0_3px_rgba(255,106,0,0.15)]" type="search" placeholder="Maydon qidirish…">' +
      '</div>' +
      '<form id="avto-resource-form" class="grid gap-3.5 px-3 pb-5 pt-2">' +
      bodyHtml +
      '</form>' +
      (mediaHtml
        ? '<div class="grid grid-cols-1 gap-3 px-3 pb-4 md:grid-cols-2">' + mediaHtml + '</div>'
        : '') +
      actionsHtml() +
      '</div>'
    );
  }

  function stripMeta(d) {
    var skipExact = {
      i18n: 1,
      lang: 1,
      crud: 1,
      items: 1,
      cars: 1,
      models: 1,
    };
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
      if (skipExact[k]) return;
      if (/_default$/.test(k)) return;
      if (/_url$/.test(k) && !keepUrl[k]) return;
      if (activeTab === 'footer' && footerDerived[k]) return;
      out[k] = d[k];
    });
    return out;
  }

  async function cloneFromLanguage(fromLang) {
    if (activeTab === 'leads') {
      toast('Leads tilga bog‘liq emas', 'error');
      return;
    }
    if (!fromLang || fromLang === activeLang) return;
    if (busy) return;
    if (
      !window.confirm(
        fromLang.toUpperCase() +
          ' → ' +
          activeLang.toUpperCase() +
          ': kontent ko‘chirilsinmi? Joriy maydonlar ustiga yoziladi.'
      )
    ) {
      return;
    }
    setBusy(true);
    try {
      var prev = activeLang;
      activeLang = fromLang;
      var source = await api(activeTab, 'GET');
      activeLang = prev;
      var saved = await api(activeTab, 'PUT', stripMeta(source));
      render(saved);
      toast('Til ko‘chirildi · ' + fromLang.toUpperCase() + ' → ' + prev.toUpperCase());
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      setBusy(false);
    }
  }

  function renderHeader(d) {
    panel.innerHTML = shell(
      'Header',
      group(
        'Kontakt',
        'manzil / telefon',
        field('address', 'Manzil', d.address, { wide: true }) +
          field('address_url', 'Manzil URL', d.address_url) +
          field('phone', 'Telefon', d.phone) +
          field('phone_href', 'Tel link', d.phone_href) +
          field('status', 'Status', d.status) +
          field('hours', 'Ish vaqti', d.hours || '')
      ) +
        group(
          'Navigatsiya',
          'servis / test-drive',
          field('service_label', 'Servis matni', d.service_label) +
            field('service_url', 'Servis URL', d.service_url) +
            field('testdrive_label', 'Test-drive matni', d.testdrive_label) +
            field('testdrive_url', 'Test-drive URL', d.testdrive_url)
        ) +
        group(
          'Brend',
          'logo / nom',
          field('brand', 'Brend nomi', d.brand) +
            field('subtitle', 'Subtitle', d.subtitle) +
            field('callback_label', 'Tugma matni', d.callback_label) +
            field('logo_id', 'Logo media ID', d.logo_id, { type: 'number' })
        ),
      mediaCard('logo', 'Logo', d.logo_url, 'avto-logo-pick', 'avto-logo-img'),
      d
    );
    bind();
    var pick = document.getElementById('avto-logo-pick');
    if (pick) pick.addEventListener('click', mediaPick('logo_id', 'avto-logo-img', '[data-preview="logo"]', 'Logo'));
  }

  function renderHero(d) {
    panel.innerHTML = shell(
      'Hero',
      group(
        'Matn',
        'sarlavha / CTA',
        field('eyebrow', 'Yuqori matn', d.eyebrow) +
          field('title', 'Sarlavha', d.title) +
          field('subtitle', 'Pastki matn', d.subtitle, { wide: true }) +
          field('cta_label', 'Tugma matni', d.cta_label) +
          field('cta_url', 'Tugma URL', d.cta_url)
      ) +
        group(
          'Rasmlar ID',
          'media IDlar',
          field('image_id', 'Desktop rasm ID', d.image_id, { type: 'number' }) +
            field('image_mobile_id', 'Mobile rasm ID', d.image_mobile_id, { type: 'number' })
        ),
      mediaCard('hero-desk', 'Desktop fon', d.image_url, 'avto-hero-pick', 'avto-hero-img') +
        mediaCard('hero-mob', 'Mobile fon', d.image_mobile_url, 'avto-hero-mob-pick', 'avto-hero-mob-img'),
      d
    );
    bind();
    var a = document.getElementById('avto-hero-pick');
    var b = document.getElementById('avto-hero-mob-pick');
    if (a) a.addEventListener('click', mediaPick('image_id', 'avto-hero-img', '[data-preview="hero-desk"]', 'Desktop'));
    if (b) b.addEventListener('click', mediaPick('image_mobile_id', 'avto-hero-mob-img', '[data-preview="hero-mob"]', 'Mobile'));
  }

  function renderCatalog(d) {
    panel.innerHTML = shell(
      'Catalog',
      group(
        'Offer / Countdown',
        'banner',
        field('offer_title', 'Offer sarlavha', d.offer_title, { wide: true }) +
          field('offer_cta_label', 'Offer tugma', d.offer_cta_label) +
          field('offer_cta_url', 'Offer URL', d.offer_cta_url) +
          field('countdown_end', 'Countdown tugash (ISO / 2026-12-31T23:59:00)', d.countdown_end, { wide: true })
      ) +
        group(
          'Cardlar',
          '3 ta afzallik',
          field('feature1_title', 'Card 1 title', d.feature1_title) +
            field('feature1_text', 'Card 1 text', d.feature1_text, { wide: true }) +
            field('feature2_title', 'Card 2 title', d.feature2_title) +
            field('feature2_text', 'Card 2 text', d.feature2_text, { wide: true }) +
            field('feature3_title', 'Card 3 title', d.feature3_title) +
            field('feature3_text', 'Card 3 text', d.feature3_text, { wide: true })
        ) +
        group(
          'Promo / Mashinalar',
          'bron + modeller',
          field('promo_title', 'Promo matn', d.promo_title, { wide: true, type: 'textarea' }) +
            field('car1_title', 'Mashina 1 nomi', d.car1_title) +
            field('car1_url', 'Mashina 1 URL', d.car1_url) +
            field('car1_image_id', 'Mashina 1 rasm ID', d.car1_image_id, { type: 'number' }) +
            field('car2_title', 'Mashina 2 nomi', d.car2_title) +
            field('car2_url', 'Mashina 2 URL', d.car2_url) +
            field('car2_image_id', 'Mashina 2 rasm ID', d.car2_image_id, { type: 'number' })
        ),
      mediaCard('car1', 'Car 1', d.car1_image_url, 'avto-car1-pick', 'avto-car1-img') +
        mediaCard('car2', 'Car 2', d.car2_image_url, 'avto-car2-pick', 'avto-car2-img'),
      d
    );
    bind();
    var a = document.getElementById('avto-car1-pick');
    var b = document.getElementById('avto-car2-pick');
    if (a) a.addEventListener('click', mediaPick('car1_image_id', 'avto-car1-img', '[data-preview="car1"]', 'Car 1'));
    if (b) b.addEventListener('click', mediaPick('car2_image_id', 'avto-car2-img', '[data-preview="car2"]', 'Car 2'));
  }

  function renderModels(d) {
    function carGroup(n, label) {
      return group(
        label,
        'model card · CRUD',
        field(n + '_eyebrow', 'Eyebrow', d[n + '_eyebrow']) +
          field(n + '_title', 'Title', d[n + '_title']) +
          field(n + '_benefit', 'Benefit', d[n + '_benefit'], { wide: true }) +
          field(n + '_badge', 'Badge', d[n + '_badge']) +
          field(n + '_offer_label', 'Уточнить наличие (link)', d[n + '_offer_label']) +
          field(n + '_offer_url', 'Offer URL', d[n + '_offer_url']) +
          field(n + '_cta_label', 'CTA', d[n + '_cta_label']) +
          field(n + '_cta_url', 'CTA URL', d[n + '_cta_url']) +
          field(n + '_td_label', 'Test-drive', d[n + '_td_label']) +
          field(n + '_td_url', 'TD URL', d[n + '_td_url']) +
          field(n + '_credit_label', 'Credit', d[n + '_credit_label']) +
          field(n + '_credit_url', 'Credit URL', d[n + '_credit_url']) +
          field(n + '_colors', 'Ranglar: #HEX|rasm_url (har qator)', d[n + '_colors'], { wide: true, type: 'textarea' }) +
          field(n + '_perks', 'Perks (line)', d[n + '_perks'], { wide: true, type: 'textarea' }) +
          field(n + '_image_id', 'Main image ID', d[n + '_image_id'], { type: 'number' }) +
          field(n + '_g1_id', 'Gallery 1 ID', d[n + '_g1_id'], { type: 'number' }) +
          field(n + '_g2_id', 'Gallery 2 ID', d[n + '_g2_id'], { type: 'number' }) +
          field(n + '_g3_id', 'Gallery 3 ID', d[n + '_g3_id'], { type: 'number' }) +
          field(n + '_g4_id', 'Gallery 4 ID', d[n + '_g4_id'], { type: 'number' }) +
          field(n + '_g5_id', 'Gallery 5 ID', d[n + '_g5_id'], { type: 'number' }),
        n === 'car1'
      );
    }

    var media =
      mediaCard('m1', 'TANK 300 main', d.car1_image_url, 'avto-m1-pick', 'avto-m1-img') +
      mediaCard('m1g1', 'TANK 300 gallery 1', d.car1_g1_url, 'avto-m1g1-pick', 'avto-m1g1-img') +
      mediaCard('m1g2', 'TANK 300 gallery 2', d.car1_g2_url, 'avto-m1g2-pick', 'avto-m1g2-img') +
      mediaCard('m1g3', 'TANK 300 gallery 3', d.car1_g3_url, 'avto-m1g3-pick', 'avto-m1g3-img') +
      mediaCard('m2', 'TANK 500 main', d.car2_image_url, 'avto-m2-pick', 'avto-m2-img') +
      mediaCard('m2g1', 'TANK 500 gallery 1', d.car2_g1_url, 'avto-m2g1-pick', 'avto-m2g1-img') +
      mediaCard('m2g2', 'TANK 500 gallery 2', d.car2_g2_url, 'avto-m2g2-pick', 'avto-m2g2-img') +
      mediaCard('m2g3', 'TANK 500 gallery 3', d.car2_g3_url, 'avto-m2g3-pick', 'avto-m2g3-img');

    panel.innerHTML = shell('Models', carGroup('car1', 'TANK 300') + carGroup('car2', 'TANK 500'), media, d);
    bind();

    var picks = [
      ['avto-m1-pick', 'car1_image_id', 'avto-m1-img', 'm1', 'TANK 300'],
      ['avto-m1g1-pick', 'car1_g1_id', 'avto-m1g1-img', 'm1g1', 'Gallery 1'],
      ['avto-m1g2-pick', 'car1_g2_id', 'avto-m1g2-img', 'm1g2', 'Gallery 2'],
      ['avto-m1g3-pick', 'car1_g3_id', 'avto-m1g3-img', 'm1g3', 'Gallery 3'],
      ['avto-m2-pick', 'car2_image_id', 'avto-m2-img', 'm2', 'TANK 500'],
      ['avto-m2g1-pick', 'car2_g1_id', 'avto-m2g1-img', 'm2g1', 'Gallery 1'],
      ['avto-m2g2-pick', 'car2_g2_id', 'avto-m2g2-img', 'm2g2', 'Gallery 2'],
      ['avto-m2g3-pick', 'car2_g3_id', 'avto-m2g3-img', 'm2g3', 'Gallery 3'],
    ];
    picks.forEach(function (row) {
      var btn = document.getElementById(row[0]);
      if (btn) btn.addEventListener('click', mediaPick(row[1], row[2], '[data-preview="' + row[3] + '"]', row[4]));
    });
  }

  function renderConfigs(d) {
    var trimGroups = '';
    for (var i = 1; i <= 6; i++) {
      trimGroups += group(
        'Trim ' + i,
        'komplektatsiya',
        field('trim' + i + '_title', 'Nomi', d['trim' + i + '_title']) +
          field('trim' + i + '_specs', 'Specs', d['trim' + i + '_specs'], { wide: true }) +
          field('trim' + i + '_price', 'Narx', d['trim' + i + '_price']) +
          field('trim' + i + '_perks', 'Afzalliklar (har qator)', d['trim' + i + '_perks'], { wide: true, type: 'textarea' }) +
          field('trim' + i + '_image_id', 'Rasm ID', d['trim' + i + '_image_id'], { type: 'number' }),
        i > 2 ? false : true
      );
    }
    var media = '';
    for (var m = 1; m <= 4; m++) {
      media += mediaCard('trim' + m, 'Trim ' + m, d['trim' + m + '_image_url'], 'avto-trim' + m + '-pick', 'avto-trim' + m + '-img');
    }
    panel.innerHTML = shell(
      'Configs',
      group(
        'Umumiy',
        'sarlavha / filter',
        field('title', 'Sarlavha', d.title, { wide: true }) +
          field('visible_count', 'Boshida nechta', d.visible_count, { type: 'number' }) +
          field('more_label', 'Ko‘proq tugma', d.more_label) +
          field('cta_label', 'CTA matni', d.cta_label) +
          field('cta_url', 'CTA URL', d.cta_url) +
          field('link_label', 'Link matni', d.link_label)
      ) +
        group(
          'Filterlar',
          'select options',
          field('filter1_label', 'Filter 1 label', d.filter1_label) +
            field('filter1_options', 'Filter 1 options', d.filter1_options, { wide: true, type: 'textarea' }) +
            field('filter2_label', 'Filter 2 label', d.filter2_label) +
            field('filter2_options', 'Filter 2 options', d.filter2_options, { wide: true, type: 'textarea' }) +
            field('filter3_label', 'Filter 3 label', d.filter3_label) +
            field('filter3_options', 'Filter 3 options', d.filter3_options, { wide: true, type: 'textarea' })
        ) +
        trimGroups,
      media,
      d
    );
    bind();
    for (var p = 1; p <= 4; p++) {
      (function (n) {
        var btn = document.getElementById('avto-trim' + n + '-pick');
        if (btn) {
          btn.addEventListener(
            'click',
            mediaPick('trim' + n + '_image_id', 'avto-trim' + n + '-img', '[data-preview="trim' + n + '"]', 'Trim ' + n)
          );
        }
      })(p);
    }
  }

  function renderTradein(d) {
    panel.innerHTML = shell(
      'Trade-in',
      group(
        'Kontent',
        'banner',
        field('title', 'Sarlavha', d.title, { wide: true }) +
          field('text', 'Matn', d.text, { wide: true, type: 'textarea' }) +
          field('cta_label', 'Tugma', d.cta_label) +
          field('cta_url', 'URL', d.cta_url) +
          field('image_id', 'Rasm ID', d.image_id, { type: 'number' })
      ),
      mediaCard('tradein', 'Fon rasm', d.image_url, 'avto-tradein-pick', 'avto-tradein-img'),
      d
    );
    bind();
    var pick = document.getElementById('avto-tradein-pick');
    if (pick) pick.addEventListener('click', mediaPick('image_id', 'avto-tradein-img', '[data-preview="tradein"]', 'Trade-in'));
  }

  function renderCredit(d) {
    panel.innerHTML = shell(
      'Credit',
      group(
        'Forma',
        'kredit',
        field('title', 'Sarlavha', d.title, { wide: true }) +
          field('subtitle', 'Subtitle', d.subtitle, { wide: true, type: 'textarea' }) +
          field('placeholder', 'Input placeholder', d.placeholder) +
          field('button', 'Tugma matni', d.button) +
          field('note', 'Izoh', d.note, { wide: true, type: 'textarea' }) +
          field('image_id', 'Fon rasm ID', d.image_id, { type: 'number' })
      ),
      mediaCard('credit', 'Fon', d.image_url, 'avto-credit-pick', 'avto-credit-img'),
      d
    );
    bind();
    var pick = document.getElementById('avto-credit-pick');
    if (pick) pick.addEventListener('click', mediaPick('image_id', 'avto-credit-img', '[data-preview="credit"]', 'Credit'));
  }

  function renderCorporate(d) {
    panel.innerHTML = shell(
      'Corporate',
      group(
        'Kontent',
        'B2B',
        field('title', 'Sarlavha', d.title, { wide: true }) +
          field('cta_label', 'Tugma', d.cta_label) +
          field('cta_url', 'URL', d.cta_url) +
          field('person_name', 'Ism', d.person_name) +
          field('person_role', 'Lavozim', d.person_role, { wide: true, type: 'textarea' }) +
          field('photo_id', 'Foto ID', d.photo_id, { type: 'number' })
      ),
      mediaCard('corp', 'Foto', d.photo_url, 'avto-corp-pick', 'avto-corp-img'),
      d
    );
    bind();
    var pick = document.getElementById('avto-corp-pick');
    if (pick) pick.addEventListener('click', mediaPick('photo_id', 'avto-corp-img', '[data-preview="corp"]', 'Foto'));
  }

  function renderContact(d) {
    panel.innerHTML = shell(
      'Contact',
      group(
        'Xarita / kartochka',
        'kontakt',
        field('map_query', 'Google Maps query', d.map_query, { wide: true }) +
          field('use_header', 'Headerdan olish (1/0)', d.use_header) +
          field('address', 'Manzil', d.address, { wide: true, type: 'textarea' }) +
          field('phone', 'Telefon', d.phone) +
          field('phone_href', 'Tel link', d.phone_href) +
          field('hours', 'Ish vaqti', d.hours) +
          field('callback_label', 'Tugma', d.callback_label)
      ),
      '',
      d
    );
    bind();
  }

  function renderFooter(d) {
    panel.innerHTML = shell(
      'Footer',
      group(
        'Legal / copy',
        'footer matnlari',
        field('legal1', 'Legal 1', d.legal1, { wide: true, type: 'textarea' }) +
          field('legal2', 'Legal 2', d.legal2, { wide: true, type: 'textarea' }) +
          field('copy_note', 'Pastki izoh', d.copy_note, { wide: true }) +
          field('use_header', 'Brand/telefon Headerdan (1)', d.use_header)
      ) +
        group(
          'Header preview',
          'faqat ko‘rish',
          field('brand', 'Brand (header)', d.brand) +
            field('phone', 'Telefon (header)', d.phone) +
            field('address', 'Manzil (header)', d.address, { wide: true })
        ),
      '',
      d
    );
    bind();
  }

  function renderModal(d) {
    panel.innerHTML = shell(
      'Modal',
      group(
        'Lead popup',
        'Заказать звонок bosilganda',
        field('title', 'Sarlavha', d.title, { wide: true }) +
          field('subtitle', 'Subtitle', d.subtitle, { wide: true, type: 'textarea' }) +
          field('model_label', 'Model label', d.model_label) +
          field('model_options', 'Modellar (har qator)', d.model_options, { wide: true, type: 'textarea' }) +
          field('phone_label', 'Telefon label', d.phone_label) +
          field('phone_placeholder', 'Telefon placeholder', d.phone_placeholder) +
          field('button', 'Tugma', d.button) +
          field('consent', 'Consent matn', d.consent, { wide: true, type: 'textarea' }) +
          field('success', 'Success matn', d.success, { wide: true })
      ),
      '',
      d
    );
    bind();
  }

  function renderLeads(d) {
    var items = Array.isArray(d && d.items) ? d.items : [];
    var rows = items.length
      ? items
          .map(function (item) {
            return (
              '<tr class="border-b border-white/5">' +
              '<td class="px-3 py-3 text-[13px] font-semibold text-white">' +
              esc(item.phone || '—') +
              '</td>' +
              '<td class="px-3 py-3 text-[12px] text-[#8b95a5]">' +
              esc(item.model || '—') +
              '</td>' +
              '<td class="px-3 py-3 text-[12px] text-[#8b95a5]">' +
              esc(item.source || '—') +
              '</td>' +
              '<td class="px-3 py-3 text-[12px] text-[#8b95a5]">' +
              esc(item.name || '—') +
              '</td>' +
              '<td class="px-3 py-3 text-[12px] text-[#8b95a5]">' +
              esc(item.created_at || '—') +
              '</td>' +
              '<td class="px-3 py-3 text-[12px] text-[#8b95a5]">' +
              (item.page
                ? '<a class="text-[#ff9549] no-underline hover:underline" href="' +
                  esc(item.page) +
                  '" target="_blank" rel="noopener">sahifa</a>'
                : '—') +
              '</td></tr>'
            );
          })
          .join('')
      : '<tr><td class="px-3 py-8 text-center text-sm text-[#8b95a5]" colspan="6">Hali ariza yo‘q</td></tr>';

    panel.innerHTML =
      navHtml() +
      '<div class="animate-[avtoIn_.35s_ease] overflow-hidden rounded-[18px] border border-white/10 bg-gradient-to-b from-[#14191f] to-[#10151b]">' +
      '<div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 bg-white/[0.02] px-5 py-4">' +
      '<div>' +
      '<div class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#ff9549]">Inbox</div>' +
      '<h2 class="m-0 mt-1 text-lg font-bold text-white">Leads · ' +
      esc(String((d && d.total) || items.length)) +
      '</h2></div>' +
      '<button type="button" class="' +
      ui.btnGhost +
      '" id="avto-leads-refresh">Yangilash</button></div>' +
      '<div class="overflow-x-auto">' +
      '<table class="min-w-full text-left">' +
      '<thead><tr class="border-b border-white/10 text-[11px] uppercase tracking-wide text-[#8b95a5]">' +
      '<th class="px-3 py-3 font-bold">Telefon</th>' +
      '<th class="px-3 py-3 font-bold">Model</th>' +
      '<th class="px-3 py-3 font-bold">Manba</th>' +
      '<th class="px-3 py-3 font-bold">Ism</th>' +
      '<th class="px-3 py-3 font-bold">Vaqt</th>' +
      '<th class="px-3 py-3 font-bold">Sahifa</th>' +
      '</tr></thead><tbody>' +
      rows +
      '</tbody></table></div></div>';

    clearDirty();
    bindNav();
    var refresh = document.getElementById('avto-leads-refresh');
    if (refresh) {
      refresh.addEventListener('click', function () {
        load();
      });
    }
  }

  function markDirty() {
    dirty = true;
    var el = document.getElementById('avto-dirty');
    if (el) el.hidden = false;
  }

  function clearDirty() {
    dirty = false;
    var el = document.getElementById('avto-dirty');
    if (el) el.hidden = true;
  }

  function setBusy(on) {
    busy = on;
    document.querySelectorAll('#avto-cms-root button').forEach(function (btn) {
      if (btn.hasAttribute('data-tab') || btn.hasAttribute('data-lang') || btn.hasAttribute('data-group-toggle')) return;
      btn.disabled = on;
    });
  }

  function filterFields(q) {
    q = String(q || '')
      .trim()
      .toLowerCase();
    document.querySelectorAll('#avto-cms-root [data-field-label]').forEach(function (el) {
      if (!q) {
        el.classList.remove('hidden');
        return;
      }
      var hay = el.getAttribute('data-field-label') || '';
      el.classList.toggle('hidden', hay.indexOf(q) === -1);
    });
  }

  function render(d) {
    if (d && d.i18n && Array.isArray(d.i18n.languages) && d.i18n.languages.length) languages = d.i18n.languages;
    if (d && d.lang) activeLang = d.lang;
    if (activeTab === 'leads') return renderLeads(d);
    if (activeTab === 'hero' || activeTab === 'hero-tank300' || activeTab === 'hero-tank500') return renderHero(d);
    if (activeTab === 'catalog') return renderCatalog(d);
    if (activeTab === 'models') return renderModels(d);
    if (activeTab === 'configs') return renderConfigs(d);
    if (activeTab === 'tradein') return renderTradein(d);
    if (activeTab === 'credit') return renderCredit(d);
    if (activeTab === 'corporate') return renderCorporate(d);
    if (activeTab === 'contact') return renderContact(d);
    if (activeTab === 'footer') return renderFooter(d);
    if (activeTab === 'modal') return renderModal(d);
    return renderHeader(d);
  }

  function bindNav() {
    document.querySelectorAll('[data-tab]').forEach(function (btn) {
      btn.addEventListener('click', async function () {
        if (dirty && !window.confirm('Saqlanmagan o‘zgarishlar bor. Davom etasizmi?')) return;
        activeTab = btn.getAttribute('data-tab') || 'header';
        await load();
      });
    });
    document.querySelectorAll('[data-lang]').forEach(function (btn) {
      btn.addEventListener('click', async function () {
        if (dirty && !window.confirm('Saqlanmagan o‘zgarishlar bor. Tilni almashtirasizmi?')) return;
        activeLang = btn.getAttribute('data-lang') || 'ru';
        await load();
      });
    });
  }

  function bind() {
    clearDirty();
    bindNav();
    var form = document.getElementById('avto-resource-form');
    var onEdit = function () {
      markDirty();
      updateMeter();
      updateLivePreview();
    };
    form.addEventListener('input', onEdit);
    form.addEventListener('change', onEdit);
    updateMeter();
    updateLivePreview();

    document.querySelectorAll('[data-group-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var body = btn.parentElement.querySelector('[data-group-body]');
        if (body) body.classList.toggle('hidden');
      });
    });

    var search = document.getElementById('avto-field-search');
    if (search) search.addEventListener('input', function () {
      filterFields(search.value);
    });

    var cloneBtn = document.getElementById('avto-clone-run');
    if (cloneBtn) {
      cloneBtn.addEventListener('click', function () {
        var sel = document.getElementById('avto-clone-from');
        cloneFromLanguage(sel ? sel.value : '');
      });
    }

    var copyBtn = document.getElementById('avto-copy-endpoint');
    if (copyBtn) {
      copyBtn.addEventListener('click', async function () {
        var code = document.getElementById('avto-endpoint-code');
        try {
          await navigator.clipboard.writeText(code ? code.textContent : '');
          toast('Endpoint nusxa olindi');
        } catch (e) {
          toast('Nusxa olish ishlamadi', 'error');
        }
      });
    }

    document.querySelectorAll('button[form="avto-resource-form"][data-method]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        saveMethod = btn.getAttribute('data-method') || 'PUT';
      });
    });

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (busy) return;
      setBusy(true);
      try {
        var data = await api(activeTab, saveMethod, formData(form));
        toast(saveMethod + ' · ' + activeTab.toUpperCase() + ' · ' + activeLang.toUpperCase());
        render(data);
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        setBusy(false);
      }
    });

    document.getElementById('avto-get').addEventListener('click', async function () {
      if (busy) return;
      setBusy(true);
      try {
        render(await api(activeTab, 'GET'));
        toast('GET · yangilandi');
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        setBusy(false);
      }
    });

    document.getElementById('avto-delete').addEventListener('click', async function () {
      if (busy) return;
      if (!window.confirm('Shu til (' + activeLang + ') defaultga qaytarilsinmi?')) return;
      setBusy(true);
      try {
        render(await api(activeTab, 'DELETE'));
        toast('DELETE · reset');
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        setBusy(false);
      }
    });
  }

  async function load() {
    panel.innerHTML = '<div class="px-5 py-12 text-center text-[#8b95a5]">Yuklanmoqda…</div>';
    try {
      render(await api(activeTab, 'GET'));
    } catch (err) {
      panel.innerHTML =
        navHtml() +
        '<div class="rounded-[18px] border border-[#ff5c5c]/30 bg-[#14191f] p-6 text-[#ffb4b4]">' +
        esc(err.message) +
        '</div>';
      bindNav();
    }
  }

  document.addEventListener('keydown', function (e) {
    var key = e.key || '';
    if ((e.ctrlKey || e.metaKey) && key.toLowerCase() === 's') {
      e.preventDefault();
      var put = document.getElementById('avto-put');
      if (put) {
        saveMethod = 'PUT';
        put.click();
      }
    }
  });

  window.addEventListener('beforeunload', function (e) {
    if (!dirty) return;
    e.preventDefault();
    e.returnValue = '';
  });

  load();
})();
