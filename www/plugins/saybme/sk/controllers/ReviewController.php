<?php namespace Saybme\Sk\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Saybme\Sk\Models\Review;
use Illuminate\Support\Facades\DB;

use Saybme\Sk\Classes\Catalog\ReviewClass;

class ReviewController extends Controller {

    public function store(Request $request){
        // Создаем отзыв
        $review = ReviewClass::createRandomReview();
        return $review;
    }

}
