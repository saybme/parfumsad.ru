<?php namespace Saybme\Sk\Models;

use Model;

class ProductOptionValue extends Model
{
    use \October\Rain\Database\Traits\Purgeable;   // ← Добавьте эту строку

    public $table = 'saybme_sk_product_option_values';

    public $belongsTo = [
        'product' => [Product::class, 'key' => 'product_id'],
        'option'  => [Option::class, 'key' => 'option_id']
    ];

    public $fillable = ['product_id', 'option_id', 'value', 'value_extra'];

    // === PURGEABLE ===
    protected $purgeable = ['value_text', 'value_multiple'];   // ← Вот это главное!

    // ... остальной код ...

    public function getOptionLabelAttribute()
    {
        return $this->option ? $this->option->label : '—';
    }

    public function filterFields($fields, $context = null)
    {
        if (!$this->option) {
            $fields->value_text->hidden = true;
            $fields->value_multiple->hidden = true;
            return;
        }

        $type = $this->option->type;

        if ($type === 'text') {
            $fields->value_text->hidden = false;
            $fields->value_multiple->hidden = true;
            $fields->value_text->required = true;
        }
        else if ($type === 'checkbox') {
            $fields->value_text->hidden = true;
            $fields->value_multiple->hidden = false;
            $fields->value_multiple->required = true;
            $fields->value_multiple->type = 'dropdown';   // или taglist

            if ($this->option->variants) {
                $fields->value_multiple->options = $this->option->variants()->lists('label', 'id');
            }
        }
    }

    // ====================== СОХРАНЕНИЕ ======================
    public function beforeSave()
    {
        $type = $this->option?->type ?? null;

        if ($type === 'text') {
            $this->value = $this->value_text;
        }
        else if ($type === 'checkbox') {
            $this->value = $this->value_multiple;
            // Если используете taglist/checkboxlist и нужно несколько значений:
            // $this->value = is_array($this->value_multiple) ? json_encode($this->value_multiple) : $this->value_multiple;
        }
    }

    public function afterFetch()
    {
        $type = $this->option?->type ?? null;

        if ($type === 'text') {
            $this->value_text = $this->value;
        }
        else if ($type === 'checkbox') {
            $this->value_multiple = $this->value;
        }
    }
}
