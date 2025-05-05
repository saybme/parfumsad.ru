<?php namespace Saybme\Sk\Models;

use Model;
use Input;
use Log;

class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;
    use \October\Rain\Database\Traits\Sortable;

    protected $fillable = [
        'name',
        'uid',
        'slug',
        'category',
        'vendor',
        'is_active',
        'price',
        'price_usd',
        'price_eur',
        'old_price',
        'article',
        'available',
        'uri',
        'content'
    ];
    
    protected $jsonable = ['props'];

    protected $slugs = ['slug' => 'name'];
  
    public $table = 'saybme_sk_products';
   
    public $rules = [
        'name' => 'required',
        'price' => 'required',
        'price_usd' => 'required',
        'price_eur' => 'required',
        'category' => 'required',
        'available' => 'required'
    ];

    public $attachOne = [
        'preview' => \System\Models\File::class
    ];

    public $attachMany = [
        'photos' => \System\Models\File::class
    ];

    public $belongsTo = [
        'category' => \Saybme\Sk\Models\Category::class,
        'vendor' => \Saybme\Sk\Models\Vendor::class
    ];    

    public $belongsToMany = [
        'categories' => [
            \Saybme\Sk\Models\Category::class,
            'table' => 'saybme_sk_category_product',
            'key' => 'product_id',
            'otherKey' => 'category_id'
        ]
    ];
    
    public $hasMany = [
        'offers' => \Saybme\Sk\Models\Offer::class,
    ];

    public function scopeActive($query) {
        $query = $query->where('is_active', true);
        if(Input::get('sort') == 'price'){
            $query = $query->orderBy('price', 'ASC');
        }
        return $query;
    }

    public function scopeIsvendor($query) {   
        if(Input::get('vendor')){
            return $query->where('vendor_id', Input::get('vendor'));
        }
        return $query;
    }

    public function scopeSearchType($query, $type){
        if(!$type) return $query;
        return $query->where('name', 'like', '%'.$type.'%');
    }

    public function scopeIsCategoriesType($query, $type) {        
        return $query->orWhereHas('categories', function ($query) use ($type) {
            $query->where('category_id', $type);
        });
    }

    public function getAvailableOptions(){
        $arr[1] = 'В наличии';
        $arr[2] = 'Нет в наличии';
        $arr[3] = 'Под заказ';
        $arr[4] = 'Узнать о поступлени';
        return $arr;
    }

    public function getLinkAttribute(){
        $arr[] = 'directory';
        $arr[] = $this->uri;
        $url = implode('/', $arr);
        
        return url($url);
    }

    // Цены
    public function getPricesAttribute(){

        $arr = array();        

        if($this->price_usd != '0.00'){
            $arr[] = $this->price_usd . '$';
        }
        
        if($this->price_usd != '0.00'){
            $arr[] = $this->price_eur . '€';
        }        

        return collect($arr);
    }

    // Имя категории
    public function getCategoryNameAttribute(){
        if($this->category){
            return $this->category->name;
        }
        return;
    }

    // Опции
    public function getOptionsAttribute(){
        $offers = $this->offers;
        if(!$offers) return;

        return $offers->groupBy(function ($item, $key) {
            return $item->option->code;
        });
    }

    // Сохраняем URI
    public function beforeSave(){          
        $this->uri = $this->productUri();    
    }
    
    // Процент скидки
    public function getPercentAttribute(){         

        if(trim($this->old_price)){
            if($this->old_price > $this->price){
                $price = 100 / ($this->old_price / $this->price) . '%';
                return $price;
            }            
        }

        return;
    }

    // Создаеи URI
    private function productUri(){

        if($this->category){
            $arr[] = $this->category->slug;
        }
        
        $arr[] = $this->slug;
        $url = implode('/', $arr);

        return $url;
    }

}
