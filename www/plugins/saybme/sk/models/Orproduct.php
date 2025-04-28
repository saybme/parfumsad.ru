<?php namespace Saybme\Sk\Models;

use Model;

class Orproduct extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $fillable = ['amount','price','name'];
   
    public $timestamps = false;
    
    public $table = 'saybme_sk_or_products';
    
    public $rules = [
        'amount' => 'required',
        'price' => 'required',
        'name' => 'required'
    ];

    public function getSumAttribute(){
        $sum = $this->price * $this->amount;
        return $sum;
    }

}
