<?php namespace Saybme\Sk\Classes\Rules;

use Lang;
use Input;
use Log;

class PhoneRule {

    public function validate($attribute, $value, $params){         
        
        $value = preg_replace("/[^0-9]/", "", $value); 

        // Пустое поле или не равно 11 символам
        if(!trim($value) OR strlen($value) != 11) return false;       
        
        $value = 7 . substr($value, 1);
        return true;
    }

    public function message(){
        return Lang::get('saybme.sk::validation');
    }

}