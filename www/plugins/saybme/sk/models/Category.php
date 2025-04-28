<?php namespace Saybme\Sk\Models;

use Model;
use Url;
use Str;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use Input;
use Log;

class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;
    use \October\Rain\Database\Traits\NestedTree;

    protected $fillable = ['name','uid','slug','parent','is_active','content'];
    protected $jsonable = ['props'];

    protected $slugs = ['slug' => 'name'];
    
    public $table = 'saybme_sk_categories';  

    public $rules = [
        'name' => 'required',
        'slug' => 'required'
    ];

    public $attachOne = [
        'preview' => \System\Models\File::class
    ];

    public $hasMany = [
        'products' => \Saybme\Sk\Models\Product::class,
    ];

    public function beforeCreate() {
        $this->uri = $this->setUri();
    }

    public function beforeSave() {
        $this->uri = $this->setUri();
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
   
    public function getLinkAttribute(){

        $arr[] = '/directory';
        $arr[] = $this->uri;
        $url = implode('/', $arr);
       
        return $url;
    }

    // Создаем URI
    private function setUri(){
        if($this->parent){
            $arr[] = $this->parent->uri;
        }
        $arr[] = $this->slug;
        $url = implode('/', $arr);
        return $url;
    }

    public function getIsOpenAttribute(){
        $currentURL = url()->current();
        $position = strpos($currentURL, $this->slug);        
        return $position !== false;
    }

    // Вложенные ресурсы
    public function getItemsAttribute(){
        $items = $this->children()->active()->get();
        return $items;
    }

    public static function getMenuTypeInfo($type) {
        $result = [];

        if ($type == 'sk-category') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }        

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {              

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    protected static function listSubCategoryOptions() {
        $category = self::getNested();

        $iterator = function($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->name;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->name,
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

        if ($item->type == 'sk-category') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = self::find($item->reference);
            if (!$category) {
                return;
            }

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

            if ($item->nesting) {
                $iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
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

    protected static function getCategoryPageUrl($pageCode, $category, $theme) {        

        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $paramName = 'ct';
        $url = CmsPage::url($page->getBaseFileName(), ['slug' => $category->slug]);     

        return $url;
    }


}
