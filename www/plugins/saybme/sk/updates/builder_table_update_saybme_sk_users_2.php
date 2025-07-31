<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkUsers2 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_users', function($table)
        {
            $table->boolean('is_active')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_users', function($table)
        {
            $table->dropColumn('is_active');
        });
    }
}
