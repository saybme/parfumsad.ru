<?php namespace Saybme\Sk\Models;

use Model;

class Payment extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    protected $jsonable = ['props'];
   
    public $timestamps = false;
  
    public $table = 'saybme_sk_payments';
   
    public $rules = [
        'name' => 'required'
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

}
