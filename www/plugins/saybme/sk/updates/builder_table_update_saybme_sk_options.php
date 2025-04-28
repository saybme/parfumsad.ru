<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOptions extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_options', function($table)
        {
            $table->string('measure')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_options', function($table)
        {
            $table->dropColumn('measure');
        });
    }
}
