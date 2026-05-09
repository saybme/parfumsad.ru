<?php namespace Saybme\Sk\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Log;
use Saybme\Sk\Models\Product;

class SphinxSearch extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Sphinx поиск',
            'description' => 'Поиск по товарам через Sphinx'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title' => 'Количество товаров',
                'description' => 'Количество товаров для вывода',
                'type' => 'string',
                'default' => '50',
            ]
        ];
    }


    public function onRun()
    {
        $this->sphinxsearch = $this->onSearch();
    }

    public function onSearch()
    {
        $query = input('q');

        $limit = (int) ($this->property('limit') ?: 50);
        if ($limit < 1) $limit = 1;
        if ($limit > 200) $limit = 200;

        // Всегда передаём в partial пагинатор, т.к. `catalog/ax-products` вызывает `pager(products)`
        $emptyProducts = Product::whereRaw('1=0')->paginate($limit);

        if (!is_string($query) || mb_strlen($query) < 2) {
            return $this->renderPartial('catalog/search-result', ['products' => $emptyProducts]);
        }

        try {
            // Подключаемся к Sphinx через порт 9306
            $sphinx = new \PDO(
                'mysql:host=127.0.0.1;port=9306;charset=utf8',
                '',
                '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
            
            // Запрос к Sphinx
            // LIMIT не биндим параметром: приводим к int и подставляем безопасно
            $stmt = $sphinx->prepare("
                SELECT id, WEIGHT() as weight 
                FROM october_index 
                WHERE MATCH(:query) 
                ORDER BY weight DESC 
                LIMIT {$limit}
            ");
            $stmt->execute(['query' => $query]);
            $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);          

            if (empty($ids)) {
                Log::info('SphinxSearch: no ids found');
                return $this->renderPartial('catalog/search', ['products' => $emptyProducts]);
            }

            // Важно: используем модель Product (аксессоры/relations для цены/фото/бренда),
            // и сохраняем порядок из Sphinx через FIELD(id, ...)
            $ids = array_values(array_unique(array_map('intval', (array) $ids)));
            $products = Product::active()
                ->with(['vendor', 'preview', 'photos'])
                ->whereIn('id', $ids)
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                ->paginate($limit);          

            return $this->renderPartial('catalog/search', ['products' => $products]);
        } catch (\Exception $e) {
            Log::error('SphinxSearch: error', [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'query'   => $query,
                'trace'   => mb_substr($e->getTraceAsString(), 0, 2000),
            ]);

            return $this->renderPartial('catalog/search', ['products' => $emptyProducts]);
        }
    }

    public $sphinxsearch;
}