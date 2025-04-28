<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Page;
use Log;

class Skpage extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Страницы',
            'description' => 'Компонент вывода страниц'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'SLUG',
                'description' => 'SLUG ресурса',
                'type' => 'string',
                'default' => '{{ :slug }}'
            ],
            'page' => [
                'title' => 'Страницы',
                'description' => 'Страницы',
                'type' => 'dropdown'
            ],
            'type' => [
                'title' => 'Тип вывода',
                'description' => 'Укажите тип вывода',
                'type' => 'dropdown',
                'default' => 'getPage',
                'options' => [
                    'getPage' => 'Страница',
                    'getPageId' => 'Страница по ID',
                    'all' => 'Все товары'
                ]
            ]
        ];
    }

    public function getPageOptions() {
        return Page::lists('title','id');
    }

    function onRun(){
        $this->skpage = $this->getContent();
    }

    private function getContent(){
        $type = $this->property('type');
        return $this->$type();        
    }

    private function getPageInfo($page){
        if(!$page) return;
        $this->page->title = $page->title;
    }

    // Все товары
    private function all(){
        $options['products'] = CatalogClass::getCategoryProducts();
        $options['filters'] = $this->getFilterCategory();  

        return $this->renderPartial('catalog/category', $options);
    }     
    
    // Страница
    private function getPage(){

        $tpl = 'pages/wrap';
        $slug = $this->property('slug');

        $page = Page::active()->where('uri', $slug)->first();

        if(!$page) return $this->controller->run('404');

        $this->getPageInfo($page);

        $options = array();
        $options['page'] = $page;

        if(trim($page->tpl)){
            // Кастомные шаблон
            $tpl = trim($page->tpl);
        }

        // Хлебные крошки
        $options['breadcrumbs'] = $this->breadcrumbsPage($page);

        return $this->renderPartial($tpl, $options);
    }

    // Хлебные крошки страницы
    private function breadcrumbsPage($page = null){
        if(!$page) return;

        $items = $page->getParentsAndSelf();

        $items->each(function ($item, $key) use ($page) {
            $item->url = $item->link;
            $item->name = $item->title;
            $item->active = $item->link === $page->link;
        });
        
        return $items;
    }

    // Страница по ID
    private function getPageId(){
        $pageId = $this->property('page');
        $page = Page::active()->find($pageId);

        if(!$page) return;

        return $page;
    }

    // Хлебные крошки товара
    private function productBreadcrumbs($page){
        if(!$page->category) return;
        $items = $page->category->getParentsAndSelf();  
        return $items;
    }

    public $skpage;
}
