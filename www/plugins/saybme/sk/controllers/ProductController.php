<?php namespace Saybme\Sk\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Saybme\Sk\Models\Review;
use Illuminate\Support\Facades\DB;

use Saybme\Sk\Models\Product;

class ProductController extends Controller {

    public function content(){
        // Выбрать случайные 20 товаров из базы
        $products = Product::with('category:id,name,props')
            ->active()
            ->where(function($query) {
                $query->whereNull('content')
                    ->orWhere('content', '');
            })
            ->whereNotNull('category_id')
            ->inRandomOrder()
            ->limit(20)
            ->get();     

        return $products;
    }

}
