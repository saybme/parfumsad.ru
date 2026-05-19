<?php namespace Saybme\Sk\Behaviors;

use October\Rain\Extension\ExtensionBase;

class AlgoliaSearchable extends ExtensionBase
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Определяем, какие поля отправлять в Algolia
     */
    public function toSearchableArray()
    {
        $array = [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'slug' => $this->model->slug,
            'article' => $this->model->article,
            'content' => strip_tags($this->model->content), // убираем HTML
            'price' => (float) $this->model->price,
            'price_usd' => (float) $this->model->price_usd,
            'price_eur' => (float) $this->model->price_eur,
            'is_active' => (bool) $this->model->is_active,
            'available' => $this->model->available,
            'category_name' => $this->model->category_name, // через аксессор
            'vendor_name' => $this->model->vendor ? $this->model->vendor->name : null,
            'categories' => $this->model->categories->pluck('name')->toArray(),
            'created_at' => $this->model->created_at ? $this->model->created_at->toDateTimeString() : null,
            'updated_at' => $this->model->updated_at ? $this->model->updated_at->toDateTimeString() : null,
        ];

        // Добавляем опции товара (если есть)
        if ($this->model->relationLoaded('options') && $this->model->options->count()) {
            $array['options'] = $this->model->options->map(function($option) {
                return [
                    'name' => $option->label,
                    'value' => $option->pivot->value,
                    'value_extra' => $option->pivot->value_extra
                ];
            })->toArray();
        }

        // Добавляем мета-информацию для поиска
        $array['searchable_text'] = implode(' ', array_filter([
            $this->model->name,
            $this->model->article,
            strip_tags($this->model->content),
            $this->model->category_name,
            $this->model->vendor ? $this->model->vendor->name : null,
        ]));

        return $array;
    }

    /**
     * Опционально: указываем, какие поля должны быть searchable в Algolia
     */
    public function searchableAs()
    {
        return 'products'; // индекс в Algolia
    }

    /**
     * Опционально: настройка очереди для синхронизации
     */
    public function shouldBeSearchable()
    {
        return $this->model->is_active == true; // только активные товары
    }
}