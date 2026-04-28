<?php namespace Saybme\Sk\Classes\Catalog;

use Saybme\Sk\Models\Review;
use Saybme\Sk\Models\Product;
use Saybme\Sk\Classes\Deepseek\DeepSeekService;
use GuzzleHttp\Client;
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

    // Тест
    public static function test(){
        return 'test';
    }

    // Поиск рандомного товара
    public static function randomProduct()
    {
        $product = Product::select('id','name','content','category_id')->where('is_active', true)->inRandomOrder()->first();

        $deepArr = $product->category->getParentsAndSelf()->pluck('name')->implode(' / ');


        $product->deeptitle = $deepArr . ' / ' . $product->name;

        return $product;
    }

    // Создаем отзыв рандомному товару
    public static function createRandomReview(){

        $product = self::randomProduct();

        $productName = $product->deeptitle;
        $productDescription = $product->content ?: $product->name;
        $productRating = rand(4,5);

        // DeepSeek
        $q = new DeepSeekService();
        $data = $q->generateReview($productName, $productDescription, $productRating);

        // Данные для модели отзыва
        $options['user_id'] = env('USER_REVIEW_ID');
        $options['product_id'] = $product->id;
        $options['title'] = $data['title'];
        $options['content'] = $data['content'];
        $options['props']['username'] = $data['reviewer_name'];
        $options['rating'] = $productRating;
        $options['pros'] = implode(PHP_EOL.PHP_EOL, $data['pros']);
        $options['cons'] = implode(PHP_EOL.PHP_EOL, $data['cons']);

        // Создаем отзыв
        $review = self::add($options);

        return $review;
    }

}
