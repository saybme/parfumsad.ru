<?php namespace Saybme\Sk\Classes\Catalog;

use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Offer;
use Saybme\Sk\Models\Payment;
use Saybme\Sk\Models\Delivery;
use Saybme\Sk\Models\Order;
use Saybme\Sk\Models\Orproduct;
use Tailor\Models\GlobalRecord;
use Mail;
use ValidationException;
use Request;
use Session;
use Input;
use Log;

class CartClass {

    // Создаем ключ
    private function getKey($data = null){
        if(!$data) return;        
        return md5($data);
    }

    // Создаем заказ
    public function createOrder(){
        if(!Session::has('cart.products')) throw new ValidationException(['no-products' => 'В корзине нет товаров, обновите страницу.']);
        $data = Input::get();        

        // Товары корзины
        $products = $this->getProducts();

        // Валидация товаров
        $this->validCartProducts($products);

        // Валидация заказа
        $order = new Order;
        $order->fill($data);
        $order->save();        

        foreach($products as $product){
            $obj = new Orproduct;
            $obj->fill($product);
            $obj->save();

            //Прикреплям в заказ
            $order->products()->add($obj);
        }

        // Отправялем уведомление на почту
        $this->sendEmailOrder($order->id);

        return $order;
    }

    // Валидация товаров при создании заказа
    private function validCartProducts($products = array()){
        if(count($products)){
            foreach($products as $product){
                $obj = Product::select('available')->find($product['id']);
                if($obj){
                    if($obj->available != 1){
                        //throw new ValidationException(['no-product' => 'Товара нет в наличии.']);
                    }
                }                
            }
        }
    }

    // Добавялем товар
    public function change(){

        $data['id'] = null;
        $data['amount'] = null;
        $data['options'] = array();

        $data = array_merge($data, Input::get());

        //Валидация
        $this->addValid($data['id']);

        // Ключ
        $prodyctKey = $this->getKey(json_encode($data, true));

        if(Session::has('cart.products.' . $prodyctKey)){
            $this->plus($data, $prodyctKey);
        } else {
            $this->add($data, $prodyctKey);
        }

        $noty['type'] = 'success';
        $noty['text'] = 'Товар добавлен в корзину';

        $result['noty'] = $noty;
        $result['cart'] = $this->cart();

        return $result;
    }

    // Меняем количество в корзине
    public function changeCount(){

        $id = Input::get('id');
        $amount = intval(Input::get('count'));

        if($amount == 0){
            $amount = 1;    
        }

        // Новое количество
        $product = Session::put('cart.products.' . $id . '.amount', $amount);        

        $result['cart'] = $this->cart();
        return $result;
    }

    // Валидация на добавление товара
    private function addValid($id = null){
        if(!$id) return;
        $product = Product::find($id);

        if(!$product){
            throw new ValidationException(['error' => 'Товар не найден.']);
        } 

        if($product->available != 1) {
            throw new ValidationException(['error' => 'Товара нет в наличии.']);
        } 

    }

    // Добавляем
    private function add($data = array(), $prodyctKey = null){
        if(!$data || !$prodyctKey) return;     
        Session::put('cart.products.' . $prodyctKey, $data);
        return;
    }

    // Плюс
    private function plus($data = array(), $prodyctKey = null){
        if(!$data || !$prodyctKey) return;         
        $data['amount'] =  intval(Session::get('cart.products.' . $prodyctKey . '.amount')) + 1;
        Session::put('cart.products.' . $prodyctKey, $data);
        return;
    }

    // Удаляем товар из корзины
    public function delete(){
        $id = Input::get('id');
        if(!$id) return;

        $products = Session::forget('cart.products.' . $id);        
    }

    // Ответ корзины
    private function cart(){
        if(!Session::has('cart.products')) return;

        // Товары корзины
        $products = $this->getProducts();
        
        $cart['total'] = $this->total($products);
        $cart['products'] = $products;

        return $cart;    
    }

    // Получаем корзину
    public function getCart(){     
        if(!Session::has('cart.products')) return;

        // Товары корзины
        $products = $this->getProducts();

        $cart['payments'] = Payment::active()->get();
        $cart['deliveries'] = Delivery::active()->get();
        $cart['total'] = $this->total($products);
        $cart['products'] = $products;

        return $cart;
    }

    // Итого
    public function total($products = null){

        if(!$products);

        $cost = array_sum(array_column($products, 'sum'));

        $total['cost'] = $cost;
        $total['total_cost'] = $cost;
        $total['count'] = count($products);
        return collect($total);
    }

    // Итого для миникорзины
    public function smallTotal(){

        $total['cost'] = 0;
        $total['total_cost'] = 0;
        $total['count'] = 0;

        $products = $this->getProducts();
        if(!$products) return $total;

        $cost = array_sum(array_column($products, 'sum'));

        $total['cost'] = $cost;
        $total['total_cost'] = $cost;
        $total['count'] = count($products);
        return collect($total);
    }

    // Получаем товары корзины
    public function getProducts(){
        
        if(Session::has('cart.products')){
            $products = array();
            foreach(Session::get('cart.products') as $key => $item){
                $productId = null;
                if(key_exists('id', $item)){
                    $productId = $item['id'];
                }
                $obj = Product::with('category','preview')->find($productId);
                if($obj){
                    $item['name'] = $obj->name;

                    $price = $this->getProductPrice($item, $obj);

                    if($obj->preview){
                        $item['image'] = $obj->preview->path;
                    } else {
                        $item['image'] = null;
                    }

                    $params = $this->carpProductParams($item);

                    if($params){
                        //$item['image'] = $params->first()->preview;    
                    }

                    $item['params'] = $params;
                    $item['price'] = $price;
                    $item['category'] = $obj->category == null ? '' : $obj->category->getParentsAndSelf()->pluck('name');

                    $productSum = $price * $item['amount'];
                    $item['sum'] = $productSum;
                    $products[$key] = $item; 
                }                   
            }
            return $products;
        }

        return;
    }    

    // Параметры в корзине
    private function carpProductParams($item = null){
        if(!$item) return;
        if(!key_exists('options', $item)) return;

        $items = array();

        foreach($item['options'] as $key => $option){
            $obj = Offer::find($option);
            if($obj){
                $items[] = $obj;       
            }            
        }

        return collect($items);
    }


    // Стоимость товара в корзине
    private function getProductPrice($arr = array(), $obj = null){
        if(!$obj) return;

        if(key_exists('options', $arr)){
            if(key_exists('obieem', $arr['options'])){
                $id = $arr['options']['obieem'];
                $offer = Offer::find($id);
                return $offer->price;
            }    
        }

        return $obj->price;
    }

    // Уведомление на почту
    public function sendEmailOrder($id = null){
        if(!$id) return;

        $order = Order::find($id);
        if(!$order) return;

        $gl = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');
        $emails = $gl->shop_emails;

        if(!$emails) return;        

        // Уведомление
        $vars['order'] = $order;

        Mail::send('order.new', $vars, function($message) use ($emails) {
            $message->to($emails, 'Admin Person');
            $message->subject('Создан новый заказ на сайте www.sakuradv.online');
        });


    }

}