<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Classes\Catalog\CartClass;
use Saybme\Sk\Models\Product;
use Redirect;
use Input;
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
        return $q->change();        
    }

    // Удаляем товар из корзины
    public function onDelete(){
        $q = new CartClass;
        $q->delete();  
        
        $result['product_id'] = Input::get('id');
        $result['cart'] = $q->getCart();
        
        return $result;
    }

    // Меняем количество в корзине
    public function onCount(){
        $q = new CartClass;
        return $q->changeCount(); 
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
    

}
