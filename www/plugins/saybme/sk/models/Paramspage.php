<?php namespace Saybme\Sk\Models;

use October\Rain\Database\ExpandoModel;

class Paramspage extends ExpandoModel {

    use \October\Rain\Database\Traits\Sortable;

    public $table = 'saybme_sk_page_params';

    protected $expandoPassthru = ['parent_id', 'sort_order'];

    public $attachMany = [
        'photos' => \System\Models\File::class,
    ];

}
