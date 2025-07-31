<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Classes\Users\UserClass;
use Saybme\Sk\Models\User;
use Input;
use Log;

class Skcabinet extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Кабинет',
            'description' => 'Кабинет покупателя'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'SLUG',
                'description' => 'SLUG документа',
                'type' => 'string',
                'default' => '{{ :slug }}'
            ],
            'type' => [
                'title' => 'Тип вывода',
                'description' => 'Укажите тип вывода',
                'type' => 'dropdown',
                'default' => 'cabinet',
                'options' => [
                    'cabinet' => 'Кабинет',
                    'page' => 'Страницы кабинета'
                ]
            ]
        ];
    }

    function onRun(){
        $this->skcabinet = $this->getContent();
    }

    private function getContent(){
        $type = $this->property('type');
        return $this->$type();        
    }
    
    // Кабинет
    private function cabinet(){
        $user = UserClass::user();

        if(!$user){
            return $this->controller->run('404');    
        }

        $options['user'] = $user;
        $options['orders'] = UserClass::getUserOrders($user->id);

        return $this->renderPartial('cabinet/default', $options);
    }

    // Страницы кабинета
    private function page(){
        return $this->controller->run('404');
    }


    // Сохраняем профиль
    function onSaveProfile(){
        $data = Input::get();
        return $data;
    }


    public $skcabinet;
}
