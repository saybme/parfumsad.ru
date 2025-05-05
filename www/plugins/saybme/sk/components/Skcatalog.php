<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Classes\Catalog\CatalogClass;
use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Vendor;
use Input;

class Skcatalog extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Каталог',
            'description' => 'Каталог'
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
                'default' => 'category',
                'options' => [
                    'category' => 'Товар или товары категории',
                    'all' => 'Все товары'
                ]
            ]
        ];
    }

    function onRun(){
        $this->skcatalog = $this->getContent();
    }

    private function getContent(){
        $type = $this->property('type');
        return $this->$type();        
    }

    private function getPageInfo($page){
        if(!$page) return;
        $this->page->title = $page->name;
    }

    // Все товары
    private function all(){
        $options['products'] = CatalogClass::getCategoryProducts();
        $options['filters'] = $this->getFilterCategory();  

        $currentURL = url()->current();
        //dd($options);

        return $this->renderPartial('catalog/category', $options);
    }

    // Товар или товары категории
    private function category(){

        $slug = $this->property('slug');

        // Поиск категории
        $page = Category::active()->where('uri', $slug)->first();
        if($page){
            $this->getPageInfo($page);            
            $options['category'] = $page;
            $options['breadcrumbs'] = $this->categoryBreadcrumbs($page);
            $options['products'] = CatalogClass::getCategoryProducts($page);
            $options['filters'] = $this->getFilterCategory();  
            return $this->renderPartial('catalog/category', $options);
        };

        // Поиск товара
        $page = Product::active()->where('uri', $slug)->first();
        if($page){
            $this->getPageInfo($page);            
            $options['product'] = $page;
            $options['products'] = CatalogClass::getSimilarProducts($page);
            $options['breadcrumbs'] = $this->productBreadcrumbs($page);
            return $this->renderPartial('catalog/product', $options);
        };

        return $this->controller->run('404');

    }

    // Фильтры категории
    private function getFilterCategory(){

        $options = array();

        if(Input::get('vendor')){
            $vendor = Vendor::find(Input::get('vendor'));
            if($vendor){
                $options['vendor']['title'] = $vendor->name;
                $options['vendor']['items'] = Input::get('vendor');
            }            
        }        

        return collect($options);
    }

    // Хлебные крошки категорий
    private function categoryBreadcrumbs($page){

        $items = $page->getParentsAndSelf();

        $items->each(function ($item, $key) use ($page) {
            $item->active = false;
            if($item->id == $page->id) $item->active = true;

            $item->url = $item->slug;
        });

        return $items;
    }

    // Хлебные крошки товара
    private function productBreadcrumbs($page){
        if(!$page->category) return;
        $items = $page->category->getParentsAndSelf();  
        return $items;
    }

    // Окно товара
    function onOpenProduct(){
        $id = Input::get('id');

        $obj = Product::active()->find($id);
        if(!$obj){
            return;
        }

        $options['product'] = $obj;
        $result['modal'] = $this->renderPartial('modals/product', $options);
        return $result;
    }

    public $skcatalog;


}
