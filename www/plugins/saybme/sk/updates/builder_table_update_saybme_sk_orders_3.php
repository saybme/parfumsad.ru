<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrders3 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->text('address')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->dropColumn('address');
        });
    }
}
