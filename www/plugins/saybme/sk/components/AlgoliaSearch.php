<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Product;

class Algoliasearch extends \Cms\Classes\ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Algolia поиск',
            'description' => 'Поиск по товарам через Algolia'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title' => 'Количество товаров',
                'description' => 'Количество товаров для вывода',
                'type' => 'string',
                'default' => '20'
            ]
        ];
    }

    public function onRun()
    {
        $this->algoliasearch = $this->onSearch();
    }

    public function onSearch()
    {
        
        $searchQuery = input('q');        
        
        $products = Product::search($searchQuery)
            ->paginate((int) ($this->property('limit') ?: 20));       
        
        return $this->renderPartial('catalog/search', ['products' => $products]);
    }

    public $algoliasearch;
}