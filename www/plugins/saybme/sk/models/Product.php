<?php namespace Saybme\Sk\Models;

use Tailor\Models\GlobalRecord;
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
        'reviews' => \Saybme\Sk\Models\Review::class
    ];

    public function scopeActive($query) {
        $query = $query->where('is_active', true);
        if(Input::get('sort') == 'price'){
            $query = $query->orderBy('price', 'ASC');
        }
        return $query;
    }

    public function scopeIsNewType($query, $isNew) {
        if($isNew){
            return $query->where('is_new', true);
        }
        return $query;
    }

    public function scopeIsvendor($query) {
        if(Input::get('vendor')){
            return $query->where('vendor_id', Input::get('vendor'));
        }
        return $query;
    }

    public function getProductPriceAttribute(){
        $currency = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');

        if($this->price_usd > 0){
            return number_format($this->price_usd * $currency->dollar, 2, '.', ' ');
        }

        if($this->price_eur > 0){
            return number_format($this->price_eur * $currency->euro, 2, '.', ' ');
        }

        return $this->price;
    }

    public function getImagesAttribute(){

        $rows = array();

        $items = $this->photos;
        $items->prepend($this->preview);

        return $items->unique('id');
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

    // Количество отзывов
    public function getReviewsCountAttribute(){
        return $this->reviews->where('status', 'approved')->count();
    }

    public function getReviewStatsAttribute() {
        $stats = Review::where('product_id', $this->id)
            ->select(
                \DB::raw('COUNT(*) as count'),
                \DB::raw('COALESCE(ROUND(AVG(rating), 1), 0) as average'),
                \DB::raw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as stars_5'),
                \DB::raw('SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as stars_4'),
                \DB::raw('SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as stars_3'),
                \DB::raw('SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as stars_2'),
                \DB::raw('SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as stars_1')
            )
            ->first();

        $data = [
            'count' => (int) $stats->count,
            'average' => (float) $stats->average,
            'stars' => [
                5 => (int) $stats->stars_5,
                4 => (int) $stats->stars_4,
                3 => (int) $stats->stars_3,
                2 => (int) $stats->stars_2,
                1 => (int) $stats->stars_1,
            ],
            'percentage' => round(($stats->average / 5) * 100)
        ];

        //Log::info(print_r($data, true));

        return $data;
    }

}
