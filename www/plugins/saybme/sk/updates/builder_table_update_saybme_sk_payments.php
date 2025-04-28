<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkPayments extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_payments', function($table)
        {
            $table->integer('sort_order')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_payments', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
}
