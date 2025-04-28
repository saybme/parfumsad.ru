<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkDeliveries extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_deliveries', function($table)
        {
            $table->integer('sort_order')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_deliveries', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
}
