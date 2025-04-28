<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkPages extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_pages', function($table)
        {
            $table->string('tpl')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_pages', function($table)
        {
            $table->dropColumn('tpl');
        });
    }
}
