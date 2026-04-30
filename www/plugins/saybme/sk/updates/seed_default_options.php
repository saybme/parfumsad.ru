<?php namespace Saybme\Sk\Updates;

use Seeder;
use Saybme\Sk\Models\Option;
use Saybme\Sk\Models\OptionVariant;

class SeedDefaultOptions extends Seeder
{
    public function run()
    {
        // Проверяем, есть ли уже данные
        if (Option::count() > 0) {
            return;
        }

        // Опция "Цвет"
        $color = Option::create([
            'name' => 'color',
            'label' => 'Цвет',
            'type' => 'color',
            'is_filterable' => true,
            'sort_order' => 1
        ]);

        $color->variants()->createMany([
            ['value' => 'red', 'label' => 'Красный', 'sort_order' => 1],
            ['value' => 'blue', 'label' => 'Синий', 'sort_order' => 2],
            ['value' => 'green', 'label' => 'Зеленый', 'sort_order' => 3],
            ['value' => 'black', 'label' => 'Черный', 'sort_order' => 4],
            ['value' => 'white', 'label' => 'Белый', 'sort_order' => 5],
        ]);

        // Опция "Размер"
        $size = Option::create([
            'name' => 'size',
            'label' => 'Размер',
            'type' => 'dropdown',
            'is_filterable' => true,
            'sort_order' => 2
        ]);

        $size->variants()->createMany([
            ['value' => 'xs', 'label' => 'XS', 'sort_order' => 1],
            ['value' => 's', 'label' => 'S', 'sort_order' => 2],
            ['value' => 'm', 'label' => 'M', 'sort_order' => 3],
            ['value' => 'l', 'label' => 'L', 'sort_order' => 4],
            ['value' => 'xl', 'label' => 'XL', 'sort_order' => 5],
        ]);

        // Опция "Материал"
        $material = Option::create([
            'name' => 'material',
            'label' => 'Материал',
            'type' => 'checkbox',
            'is_filterable' => true,
            'sort_order' => 3
        ]);

        $material->variants()->createMany([
            ['value' => 'cotton', 'label' => 'Хлопок', 'sort_order' => 1],
            ['value' => 'polyester', 'label' => 'Полиэстер', 'sort_order' => 2],
            ['value' => 'wool', 'label' => 'Шерсть', 'sort_order' => 3],
        ]);
    }
}
