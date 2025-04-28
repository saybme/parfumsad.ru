<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkCategoryProduct extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_category_product', function($table)
        {
            $table->integer('category_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->primary(['category_id','product_id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_category_product');
    }
}
