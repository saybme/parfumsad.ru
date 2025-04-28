<?php namespace Saybme\Sk\Models;

use Model;

class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    use \October\Rain\Database\Traits\Sluggable;

    protected $jsonable = ['props'];
    protected $slugs = ['slug' => 'name'];
   
    public $table = 'saybme_sk_posts';
    
    public $rules = [
        'name' => 'required',
        'slug' => 'required'
    ];

    public $attachOne = [
        'preview' => \System\Models\File::class
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }  

    public function getLinkAttribute(){

        $arr[] = 'blog';
        $arr[] = $this->slug;

        $url = implode('/', $arr);
        return url($url);
    }

}
