<?php namespace Saybme\Sk\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Saybme\Sk\Models\Review;
use Flash;

class Reviews extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Saybme.Sk', 'main-menu-item2', 'side-menu-item7');
    }

    public function onApprove()
    {
        $selected = post('checked');
        $reviews = Review::whereIn('id', $selected)->get();
        foreach ($reviews as $review) {
            $review->status = Review::STATUS_APPROVED;
            $review->save();
        }
        Flash::success('Отзывы одобрены');
        return $this->listRefresh();
    }

}
