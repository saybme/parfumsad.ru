<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts11 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->decimal('avg_rating', 2, 1)->default(0);
            $table->integer('reviews_count')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->dropColumn('avg_rating');
            $table->dropColumn('reviews_count');
        });
    }
}
