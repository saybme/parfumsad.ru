<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkPageParams extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_page_params', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('parent_id')->nullable()->unsigned();
            $table->mediumText('value')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_page_params');
    }
}
