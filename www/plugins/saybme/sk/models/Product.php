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
        ],
        // ЭТО УЖЕ ЕСТЬ - ОСТАВЬТЕ
        'options' => [
            Option::class,
            'table' => 'saybme_sk_product_option_values',
            'key' => 'product_id',
            'otherKey' => 'option_id',
            'pivot' => ['value', 'value_extra'],  // ← ДОБАВЬТЕ ЭТУ СТРОКУ
            'timestamps' => true
        ]
    ];

    // ЭТО УЖЕ ЕСТЬ - ОСТАВЬТЕ
    public $hasMany = [
        'reviews' => \Saybme\Sk\Models\Review::class,
        'option_values' => [ProductOptionValue::class, 'key' => 'product_id']  // ← ДОБАВЬТЕ ЭТУ СТРОКУ
    ];

    // Аксессор для отображения опций в списке
    public function getOptionsListAttribute()
    {
        return $this->options->map(function($option) {
            return $option->label . ': ' . $option->pivot->value;
        })->implode(', ');
    }

    // ========== ДОБАВЬТЕ ЭТИ МЕТОДЫ ==========

    // Получить все характеристики товара для отображения на сайте
    public function getProductOptionsAttribute()
    {
        $options = [];
        foreach ($this->option_values as $value) {
            $option = $value->option;
            if (!isset($options[$option->id])) {
                $options[$option->id] = [
                    'option' => $option,
                    'values' => []
                ];
            }
            $options[$option->id]['values'][] = $value;
        }
        return $options;
    }

    // Получить HTML для отображения характеристик
    public function getOptionsHtmlAttribute()
    {
        if ($this->option_values->isEmpty()) {
            return '';
        }

        $html = '<div class="product-options">';
        foreach ($this->option_values->groupBy('option_id') as $optionId => $values) {
            $option = $values->first()->option;
            $html .= '<div class="option-group">';
            $html .= '<strong>' . e($option->label) . ':</strong> ';
            $html .= implode(', ', $values->pluck('value')->toArray());
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    // ========== ОСТАЛЬНОЙ ВАШ КОД БЕЗ ИЗМЕНЕНИЙ ==========

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

    public function getCategoryNameAttribute(){
        if($this->category){
            return $this->category->name;
        }
        return;
    }

    public function getOptionsAttribute(){
        $offers = $this->offers;
        if(!$offers) return;

        return $offers->groupBy(function ($item, $key) {
            return $item->option->code;
        });
    }

    public function beforeSave(){
        $this->uri = $this->productUri();
    }

    public function getPercentAttribute(){

        if(trim($this->old_price)){
            if($this->old_price > $this->price){
                $price = 100 / ($this->old_price / $this->price) . '%';
                return $price;
            }
        }

        return;
    }

    private function productUri(){

        if($this->category){
            $arr[] = $this->category->slug;
        }

        $arr[] = $this->slug;
        $url = implode('/', $arr);

        return $url;
    }

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

        return $data;
    }
}
