import { Fancybox } from "@fancyapps/ui/dist/fancybox/";
import { Carousel } from "@fancyapps/ui/dist/carousel/";
import { Dots } from "@fancyapps/ui/dist/carousel/carousel.dots.js";
import { Thumbs } from "@fancyapps/ui/dist/carousel/carousel.thumbs.js";
import { Autoplay } from "@fancyapps/ui/dist/carousel/carousel.autoplay.js";
import { Lazyload } from "@fancyapps/ui/dist/carousel/carousel.lazyload.js";

import marquee from 'vanilla-marquee';
import Alpine from 'alpinejs';

import {
    OverlayScrollbars,
    ScrollbarsHidingPlugin,
    SizeObserverPlugin,
    ClickScrollPlugin
} from 'overlayscrollbars';

window.Alpine = Alpine;
Alpine.start();

// Noty
window.Noty = require('noty');

// Инициализация Fancybox (можно сразу)
Fancybox.bind("[data-fancybox]");

// Если у href="#" - плавный скролл к блоку с id
document.addEventListener('click', function (event) {
    const target = event.target.closest('a[href^="#"]');
    if (target) {
        const id = target.getAttribute('href').substring(1);
        const block = document.getElementById(id);
        if (block) {
            event.preventDefault();
            block.scrollIntoView({ behavior: 'smooth' });
            // Добавляем хеш в адресную строку без прокрутки
            history.pushState(null, null, '#' + id);
        }
    }
});

// ====================== COOKIE ======================
window.addEventListener('DOMContentLoaded', function () {
    if (!getCookie('cookie_consent')) {
        const cookieContainer = document.getElementById('cookie-container');
        if (cookieContainer) cookieContainer.style.display = 'block';
    }
});

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

window.saveCookie = function (el) {
    document.cookie = "cookie_consent=true; max-age=" + 60 * 60 * 24 * 365 + "; path=/";
    el.parentElement.style.display = 'none';
};

window.clearCookie = function () {
    document.cookie = "cookie_consent=; max-age=0; path=/";
    location.reload();
};

// ====================== SCROLL HEADER (с RAF) ======================
let ticking = false;

window.addEventListener('scroll', function () {
    if (!ticking) {
        requestAnimationFrame(() => {
            const header = document.querySelector('header');
            const fixMenu = document.getElementById('fix-menu');
            if (!header || !fixMenu) return;

            if (window.scrollY > header.offsetHeight) {
                fixMenu.classList.remove('invisible', 'opacity-0');
                fixMenu.classList.add('visible', 'opacity-100');
                fixMenu.style.transform = 'translateY(0)';
            } else {
                fixMenu.classList.remove('visible', 'opacity-100');
                fixMenu.classList.add('invisible', 'opacity-0');
                fixMenu.style.transform = 'translateY(-20px)';
            }
            ticking = false;
        });
        ticking = true;
    }
});

// ====================== ВСЕ ТЯЖЁЛЫЕ КАРУСЕЛИ И MARQUEE ======================
window.addEventListener('load', function () {

    // Карусель постов
    if (document.getElementById('posts')) scrollPOsts();

    // Главная горизонтальная карусель товаров
    if (document.getElementById("mainHorizontScroll")) mainHorizontScroll();

    // Галерея фото товаров
    productGallery();

    // Большая главная карусель
    mainCarousel();

    // Популярные / новинки
    pupularScroll();

    // Фото в карточке товара
    productPhotos();

    // Marquee логотипов
    marqueeLogos();
});

// ====================== КАРУСЕЛИ ======================
window.scrollPOsts = function () {
    const container = document.getElementById('posts');
    if (!container) return;

    const options = {
        infinite: true,
        Dots: true
    };

    Carousel(container, options).init();
};

window.mainHorizontScroll = function () {
    const container = document.getElementById("mainHorizontScroll");
    if (!container) return;

    const options = {
        Autoplay: {
            showProgressbar: false
        },
        on: {
            initSlides: () => {
                productGallery();
            },
        },
    };

    Carousel(container, options, { Autoplay }).init();
};

