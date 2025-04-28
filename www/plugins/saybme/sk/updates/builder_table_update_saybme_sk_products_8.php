<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts8 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->integer('price_usd')->nullable();
            $table->integer('price_eur')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->dropColumn('price_usd');
            $table->dropColumn('price_eur');
        });
    }
}
