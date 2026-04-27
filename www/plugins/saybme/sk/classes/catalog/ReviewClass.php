<?php namespace Saybme\Sk\Classes\Catalog;

use Saybme\Sk\Models\Review;
use Input;
use Log;

class ReviewClass {

    // Создание отзыва
    public static function add($data = array(), $status = 'pending'){

         // Статус отзыва
         $data['status'] = $status;

         // Сохранение отзыва
         $review = new Review;
         $review->fill($data);
         $review->save();

         return $review;
    }

    // Все отзывы
    public static function all(){
        return Review::all();
    }

}
