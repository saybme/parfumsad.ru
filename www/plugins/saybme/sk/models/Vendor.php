<?php namespace Saybme\Sk\Models;

use Model;

class Vendor extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;
    use \October\Rain\Database\Traits\Sortable;

    protected $fillable = ['name','uid','slug','is_active'];
    protected $jsonable = ['props'];

    protected $slugs = ['slug' => 'name'];
    
    public $table = 'saybme_sk_vendor';
    
    public $rules = [
        'name' => 'required',
        'slug' => 'required'
    ];

    public $attachOne = [
        'logo' => \System\Models\File::class
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function getLinkAttribute(){
        
        $url = '/shop?vendor=' . $this->id;

        return $url;
    }

}
