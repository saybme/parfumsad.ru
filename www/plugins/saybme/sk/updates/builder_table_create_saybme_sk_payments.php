<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkPayments extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_payments', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->nullable();
            $table->text('props')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_payments');
    }
}
