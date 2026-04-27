<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Review;
use Saybme\Sk\Models\Product;
use Cms\Classes\ComponentBase;

class Skreview extends ComponentBase
{
    public $skreview;
    public $productId; // Для отладки

    public function componentDetails()
    {
        return [
            'name' => 'Отзывы',
            'description' => 'Компонент вывода отзывов'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title' => 'Тип',
                'type' => 'dropdown',
                'options' => [
                    'all' => 'Все',
                    'product' => 'Отзывы о товаре'
                ],
                'default' => 'all'
            ],
            'product_id' => [
                'title' => 'ID товара',
                'type' => 'string',
                'default' => ''
            ]
        ];
    }

    public function onRun()
    {
        $this->productId = $this->property('product_id'); // Для отладки
        $this->skreview = $this->getContent();
    }

    private function getContent()
    {
        $type = $this->property('type');

        if (method_exists($this, $type)) {
            return $this->$type();
        }

        return $this->defaultContent();
    }

    private function all()
    {
        return Review::active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function product()
    {
        $id = (int) $this->property('product_id');

        if (!$id) {
            return collect();
        }

        $options['reviews'] = Review::active()
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $options['product_reviews'] = $this->reviewsProduct($id);

        return $this->renderPartial('reviews/product', $options);
    }

    // Количество отзывов у товара
    public function reviewsProduct($id) {
        $product = Product::find($id);
        return $product->review_stats;
    }

    private function defaultContent()
    {
        return collect();
    }
}
