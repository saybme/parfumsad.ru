<?php namespace Saybme\Sk\Classes\Catalog;

use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Product;
use Session;
use Input;

class CatalogClass {

    // Категории верхнего порядка
    static public function getTopCategories(){
        $catalog = Category::active()->find(371);
        return $catalog->children()->active()->get();
    }

    // Все товары
    static public function getAllProducts($params = array()){
        $isNew = isset($params['is_new']) ? $params['is_new'] : false;
        $paginate = isset($params['paginate']) ? $params['paginate'] : 30;
        $isRandom = isset($params['is_random']) ? $params['is_random'] : false;
        
        $query = Product::active()->isNewType($isNew);
        if ($isRandom) {
            $query->inRandomOrder();
        }
        return $query->paginate($paginate);
    }

    // Дерево категорий
    static public function getTreeCategories(){
        return Category::active()->select('id','parent_id','name','slug','nest_depth')->with('children')->getNested();       
    }

    // Товары категории
    static public function getCategoryProducts($category = null, $isNew = false){
        
        $data['q'] = Input::get('q');
        $data['vendor'] = Input::get('vendor');
        
        if(!$category){     
            $products = Product::active()->isvendor()->searchType($data['q'])->isNewType($isNew)->paginate(30)->appends($data);  
            // проверяем есть ли товар в корзине
            foreach($products as $product){
                $product->in_cart = CatalogClass::checkInCart($product->id);
            }     
            return $products;
        };       

        $idx = $category->getAllChildrenAndSelf()->pluck('id');
        $products = Product::active()->whereIn('category_id', $idx)->orderBy('available', 'ASC')->iscategoriesType($category->id)->isNewType($isNew)->paginate(30);  
        // проверяем есть ли товар в корзине
        foreach($products as $product){
            $product->in_cart = CatalogClass::checkInCart($product->id);
        }         

        return $products;
    }

    // Проверка наличия товара в корзине
    static public function checkInCart($productId){
        $cart = Session::get('cart.products', []);
        if (!is_array($cart) || empty($cart)) {
            return false;
        }

        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['id'] ?? null) == $productId) {
                return $item['amount'] ?? false;
            }
        }

        return false;
    }

    // Похожие товары
    static public function getSimilarProducts($product = null, $isNew = false){
        if(!$product || !$product->category) return;

        $products = Product::active()->where('category_id', $product->category->id)->orderBy('available', 'ASC')->isNewType($isNew)->get();

        return $products->take(12);
    }

}