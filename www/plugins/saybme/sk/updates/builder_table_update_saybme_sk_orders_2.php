<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrders2 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->integer('delivery_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->dropColumn('delivery_id');
        });
    }
}
