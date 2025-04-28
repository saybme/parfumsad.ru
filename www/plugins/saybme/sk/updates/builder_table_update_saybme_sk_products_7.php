<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts7 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->string('uri')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->dropColumn('uri');
        });
    }
}
