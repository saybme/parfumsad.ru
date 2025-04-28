<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrders5 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->text('profile')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_orders', function($table)
        {
            $table->dropColumn('profile');
        });
    }
}
