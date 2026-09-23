document.addEventListener('DOMContentLoaded', function () {
    initPageLoader();
    initMobileMenu();
    initCountdown();
    initConfigsMore();
    initCreditForm();
    initLeadModal();
    initSmoothAnchors();
    initModelCards();
    initDisclaimerLinks();
    scrollToHash(true);
});

function initPageLoader() {
    var loader = document.getElementById('avto-page-loader');
    if (!loader) {
        document.documentElement.classList.remove('avto-loading');
        return;
    }

    var started = Date.now();
    var minMs = 700;
    var done = false;

    function hide() {
        if (done) return;
        done = true;
        var wait = Math.max(0, minMs - (Date.now() - started));
        setTimeout(function () {
            loader.classList.add('is-done');
            loader.setAttribute('aria-busy', 'false');
            document.documentElement.classList.remove('avto-loading');
            setTimeout(function () {
                if (loader.parentNode) loader.parentNode.removeChild(loader);
                scrollToHash(true);
            }, 600);
        }, wait);
    }

    if (document.readyState === 'complete') {
        hide();
    } else {
        window.addEventListener('load', hide);
        setTimeout(hide, 8000);
    }
}

function initMobileMenu() {
    var style = document.createElement('style');
    style.textContent = [
        '[data-menu]{',
        'position:fixed;',
        'left:0;',
        'right:0;',
        'top:var(--avto-header-h, 60px);',
        'bottom:auto;',
        'z-index:99998;',
        'min-height:0!important;',
        'height:0;',
        'visibility:hidden;',
        'opacity:0;',
        'transform:translateY(-12px);',
        'overflow:hidden;',
        'pointer-events:none;',
        'transition:opacity .35s ease,transform .35s ease,visibility .35s ease,height .35s ease;',
        '}',
        '[data-menu].open{',
        'height:calc(100dvh - var(--avto-header-h, 60px));',
        'visibility:visible;',
        'opacity:1;',
        'transform:translateY(0);',
        'overflow:auto;',
        'pointer-events:auto;',
        '}',
        '[data-menu-link]{',
        'opacity:0;',
        'transform:translateY(16px);',
        'transition:opacity .4s ease,transform .4s ease;',
        '}',
        '[data-menu-link].fade{',
        'opacity:1;',
        'transform:translateY(0);',
        '}',
        '[data-menu-btn] .line1,',
        '[data-menu-btn] .line2,',
        '[data-menu-btn] .line3{',
        'display:block;',
        'transition:transform .3s ease,opacity .3s ease;',
        '}',
        '[data-menu-btn].toggle .line1{',
        'transform:translateY(7px) rotate(45deg);',
        '}',
        '[data-menu-btn].toggle .line2{',
        'opacity:0;',
        '}',
        '[data-menu-btn].toggle .line3{',
        'transform:translateY(-7px) rotate(-45deg);',
        '}',
        '@media (min-width:1024px){',
        '[data-menu]{display:none!important;}',
        '}'
    ].join('');
    document.head.appendChild(style);

    var hamburger = document.querySelector('[data-menu-btn]');
    var navLinks = document.querySelector('[data-menu]');
    var headerBar = document.querySelector('[data-header-bar]');
    var links = document.querySelectorAll('[data-menu-link]');

    if (!hamburger || !navLinks) {
        return;
    }

    function syncMenuTop() {
        var h = 60;
        if (headerBar) {
            h = Math.ceil(headerBar.getBoundingClientRect().height);
        }
        if (h < 48) h = 60;
        document.documentElement.style.setProperty('--avto-header-h', h + 'px');
    }

    syncMenuTop();
    window.addEventListener('resize', syncMenuTop);

    links.forEach(function (link, i) {
        link.style.transitionDelay = (i * 0.07) + 's';
    });

    hamburger.addEventListener('click', function () {
        syncMenuTop();
        navLinks.classList.toggle('open');

        links.forEach(function (link) {
            link.classList.toggle('fade');
        });

        hamburger.classList.toggle('toggle');

        var open = navLinks.classList.contains('open');
        hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
        hamburger.setAttribute('aria-label', open ? 'Закрыть' : 'Меню');
        document.body.classList.toggle('overflow-hidden', open);
    });

    navLinks.querySelectorAll('a[href]').forEach(function (a) {
        a.addEventListener('click', function () {
            if (!navLinks.classList.contains('open')) return;
            navLinks.classList.remove('open');
            hamburger.classList.remove('toggle');
            links.forEach(function (link) {
                link.classList.remove('fade');
            });
            hamburger.setAttribute('aria-expanded', 'false');
            hamburger.setAttribute('aria-label', 'Меню');
            document.body.classList.remove('overflow-hidden');
        });
    });
}

