<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Создаёт таблицы опций, если v1.0.57 не отработала (неверный namespace в create_all_tables.php).
 */
class CreateProductOptionValuesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('saybme_sk_option_variants')) {
            Schema::create('saybme_sk_option_variants', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('option_id')->unsigned()->index();
                $table->string('value');
                $table->string('label');
                $table->integer('sort_order')->nullable();
                $table->timestamps();

                if (Schema::hasTable('saybme_sk_options')) {
                    $table->foreign('option_id')
                        ->references('id')
                        ->on('saybme_sk_options')
                        ->onDelete('cascade');
                }
            });
        }

        if (!Schema::hasTable('saybme_sk_product_option_values')) {
            Schema::create('saybme_sk_product_option_values', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id')->unsigned()->index();
                $table->integer('option_id')->unsigned()->index();
                $table->string('value')->index();
                $table->string('value_extra')->nullable();
                $table->timestamps();

                if (Schema::hasTable('saybme_sk_products')) {
                    $table->foreign('product_id')
                        ->references('id')
                        ->on('saybme_sk_products')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('saybme_sk_options')) {
                    $table->foreign('option_id')
                        ->references('id')
                        ->on('saybme_sk_options')
                        ->onDelete('cascade');
                }

                $table->unique(['product_id', 'option_id', 'value'], 'product_option_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('saybme_sk_product_option_values');
    }
}
