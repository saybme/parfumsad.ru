<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkPosts extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_posts', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_active')->nullable();
            $table->text('props')->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('slug')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_posts');
    }
}
