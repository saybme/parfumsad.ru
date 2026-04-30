<?php namespace Saybme\Sk\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddForeignKeysToOptionVariants extends Migration
{
    public function up()
    {
        // Добавляем внешний ключ для option_variants
        if (Schema::hasTable('saybme_sk_option_variants') &&
            Schema::hasTable('saybme_sk_options')) {

            // Проверяем, существует ли уже внешний ключ
            $foreignKeys = $this->getForeignKeys('saybme_sk_option_variants');

            if (!in_array('saybme_sk_option_variants_option_id_foreign', $foreignKeys)) {
                Schema::table('saybme_sk_option_variants', function($table) {
                    $table->foreign('option_id')
                          ->references('id')
                          ->on('saybme_sk_options')
                          ->onDelete('cascade');
                });

                echo "Added foreign key to saybme_sk_option_variants\n";
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('saybme_sk_option_variants')) {
            Schema::table('saybme_sk_option_variants', function($table) {
                $table->dropForeign(['option_id']);
            });
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
}