function initCountdown() {
    var root = document.querySelector('[data-countdown]');
    if (!root) {
        return;
    }

    var endAttr = String(root.getAttribute('data-countdown-end') || '').trim();
    var end = NaN;
    if (endAttr) {
        end = Date.parse(endAttr);
        if (isNaN(end)) {
            // "YYYY-MM-DD HH:MM:SS" yoki local format
            end = Date.parse(endAttr.replace(' ', 'T'));
        }
    }
    if (isNaN(end) || end <= Date.now()) {
        end = Date.now() + 3 * 24 * 60 * 60 * 1000;
    }

    var circumference = 2 * Math.PI * 38;
    var timer = null;

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function tick() {
        var diff = Math.max(0, end - Date.now());
        var totalSec = Math.floor(diff / 1000);
        var days = Math.floor(totalSec / 86400);
        var hours = Math.floor((totalSec % 86400) / 3600);
        var minutes = Math.floor((totalSec % 3600) / 60);
        var seconds = totalSec % 60;

        var values = {
            days: days,
            hours: hours,
            minutes: minutes,
            seconds: seconds
        };

        root.querySelectorAll('[data-unit]').forEach(function (unit) {
            var key = unit.getAttribute('data-unit');
            var max = parseInt(unit.getAttribute('data-max'), 10) || 60;
            var value = values[key] || 0;
            var valueEl = unit.querySelector('[data-value]');
            var ring = unit.querySelector('[data-ring]');

            if (valueEl) {
                valueEl.textContent = key === 'days' ? String(value) : pad(value);
            }

            if (ring) {
                var progress = max > 0 ? value / max : 0;
                ring.style.strokeDasharray = String(circumference);
                ring.style.strokeDashoffset = String(circumference * (1 - Math.min(1, progress)));
            }
        });

        if (diff <= 0 && timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    tick();
    timer = setInterval(tick, 1000);
}

function initConfigsMore() {
    var btn = document.querySelector('[data-configs-more]');
    if (!btn) return;

    btn.addEventListener('click', function () {
        document.querySelectorAll('[data-config-more]').forEach(function (el) {
            el.classList.remove('hidden');
        });
        btn.hidden = true;
    });
}

function initLeadModal() {
    var modal = document.querySelector('[data-lead-modal]');
    if (!modal) return;

    var form = modal.querySelector('[data-lead-form]');
    var phoneInput = form ? form.querySelector('input[name="phone"]') : null;
    var lastFocus = null;

    function openModal(e) {
        if (e) e.preventDefault();
        lastFocus = document.activeElement;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        // close mobile menu if open
        var menu = document.querySelector('[data-menu]');
        var hamburger = document.querySelector('[data-menu-btn]');
        if (menu && menu.classList.contains('open')) {
            menu.classList.remove('open');
            if (hamburger) {
                hamburger.classList.remove('toggle');
                hamburger.setAttribute('aria-expanded', 'false');
            }
            document.querySelectorAll('[data-menu-link]').forEach(function (link) {
                link.classList.remove('fade');
            });
        }

        var first = modal.querySelector('select, input');
        if (first) setTimeout(function () { first.focus(); }, 50);
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    document.querySelectorAll('[data-open-lead]').forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });

    modal.querySelectorAll('[data-lead-close]').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            phoneInput.value = formatRuPhone(phoneInput.value);
        });
    }

    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var model = form.querySelector('select[name="model"]');
        var consent = form.querySelector('input[name="consent"]');
        var btn = form.querySelector('button[type="submit"]');
        var phone = phoneInput ? String(phoneInput.value || '').trim() : '';
        var modelVal = model ? String(model.value || '').trim() : '';

        if (!modelVal) {
            btnFlash(btn, 'Выберите модель');
            return;
        }
        if (consent && !consent.checked) {
            btnFlash(btn, 'Нужно согласие');
            return;
        }
        if (phone.replace(/\D/g, '').length < 10) {
            btnFlash(btn, 'Неверный телефон');
            return;
        }

        if (btn) btn.disabled = true;
        try {
            var res = await frontApi('leads', 'POST', {
                phone: phone,
                model: modelVal,
                source: 'callback',
                page: window.location.href
            });
            if (phoneInput) phoneInput.value = '';
            if (model) model.selectedIndex = 0;
            btnFlash(btn, (res && res.message) || 'Отправлено');
            setTimeout(closeModal, 1400);
        } catch (err) {
            btnFlash(btn, err.message || 'Ошибка');
        }
    });
}

