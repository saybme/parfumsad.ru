<?php namespace Saybme\Sk\Components;

use Db;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Log;

class SphinxSearch extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Sphinx поиск',
            'description' => 'Поиск по товарам через Sphinx'
        ];
    }


    public function onRun()
    {
        $this->sphinxsearch = $this->onSearch();
    }

    public function onSearch()
    {
        $query = input('q');

        if (!is_string($query) || mb_strlen($query) < 2) {
            return $this->renderPartial('sphinxsearch/default', ['products' => []]);
        }

        

        try {
            // Подключаемся к Sphinx через порт 9306
            $sphinx = new \PDO('mysql:host=127.0.0.1;port=9306;charset=utf8', '', '');               
            
            // Запрос к Sphinx
            $stmt = $sphinx->prepare("
                SELECT id, WEIGHT() as weight 
                FROM october_index 
                WHERE MATCH(:query) 
                ORDER BY weight DESC 
                LIMIT 50
            ");
            $stmt->execute(['query' => $query]);
            $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);          

            
            
            if (empty($ids)) {
                Log::info('SphinxSearch: no ids found');
                return $this->renderPartial('sphinxsearch/default', ['products' => []]);
            }
            
            // Получаем товары из основной БД
            $products = Db::table('saybme_sk_products')
                ->whereIn('id', $ids)
                ->orderByRaw(Db::raw("FIELD(id, " . implode(',', $ids) . ")"))
                ->get();

            Log::info('SphinxSearch: products loaded', [
                'products_count' => count($products),
            ], JSON_PRETTY_PRINT);

            //dd($products);

            return $this->renderPartial('sphinxsearch/default', ['products' => $products]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public $sphinxsearch;

}