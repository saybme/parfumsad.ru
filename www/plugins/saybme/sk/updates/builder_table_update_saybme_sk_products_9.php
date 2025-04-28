<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts9 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->decimal('price', 12, 2)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->integer('price')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
