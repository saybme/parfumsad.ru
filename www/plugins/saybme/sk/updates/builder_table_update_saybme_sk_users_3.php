<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkUsers3 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_users', function($table)
        {
            $table->string('inn', 255)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_users', function($table)
        {
            $table->integer('inn')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
