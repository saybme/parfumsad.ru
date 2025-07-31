<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSaybmeSkUsers extends Migration
{
    public function up()
    {
        Schema::create('saybme_sk_users', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('type')->nullable();
            $table->integer('inn')->nullable();
            $table->text('profile')->nullable();
            $table->text('props')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('saybme_sk_users');
    }
}
