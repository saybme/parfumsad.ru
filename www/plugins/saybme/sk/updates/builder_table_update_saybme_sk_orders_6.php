<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrders6 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->integer('type')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->dropColumn('type');
        });
    }
}
