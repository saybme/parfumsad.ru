<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Classes\Catalog\CartClass;
use Saybme\Sk\Models\Order;
use Input;
use Log;

class Skcart extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Корзина',
            'description' => 'Скрипты корзины'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title' => 'Тип',
                'description' => 'Укажите тип вывода',
                'type' => 'dropdown',
                'options' => [
                    'cart' => 'Корзина',
                    'order' => 'Заказ'
                ],
                'default' => 'cart'
            ],
            'hash' => [
                'title' => 'HASH',
                'description' => 'HASH заказа для поиска',
                'default' => '{{ :hash }}'
            ]
        ];
    }


    function onRun(){
        $this->skcart = $this->getContent();
    }

    // Контент
    private function getContent(){
        $type = $this->property('type');       
        return $this->$type();    
    }

    // Заказ
    private function order(){
        $hash = $this->property('hash');
        if(!$hash) return $this->controller->run('404');

        $order = Order::where('hash', $hash)->first();
        if(!$order) return $this->controller->run('404');

        $options['order'] = $order;
        return $this->renderPartial('order/page', $options);
    }

    // Корзина
    private function cart(){
        $q = new CartClass;
        $cart = $q->getCart();

        // Пустая корзина        
        if(!$cart) return $this->renderPartial('cart/cart-null');

        $options['cart'] = $cart;
        return $this->renderPartial('cart/step_one', $options);
    }
    
    public $skcart;

}
