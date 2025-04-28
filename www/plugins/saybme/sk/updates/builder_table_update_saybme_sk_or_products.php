<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSaybmeSkOrProducts extends Migration
{
    public function up()
    {
        Schema::rename('saybme_sk_pr_products', 'saybme_sk_or_products');
    }
    
    public function down()
    {
        Schema::rename('saybme_sk_or_products', 'saybme_sk_pr_products');
    }
}
