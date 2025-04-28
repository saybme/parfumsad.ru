<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkVendor extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_vendor', function($table)
        {
            $table->integer('uid')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_vendor', function($table)
        {
            $table->dropColumn('uid');
        });
    }
}
