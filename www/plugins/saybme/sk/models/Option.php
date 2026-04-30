<?php namespace Saybme\Sk\Models;

use Model;

class Option extends Model
{
    use \October\Rain\Database\Traits\Sortable;
    use \October\Rain\Database\Traits\Validation;

    public $table = 'saybme_sk_options';

    public $rules = [
        'name' => 'required|unique:saybme_sk_options',
        'label' => 'required'
    ];

    public $hasMany = [
        'variants' => [OptionVariant::class]
    ];

    public static function getOptionsList()
    {
        return self::orderBy('sort_order')->pluck('label', 'id')->toArray();
    }

    public function getTypeOptions()
    {
        return [
            'dropdown' => 'Выпадающий список',
            'checkbox' => 'Чекбоксы',
            'radio' => 'Переключатели',
            'range' => 'Диапазон чисел',
            'color' => 'Цвет',
            'text' => 'Текст'
        ];
    }

    public function getVariantsCountAttribute()
    {
        return $this->variants()->count();
    }
}
