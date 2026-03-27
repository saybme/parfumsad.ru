
import { Fancybox } from "@fancyapps/ui/dist/fancybox/";
import { Carousel } from "@fancyapps/ui/dist/carousel/";
import { Dots } from "@fancyapps/ui/dist/carousel/carousel.dots.js";
import { Thumbs } from "@fancyapps/ui/dist/carousel/carousel.thumbs.js";
import { Autoplay } from "@fancyapps/ui/dist/carousel/carousel.autoplay.js";

import marquee from 'vanilla-marquee'
import Alpine from 'alpinejs'

import {
    OverlayScrollbars,
    ScrollbarsHidingPlugin,
    SizeObserverPlugin,
    ClickScrollPlugin
} from 'overlayscrollbars';

window.Alpine = Alpine

Alpine.start()

// document.addEventListener("DOMContentLoaded", function () {

//     OverlayScrollbars(document.body, {});

//     OverlayScrollbars(document.querySelector('#productsScroll'), {

//     });

// });

// Noty
window.Noty = require('noty');

// Инициализация
Fancybox.bind("[data-fancybox]");


// Перед отправкой
addEventListener('ajax:before-send', function (event) {

    let handler = event.detail.context.handler;

    // Присваиваем категорию
    if (handler == 'onSetCategory') {
        categoryForm(true);
    }

});

// Ошибка
addEventListener('ajax:request-error', function (event) {

    let handler = event.detail.context.handler;

    // Присваиваем категорию
    if (handler == 'onSetCategory') {
        categoryForm(false);
    }

    if (handler == 'onAdd') {
        let noty = { 'type': 'error', 'text': event.detail.message.X_OCTOBER_ERROR_MESSAGE };
        sakuraNoty(noty);
    }

    //console.log();

});

// Перед отправкой
addEventListener('ajax:promise', function (event) {
    let handler = event.detail.context.handler;

    // Карусель главной
    // if (handler == 'onGetMainProducts') {
    //     document.getElementById('result-products').classList.add('active');
    // }

});

// Успешная отправка
addEventListener('ajax:done', function (event) {

    let handler = event.detail.context.handler;

    if (handler == 'onAdd') {
        sakuraNoty(event.detail.data.noty);
        totalCart(event.detail.data.cart.total);
    }

    // Удаляем товар
    if (handler == 'onDelete') {
        productDelete(event.detail.data.product_id);
        totalCart(event.detail.data.cart.total);
    }

    // Присваиваем категорию
    if (handler == 'onSetCategory') {
        categoryForm(false);
    }

   

    // Меняем количество в корзине
    if (handler == 'onCount') {
        changeCartCount(event.detail.data.cart);
        totalCart(event.detail.data.cart.total);
    }

    // Модальное окно
    if (handler == 'onModal') {
        modal(event.detail.data);
    }

    // Обновление карусели
    if(handler == 'onGetMainProducts'){
        mainHorizontScroll();
    }

});

// Модельное окно
window.modal = function (data) {
    console.log(data);

    Fancybox.show([
        {
            html: data.modal,
            type: "html",
        },
    ]);

}

// Меняем количество в корзине
window.changeCartCount = function (data) {

    // Перебор товаров
    for (let el in data.products) {
        let product = data.products[el];
        let row = document.querySelector('[product-id="' + el + '"]');
        row.querySelector('.product-sum').innerHTML = sumFormat(product.sum);
    }

    totalCart(data.total);

}

// Форма присвоения категории
window.categoryForm = function (data = null) {
    let wrap = document.getElementById('categoryForm');

    wrap.querySelectorAll('input,button,select').forEach(el => {
        if (data == false) {
            el.parentElement.style.opacity = 1;
            el.removeAttribute('disabled');
        } else {
            el.parentElement.style.opacity = 0.5;
            el.setAttribute('disabled', true);
        }
    })

}

// Удяляем товар из корзины
window.productDelete = function (id) {
    if (!id) return;
    let product = document.querySelector('[product-id="' + id + '"]');
    if (!product) return;

    product.remove();
}

// Итоги корзины
window.totalCart = function (data) {

    document.querySelectorAll('.cart-count').forEach(el => {
        el.innerText = data.count;
    });

    document.querySelectorAll('.total-cost').forEach(el => {
        el.innerText = sumFormat(data.total_cost);
    });

    document.querySelectorAll('.total-sum').forEach(el => {
        el.innerText = sumFormat(data.total_cost);
    });

    console.log(data);

}

// Формат суммы
window.sumFormat = function (number) {
    number = new Intl.NumberFormat().format(number)
    return number;
}

// Уведомление
window.sakuraNoty = function (data) {
    console.log(data);
    new Noty({
        type: data.type,
        theme: 'metroui',
        text: data.text,
        timeout: 1000
    }).show();
}


