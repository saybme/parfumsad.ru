<?php namespace Saybme\Sk;

use System\Classes\PluginBase;
use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Page;
use Saybme\Sk\Models\Option;
use Saybme\Sk\Classes\Global\GlobalClass;
use Saybme\Sk\Classes\Users\UserClass;
use Saybme\Sk\Classes\Rules\PhoneRule;
use Saybme\Sk\Classes\Rules\UserRule;
use Saybme\Sk\Classes\Rules\PswRule;
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
        $this->registerConsoleCommand('saybme.currency', \Saybme\Sk\Console\Currency::class);
        $this->registerConsoleCommand('saybme.skreview', \Saybme\Sk\Console\Skreview::class);
        $this->registerConsoleCommand('saybme.skdeepproducts', \Saybme\Sk\Console\Skdeepproducts::class);

        $this->registerValidationRule('phone', PhoneRule::class);
        $this->registerValidationRule('user', UserRule::class);
        $this->registerValidationRule('psw', PswRule::class);
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot(){

        \Backend\Controllers\Auth::extend(function($controller) {
            if ($controller->action == 'update' && get_class($controller->controller) == 'Saybme\Sk\Controllers\Options') {
                $controller->addJs('/plugins/saybme/sk/assets/js/option-form.js');
            }
        });

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
            $controller->vars['isUser'] = UserClass::isUser();
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
            \Saybme\Sk\Components\Skpage::class => 'skpage',
            \Saybme\Sk\Components\Skcabinet::class => 'skcabinet',
            \Saybme\Sk\Components\Skreview::class => 'skreview'
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
