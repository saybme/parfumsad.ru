
import { Fancybox } from "@fancyapps/ui";
import { Carousel } from "@fancyapps/ui/dist/carousel/carousel.esm.js";
import { Thumbs } from "@fancyapps/ui/dist/carousel/carousel.thumbs.esm.js";
import { Autoplay } from "@fancyapps/ui/dist/carousel/carousel.autoplay.esm.js";
import marquee from 'vanilla-marquee'
import Alpine from 'alpinejs'
 
window.Alpine = Alpine
 
Alpine.start()


// Noty
window.Noty = require('noty');

// Инициализация
Fancybox.bind("[data-fancybox]");


// Перед отправкой
addEventListener('ajax:before-send', function(event) {

    let handler = event.detail.context.handler;   

    // Присваиваем категорию
    if(handler == 'onSetCategory'){
        categoryForm(true);
    }

});

// Ошибка
addEventListener('ajax:request-error', function(event) {

    let handler = event.detail.context.handler;   

    // Присваиваем категорию
    if(handler == 'onSetCategory'){
        categoryForm(false);
    }

    if(handler == 'onAdd'){
        let noty = {'type':'error','text':event.detail.message.X_OCTOBER_ERROR_MESSAGE};
        sakuraNoty(noty);
    }    

    //console.log();

});

// Успешная отправка
addEventListener('ajax:done', function(event) {  

    let handler = event.detail.context.handler;

    if(handler == 'onAdd'){
        sakuraNoty(event.detail.data.noty);
        totalCart(event.detail.data.cart.total);
    }  

    // Удаляем товар
    if(handler == 'onDelete'){
        productDelete(event.detail.data.product_id);
        totalCart(event.detail.data.cart.total);
    } 

    // Присваиваем категорию
    if(handler == 'onSetCategory'){
        categoryForm(false);
    }

    // Карусель популярных
    if(handler == 'onGetMainProducts'){
        pupularScroll();    
    }

});

// Форма присвоения категории
window.categoryForm = function(data = null){
    let wrap = document.getElementById('categoryForm');
    
    wrap.querySelectorAll('input,button,select').forEach(el => {
        if(data == false){
            el.parentElement.style.opacity = 1;
            el.removeAttribute('disabled');
        } else {
            el.parentElement.style.opacity = 0.5;
            el.setAttribute('disabled', true);
        }        
    })
    
}

// Удяляем товар из корзины
window.productDelete = function(id){
    if(!id) return;
    let product = document.querySelector('[product-id="'+id+'"]');
    if(!product) return;

    product.remove();
}

// Итоги корзины
window.totalCart = function(data){
    document.querySelectorAll('.cart-count').forEach(el => {
        el.innerText = data.count;    
    });  
}

// Уведомление
window.sakuraNoty = function(data){    
    console.log(data);
    new Noty({        
        type: data.type,
        theme: 'metroui',
        text: data.text,    
        timeout: 1000  
    }).show();
}