function initCreditForm() {
    var form = document.querySelector('[data-credit-form]');
    if (!form) return;

    var input = form.querySelector('input[name="phone"]');
    if (input) {
        input.setAttribute('autocomplete', 'tel');
        input.setAttribute('inputmode', 'tel');
        input.addEventListener('input', function () {
            input.value = formatRuPhone(input.value);
        });
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var phoneInput = form.querySelector('input[name="phone"]');
        var consent = form.querySelector('input[name="consent"]');
        var btn = form.querySelector('button[type="submit"]');
        var phone = phoneInput ? String(phoneInput.value || '').trim() : '';
        if (!phone) return;
        if (consent && !consent.checked) {
            btnFlash(btn, 'Нужно согласие');
            return;
        }

        var digits = phone.replace(/\D/g, '');
        if (digits.length < 10) {
            btnFlash(btn, 'Неверный телефон');
            return;
        }

        if (btn) btn.disabled = true;
        try {
            var res = await frontApi('leads', 'POST', {
                phone: phone,
                source: 'credit',
                page: window.location.href
            });
            if (phoneInput) phoneInput.value = '';
            btnFlash(btn, (res && res.message) || 'Отправлено');
        } catch (err) {
            btnFlash(btn, err.message || 'Ошибка');
        }
    });
}

function formatRuPhone(value) {
    var digits = String(value || '').replace(/\D/g, '');
    if (digits.charAt(0) === '8') digits = '7' + digits.slice(1);
    if (digits.charAt(0) !== '7' && digits.length) digits = '7' + digits;
    digits = digits.slice(0, 11);
    var out = '+7';
    if (digits.length > 1) out += ' (' + digits.slice(1, 4);
    if (digits.length >= 4) out += ')';
    if (digits.length > 4) out += ' ' + digits.slice(4, 7);
    if (digits.length > 7) out += '-' + digits.slice(7, 9);
    if (digits.length > 9) out += '-' + digits.slice(9, 11);
    return out;
}

