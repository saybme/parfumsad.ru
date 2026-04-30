<?php namespace Saybme\Sk\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

class Products extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Saybme.Sk', 'main-menu-item', 'side-menu-item3');
    }

    public function onModalProductPrice()
    {
        $this->asExtension('FormController')->update(post('record_id'));
        $this->vars['recordId'] = post('record_id');
        return $this->makePartial('update_form');
    }

    public function onUpdate()
    {
        $this->asExtension('FormController')->update_onSave(post('record_id'));
        return $this->listRefresh();
    }

    public function onGetOptionVariants()
    {
        $optionId = post('option_id');

        if (!$optionId) {
            return ['variants' => []];
        }

        $option = Option::find($optionId);

        $variants = [];
        if ($option && $option->variants) {
            foreach ($option->variants as $variant) {
                $variants[$variant->value] = $variant->label;
            }
        }

        return [
            '#value-container' => $this->makePartial('$/saybme/sk/models/productoptionvalue/_value_container.htm', [
                'variants' => $variants,
                'selectedValue' => ''
            ])
        ];
    }

}
