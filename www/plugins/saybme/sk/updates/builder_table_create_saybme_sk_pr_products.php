<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkPrProducts extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_pr_products', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('product_id')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('price')->nullable();
            $table->integer('order_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_pr_products');
    }
}
