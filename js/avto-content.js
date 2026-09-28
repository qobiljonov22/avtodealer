/**
 * Avtodealer content store.
 * Priority: WordPress REST (/content) → localStorage → /data/content.json → defaults.
 */
(function (global) {
  'use strict';

  var STORAGE_KEY = 'avtodealer_content_v1';

  var DEFAULTS = {
    header: {
      address: 'Ярославское шоссе, владение 2 В, строение 3 (МКАД, 95 км)',
      address_url: '',
      service_label: 'Записаться на сервис',
      service_url: '#contact',
      testdrive_label: 'Тест-драйв',
      testdrive_url: '#credit',
      brand: 'АВТОРУСЬ TANK',
      subtitle: 'Официальный дилер',
      phone: '+7 (999) 999-99-99',
      phone_href: 'tel:+79999999999',
      status: 'Мы на связи',
      hours: 'Ежедневно с 09:00 до 21:00',
      callback_label: 'Заказать звонок',
      menu_label: 'Меню',
      loader_text: 'Yuklanmoqda…',
      logo_url: '',
    },
    hero: {
      eyebrow: 'Улучшим любые условия',
      title: 'TANK 500',
      subtitle: 'Осталось всего 5 автомобилей!',
      cta_label: 'Получить предложение',
      cta_url: '#credit',
      image_url: '',
      image_mobile_url: '',
    },
    catalog: {
      offer_title: 'Срок действия спецпредложения:',
      offer_cta_label: 'Получить предложение',
      offer_cta_url: '#credit',
      countdown_days: 'дни',
      countdown_hours: 'часа',
      countdown_mins: 'минут',
      countdown_secs: 'секунд',
      feature1_title: 'Официальный дилер',
      feature1_text: 'Гарантируем высокое качество обслуживания.',
      feature2_title: 'Покупка авто за 1 день',
      feature2_text: 'Удобный процесс покупки, включая оформление всех документов.',
      feature3_title: 'Все комплектации в наличии',
      feature3_text: 'Широкий выбор комплектаций, с полным пакетом документов.',
      promo_title: 'Забронируйте автомобиль сегодня и получите дополнительную выгоду 100 000 ₽',
      car1_title: 'TANK 300',
      car1_label: 'TANK 300',
      car2_title: 'TANK 500',
      car2_label: 'TANK 500',
      car1_image_url: '',
      car2_image_url: '',
    },
    models: {
      car1_eyebrow: 'Только в АВТОРУСЬ',
      car1_title: 'TANK 300',
      car1_benefit: 'Выгода по Trade-in до 450 000 ₽',
      car1_badge: 'Выгода по Trade-in до 450 000 ₽',
      car1_cta_label: 'Получить предложение',
      car1_cta_url: '#credit',
      car1_td_label: 'Тест-драйв',
      car1_td_url: '#credit',
      car1_credit_label: 'В кредит',
      car1_credit_url: '#credit',
      car1_colors: '',
      car1_perks:
        'Автомобили в наличии с ПТС\nГарантия на 5 лет или 150 000 км\nЛучшие условия кредитования\nВыкуп авто',
      car1_image_url: '/images/tank%20300.png',
      car1_g1_url: '/images/image%20285%20%281%29.png',
      car1_g2_url: '/images/image%20285%20%282%29.png',
      car1_g3_url: '/images/image%20285%20%283%29.png',
      car1_g4_url: '/images/image%20287.png',
      car1_g5_url: '/images/image%20288.png',
      car2_eyebrow: 'Только в АВТОРУСЬ',
      car2_title: 'TANK 500',
      car2_benefit: 'Выгода до 950 000 ₽',
      car2_badge: 'Выгода до 950 000 ₽',
      car2_cta_label: 'Узнать стоимость',
      car2_cta_url: '#credit',
      car2_td_label: 'Тест-драйв',
      car2_td_url: '#credit',
      car2_credit_label: 'Рассчитать кредит',
      car2_credit_url: '#credit',
      car2_colors: '',
      car2_perks:
        'Кредит от 0%\nГарантия 5 лет или 150 000 км\nАвтомобили в наличии с ПТС\nTrade-in на выгодных условиях',
      car2_image_url: '/images/image%20286.png',
      car2_g1_url: '/images/image%20286%20%281%29.png',
      car2_g2_url: '/images/image%20286%20%282%29.png',
      car2_g3_url: '/images/image%20286%20%283%29.png',
      car2_g4_url: '/images/image%20289.png',
      car2_g5_url: '/images/Frame%206172.png',
      cars: [],
    },
    footer: {
      disclaimer_title: 'Дисклеймер',
      link_legal: 'Правовая информация',
      link_promo: 'Условия акции',
      copy_note: 'Официальный дилер ООО «ГК АВТОРУСЬ МЫТИЩИ» | ОГРН – 1147746893635, ИНН – 7728882903',
      legal1: '',
      legal2: '',
    },
    modal: {
      title: 'Получить предложение',
      subtitle: 'Пожалуйста, укажите свои данные. Наш менеджер свяжется с вами в течение 15 минут.',
      model_label: 'Модель',
      model_options: 'TANK 300\nTANK 500',
      phone_label: 'Телефон',
      phone_placeholder: '+7 (___) ___-__-__',
      button: 'Получить предложение',
      consent: 'Согласен на обработку персональных данных.',
      success: 'Заявка принята. Мы перезвоним.',
      close_label: 'Закрыть',
    },
  };

  function deepMerge(base, extra) {
    var out = {};
    var k;
    for (k in base) {
      if (!Object.prototype.hasOwnProperty.call(base, k)) continue;
      if (base[k] && typeof base[k] === 'object' && !Array.isArray(base[k])) {
        out[k] = deepMerge(base[k], (extra && extra[k]) || {});
      } else {
        out[k] = extra && extra[k] != null ? extra[k] : base[k];
      }
    }
    if (extra) {
      for (k in extra) {
        if (!Object.prototype.hasOwnProperty.call(extra, k)) continue;
        if (!(k in out)) out[k] = extra[k];
      }
    }
    return out;
  }

  function getPath(obj, path) {
    return String(path || '')
      .split('.')
      .reduce(function (o, key) {
        return o && o[key] != null ? o[key] : '';
      }, obj);
  }

  function readLocal() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  function writeLocal(data) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  }

  function clearLocal() {
    localStorage.removeItem(STORAGE_KEY);
  }

  function fetchJson(url) {
    return fetch(url, { cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .catch(function () {
        return null;
      });
  }

  function restBase() {
    if (typeof avtodealerFront !== 'undefined' && avtodealerFront && avtodealerFront.restUrl) {
      return String(avtodealerFront.restUrl).replace(/\/?$/, '/');
    }
    return '/wp-json/avtodealer/v1/';
  }

  function contentUrl() {
    var base = restBase();
    if (typeof avtodealerFront !== 'undefined' && avtodealerFront && avtodealerFront.useQuery) {
      var origin = typeof location !== 'undefined' ? location.origin : '';
      var site = (avtodealerFront.siteUrl || origin || '/').replace(/\/?$/, '/');
      try {
        var u = new URL(site, origin || 'http://localhost');
        u.searchParams.set('rest_route', '/avtodealer/v1/content');
        return u.toString();
      } catch (e) {
        return base + 'content';
      }
    }
    return base + 'content';
  }

  function normalizeWpBundle(raw) {
    if (!raw || typeof raw !== 'object') return null;
    var out = {};
    ['header', 'hero', 'catalog', 'models', 'footer', 'modal', 'configs', 'tradein', 'credit', 'corporate', 'contact'].forEach(function (k) {
      if (raw[k]) out[k] = raw[k];
    });
    if (raw['hero-tank300'] || raw.hero_tank300) {
      out.hero_tank300 = raw['hero-tank300'] || raw.hero_tank300;
    }
    if (raw['hero-tank500'] || raw.hero_tank500) {
      out.hero_tank500 = raw['hero-tank500'] || raw.hero_tank500;
    }
    out._fromWp = true;
    out.lang = raw.lang || 'ru';
    return out;
  }

  function loadContent() {
    // 1) WordPress REST /content — asosiy manba
    return fetchJson(contentUrl())
      .then(function (wp) {
        var fromWp = normalizeWpBundle(wp);
        if (fromWp && (fromWp.models || fromWp.header || fromWp.hero)) {
          return deepMerge(DEFAULTS, fromWp);
        }
        // 2) Alohida models endpoint (content yo‘q bo‘lsa)
        return fetchJson(restBase() + 'models')
          .then(function (models) {
            if (models && (models.car1_title || models.cars)) {
              return deepMerge(DEFAULTS, { models: models, _fromWp: true });
            }
            var cached = readLocal();
            if (cached) return deepMerge(DEFAULTS, cached);
            return fetchJson('/data/content.json').then(function (file) {
              return deepMerge(DEFAULTS, file || {});
            });
          });
      })
      .catch(function () {
        var cached = readLocal();
        if (cached) return deepMerge(DEFAULTS, cached);
        return deepMerge(DEFAULTS, {});
      });
  }

  function saveContent(data) {
    var merged = deepMerge(DEFAULTS, data || {});
    writeLocal(merged);
    return merged;
  }

  function exportBlob(data) {
    return new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  }

  global.AvtoContent = {
    STORAGE_KEY: STORAGE_KEY,
    DEFAULTS: DEFAULTS,
    deepMerge: deepMerge,
    getPath: getPath,
    loadContent: loadContent,
    saveContent: saveContent,
    clearLocal: clearLocal,
    readLocal: readLocal,
    exportBlob: exportBlob,
    fetchJson: fetchJson,
    contentUrl: contentUrl,
  };
})(typeof window !== 'undefined' ? window : globalThis);
