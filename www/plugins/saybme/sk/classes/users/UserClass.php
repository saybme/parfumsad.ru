<?php namespace Saybme\Sk\Classes\Users;

use Saybme\Sk\Models\User;
use Saybme\Sk\Models\Order;
use Saybme\Sk\Classes\Global\GlobalClass;
use Illuminate\Support\Facades\Cookie;
use Response;
use Redirect;
use Mail;
use Input;
use Log;
use Lang;
use Request;

class UserClass {

    // Создаем пользователя
    public function create(){

        // пароль
        $password = $this->createPassword();
        $hash = md5(time());

        $data = Input::get();
        $data['password'] = $password;
        $data['password_confirmation'] = $password;
        $data['hash'] = $hash;

        // Создаем
        $user = new User;
        $user->fill($data);
        $user->save();

        $data['current'] = URL::to('/') . '/useractive/' . $hash;

        // Отправляем письмо с подтверждением
        Mail::send('saybme.sk:profile', $data, function($message) use ($data) {
            $message->to($data['email'], 'Admin Person');
        });
        
    }

    // Авторизация пользователя
    public function enter(){       

        $phone = GlobalClass::formatPhone(Input::get('phone'));
        $data = Input::get();
        $data['phone'] = $phone;

        $user = User::where('phone', $phone)->first();      

        // Сохраняем куки на 60 дней
        Cookie::queue('userid', $user->id, 86400);

        return Redirect::to('/cabinet');
    }

    // Активация профиля
    public function activeProfile($hash = ''){
        if(!$hash) return;

        $user = User::where('hash', $hash)->first();

        if(!$user){
            return;
        }

        // Активация
        $user->is_active = true;
        $user->hash = md5(time());
        $user->save();

        return Redirect::to('/cabinet');
    }

    // Профиль
    public static function user(){
        $userId = Cookie::get('userid');  
        $user = User::where('is_active', true)->find($userId);
        return $user;  
    }

    // Проверка авторизации
    public static function isUser(){
        $userId = Cookie::get('userid');  
        $user = User::select('phone')->where('is_active', true)->find($userId);
        return $user;  
    }

    // Заказы покупателя
    public static function getUserOrders($id = ''){
        if(!$id) return;

        $orders = Order::where('user_id', $id)->orderBy('id', 'desc')->get();
        return $orders;
    }

    // Создаем пароль
    public function createPassword(){
        $text = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        return substr($text,0,6);;
    }

}