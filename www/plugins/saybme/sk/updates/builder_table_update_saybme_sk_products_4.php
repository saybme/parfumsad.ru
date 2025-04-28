<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkProducts4 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->boolean('is_new')->nullable();
            $table->boolean('is_popular')->nullable();
            $table->boolean('is_recomended')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_products', function($table)
        {
            $table->dropColumn('is_new');
            $table->dropColumn('is_popular');
            $table->dropColumn('is_recomended');
        });
    }
}
