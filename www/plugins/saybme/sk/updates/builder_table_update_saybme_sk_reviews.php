<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkReviews extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_reviews', function($table)
        {
            $table->text('props')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_reviews', function($table)
        {
            $table->dropColumn('props');
        });
    }
}
