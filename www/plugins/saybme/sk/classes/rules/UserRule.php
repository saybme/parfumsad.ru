<?php namespace Saybme\Sk\Classes\Rules;

use Saybme\Sk\Classes\Global\GlobalClass;
use Saybme\Sk\Models\User;
use Lang;
use Input;
use Log;

class UserRule {

    public function validate($attribute, $value, $params){         
        
        $value = GlobalClass::formatPhone($value); 

        $user = User::where('phone', $value)->where('is_active', true)->first();
        if(!$user) return false;      
        
        return true;
    }

    public function message(){
        return Lang::get('saybme.sk::validation');
    }

}