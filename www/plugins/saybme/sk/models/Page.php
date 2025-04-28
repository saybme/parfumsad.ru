<?php namespace Saybme\Sk\Models;

use Model;

class Page extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;
    use \October\Rain\Database\Traits\Sluggable;

    protected $slugs = ['slug' => 'title'];
    protected $jsonable = ['seo','props'];
   
    public $table = 'saybme_sk_pages';
   
    public $rules = [
        'title' => 'required'
    ];

    public $attachMany = [
        'photos' => \System\Models\File::class
    ];

    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }

    public function beforeCreate() {
        $this->uri = $this->createUri();
    }

    public function beforeSave() {
        $this->uri = $this->createUri();
    }

    public $hasMany = [
        'params' => [
            Paramspage::class,
            'key' => 'parent_id',
            'delete' => true
        ],
    ];
    

    // Создаем URI
    private function createUri(){

        if($this->parent){
            $arr[] = $this->parent->uri;
        };

        $arr[] = $this->slug;
        $uri = implode('/', $arr);

        return $uri;
    }

    // Ссылка
    public function getLinkAttribute(){
        $link = url($this->uri);
        return $link;
    }

    // Опции
    public function getOptionsAttribute(){

        $options = $this->params;

        $rows = array();
        foreach($options as $option){
            if(trim($option->code)){
                $rows[$option->code] = $option;
            }            
        }

        return collect($rows);
    }


    public static function getMenuTypeInfo($type) {
        $result = [];

        if ($type == 'sk-page') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }          

        return $result;
    }

    protected static function listSubCategoryOptions() {
        $category = self::getNested();

        $iterator = function($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->title;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->title,
                        'items' => $iterator($category->children)
                    ];
                }
            }

            return $result;
        };

        return $iterator($category);
    }

    public static function resolveMenuItem($item, $url, $theme){
        $result = null;

        if ($item->type == 'sk-page') {
            if (!$item->reference) {
                return;
            }

            $category = self::find($item->reference);
            if (!$category) {
                return;
            }           

            // Ссылка на страницу
            $pageUrl = $category->link;

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

            if ($item->nesting) {
                $iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = $category->link;
                        $branchItem['isActive'] = $branchItem['url'] == $url;
                        $branchItem['title'] = $category->name;
                        $branchItem['mtime'] = $category->updated_at;

                        if ($category->children) {
                            $branchItem['items'] = $iterator($category->children);
                        }

                        $branch[] = $branchItem;
                    }

                    return $branch;
                };

                $result['items'] = $iterator($category->children);
            }
        }        

        return $result;
    }    


}
