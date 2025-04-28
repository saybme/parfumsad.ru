<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkPages extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_pages', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('title')->nullable();
            $table->text('introtext')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('slug')->nullable();
            $table->string('uri')->nullable();
            $table->text('seo')->nullable();
            $table->text('props')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_pages');
    }
}
