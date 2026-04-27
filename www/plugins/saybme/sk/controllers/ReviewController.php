<?php namespace Saybme\Sk\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Saybme\Sk\Models\Review;
use Illuminate\Support\Facades\DB;

use Saybme\Sk\Classes\Catalog\ReviewClass;

class ReviewController extends Controller {

    public function store(Request $request){

        $options['user_id'] = 21;
        $options['product_id'] = 6717;
        $options['content'] = 'Тест';
        $options['rating'] = 4;
        $options['pros'] = 'Преимущества';
        $options['cons'] = 'Недостатки';

        $review = ReviewClass::add($options);

        return $review;
    }

    public function index($productId){
        return 122;
    }

}
