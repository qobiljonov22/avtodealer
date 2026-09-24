/**
 * Apply AvtoContent to static pages — OpenServer / PHP shart emas.
 */
(function () {
  'use strict';
  if (!window.AvtoContent) return;

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function text(el, value) {
    if (!el || value == null || value === '') return;
    el.textContent = String(value);
  }

  function replaceExact(selector, from, to) {
    if (!to) return;
    qsa(selector).forEach(function (el) {
      if (el.children.length === 0 && el.textContent.trim() === from) {
        el.textContent = to;
      }
    });
  }

  function applyDataCms(data) {
    qsa('[data-cms]').forEach(function (el) {
      var path = el.getAttribute('data-cms');
      var val = AvtoContent.getPath(data, path);
      if (val === '' || val == null) return;
      var attr = el.getAttribute('data-cms-attr');
      if (attr === 'html') el.innerHTML = String(val);
      else if (attr) el.setAttribute(attr, String(val));
      else el.textContent = String(val);
    });
  }

  function apply(data) {
    var h = data.header || {};
    var hero = data.hero || {};
    var cat = data.catalog || {};
    var f = data.footer || {};
    var m = data.modal || {};

    applyDataCms(data);

    text(qs('.avto-loader-brand'), h.brand);
    text(qs('.avto-loader-sub'), h.loader_text);

    if (h.brand) {
      qsa('header span, .avto-loader-brand').forEach(function (el) {
        if (el.children.length) return;
        if (el.textContent.trim() === 'АВТОРУСЬ TANK') text(el, h.brand);
      });
      qsa('footer p').forEach(function (el) {
        if (el.textContent.indexOf('АВТОРУСЬ TANK') !== -1) {
          el.textContent = el.textContent.replace(/АВТОРУСЬ TANK/g, h.brand);
        }
      });
    }

    if (h.subtitle) replaceExact('header span', 'Официальный дилер', h.subtitle);

    if (h.address) {
      qsa('header span.truncate, header a span').forEach(function (el) {
        if (el.textContent.indexOf('Ярославское') !== -1 || el.textContent.indexOf('МКАД') !== -1) {
          text(el, h.address);
        }
      });
    }

    if (h.service_label) replaceExact('header a, footer a', 'Записаться на сервис', h.service_label);
    if (h.testdrive_label) replaceExact('header a, footer a', 'Тест-драйв', h.testdrive_label);

    if (h.callback_label) {
      qsa('[data-open-lead]').forEach(function (btn) {
        if (btn.closest('#avto-lead-modal')) return;
        qsa('span', btn).forEach(function (sp) {
          if (sp.getAttribute('aria-hidden')) return;
          if (/Заказать|звонок|callback/i.test(sp.textContent) || String(sp.className).indexOf('sm:inline') !== -1) {
            text(sp, h.callback_label);
          }
        });
        btn.childNodes.forEach(function (n) {
          if (n.nodeType === 3 && n.textContent.trim()) {
            n.textContent = ' ' + h.callback_label + ' ';
          }
        });
      });
    }

    if (h.phone) {
      qsa('a[href^="tel:"]').forEach(function (a) {
        if (h.phone_href) a.setAttribute('href', h.phone_href);
        if (a.children.length === 0) text(a, h.phone);
        else {
          var num = a.querySelector('.font-semibold, span.block');
          if (num && num.children.length === 0) text(num, h.phone);
        }
      });
    }

    if (h.status) {
      qsa('header span, footer p').forEach(function (el) {
        if (el.children.length === 0 && /связи|на связи|online/i.test(el.textContent)) {
          text(el, h.status);
        }
      });
    }

    if (h.hours) {
      qsa('header span').forEach(function (el) {
        if (/Ежедневно|09:00|21:00/.test(el.textContent)) text(el, h.hours);
      });
    }

    var heroRoot = qs('#hero');
    if (heroRoot) {
      var h1 = qs('h1', heroRoot);
      var paras = qsa('p', heroRoot);
      if (paras[0] && hero.eyebrow) text(paras[0], hero.eyebrow);
      if (h1 && hero.title) text(h1, hero.title);
      if (paras[1] && hero.subtitle) text(paras[1], hero.subtitle);
      var cta = qs('[data-open-lead], a[href="#credit"]', heroRoot);
      if (cta && hero.cta_label) {
        var set = false;
        cta.childNodes.forEach(function (n) {
          if (n.nodeType === 3 && n.textContent.trim()) {
            n.textContent = hero.cta_label + ' ';
            set = true;
          }
        });
        if (!set) {
          var s = qs('span:not([aria-hidden])', cta);
          if (s) text(s, hero.cta_label);
        }
      }
    }

    if (cat.offer_title) {
      var ot = qs('#catalog p.font-bold, #catalog .uppercase');
      if (ot) text(ot, cat.offer_title);
    }

    if (cat.offer_cta_label) {
      var oc = qs('#catalog a[data-open-lead]');
      if (oc) {
        oc.childNodes.forEach(function (n) {
          if (n.nodeType === 3 && n.textContent.trim()) {
            n.textContent = cat.offer_cta_label + ' ';
          }
        });
      }
    }

    if (cat.promo_title) {
      qsa('#catalog p').forEach(function (p) {
        if (p.textContent.indexOf('100 000') !== -1 || p.textContent.indexOf('Забронируйте') !== -1) {
          text(p, cat.promo_title);
        }
      });
    }

    if (cat.car1_title || cat.car2_title) {
      qsa('#catalog a span, #catalog a').forEach(function (el) {
        if (el.children.length) return;
        var t = el.textContent.trim();
        if (t === 'TANK 300' && cat.car1_title) text(el, cat.car1_title);
        if (t === 'TANK 500' && cat.car2_title) text(el, cat.car2_title);
      });
    }

    var labels = [cat.countdown_days, cat.countdown_hours, cat.countdown_mins, cat.countdown_secs];
    qsa('#catalog [data-unit]').forEach(function (u, i) {
      var lab = qs('span.mt-1, span.uppercase', u);
      if (lab && labels[i]) text(lab, labels[i]);
    });

    if (cat.feature1_title) {
      var cards = qsa('#catalog article h3, #catalog article .font-bold');
      if (cards[0] && cat.feature1_title) text(cards[0], cat.feature1_title);
      if (cards[1] && cat.feature2_title) text(cards[1], cat.feature2_title);
      if (cards[2] && cat.feature3_title) text(cards[2], cat.feature3_title);
      var texts = qsa('#catalog article p');
      if (texts[0] && cat.feature1_text) text(texts[0], cat.feature1_text);
      if (texts[1] && cat.feature2_text) text(texts[1], cat.feature2_text);
      if (texts[2] && cat.feature3_text) text(texts[2], cat.feature3_text);
    }

    if (f.disclaimer_title) {
      var sumSpan = qs('footer summary span');
      if (sumSpan) text(sumSpan, f.disclaimer_title);
    }

    if (f.link_legal) {
      qsa('footer [data-open-disclaimer]').forEach(function (a, i) {
        text(a, i === 0 ? f.link_legal : f.link_promo || a.textContent);
      });
    }

    if (f.copy_note) {
      qsa('footer p').forEach(function (p) {
        if (p.textContent.indexOf('ОГРН') !== -1 || p.textContent.indexOf('ИНН') !== -1) {
          text(p, f.copy_note);
        }
      });
    }

    if (f.legal1) {
      var legalPs = qsa('footer details p');
      if (legalPs[0]) text(legalPs[0], f.legal1);
      if (legalPs[1] && f.legal2) text(legalPs[1], f.legal2);
    }

    var modal = qs('#avto-lead-modal');
    if (modal && m) {
      text(qs('#avto-lead-title', modal), m.title);
      var sub = qs('#avto-lead-title + p', modal);
      text(sub, m.subtitle);
      qsa('[data-lead-close]', modal).forEach(function (b) {
        if (m.close_label) b.setAttribute('aria-label', m.close_label);
      });
      var labEls = qsa('label > span', modal);
      if (labEls[0] && m.model_label) text(labEls[0], m.model_label);
      if (labEls[1] && m.phone_label) text(labEls[1], m.phone_label);
      var phone = qs('input[name="phone"]', modal);
      if (phone && m.phone_placeholder) phone.setAttribute('placeholder', m.phone_placeholder);
      var consent = qs('input[name="consent"]', modal);
      if (consent && consent.parentElement && m.consent) {
        text(qs('span', consent.parentElement), m.consent);
      }
      var submit = qs('button[type="submit"]', modal);
      if (submit && m.button) {
        submit.innerHTML = '';
        submit.appendChild(document.createTextNode(m.button + ' '));
        var tip = document.createElement('span');
        tip.setAttribute('aria-hidden', 'true');
        tip.textContent = '>';
        submit.appendChild(tip);
      }
      var select = qs('select[name="model"]', modal);
      if (select && m.model_options) {
        var opts = String(m.model_options)
          .split(/\r?\n/)
          .map(function (s) {
            return s.trim();
          })
          .filter(Boolean);
        var ph = document.createElement('option');
        ph.value = '';
        ph.disabled = true;
        ph.selected = true;
        ph.textContent = m.model_label || 'Модель';
        select.innerHTML = '';
        select.appendChild(ph);
        opts.forEach(function (o) {
          var op = document.createElement('option');
          op.value = o;
          op.textContent = o;
          select.appendChild(op);
        });
      }
    }

    document.documentElement.setAttribute('data-avto-cms', 'ready');
  }

  AvtoContent.loadContent()
    .then(apply)
    .catch(function () {
      apply(AvtoContent.DEFAULTS);
    });
})();
