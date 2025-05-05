<?php namespace Saybme\Sk;

use System\Classes\PluginBase;
use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Page;
use Saybme\Sk\Models\Option;
use Saybme\Sk\Classes\Global\GlobalClass;
use Event;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register(){
        $this->registerConsoleCommand('saybme.skimport', \Saybme\Sk\Console\Skimport::class);
        $this->registerConsoleCommand('saybme.skproducts', \Saybme\Sk\Console\Skproducts::class);
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot(){

        Event::listen('cms.pageLookup.listTypes', function() {
            return [
                'sk-category' => 'SK категории',
                'sk-page' => 'SK страницы'
            ];
        });

        Event::listen('pages.menuitem.listTypes', function() {
            return [
                'sk-category' => 'SK категории',
                'sk-page' => 'SK страницы'
            ];
        });

        Event::listen(['cms.pageLookup.getTypeInfo', 'pages.menuitem.getTypeInfo'], function($type) {
            if ($type == 'sk-category') {
                return Category::getMenuTypeInfo($type);
            }
            if ($type == 'sk-page') {
                return Page::getMenuTypeInfo($type);
            }
        });

        Event::listen(['cms.pageLookup.resolveItem', 'pages.menuitem.resolveItem'], function($type, $item, $url, $theme) {
            if ($type == 'sk-category') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
            if ($type == 'sk-page') {
                return Page::resolveMenuItem($item, $url, $theme);
            }
        });

        // Глобальные переменные
        Event::listen('cms.page.init', function($controller) {
            $controller->vars['networks'] = GlobalClass::networks();
        });

    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            \Saybme\Sk\Components\Skbanner::class => 'skbanner',
            \Saybme\Sk\Components\Skblog::class => 'skblog',
            \Saybme\Sk\Components\Skcatalog::class => 'skcatalog',
            \Saybme\Sk\Components\Skvendor::class => 'skvendor',
            \Saybme\Sk\Components\Skcart::class => 'skcart',
            \Saybme\Sk\Components\Skapp::class => 'skapp',
            \Saybme\Sk\Components\Skcategory::class => 'skcategory',
            \Saybme\Sk\Components\Skpage::class => 'skpage'
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }


    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'phone' => [$this, 'formatPhone'],
                'option' => [$this, 'getOptionKey'],
                'get_content' => [$this, 'getContent']
            ]
        ];
    }

    // Получаем контент по сылке
    public function getContent($url = null){
        if(!$url) return;
        return $url;
        $file = file_get_contents($url);

        return $file;
    }

    // Опция по ключу
    public function getOptionKey($value = null){
        if(!$value) return;
        $obj = Option::where('code', $value)->first();
        return $obj;
    }

    public function formatPhone($value = null){
        if(!$value) return;
        $value = preg_replace("/[^0-9]/", '', $value);
        return $value;
    }



   



}