window.productGallery = function () {
    document.querySelectorAll('.product-photos').forEach(row => {
        Carousel(row, {
            infinite: false,
            on: {
                ready: (instance) => {
                    row.querySelectorAll('button').forEach(el => {
                        el.addEventListener("mouseenter", () => {
                            const index = parseInt(el.getAttribute('data-carousel-go-to'), 10);
                            if (!isNaN(index)) instance.goTo(index);
                        });
                    });
                },
            },
        }, { Dots, Lazyload }).init();
    });
};

window.mainCarousel = function () {
    const container = document.getElementById("mainCarousel");
    if (!container) return;

    const options = {
        infinite: true,
        Dots: false,
        Autoplay: {
            timeout: 3000,
            showProgressbar: true
        }
    };

    Carousel(container, options, { Autoplay, Dots, Lazyload }).init();
};

window.pupularScroll = function () {
    const FCarousel = document.querySelector('.p-carousel');
    if (FCarousel) {
        new Carousel(FCarousel, {
            infinite: false,
            Dots: false
        });
    }
};

window.productPhotos = function () {
    const container = document.getElementById('productPhotos');
    if (container) {
        Carousel(container, {
            infinite: false,
            Dots: true
        }, { Dots }).init();
    }
};

// ====================== SCROLL TO TOP BUTTON (с RAF) ======================
let scrollTicking = false;

window.addEventListener('scroll', function () {
    if (!scrollTicking) {
        requestAnimationFrame(() => {
            const scrollToTopBtn = document.getElementById('scroll-to-top');
            if (!scrollToTopBtn) return;

            if (window.scrollY > 300) {
                scrollToTopBtn.classList.remove('hidden');
                // Плавное появление кнопки при скролле вниз
                scrollToTopBtn.style.opacity = '1';
            } else {
                // Плавное скрытие кнопки при возвращении вверх
                scrollToTopBtn.style.opacity = '0';
                setTimeout(() => {
                    scrollToTopBtn.classList.add('hidden');
                }, 300);
            }
            scrollTicking = false;
        });
        scrollTicking = true;
    }
});

// ====================== MARQUEE ======================
let marqueeInitialized = false;

window.marqueeLogos = function () {
    const wrap = document.getElementById('logos');
    if (!wrap || marqueeInitialized) return;

    new marquee(wrap, {
        speed: 100,
        duplicated: true,
        startVisible: true,
        pauseOnHover: true
    });

    marqueeInitialized = true;
};

// ====================== ОСТАЛЬНЫЕ ФУНКЦИИ ======================
window.modal = function (data) {
    Fancybox.show([
        {
            html: data.modal,
            type: "html",
        },
    ]);
};

window.changeCartCount = function (data) {
    for (let el in data.products) {
        let product = data.products[el];
        let row = document.querySelector('[product-id="' + el + '"]');
        if (row) {
            let sumEl = row.querySelector('.product-sum');
            if (sumEl) sumEl.innerHTML = sumFormat(product.sum);
        }
    }
    totalCart(data.total);
};

window.categoryForm = function (data = null) {
    let wrap = document.getElementById('categoryForm');
    if (!wrap) return;

    wrap.querySelectorAll('input,button,select').forEach(el => {
        if (data === false) {
            el.parentElement.style.opacity = 1;
            el.removeAttribute('disabled');
        } else {
            el.parentElement.style.opacity = 0.5;
            el.setAttribute('disabled', true);
        }
    });
};

window.productDelete = function (id) {
    if (!id) return;
    let product = document.querySelector('[product-id="' + id + '"]');
    if (product) product.remove();
};

window.changeProductCount = function (data) {
    let row = document.querySelector('[data-product-id="' + data.id + '"]');
    if (!row) return;

    let form = row.querySelector('form');
    let input = row.querySelector('input[name="amount"]');
    let span = row.querySelector('span[data-amount]');

    if (input) input.value = data.amount;
    if (span) span.innerText = data.amount;

    if (data.amount > 0) {
        form.classList.add('in-cart');
    } else {
        form.classList.remove('in-cart');
    }
};

