<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        // Проверяем, существует ли уже таблица
        if (Schema::hasTable('saybme_sk_reviews')) {
            return;
        }
        
        Schema::create('saybme_sk_reviews', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            
            // Кто оставил отзыв
            $table->integer('user_id')->unsigned()->index();
            
            // На какой товар/услугу отзыв
            $table->integer('product_id')->unsigned()->nullable()->index();
            
            // Рейтинг (от 1 до 5)
            $table->smallInteger('rating')->unsigned()->default(5);
            
            // Заголовок и текст отзыва
            $table->string('title')->nullable();
            $table->text('content');
            
            // Преимущества и недостатки
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();
            
            // СТАТУС - ИЗМЕНЕНО НА STRING (вместо enum)
            $table->string('status')->default('pending');
            
            // Подтверждение покупки
            $table->boolean('is_verified_purchase')->default(false);
            
            // Ответ администратора
            $table->text('admin_response')->nullable();
            $table->timestamp('admin_responded_at')->nullable();
            
            // Голосования
            $table->integer('helpful_count')->unsigned()->default(0);
            $table->integer('unhelpful_count')->unsigned()->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы
            $table->index(['product_id', 'status']);
            $table->index('rating');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('saybme_sk_reviews');
    }
}