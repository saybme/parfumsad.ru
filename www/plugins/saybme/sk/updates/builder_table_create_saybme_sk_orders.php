<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkOrders extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_orders', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('num')->nullable();
            $table->text('comment')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('user_id')->nullable();
            $table->text('props')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_orders');
    }
}
