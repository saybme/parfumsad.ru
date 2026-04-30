<?php namespace Saybme\Sk\Models;

use Model;
use Str;

class OptionVariant extends Model
{
    use \October\Rain\Database\Traits\Sortable;
    use \October\Rain\Database\Traits\Validation;

    public $table = 'saybme_sk_option_variants';

    public $belongsTo = [
        'option' => [Option::class]
    ];

    public $fillable = ['option_id', 'value', 'label', 'sort_order'];

    public $rules = [
        'label' => 'required'
    ];

    public function beforeSave()
    {
        if (empty($this->value) && !empty($this->label)) {
            $slug = Str::slug($this->label, '_');

            // Добавляем префикс от опции для уникальности
            if ($this->option && $this->option->name) {
                $slug = $this->option->name . '_' . $slug;
            }

            $this->value = $slug;
        }
    }
}