// Карусель главной
window.mainCarousel = function(){
    const container = document.getElementById('mainCarousel');   

    new Carousel(container, { 
        infinite: true,
        Dots: false,         
        Autoplay: {
            timeout: 3000,
            showProgress: false,
        },
        Navigation: {
            nextTpl: "<svg viewBox='0 0 17 34' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M-9.53674e-05 2.12838C0.000400543 2.69242 0.224863 3.23317 0.623945 3.63175L11.505 14.5128C11.8343 14.842 12.0956 15.233 12.2738 15.6632C12.4521 16.0934 12.5438 16.5546 12.5438 17.0203C12.5438 17.486 12.4521 17.9471 12.2738 18.3774C12.0956 18.8076 11.8343 19.1985 11.505 19.5278L0.638128 30.3946C0.250601 30.7959 0.036171 31.3333 0.0410175 31.8911C0.0458641 32.4489 0.269602 32.9825 0.664043 33.3769C1.05848 33.7713 1.59207 33.9951 2.14987 33.9999C2.70767 34.0048 3.24506 33.7903 3.64629 33.4028L14.5131 22.5445C15.9743 21.0804 16.7949 19.0965 16.7949 17.0281C16.7949 14.9597 15.9743 12.9757 14.5131 11.5117L3.63211 0.623589C3.33458 0.32587 2.95543 0.123098 2.54262 0.0409314C2.12982 -0.0412352 1.70192 0.000896334 1.31306 0.161995C0.924209 0.323093 0.591881 0.595918 0.358131 0.945947C0.12438 1.29598 -0.000286102 1.70748 -9.53674e-05 2.12838Z' fill='#8D47F6'/></svg>",
            prevTpl: "<svg viewBox='0 0 17 34' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M16.795 2.12838C16.7945 2.69242 16.5701 3.23317 16.171 3.63175L5.28995 14.5128C4.96061 14.842 4.69935 15.233 4.5211 15.6632C4.34286 16.0934 4.25111 16.5546 4.25111 17.0203C4.25111 17.486 4.34286 17.9471 4.5211 18.3774C4.69935 18.8076 4.96061 19.1985 5.28995 19.5278L16.1568 30.3946C16.5443 30.7959 16.7588 31.3333 16.7539 31.8911C16.7491 32.4489 16.5253 32.9825 16.1309 33.3769C15.7364 33.7713 15.2029 33.9951 14.6451 33.9999C14.0873 34.0048 13.5499 33.7903 13.1486 33.4028L2.28179 22.5445C0.820629 21.0804 0 19.0965 0 17.0281C0 14.9597 0.820629 12.9757 2.28179 11.5117L13.1628 0.623589C13.4603 0.32587 13.8395 0.123098 14.2523 0.0409314C14.6651 -0.0412352 15.093 0.000896334 15.4819 0.161995C15.8707 0.323093 16.203 0.595918 16.4368 0.945947C16.6705 1.29598 16.7952 1.70748 16.795 2.12838Z' fill='#8D47F6'/></svg>",
        },
    }, { Autoplay });

}

// Карусель постов
window.scrollPOsts = function(){
    const container = document.getElementById('posts');   
    new Carousel(container, { 
        infinite: false 
    });
}

// Событие пагинации
function pagination(el){
    el.remove();
    document.querySelector('.pagination').remove();
}

// Мобильное меню
window.showMobileMenu = function(){
    let wrap = document.getElementById('mobileMenuWrap');
    wrap.classList.toggle('hidden');
}

// Скрол новинок / поулярных
window.pupularScroll = function(){
    let FCarousel = document.querySelector('.p-carousel');   
    if(FCarousel){
        new Carousel(FCarousel, { 
            infinite: false,
            Dots: false
        });
    }
}  

pupularScroll();

// Скролл фото в товаре
window.productPhotos = function(){
    let productPhotos = document.getElementById('productPhotos');
    if(productPhotos){
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
window.setProductPrice = function(data){

    let price = data.getAttribute('data-price');
    let path =  data.getAttribute('data-image');

    document.querySelector('.product-price').innerHTML = price;    

    if(path){
        let image = document.getElementById('product-image');
        console.log(image);
        document.getElementById('product-image').setAttribute('src', path);
    }
    

}

// Прокрутка логотипов
window.marqueeLogos = function(){
    let wrap = document.getElementById('logos');
    if(wrap){
        new marquee( wrap, {
            speed: 100,
            duplicated: true,
            startVisible: true,
            pauseOnHover: true
        })
    }
}


// Окно товара
window.openProduct = function(data){

    let params = [];
    params['id'] = data.getAttribute('data-id');
    
    oc.ajax('onOpenProduct', {
        data: params,
        success: function(data) {
            this.success(data).done(function() {                
                new Fancybox([
                    {
                      src: data.modal,
                      type: "html",
                      showClass: 'productFancyBox',
                      mainClass: 'productFancyBoxMain'
                    },
                  ]);  
            });
        }
    })
    

}