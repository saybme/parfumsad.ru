<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Classes\Catalog\CatalogClass;
use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Page;
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
                    'all' => 'Все каталог',
                    'products' => 'Товары',
                    'search' => 'Результаты поиска'
                ]
            ],
            'tmp' => [
                'title' => 'Шаблон',
                'description' => 'Шаблон вывода'
            ],
            'is_new' => [
                'title' => 'Новинки',
                'description' => 'Выводить только новинки',
                'type' => 'checkbox',
                'default' => false
            ],
            'is_random' => [
                'title' => 'Случайные',
                'description' => 'Выводить в случайном порядке',
                'type' => 'checkbox',
                'default' => false
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
        $this->page->url = $page->link;
        $this->page->meta_title = $page->meta_title ?: $page->name;
        $this->page->meta_description = $page->meta_description;
        $this->page->meta_keywords = $page->meta_keywords;

    }

    // Все товары
    private function products(){

        $params['is_new'] = $this->property('is_new');
        $params['paginate'] = 30;
        $params['is_random'] = $this->property('is_random') ? true : false;
        $tmp = $this->property('tmp') ?: 'catalog/products';

        $products = CatalogClass::getAllProducts($params);


        return $this->renderPartial($tmp, ['products' => $products]);
    }

    // Страница каталога
    private function all(){

        $isNew = $this->property('is_new');
        $tmp = $this->property('tmp') ?: 'catalog/category';

        $products = CatalogClass::getCategoryProducts(null, $isNew);

        $options['page'] = Page::active()->find(2);
        $options['products'] = $products;
        $options['filters'] = $this->getFilterCategory();

        $currentURL = url()->current();

        return $this->renderPartial($tmp, $options);
    }

    // Результаты поиска
    private function search(){
        $tmp = $this->property('tmp') ?: 'catalog/search';
        $isNew = $this->property('is_new');
        $options['products'] = CatalogClass::getCategoryProducts(null, $isNew);
        return $this->renderPartial($tmp, $options);
    }

    // Товар или товары категории
    private function category(){

        $slug = $this->property('slug');

        // Поиск категории
        $page = Category::active()->where('uri', $slug)->first();
        if($page){
            $this->categoryMeta($page);
            $options['page'] = $page;
            $options['breadcrumbs'] = $this->categoryBreadcrumbs($page);
            $isNew = $this->property('is_new');
            $options['products'] = CatalogClass::getCategoryProducts($page, $isNew);
            $options['filters'] = $this->getFilterCategory();
            return $this->renderPartial('catalog/category', $options);
        };

        // Поиск товара
        $page = Product::active()->where('uri', $slug)->first();
        if($page){
            $this->getPageInfo($page);
            $options['product'] = $page;
            $isNew = $this->property('is_new');
            $options['products'] = CatalogClass::getSimilarProducts($page, $isNew);
            $options['breadcrumbs'] = $this->productBreadcrumbs($page);
            return $this->renderPartial('catalog/product', $options);
        };

        return $this->controller->run('404');

    }

    // Мета теги для категории
    private function categoryMeta($page){
        if(!$page) return;

        $this->page->title = $page->name;
        $this->page->meta_title = $page->props['seo_title'] ?? $page->name;
        $this->page->url = $page->link;
        $this->page->meta_description = $page->props['seo_description'] ?? '';
        $this->page->meta_keywords = $page->props['seo_keywords'] ?? '';

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
            $item->active = $page->id === $item->id;;
            $item->url = $item->link;
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
    // function onOpenProduct(){
    //     $id = Input::get('id');

    //     $obj = Product::active()->find($id);
    //     if(!$obj){
    //         return;
    //     }

    //     $options['product'] = $obj;
    //     $result['modal'] = $this->renderPartial('modals/product', $options);
    //     return $result;
    // }

    public $skcatalog;
}
