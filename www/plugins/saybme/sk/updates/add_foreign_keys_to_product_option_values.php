<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddForeignKeysToProductOptionValues extends Migration
{
    public function up()
    {
        $this->addForeignKeyForProduct();
        $this->addForeignKeyForOption();
    }

    protected function addForeignKeyForProduct()
    {
        if (!Schema::hasTable('saybme_sk_product_option_values') ||
            !Schema::hasTable('saybme_sk_products')) {
            return;
        }

        $foreignKeys = $this->getForeignKeys('saybme_sk_product_option_values');

        if (!in_array('saybme_sk_product_option_values_product_id_foreign', $foreignKeys)) {
            try {
                Schema::table('saybme_sk_product_option_values', function($table) {
                    $table->foreign('product_id')
                          ->references('id')
                          ->on('saybme_sk_products')
                          ->onDelete('cascade');
                });
                echo "Added foreign key for product_id\n";
            } catch (\Exception $e) {
                echo "Error adding product foreign key: " . $e->getMessage() . "\n";
            }
        }
    }

    protected function addForeignKeyForOption()
    {
        if (!Schema::hasTable('saybme_sk_product_option_values') ||
            !Schema::hasTable('saybme_sk_options')) {
            return;
        }

        $foreignKeys = $this->getForeignKeys('saybme_sk_product_option_values');

        if (!in_array('saybme_sk_product_option_values_option_id_foreign', $foreignKeys)) {
            try {
                Schema::table('saybme_sk_product_option_values', function($table) {
                    $table->foreign('option_id')
                          ->references('id')
                          ->on('saybme_sk_options')
                          ->onDelete('cascade');
                });
                echo "Added foreign key for option_id\n";
            } catch (\Exception $e) {
                echo "Error adding option foreign key: " . $e->getMessage() . "\n";
            }
        }
    }

    protected function getForeignKeys($table)
    {
        try {
            $result = \DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);

            return array_map(function($item) {
                return $item->CONSTRAINT_NAME;
            }, $result);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function down()
    {
        if (Schema::hasTable('saybme_sk_product_option_values')) {
            Schema::table('saybme_sk_product_option_values', function($table) {
                try {
                    $table->dropForeign(['product_id']);
                } catch (\Exception $e) {}

                try {
                    $table->dropForeign(['option_id']);
                } catch (\Exception $e) {}
            });
        }
    }
}
