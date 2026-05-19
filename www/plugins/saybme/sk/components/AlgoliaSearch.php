<?php namespace Saybme\Sk\Components;

use Cms\Classes\ComponentBase;
use Saybme\Sk\Models\Product;

class AlgoliaSearch extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Algolia поиск',
            'description' => 'Поиск по товарам через Algolia'
        ];
    }

    public function onRun()
    {
        $this->algoliaSearch = $this->onSearch();
    }

    public function onSearch()
    {
        
        $searchQuery = input('q');
        $products = Product::search($searchQuery)
            ->paginate(20);
        
        return $this->renderPartial('catalog/search', ['products' => $products]);
    }

    public $algoliaSearch;
}