<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOffers5 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_offers', function($table)
        {
            $table->integer('option_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_offers', function($table)
        {
            $table->dropColumn('option_id');
        });
    }
}
