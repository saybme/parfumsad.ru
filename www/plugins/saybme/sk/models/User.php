<?php namespace Saybme\Sk\Models;

use Model;

class User extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Hashable;
    use \October\Rain\Database\Traits\Purgeable;

    protected $hashable = ['password'];
    protected $purgeable = ['password_confirmation'];

    protected $fillable = [
        'email',
        'phone',
        'inn',
        'email',
        'password',
        'password_confirmation',
        'hash'
    ];
    
    public $table = 'saybme_sk_users';
    
    public $rules = [
        'email' => 'required|email|unique:saybme_sk_users,email',
        'phone' => 'required|phone|unique:saybme_sk_users,phone',
        'inn' => 'required|size:10|unique:saybme_sk_users,inn',
        'password' => ['required:create', 'string', 'confirmed'],
    ];

    public function beforeValidate(){
        $phone = preg_replace("/[^0-9]/", "", $this->phone); 
        $phone = 7 . substr($phone, 1);
        $this->phone = $phone;
    }


    public function beforeUpdate(){
        if(!$this->password){
            unset($this->password);
        }
    }


}