window.totalCart = function (data) {
    document.querySelectorAll('.cart-count').forEach(el => {
        el.innerText = data.count;
    });

    document.querySelectorAll('.total-cost, .total-sum').forEach(el => {
        el.innerText = sumFormat(data.total_cost);
    });
};

window.sumFormat = function (number) {
    return new Intl.NumberFormat().format(number);
};

window.sakuraNoty = function (data) {
    new Noty({
        type: data.type,
        theme: 'metroui',
        text: data.text,
        timeout: 1000
    }).show();
};

window.getModalProduct = function (el) {
    oc.ajax('onModalProduct', {
        data: { id: el.getAttribute('data-id') },
        success: function (data) {
            this.success(data).done(function () {
                Fancybox.show([
                    {
                        html: data.modal,
                        showClass: 'productFancyBox f-zoomInUp',
                        mainClass: 'productFancyBoxMain'
                    },
                ]);
            });
        }
    });
};

window.setProductPrice = function (data) {
    const price = data.getAttribute('data-price');
    const path = data.getAttribute('data-image');

    document.querySelector('.product-price').innerHTML = price;

    if (path) {
        const image = document.getElementById('product-image');
        if (image) image.setAttribute('src', path);
    }
};

window.showMobileMenu = function () {
    const wrap = document.getElementById('mobileMenuWrap');
    if (wrap) wrap.classList.toggle('hidden');
};

window.showOrder = function (el) {
    const id = el.getAttribute('data-id');
    const wrap = document.querySelector('[data-order-id="' + id + '"]');
    if (wrap) wrap.classList.toggle('active');
};

window.passwordSee = function (el) {
    const input = el.parentElement.querySelector('[name="password"]');
    if (!input) return;

    el.classList.toggle('active');
    input.type = el.classList.contains('active') ? 'text' : 'password';
};

window.counter = function (el, type) {
    const target = el && el.target ? el.target : el;
    if (el && typeof el.preventDefault === 'function') {
        el.preventDefault();
    }

    const form = target.closest('form');
    if (!form) return;

    const input = form.querySelector('input[name="amount"]');
    const span = form.querySelector('span');
    if (!input) return;

    let value = parseInt(input.value) || 1;

    if (type === 'minus') {
        value = Math.max(1, value - 1);
    } else if (type === 'plus') {
        value++;
    }

    input.value = value;
    if (span) span.innerText = value;

    return false;
};

// ====================== AJAX ОБРАБОТЧИКИ ======================
addEventListener('ajax:before-send', function (event) {
    const handler = event.detail.context.handler;
    if (handler === 'onSetCategory') {
        categoryForm(true);
    }
});

addEventListener('ajax:request-error', function (event) {
    const handler = event.detail.context.handler;
    if (handler === 'onSetCategory') {
        categoryForm(false);
    }
    if (handler === 'onAdd') {
        sakuraNoty({ type: 'error', text: event.detail.message.X_OCTOBER_ERROR_MESSAGE });
    }
});

addEventListener('ajax:done', function (event) {
    const handler = event.detail.context.handler;

    if (handler === 'onAdd' || handler === 'onPlus' || handler === 'onMinus') {
        sakuraNoty(event.detail.data.noty);
        totalCart(event.detail.data.cart.total);
        changeProductCount(event.detail.data.product);
    }

    if (handler === 'onDelete') {
        productDelete(event.detail.data.product_id);
        totalCart(event.detail.data.cart.total);
    }

    if (handler === 'onSetCategory') {
        categoryForm(false);
    }

    if (handler === 'onCount' || handler === 'onCountCart') {
        changeCartCount(event.detail.data.cart);
        totalCart(event.detail.data.cart.total);
    }

    if (handler === 'onModal') {
        modal(event.detail.data);
    }

    if (handler === 'onGetMainProducts') {
        mainHorizontScroll();
    }
});