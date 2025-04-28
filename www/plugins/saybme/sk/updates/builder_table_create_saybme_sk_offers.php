<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkOffers extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_offers', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('price')->nullable();
            $table->integer('old_price')->nullable();
            $table->boolean('is_active')->nullable();
            $table->integer('sort_order')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_offers');
    }
}
