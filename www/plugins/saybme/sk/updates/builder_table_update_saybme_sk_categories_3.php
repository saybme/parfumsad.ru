<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkCategories3 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_categories', function($table)
        {
            $table->renameColumn('link', 'uri');
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_categories', function($table)
        {
            $table->renameColumn('uri', 'link');
        });
    }
}
