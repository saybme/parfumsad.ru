<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts10 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->decimal('price_usd', 12, 2)->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->decimal('price_eur', 12, 2)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->integer('price_usd')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->integer('price_eur')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
