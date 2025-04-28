<?php namespace Saybme\Sk\Models;

use Model;
use Str;

class Option extends Model
{
    use \October\Rain\Database\Traits\Validation;    

    protected $jsonable = ['props'];
    
    public $table = 'saybme_sk_options';
    
    public $rules = [
        'name' => 'required'
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function beforeCreate() {
        $this->code = Str::slug($this->name);
    }


}
