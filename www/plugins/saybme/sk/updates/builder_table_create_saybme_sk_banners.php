<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkBanners extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_banners', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->nullable();
            $table->text('props')->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('link')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_banners');
    }
}
