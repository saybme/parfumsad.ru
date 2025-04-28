<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Category;
use Request;
use Input;
use Log;

class Skcategory extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Категории',
            'description' => 'ВРаспределние по категорям'
        ];
    }

    public function defineProperties()
    {
        return [
        ];
    }

    function onRun(){
        $this->skcategory = $this->getContent();
    }

    private function getContent(){   
        
        $categories = Category::select('id','name','nest_depth')->get();

        $categories->each(function ($item, $key) {
            $steps = '';
            for($i = 1; $i <= $item->nest_depth; $i++){
                $steps .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            $item->title = $steps . $item->name;
        });

        $options['products'] = $this->getProducts();
        $options['categories'] = $categories;

        return $this->renderPartial('settings/set-category', $options);
    }    


    // Товары
    private function getProducts(){
        $products = Product::with('category:id,name')->select('id','name','category_id')->doesntHave('category')->get();
        return $products;    
    }

    // Присваиваем категорию
    public function onSetCategory(){

        $rules['category'] = 'required';
        $rules['products'] = 'required';

        $messages['products.required'] = 'Выберите товары';

        // Валидация
        Request::validate($rules, $messages);

        $idx = Input::get('products');
        foreach($idx as $id){
            $obj = Product::find($id);
            if($obj){
                $obj->category = Input::get('category');
                $obj->save();
            }
        }

        $options['items'] = $this->getProducts();
        $result['#products'] = $this->renderPartial('settings/products', $options);        
        return $result;
    }

    public $skcategory;
}
