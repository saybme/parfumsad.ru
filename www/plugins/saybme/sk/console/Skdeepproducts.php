<?php

namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Markdown;                    // ← Встроенный парсер October CMS
use Saybme\Sk\Models\Product;    // ← Замени на правильный путь к твоей модели

/**
 * Генерация SEO-описаний товаров через DeepSeek API
 */
class Skdeepproducts extends Command
{
    protected $signature = 'sk:skdeepproducts {limit?}';

    protected $description = 'Генерация SEO-описаний для товаров через DeepSeek API';

    public function handle()
    {
        $limit = $this->argument('limit') ?? 10;

        $this->info("Запуск генерации SEO-описаний...");

        $this->processProducts();
    }

    private function processProducts()
    {
        $products = $this->getProductsWithoutContent();

        if ($products->isEmpty()) {
            $this->warn('✅ Нет товаров без описания.');
            return;
        }

        $this->info("Найдено товаров: {$products->count()}");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $bar->setMessage(Str::limit($product->name, 40));

            if (!$product->category) {
                Log::info("Товар ID {$product->id} пропущен — нет категории");
                $bar->advance();
                continue;
            }

            $prompt = $this->buildPrompt($product, $product->category->name);
            $description = $this->generateTextWithDeepSeek($prompt);

            if ($description && strlen(strip_tags($description)) > 150) {
                try {
                    DB::transaction(function () use ($product, $description) {
                        $product->content = $description;
                        $product->save();
                    });

                    Log::info("Описание сгенерировано", [
                        'product_id' => $product->id,
                        'name'       => $product->name,
                        'length'     => strlen($description)
                    ]);

                    $this->newLine();
                    $this->info("✅ #{$product->id} — {$product->name}");
                } catch (\Exception $e) {
                    Log::error("Ошибка сохранения товара #{$product->id}", ['error' => $e->getMessage()]);
                    $this->error("❌ Ошибка сохранения #{$product->id}");
                }
            } else {
                Log::warning("Не удалось получить описание", ['product_id' => $product->id]);
                $this->error("❌ Ошибка генерации для #{$product->id}");
            }

            $bar->advance();
            usleep(1200000); // 1.2 секунды
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('🎉 Генерация завершена!');
    }

    private function getProductsWithoutContent()
    {
        return Product::where(function ($q) {
                $q->whereNull('content')
                  ->orWhere('content', '')
                  ->orWhere('content', 'LIKE', '%<p></p>%');
            })
            ->with('category')
            ->limit($this->argument('limit') ?? 10)
            ->get();
    }

    private function buildPrompt($product, $categoryName)
    {
        $prompt = "Составь продающее SEO-описание для интернет-магазина парфюмерии PARFUMSAD.\n\n";
        $prompt .= "Название товара: {$product->name}\n";
        $prompt .= "Категория: {$categoryName}\n";

        if (!empty($product->props)) {
            $props = is_string($product->props) ? json_decode($product->props, true) : $product->props;
            if (is_array($props) && !empty($props)) {
                $prompt .= "Характеристики:\n";
                foreach ($props as $key => $value) {
                    if (is_string($key) && is_scalar($value)) {
                        $prompt .= "- {$key}: {$value}\n";
                    }
                }
            }
        }

        $prompt .= "\nТребования:\n";
        $prompt .= "- Длина: 600–950 символов\n";
        $prompt .= "- Стиль: продающий, естественный, экспертный\n";
        $prompt .= "- Обязательно используй слова: купить, парфюм, аромат, оригинальный\n";
        $prompt .= "- Упомяни магазин PARFUMSAD\n";
        $prompt .= "- Используй Markdown: **жирный**, *курсив*, - списки\n";
        $prompt .= "- Не пиши вступления типа «Вот описание», «SEO-описание» и т.п.";

        return $prompt;
    }

    private function generateTextWithDeepSeek($prompt, $attempt = 1)
    {
        $apiKey = env('DEEPSEEK_API_KEY');

        if (empty($apiKey)) {
            $this->error('DEEPSEEK_API_KEY не найден в .env');
            return false;
        }

        $url = "https://api.deepseek.com/v1/chat/completions";

        $systemPrompt = "Ты — профессиональный SEO-копирайтер магазина PARFUMSAD. "
            . "Пиши только чистый Markdown без вступлений.";

        $data = [
            "model" => "deepseek-chat",
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user",   "content" => $prompt]
            ],
            "temperature" => 0.75,
            "max_tokens"  => 1200,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey
            ],
            CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 40,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('cURL ошибка', ['error' => $curlError]);
            return false;
        }

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $markdownText = $result['choices'][0]['message']['content'] ?? '';

            return $this->markdownToHtml($markdownText);
        }

        // Retry при временных ошибках
        if ($attempt < 3 && in_array($httpCode, [429, 500, 502, 503, 504])) {
            sleep($attempt * 2);
            return $this->generateTextWithDeepSeek($prompt, $attempt + 1);
        }

        Log::error('DeepSeek API error', ['code' => $httpCode]);
        return false;
    }

    /**
     * Конвертация Markdown в HTML с помощью встроенного парсера October CMS
     */
    private function markdownToHtml(string $markdown): string
    {
        if (empty(trim($markdown))) {
            return '';
        }

        // Удаляем нежелательные вступления
        $markdown = preg_replace('/^(Вот|SEO-описание|Описание для|Предлагаю).*?[:\n]/imu', '', $markdown);
        $markdown = trim($markdown);

        // Используем встроенный парсер October CMS
        $html = Markdown::parse($markdown);

        // Дополнительная очистка
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        return trim($html);
    }
}