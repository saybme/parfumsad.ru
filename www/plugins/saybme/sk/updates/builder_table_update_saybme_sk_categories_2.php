<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkCategories2 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_categories', function($table)
        {
            $table->string('link')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_categories', function($table)
        {
            $table->dropColumn('link');
        });
    }
}
