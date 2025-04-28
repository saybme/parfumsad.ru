<?php namespace Saybme\Sk\Models;

use Model;

class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $fillable = ['phone','email','payment','delivery','products','profile','comment','present','is_present','address'];

    protected $jsonable = ['address','profile','props','present'];
  
    public $table = 'saybme_sk_orders';
    
    public $rules = [
        'phone' => 'required',
        'email' => 'required',
        'payment' => 'required',
        'delivery' => 'required'
    ];

    public $customMessages = [
        'phone.required' => 'Введите телефон.',
        'email.required' => 'Введите E-mail.'
    ];

    public $belongsTo = [
        'payment' => \Saybme\Sk\Models\Payment::class,
        'delivery' => \Saybme\Sk\Models\Delivery::class
    ];

    public $hasMany = [
        'products' => \Saybme\Sk\Models\Orproduct::class,
    ];

    public function beforeCreate(){  
        // Создаем номер заказа
        $num = date('ymj') .'-'. str_pad(mt_rand(1, 99999), 4, '0', STR_PAD_LEFT); // Создаем номер заказа
        $this->num = $num;   
        
        // HASH
        $this->hash = md5($num);
    }

    public function beforeValidate(){
        $phone = preg_replace("/[^0-9]/", '', $this->phone);
        $this->phone = $phone;
    }

    public function getSumAttribute(){
        if(!$this->products) return;
        $rows = array();
        foreach($this->products as $item){
            $rows[] = $item->sum;    
        }
        return array_sum($rows);
    }

    // Адрес доставки
    public function getAddressDeliveryAttribute(){
        if(!$this->address) return;

        $titles['city'] = 'г.';
        $titles['street'] = 'ул.';
        $titles['house'] = 'дом';
        $titles['apartment'] = 'кв.';

        $arr['city'] = null;
        $arr['street'] = null;
        $arr['house'] = null;
        $arr['apartment'] = null;

        $rows = array();
        $data = array_merge($arr, $this->address);

        foreach($data as $key => $item){
            if($item){
                $rows[] = $titles[$key] . $item;
            }            
        }

        return implode(', ', $rows);
    }

}
