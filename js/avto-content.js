/**
 * Avtodealer static content store.
 * Works without OpenServer / PHP: embedded defaults + localStorage + optional content.json
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
    },
    hero: {
      eyebrow: 'Улучшим любые условия',
      title: 'TANK 500',
      subtitle: 'Осталось всего 5 автомобилей!',
      cta_label: 'Получить предложение',
      cta_url: '#credit',
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
      car2_title: 'TANK 500',
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
    return fetch(url, { cache: 'no-store' })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .catch(function () {
        return null;
      });
  }

  function loadContent() {
    var local = readLocal();
    if (local) return Promise.resolve(deepMerge(DEFAULTS, local));

    var url = '/data/content.json';
    try {
      if (typeof document !== 'undefined' && document.currentScript) {
        /* keep default */
      }
    } catch (e) {}

    return fetchJson(url).then(function (file) {
      return deepMerge(DEFAULTS, file || {});
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
  };
})(typeof window !== 'undefined' ? window : globalThis);
