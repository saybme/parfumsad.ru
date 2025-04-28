<?php namespace Saybme\Sk\Models;

use Model;

class Offer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    
    public $table = 'saybme_sk_offers';
    
    public $rules = [
        'price' => 'required',
        'value' => 'required',
        'option' => 'required'
    ];

    public $attachOne = [
        'preview' => \System\Models\File::class
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public $belongsTo = [
        'option' => \Saybme\Sk\Models\Option::class
    ]; 

    public function getHashAttribute(){
        if(!$this->option) return;
        $value = $this->option->code;
        return md5($value);
    }

    public function getCodeAttribute(){
        if(!$this->option) return;
        $value = $this->option->code;
        return $value;
    }

    public function getTitleAttribute(){
        if(!$this->option) return;
        $arr[] = $this->value;
        $arr[] = $this->option->measure;
        return implode(' ', $arr);
    }

}
