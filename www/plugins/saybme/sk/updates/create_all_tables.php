<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAllTables extends Migration
{
    public function up()
    {
        // 1. Таблица товаров
        if (!Schema::hasTable('saybme_sk_products')) {
            Schema::create('saybme_sk_products', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->decimal('price', 10, 2)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Таблица опций
        if (!Schema::hasTable('saybme_sk_options')) {
            Schema::create('saybme_sk_options', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('label');
                $table->string('type')->default('dropdown');
                $table->boolean('is_filterable')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();
            });
        }

        // 3. Таблица вариантов значений (если еще не создана)
        if (!Schema::hasTable('saybme_sk_option_variants')) {
            Schema::create('saybme_sk_option_variants', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('option_id')->unsigned()->index();
                $table->string('value');
                $table->string('label');
                $table->integer('sort_order')->nullable();
                $table->timestamps();

                $table->foreign('option_id')
                      ->references('id')
                      ->on('saybme_sk_options')
                      ->onDelete('cascade');
            });
        }

        // 4. Связующая таблица
        if (!Schema::hasTable('saybme_sk_product_option_values')) {
            Schema::create('saybme_sk_product_option_values', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id')->unsigned()->index();
                $table->integer('option_id')->unsigned()->index();
                $table->string('value')->index();
                $table->string('value_extra')->nullable();
                $table->timestamps();

                $table->foreign('product_id')
                      ->references('id')
                      ->on('saybme_sk_products')
                      ->onDelete('cascade');

                $table->foreign('option_id')
                      ->references('id')
                      ->on('saybme_sk_options')
                      ->onDelete('cascade');

                $table->unique(['product_id', 'option_id', 'value'], 'product_option_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('saybme_sk_product_option_values');
        Schema::dropIfExists('saybme_sk_option_variants');
        Schema::dropIfExists('saybme_sk_options');
        Schema::dropIfExists('saybme_sk_products');
    }
}
