<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Banner;

class Skbanner extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Баннеры',
            'description' => 'Вывод баннеров'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title' => 'Тип вывода',
                'description' => 'Укажите тип вывода баннеров',
                'default' => 'getMainBanners',
                'type' => 'dropdown',
                'options' => [
                    'getMainBanners' => '3 баннер'
                ]             
            ]
        ];
    }

    function onRun(){
        $this->skbanner = $this->getContent();
    }

    private function getContent(){
        $type = $this->property('type');
        return $this->$type();
    }

    private function getMainBanners(){
        $banners = Banner::select('id','name')->with('image_pc')->active();  
        $options['banners'] = $banners->get();
        return $this->renderPartial('banners/mainbaners', $options);
    }

    public $skbanner;


}
