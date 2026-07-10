<?php namespace Saybme\Sk\Components;

use Illuminate\Support\Facades\Log;
use Saybme\Sk\Models\Product;

class Algoliasearch extends \Cms\Classes\ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Algolia поиск',
            'description' => 'Поиск по товарам через Algolia'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title' => 'Количество товаров',
                'description' => 'Количество товаров для вывода',
                'type' => 'string',
                'default' => '20'
            ]
        ];
    }

    public function onRun()
    {
        $this->algoliasearch = $this->onSearch();
    }

    public function onSearch()
    {
        $searchQuery = trim((string) input('q', ''));
        $limit = (int) ($this->property('limit') ?: 20);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $options = [
            'breadcrumbs' => [],
            'products' => $this->emptyPaginator($limit, $searchQuery),
        ];

        if (mb_strlen($searchQuery) < 2) {
            return $this->renderPartial('catalog/search', $options);
        }

        try {
            $products = Product::search($searchQuery)
                ->query(function ($builder) {
                    $builder->active()->with(['vendor', 'preview', 'photos']);
                })
                ->paginate($limit)
                ->appends(['q' => $searchQuery]);

            $options['products'] = $products;

            return $this->renderPartial('catalog/search', $options);
        } catch (\Throwable $e) {
            Log::error('Algoliasearch: search failed', [
                'message' => $e->getMessage(),
                'query' => $searchQuery,
                'page' => input('page'),
                'trace' => mb_substr($e->getTraceAsString(), 0, 2000),
            ]);

            $options['error'] = 'Ошибка поиска. Попробуйте позже.';
            return $this->renderPartial('catalog/search', $options);
        }
    }

    private function emptyPaginator(int $limit, string $searchQuery)
    {
        return Product::whereRaw('1=0')
            ->paginate($limit)
            ->appends(['q' => $searchQuery]);
    }

    public $algoliasearch;
}