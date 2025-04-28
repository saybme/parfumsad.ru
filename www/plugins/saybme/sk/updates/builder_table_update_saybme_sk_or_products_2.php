<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrProducts2 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_or_products', function($table)
        {
            $table->string('name')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_or_products', function($table)
        {
            $table->dropColumn('name');
        });
    }
}
