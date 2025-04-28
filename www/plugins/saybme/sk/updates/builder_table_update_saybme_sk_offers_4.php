<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOffers4 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_offers', function($table)
        {
            $table->string('value')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_offers', function($table)
        {
            $table->dropColumn('value');
        });
    }
}
