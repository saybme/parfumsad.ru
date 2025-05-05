<?php namespace Saybme\Sk\Classes\Catalog;

use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Product;
use Input;

class CatalogClass {

    // Категории верхнего порядка
    static public function getTopCategories(){
        $catalog = Category::active()->find(371);
        return $catalog->children()->active()->get();
    }

    // Дерево категорий
    static public function getTreeCategories(){
        return Category::active()->select('id','parent_id','name','slug','nest_depth')->with('children')->getNested();       
    }

    // Товары категории
    static public function getCategoryProducts($category = null){
        
        $data['q'] = Input::get('q');
        $data['vendor'] = Input::get('vendor');
        
        if(!$category){            
            return Product::active()->isvendor()->searchType($data['q'])->paginate(30)->appends($data);
        };       

        $idx = $category->getAllChildrenAndSelf()->pluck('id');
        $products = Product::active()->whereIn('category_id', $idx)->orderBy('available', 'ASC')->iscategoriesType($category->id)->paginate(30);      

        return $products;
    }

    // Похожие товары
    static public function getSimilarProducts($product = null){
        if(!$product || !$product->category) return;

        $products = Product::active()->where('category_id', $product->category->id)->orderBy('available', 'ASC')->get();

        return $products->take(12);
    }

}