// Карусель постов
window.scrollPOsts = function () {

    const container = document.getElementById('posts');

    const options = {
        infinite: true,
        Dots: true,
        Autoplay: {
            timeout: 3000,
            showProgressbar: false,
        },
    };   

    Carousel(container, options, { Autoplay }).init();

}

// Карусель товаров главной
window.mainHorizontScroll = function(){

    const container = document.getElementById("mainHorizontScroll");
    
    const options = {
        Autoplay: {
            showProgressbar: false
        },
        on: {
            initSlides: (instance) => {
                productGallery();
            },
        },
    };

    Carousel(container, options, { Autoplay }).init();    

}

// Карусель фото товаров
window.productGallery = function () {    

    document.querySelectorAll('.product-photos').forEach(row => {

        const ref = Carousel(row, {
            infinite: false,    
                on: {
                ready: (instance) => {
                    row.querySelectorAll('button').forEach(el => {
                        el.addEventListener("mouseenter", (e) => {
                            instance.goTo(el.getAttribute('data-carousel-go-to'));;
                        })
                    })
                },
            },
        }, { Dots }).init();

    })

}

productGallery();

// Окно товара
window.getModalProduct = function(el){
    
    oc.ajax('onModalProduct', {
        data: {
            id: el.getAttribute('data-id')
        },
        success: function(data) {
            this.success(data).done(function() {
                Fancybox.show([
                    {
                        html: data.modal,
                        showClass: 'productFancyBox f-zoomInUp',
                        mainClass: 'productFancyBoxMain'
                    },
                ]);
            });
        }
    })

}

// mainCarousel
window.mainCarousel = function () {
    const container = document.getElementById("mainCarousel");
    const options = { 
        infinite: true, 
        Dots: false, 
        Autoplay: { 
            timeout: 3000, 
            showProgressbar: true 
        } 
    };
    Carousel(container, options, { Autoplay, Dots }).init();
}

// Событие пагинации
function pagination(el) {
    el.remove();
    document.querySelector('.pagination').remove();
}

// Мобильное меню
window.showMobileMenu = function () {
    let wrap = document.getElementById('mobileMenuWrap');
    wrap.classList.toggle('hidden');
}

// Скрол новинок / поулярных
window.pupularScroll = function () {
    let FCarousel = document.querySelector('.p-carousel');
    if (FCarousel) {
        new Carousel(FCarousel, {
            infinite: false,
            Dots: false
        });
    }
}

pupularScroll();

// Скролл фото в товаре
window.productPhotos = function () {
    let productPhotos = document.getElementById('productPhotos');
    if (productPhotos) {
        new Carousel(productPhotos, {
            infinite: false,
            Thumbs: {
                type: "classic",
            }
        }, { Thumbs });
    }
}
productPhotos();

// Смена цены
window.setProductPrice = function (data) {

    let price = data.getAttribute('data-price');
    let path = data.getAttribute('data-image');

    document.querySelector('.product-price').innerHTML = price;

    if (path) {
        let image = document.getElementById('product-image');
        console.log(image);
        document.getElementById('product-image').setAttribute('src', path);
    }


}

// Прокрутка логотипов
window.marqueeLogos = function () {
    let wrap = document.getElementById('logos');
    if (wrap) {
        new marquee(wrap, {
            speed: 100,
            duplicated: true,
            startVisible: true,
            pauseOnHover: true
        })
    }
}


// Окно товара
// window.openProduct = function (data) {

//     let params = [];
//     params['id'] = data.getAttribute('data-id');

//     oc.ajax('onOpenProduct', {
//         data: params,
//         success: function (data) {
//             this.success(data).done(function () {
//                 new Fancybox([
//                     {
//                         src: data.modal,
//                         type: "html",
//                         showClass: 'productFancyBox f-zoomInUp',
//                         mainClass: 'productFancyBoxMain'
//                     },
//                 ]);
//             });
//         }
//     })


// }


// Открыть пароль
window.passwordSee = function(el){
    let input = el.parentElement.querySelector('[name="password"]');
    el.classList.toggle('active');

    if(el.classList.contains('active')){
        input.setAttribute('type', 'text');
    } else {
        input.setAttribute('type', 'password');
    }

}

// ОТкрыть заказ
window.showOrder = function(el){
    let id = el.getAttribute('data-id');
    let wrap = document.querySelector('[data-order-id="'+id+'"]');
    wrap.classList.toggle('active');
}

// counter
window.counter = function(el, type){
    const target = el && el.target ? el.target : el;

    if (el && typeof el.preventDefault === 'function') {
        el.preventDefault();
    }

    let form = target.closest('form');
    let input = form.querySelector('input[name="amount"]');
    let value = parseInt(input.value);
    let span = form.querySelector('span');
    

    if (type === 'minus') {
        value = Math.max(1, value - 1);
    } else if (type === 'plus') {
        value = value + 1;
    }

    input.value = value;
    span.innerText = value;

    return false;
}