<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrders extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->integer('payment_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->dropColumn('payment_id');
        });
    }
}
