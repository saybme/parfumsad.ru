<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOptions2 extends Migration
{
    public function up()
    {
        Schema::table('saybme_sk_options', function($table)
        {
            $table->string('label');
            $table->boolean('is_filterable')->default(true);
            $table->integer('sort_order')->nullable();
            $table->string('name', 255)->nullable(false)->unique()->change();
            $table->string('type')->nullable(false)->default('dropdown')->change();
            $table->dropColumn('description');
            $table->dropColumn('is_active');
            $table->dropColumn('props');
            $table->dropColumn('code');
            $table->dropColumn('measure');
        });
    }
    
    public function down()
    {
        Schema::table('saybme_sk_options', function($table)
        {
            $table->dropColumn('label');
            $table->dropColumn('is_filterable');
            $table->dropColumn('sort_order');
            $table->string('name', 255)->nullable()->change();
            $table->string('type', 255)->nullable()->change();
            $table->text('description')->nullable();
            $table->boolean('is_active')->nullable();
            $table->text('props')->nullable();
            $table->string('code', 255)->nullable();
            $table->string('measure', 255)->nullable();
        });
    }
}
