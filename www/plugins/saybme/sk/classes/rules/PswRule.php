<?php namespace Saybme\Sk\Classes\Rules;

use Saybme\Sk\Classes\Global\GlobalClass;
use Saybme\Sk\Models\User;
use Illuminate\Support\Facades\Hash; 
use Lang;
use Input;
use Log;

class PswRule {

    public function validate($attribute, $value, $params){         
        
        $phone = GlobalClass::formatPhone(Input::get('phone')); 
        $value = trim($value);         

        $user = User::where('phone', $phone)->first();
        if(!$user) return false;     

        if (!Hash::check($value, $user->password)) {  
            return false;  
        } 
        
        return true;
    }

    public function message(){
        return Lang::get('saybme.sk::validation');
    }

}