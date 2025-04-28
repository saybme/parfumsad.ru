<?php namespace Saybme\Sk\Models;

use Model;

class Banner extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    
    public $table = 'saybme_sk_banners';
    
    public $rules = [
        'name' => 'required'
    ];

    public $attachOne = [
        'image_pc' => \System\Models\File::class,
        'image_mb' => \System\Models\File::class
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }    

}
