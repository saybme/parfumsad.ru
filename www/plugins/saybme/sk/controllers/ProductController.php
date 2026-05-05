<?php namespace Saybme\Sk\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Saybme\Sk\Models\Review;
use Illuminate\Support\Facades\DB;

use Saybme\Sk\Models\Product;

class ProductController extends Controller {

    public function content(Request $request){
        $products = Product::active()->get();
        return $products;
    }

}
