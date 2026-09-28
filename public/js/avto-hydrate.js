/**
 * Apply AvtoContent to static pages — OpenServer / PHP shart emas.
 * Matn + rasmlar WordPress /content (yoki localStorage) dan keladi.
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

  function setImg(el, url, alt) {
    if (!el || !url) return;
    el.setAttribute('src', publicImg(url));
    if (alt) el.setAttribute('alt', String(alt));
  }

  /** WP tema URL → static /images/... (Vercel / lokal static) */
  function publicImg(url) {
    if (!url) return '';
    var s = String(url);
    try {
      var u = new URL(s, window.location.origin);
      var m = u.pathname.match(/\/images\/(.+)$/i);
      if (m) {
        return (
          '/images/' +
          m[1]
            .split('/')
            .map(function (p) {
              return encodeURIComponent(decodeURIComponent(p));
            })
            .join('/')
        );
      }
      return s;
    } catch (e) {
      return s;
    }
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
      else if (attr === 'src') el.setAttribute('src', publicImg(val));
      else if (attr) el.setAttribute(attr, String(val));
      else el.textContent = String(val);
    });
  }

  function parseLines(raw) {
    return String(raw || '')
      .split(/\r?\n/)
      .map(function (l) {
        return l.trim();
      })
      .filter(Boolean);
  }

  function parseColors(raw, fallbackImg) {
    return parseLines(raw).map(function (line) {
      var hex = line;
      var img = '';
      if (line.indexOf('|') !== -1) {
        var parts = line.split('|');
        hex = (parts[0] || '').trim();
        img = (parts[1] || '').trim();
      }
      if (hex && hex.charAt(0) !== '#') hex = '#' + hex;
      return { hex: hex, image: publicImg(img || fallbackImg || '') };
    });
  }

  function carsFromModels(m) {
    if (!m) return [];
    if (Array.isArray(m.cars) && m.cars.length) {
      return m.cars.map(function (c) {
        return {
          id: c.id || '',
          anchor: c.anchor || '',
          eyebrow: c.eyebrow || '',
          title: c.title || '',
          benefit: c.benefit || '',
          badge: c.badge || '',
          cta_label: c.cta_label || '',
          cta_url: c.cta_url || '',
          td_label: c.td_label || '',
          td_url: c.td_url || '',
          credit_label: c.credit_label || '',
          credit_url: c.credit_url || '',
          image: publicImg(c.image || c.image_url || ''),
          gallery: (c.gallery || []).map(publicImg).filter(Boolean),
          colors: Array.isArray(c.colors)
            ? c.colors.map(function (col) {
                return {
                  hex: col.hex || col,
                  image: publicImg((col && col.image) || c.image || ''),
                };
              })
            : [],
          perks: Array.isArray(c.perks) ? c.perks : parseLines(c.perks),
        };
      });
    }
    return ['car1', 'car2'].map(function (key) {
      var image = publicImg(m[key + '_image_url'] || '');
      var gallery = ['g1', 'g2', 'g3', 'g4', 'g5']
        .map(function (g) {
          return publicImg(m[key + '_' + g + '_url'] || '');
        })
        .filter(Boolean);
      return {
        id: key,
        anchor: key === 'car1' ? 'tank-300' : 'tank-500',
        eyebrow: m[key + '_eyebrow'] || '',
        title: m[key + '_title'] || '',
        benefit: m[key + '_benefit'] || '',
        badge: m[key + '_badge'] || '',
        cta_label: m[key + '_cta_label'] || '',
        cta_url: m[key + '_cta_url'] || '',
        td_label: m[key + '_td_label'] || '',
        td_url: m[key + '_td_url'] || '',
        credit_label: m[key + '_credit_label'] || '',
        credit_url: m[key + '_credit_url'] || '',
        image: image,
        gallery: gallery,
        colors: parseColors(m[key + '_colors'], image),
        perks: parseLines(m[key + '_perks']),
      };
    });
  }

  function matchCar(cars, card) {
    var title = (card.getAttribute('data-model-title') || '').toLowerCase();
    var id = (card.id || '').toLowerCase();
    var found = cars.filter(function (c) {
      var t = (c.title || '').toLowerCase();
      var a = (c.anchor || '').toLowerCase();
      var cid = (c.id || '').toLowerCase();
      if (id && (id === a || id.indexOf(a) !== -1)) return true;
      if (title && t && (t === title || t.indexOf(title) !== -1 || title.indexOf(t) !== -1)) return true;
      if (cid && id.indexOf(cid) !== -1) return true;
      return false;
    })[0];
    return found || null;
  }

  function applyModelCard(card, car) {
    if (!card || !car) return;

    if (car.title) card.setAttribute('data-model-title', car.title);
    if (car.badge) card.setAttribute('data-model-badge', car.badge);
    if (car.image) card.setAttribute('data-model-image', car.image);
    if (car.credit_url) card.setAttribute('data-model-credit-url', car.credit_url);
    if (car.credit_label) card.setAttribute('data-model-credit-label', car.credit_label);
    if (car.gallery && car.gallery.length) {
      card.setAttribute('data-model-gallery', JSON.stringify(car.gallery));
    }
    if (car.perks && car.perks.length) {
      card.setAttribute('data-model-perks', JSON.stringify(car.perks));
    }

    var mainImg = qs('img[data-model-image], button[data-open-gallery] img', card);
    setImg(mainImg, car.image, car.title);

    qsa('span', card).forEach(function (sp) {
      if (sp.className && String(sp.className).indexOf('skew-x-12') !== -1 && car.badge) {
        text(sp, car.badge);
      }
    });

    var h2 = qs('h2', card);
    if (h2 && car.title) text(h2, car.title);

    var paras = qsa('p', card);
    if (paras[0] && car.eyebrow) text(paras[0], car.eyebrow);
    if (paras[1] && car.benefit) text(paras[1], car.benefit);

    qsa('a', card).forEach(function (a) {
      var label = '';
      var url = '';
      var cls = String(a.className || '');
      var isPrimary = cls.indexOf('FF9549') !== -1;
      var txt = (a.textContent || '').trim();
      if (isPrimary && car.cta_label) {
        label = car.cta_label;
        url = car.cta_url;
      } else if (/Тест|Test|драйв|drive/i.test(txt) && car.td_label) {
        label = car.td_label;
        url = car.td_url;
      } else if (/кредит|credit|Рассчитать|В кредит/i.test(txt) && car.credit_label) {
        label = car.credit_label;
        url = car.credit_url;
      }
      if (label) {
        var span = qs('span:not([aria-hidden])', a);
        if (span && span.children.length === 0) text(span, label);
        else {
          a.childNodes.forEach(function (n) {
            if (n.nodeType === 3 && n.textContent.trim()) n.textContent = label + ' ';
          });
        }
      }
      if (url) a.setAttribute('href', url);
    });

    if (car.colors && car.colors.length) {
      qsa('[data-color]', card).forEach(function (btn, i) {
        var col = car.colors[i];
        if (!col) return;
        if (col.hex) {
          btn.style.backgroundColor = col.hex;
          btn.setAttribute('data-color-value', col.hex);
        }
        if (col.image) btn.setAttribute('data-color-image', col.image);
      });
    }

    if (car.gallery && car.gallery.length) {
      qsa('[data-open-gallery] img', card).forEach(function (img, i) {
        if (img.hasAttribute('data-model-image')) return;
        var src = car.gallery[i] || car.gallery[0];
        setImg(img, src);
      });
    }

    if (car.perks && car.perks.length) {
      qsa('ul li span', card).forEach(function (sp, i) {
        if (car.perks[i]) text(sp, car.perks[i]);
      });
    }
  }

  function applyModels(data) {
    var cars = carsFromModels(data.models || {});
    if (!cars.length) return;
    var cards = qsa('#models [data-model-card]');
    if (!cards.length) return;

    if (cards.length === 1) {
      applyModelCard(cards[0], matchCar(cars, cards[0]) || cars[0]);
      return;
    }

    cards.forEach(function (card, i) {
      applyModelCard(card, matchCar(cars, card) || cars[i]);
    });
  }

  function apply(data) {
    var h = data.header || {};
    var hero = data.hero || {};
    var cat = data.catalog || {};
    var f = data.footer || {};
    var m = data.modal || {};

    applyDataCms(data);
    applyModels(data);

    // Trade-in / Credit / Corporate rasmlar
    var trade = data.tradein || {};
    if (trade.image_url) {
      var tImg = qs('#tradein img, [data-tradein-image], section[aria-label*="Trade"] img');
      setImg(tImg, trade.image_url);
    }
    if (trade.title) {
      var tH = qs('#tradein h2, section[aria-label*="Trade"] h2');
      text(tH, String(trade.title).split('\n')[0]);
    }
    var credit = data.credit || {};
    if (credit.image_url) {
      var cImg = qs('#credit img, [data-credit-image]');
      setImg(cImg, credit.image_url);
    }
    if (credit.title) text(qs('#credit h2'), credit.title);
    if (credit.subtitle) {
      var cSub = qs('#credit h2 + p, #credit .subtitle');
      text(cSub, credit.subtitle);
    }
    var corp = data.corporate || {};
    if (corp.photo_url) {
      var pImg = qs('#corporate img, [data-corporate-photo]');
      setImg(pImg, corp.photo_url);
    }
    if (corp.title) text(qs('#corporate h2'), corp.title);
    if (corp.person_name) {
      qsa('#corporate p, #corporate .font-bold').forEach(function (el) {
        if (/Татьяна|Sannikova|name/i.test(el.textContent) || el.getAttribute('data-person-name') != null) {
          text(el, corp.person_name);
        }
      });
    }

    text(qs('.avto-loader-brand'), h.brand);
    text(qs('.avto-loader-sub'), h.loader_text);

    if (h.logo_url) {
      qsa('header a img, header img').forEach(function (img, i) {
        if (i === 0) setImg(img, h.logo_url, h.brand);
      });
    }

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
      if (hero.image_url) {
        var heroImg = qs('img', heroRoot);
        setImg(heroImg, hero.image_url, hero.title);
      }
      if (hero.image_mobile_url) {
        var srcMob = qs('source[media]', heroRoot);
        if (srcMob) srcMob.setAttribute('srcset', publicImg(hero.image_mobile_url));
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

    if (cat.car1_title || cat.car2_title || cat.car1_label || cat.car2_label) {
      qsa('#catalog a span, #catalog a').forEach(function (el) {
        if (el.children.length) return;
        var t = el.textContent.trim();
        if (t === 'TANK 300' && (cat.car1_label || cat.car1_title)) {
          text(el, cat.car1_label || cat.car1_title);
        }
        if (t === 'TANK 500' && (cat.car2_label || cat.car2_title)) {
          text(el, cat.car2_label || cat.car2_title);
        }
      });
    }

    if (cat.car1_image_url || cat.car2_image_url) {
      var catImgs = qsa('#catalog img');
      if (cat.car1_image_url && catImgs[0]) setImg(catImgs[0], cat.car1_image_url, cat.car1_title || 'TANK 300');
      if (cat.car2_image_url && catImgs[1]) setImg(catImgs[1], cat.car2_image_url, cat.car2_title || 'TANK 500');
    }

    var labels = [cat.countdown_days, cat.countdown_hours, cat.countdown_mins, cat.countdown_secs];
    qsa('#catalog [data-unit]').forEach(function (u, i) {
      var lab = qs('span.mt-1, span.uppercase', u);
      if (lab && labels[i]) text(lab, labels[i]);
    });

    if (cat.feature1_title) {
      var featCards = qsa('#catalog article h3, #catalog article .font-bold');
      if (featCards[0] && cat.feature1_title) text(featCards[0], cat.feature1_title);
      if (featCards[1] && cat.feature2_title) text(featCards[1], cat.feature2_title);
      if (featCards[2] && cat.feature3_title) text(featCards[2], cat.feature3_title);
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
