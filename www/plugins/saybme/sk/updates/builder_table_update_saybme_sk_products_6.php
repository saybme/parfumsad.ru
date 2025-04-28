<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts6 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->string('article')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->dropColumn('article');
        });
    }
}
