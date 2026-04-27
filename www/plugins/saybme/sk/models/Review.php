<?php namespace Saybme\Sk\Models;

use Model;
use Saybme\Sk\Models\Product;
use Log;

/**
 * Model
 */
class Review extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var array dates to cast from the database.
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'title',
        'content',
        'pros',
        'cons',
        'status',
        'is_verified_purchase',
        'admin_response',
        'admin_responded_at',
        'helpful_count',
        'unhelpful_count'
    ];

    protected $casts = [
        'is_verified_purchase' => 'boolean',
        'admin_responded_at' => 'datetime'
    ];

    // Константы для статусов
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Доступные статусы
    public static $statuses = [
        self::STATUS_PENDING => 'На модерации',
        self::STATUS_APPROVED => 'Одобрено',
        self::STATUS_REJECTED => 'Отклонено'
    ];

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'saybme_sk_reviews';

    /**
     * @var array rules for validation.
     */
    public $rules = [
        'product_id' => 'required|exists:saybme_sk_products,id',
        'rating' => 'required|integer|min:1|max:5',
        'content' => 'required|string|min:3',
        'status' => 'required|in:pending,approved,rejected',
        'user_id' => 'required'
    ];

    // Кастомные сообщения валидации
    public $customMessages = [
        'rating.required' => 'Пожалуйста, выберите рейтинг отзыва.',
        'rating.min' => 'Рейтинг должен быть от 1 до 5.',
        'content.required' => 'Пожалуйста, напишите содержание отзыва.'
    ];

    public $belongsTo = [
        'user' => ['Saybme\Sk\Models\User'],
        'product' => [
            'Saybme\Sk\Models\Product',
            'key' => 'product_id'
        ]
    ];


    // scopeActive для получения только одобренных отзывов
    public function scopeActive($query){
        return $query->where('status', self::STATUS_APPROVED);
    }

    // getRatingOptions для генерации списка рейтингов
    public function getRatingOptions(){
        return [
            1 => '1 звезда',
            2 => '2 звезды',
            3 => '3 звезды',
            4 => '4 звезды',
            5 => '5 звезд'
        ];
    }

    // getStatusOptions для генерации списка статусов
    public function getStatusOptions(){
        return self::$statuses;
    }

    // Scope для одобренных отзывов
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // Scope для отзывов на модерации
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Аксессор для получения текста статуса
    public function getStatusTextAttribute()
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    // Цвет статуса в зависимости от статуса
    public function getStatusColorAttribute(){
        // Код цвета под статусом
        return match ($this->status) {
            self::STATUS_APPROVED => '#28a745',
            self::STATUS_REJECTED => '#dc3545',
            default => '#ffc107'
        };

    }

    public function afterSave() {
        $product = Product::find($this->product_id);
        $product->avg_rating = $product->reviews()->avg('rating') ?? 0;
        $product->reviews_count = $product->reviews()->count();
        $product->save();
    }

}
