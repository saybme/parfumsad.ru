<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Classes\Catalog\CartClass;
use Saybme\Sk\Classes\Users\UserClass;
use Saybme\Sk\Models\Product;
use Request;
use Redirect;
use Input;
use Lang;
use Log;

class Skapp extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Скрипты',
            'description' => 'Общие скрипты сайта'
        ];
    }

    public function defineProperties()
    {
        return null;
    }


    // Получаем товар
    public function onGetMainProducts(){
        $type = Input::get('type');

        $products = Product::active()->where($type, true);

        $options['products'] = $products->inRandomOrder()->get()->take(20);

        $result['#result-products'] = $this->renderPartial('main/main-catalog-products', $options);
        return $result;
    }


    // Добавляем товар
    public function onAdd(){        
        $q = new CartClass;
        return $q->changeCount('plus');        
    }

    // Увеличиваем количество товара
    public function onPlus(){
        $q = new CartClass;
        return $q->changeCount('plus'); 
    }

    // Уменьшаем количество товара
    public function onMinus(){
        $q = new CartClass;
        return $q->changeCount('minus'); 
    }

    // Удаляем товар из корзины
    public function onDelete(){
        $q = new CartClass;
        $q->delete();  
        
        $result['product_id'] = Input::get('id');
        $result['cart'] = $q->getCart();
        
        return $result;
    }

    // Меняем количество товара
    public function onCount(){
        $q = new CartClass;
        return $q->changeCount(); 
    }

    // Количество в корзине
    public function onCountCart(){
        $q = new CartClass;
        return $q->changeCountCart(); 
    }

    // Подарок в корзине
    public function onGift(){     
        $options['gift'] = Input::get('is_present');
        $result['#gift-result'] = $this->renderPartial('cart/gift', $options);
        return $result;
    }

    // Поля адреса
    public function onCartAddress(){
        $q = new CartClass;

        $options['delivery'] = Input::get('delivery');
        $options['cart'] = $q->getCart();

        $result['#address-result'] = $this->renderPartial('cart/form-contact', $options);
        return $result;
    }

    // Создаем заказ
    public function onCreateOrder(){
        $q = new CartClass;
        $order = $q->createOrder();

        // Редирект
        $url = '/cart/order/' . $order->hash;
        return Redirect::to($url);
    }


    // Модальное окно
    function onModal(){

        $tmp = '';
        $type = Input::get('type');

        if($type == 'authorization'){
            $result['modal'] = $this->renderPartial('modal/authorization');
            return $result;
        }

        return $id;
    }

    // Тип авторизации
    function onSelectAuthorization(){
        $options['id'] = Input::get('mode');
        $result['#authorization-type'] = $this->renderPartial('modal/authorization-type', $options);
        return $result;
    }

    // Авторизация
    function onAuthorization(){
        
        $tpl = '';
        $type = Input::get('type');
        $data = Input::get();

        $rules['type'] = 'required';

        // Регистрация
        if($type == 'registration'){            
            $rules['username'] = 'required';
            $rules['email'] = 'required|email';
            $rules['phone'] = 'required|phone';
            $rules['inn'] = 'required|digits_between:10,12';
        }

        // Вход
        if($type == 'enter'){            
            $rules['phone'] = 'required|phone|user';
            $rules['password'] = 'required|psw';
        }

        // Валидация
        Request::validate($rules, Lang::get('saybme.sk::validation'));

        $q = new UserClass;

        // Регистрация
        if($type == 'registration'){            
            $q->create();      
            $tpl = 'modal/authorization-success';          
        }

        // Вход
        if($type == 'enter'){            
            return $q->enter();              
        }

        $result['#authorization-modal'] = $this->renderPartial($tpl);
        return $result;
    }

    // Модальное окно товара
    public function onModalProduct(){
        
        $id = Input::get('id');

        $obj = Product::active()->find($id);
        if(!$obj){
            return;
        }

        $options['product'] = $obj;
        $result['modal'] = $this->renderPartial('modals/product', $options);
        return $result;
    }
    

}
