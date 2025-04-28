<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Vendor;

class Skvendor extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Производители',
            'description' => 'Вывод производителей'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'SLUG',
                'description' => 'SLUG поста',
                'type' => 'string',
                'default' => '{{ :slug }}'
            ],          
            'type' => [
                'title' => 'Тип вывода',
                'description' => 'Укажите тип вывода',
                'default' => 'getAll',
                'type' => 'dropdown',
                'options' => [
                    'getAll' => 'Все производители',
                    'getPage' => 'Производитель'
                ]             
            ]
        ];
    }

    function onRun(){
        $this->skvendor = $this->getContent();
    }

    private function getContent(){
        $type = $this->property('type');
        return $this->$type();
    }

    private function getAll(){
        $items = Vendor::active()->get();
        return $items;
    }

    private function getPage(){
        return;
    }

    public $skvendor;


}