function frontApi(path, method, body) {
    var cfg = typeof avtodealerFront !== 'undefined' ? avtodealerFront : {};
    var url;
    if (cfg.useQuery) {
        url = new URL(cfg.siteUrl || '/', window.location.origin);
        url.searchParams.set('rest_route', '/avtodealer/v1/' + String(path || '').replace(/^\//, ''));
    } else {
        url = new URL((cfg.restUrl || '/wp-json/avtodealer/v1/') + String(path || '').replace(/^\//, ''), window.location.origin);
    }

    var opts = {
        method: method || 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    };
    if (cfg.nonce) opts.headers['X-WP-Nonce'] = cfg.nonce;
    if (body !== undefined) opts.body = JSON.stringify(body);

    return fetch(url.toString(), opts).then(function (res) {
        return res.json().catch(function () {
            return {};
        }).then(function (data) {
            if (!res.ok) throw new Error(data.message || data.code || 'Ошибка ' + res.status);
            return data;
        });
    });
}

function initSmoothAnchors() {
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[href^="#"]');
        if (!a) return;
        var hash = a.getAttribute('href');
        if (!hash || hash === '#') return;
        var id = hash.slice(1);
        if (!document.getElementById(id)) return;
        e.preventDefault();
        if (history.pushState) history.pushState(null, '', hash);
        scrollToHash(false);
    });

    window.addEventListener('hashchange', function () {
        scrollToHash(false);
    });
}

function scrollToHash(instant) {
    var hash = window.location.hash;
    if (!hash || hash === '#') return;
    var target = document.getElementById(hash.slice(1));
    if (!target) return;
    var header = document.querySelector('header.fixed');
    var offset = header ? header.getBoundingClientRect().height : 0;
    var top = window.pageYOffset + target.getBoundingClientRect().top - offset - 8;
    window.scrollTo({
        top: Math.max(0, top),
        behavior: instant ? 'auto' : 'smooth'
    });
}

function btnFlash(btn, text) {
    if (!btn) return;
    var prev = btn.getAttribute('data-label') || btn.textContent;
    btn.setAttribute('data-label', prev);
    btn.textContent = text;
    btn.disabled = true;
    setTimeout(function () {
        btn.textContent = prev;
        btn.disabled = false;
    }, 2200);
}

function initModelCards() {
    var modal = document.querySelector('[data-gallery-modal]');

    function parseJson(raw, fallback) {
        try {
            var v = JSON.parse(raw || 'null');
            return v == null ? fallback : v;
        } catch (e) {
            return fallback;
        }
    }

    function closeGallery() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    function openGallery(card, startIndex) {
        if (!modal || !card) return;
        var gallery = parseJson(card.getAttribute('data-model-gallery'), []);
        var mainSrc = card.getAttribute('data-model-image') || (gallery[0] || '');
        var title = card.getAttribute('data-model-title') || '';

        if (!gallery.length && mainSrc) gallery = [mainSrc];
        var idx = Math.max(0, Math.min(startIndex || 0, gallery.length - 1));

        var main = modal.querySelector('[data-gallery-main]');
        var thumbs = modal.querySelector('[data-gallery-thumbs]');

        if (main) {
            main.src = gallery[idx] || mainSrc;
            main.alt = title;
        }

        if (thumbs) {
            thumbs.innerHTML = gallery
                .map(function (src, i) {
                    return (
                        '<button type="button" class="overflow-hidden rounded-lg border transition sm:rounded-xl ' +
                        (i === idx ? 'border-white' : 'border-white/10 hover:border-[#FF9549]') +
                        ' focus:outline-none" data-gallery-pick data-src="' +
                        String(src).replace(/"/g, '&quot;') +
                        '"><img src="' +
                        String(src).replace(/"/g, '&quot;') +
                        '" alt="" class="aspect-[16/10] h-auto w-full object-cover" loading="lazy"></button>'
                    );
                })
                .join('');
            thumbs.querySelectorAll('[data-gallery-pick]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var src = btn.getAttribute('data-src');
                    if (main && src) main.src = src;
                    thumbs.querySelectorAll('[data-gallery-pick]').forEach(function (b) {
                        b.classList.remove('border-white');
                        b.classList.add('border-white/10');
                    });
                    btn.classList.add('border-white');
                    btn.classList.remove('border-white/10');
                });
            });
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    document.querySelectorAll('[data-model-card]').forEach(function (card) {
        var mainImg = card.querySelector('[data-model-image]');
        card.querySelectorAll('[data-color]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                card.querySelectorAll('[data-color]').forEach(function (b) {
                    b.classList.remove('ring-2', 'ring-white', 'ring-[#FF9549]', 'ring-[#FF6A00]', 'ring-offset-2', 'ring-offset-[#181818]', 'ring-offset-[#141414]');
                    b.setAttribute('aria-pressed', 'false');
                    var check = b.querySelector('[data-color-check]');
                    if (check) check.classList.add('hidden');
                });
                btn.classList.add('ring-2', 'ring-white', 'ring-offset-2', 'ring-offset-[#181818]');
                btn.setAttribute('aria-pressed', 'true');
                var check = btn.querySelector('[data-color-check]');
                if (check) check.classList.remove('hidden');

                var colorImg = btn.getAttribute('data-color-image') || '';
                if (mainImg && colorImg) {
                    mainImg.src = colorImg;
                    card.setAttribute('data-model-image', colorImg);
                    var gallery = parseJson(card.getAttribute('data-model-gallery'), []);
                    if (gallery.length) {
                        gallery[0] = colorImg;
                        card.setAttribute('data-model-gallery', JSON.stringify(gallery));
                    }
                }
            });
        });

        card.querySelectorAll('[data-open-gallery]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var idx = parseInt(btn.getAttribute('data-gallery-index') || '0', 10) || 0;
                openGallery(card, idx);
            });
        });
    });

    if (modal) {
        modal.querySelectorAll('[data-gallery-close]').forEach(function (btn) {
            btn.addEventListener('click', closeGallery);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeGallery();
        });
    }
}

function initDisclaimerLinks() {
    document.querySelectorAll('[data-open-disclaimer]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var box = document.querySelector('[data-disclaimer]');
            if (!box) return;
            e.preventDefault();
            box.open = true;
            var header = document.querySelector('header.fixed');
            var offset = header ? header.getBoundingClientRect().height : 0;
            var top = window.pageYOffset + box.getBoundingClientRect().top - offset - 8;
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        });
    });
}
