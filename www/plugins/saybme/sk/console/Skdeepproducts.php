<?php

namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Markdown;
use Saybme\Sk\Models\Product;

/**
 * Генерация SEO + описаний товаров через DeepSeek API
 */
class Skdeepproducts extends Command
{
    protected $signature = 'sk:skdeepproducts {limit?}';

    protected $description = 'Генерация SEO-полей и описаний для товаров';

    public function handle()
    {
        $limit = (int) ($this->argument('limit') ?? 10);

        $this->info("🚀 Запуск генерации SEO (лимит: {$limit} товаров)...");

        $this->processProducts($limit);
    }

    private function processProducts(int $limit)
    {
        $products = $this->getProductsWithoutContent($limit);

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
                $bar->advance();
                continue;
            }

            $categoryName = $product->category->name;

            $seoTitle       = $this->generateSeoField($product, $categoryName, 'title');
            $seoDescription = $this->generateSeoField($product, $categoryName, 'description');
            $seoKeywords    = $this->generateSeoField($product, $categoryName, 'keywords');
            $content        = $this->generateMainContent($product, $categoryName);

            if ($content && strlen(strip_tags($content)) > 150) {
                try {
                    DB::transaction(function () use ($product, $seoTitle, $seoDescription, $seoKeywords, $content) {
                        $product->content = $content;

                        $props = is_string($product->props) ? json_decode($product->props, true) : ($product->props ?? []);
                        if (!is_array($props)) $props = [];

                        $props['seo_title']       = $seoTitle;
                        $props['seo_description'] = $seoDescription;
                        $props['seo_keywords']    = $seoKeywords;

                        $product->props = $props;
                        $product->save();
                    });

                    $this->newLine();
                    $this->info("✅ #{$product->id} — {$product->name}");
                } catch (\Exception $e) {
                    Log::error("Ошибка сохранения #{$product->id}", ['error' => $e->getMessage()]);
                    $this->error("❌ Ошибка сохранения #{$product->id}");
                }
            } else {
                $this->error("❌ Не удалось сгенерировать контент для #{$product->id}");
            }

            $bar->advance();
            usleep(1500000); // 1.5 сек
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('🎉 Генерация завершена!');
    }

    private function getProductsWithoutContent(int $limit)
    {
        return Product::where(function ($q) {
                $q->whereNull('content')
                  ->orWhere('content', '')
                  ->orWhere('content', 'LIKE', '%<p></p>%');
            })
            ->with('category')
            ->limit($limit)
            ->get();
    }

    private function generateSeoField($product, $categoryName, string $type)
    {
        $prompts = [
            'title' => "Составь только SEO Title (максимум 60 символов) для товара:\nНазвание: {$product->name}\nКатегория: {$categoryName}\nМагазин: PARFUMSAD",
            'description' => "Составь только SEO Description (150-170 символов) для товара:\nНазвание: {$product->name}\nКатегория: {$categoryName}\nМагазин: PARFUMSAD",
            'keywords' => "Составь только SEO Keywords (через запятую, 10-15 слов) для товара:\nНазвание: {$product->name}\nКатегория: {$categoryName}"
        ];

        $text = $this->callDeepSeek($prompts[$type]);
        return trim($text);
    }

    private function generateMainContent($product, $categoryName)
    {
        $prompt = "Составь продающее описание товара для магазина PARFUMSAD.\n\n";
        $prompt .= "Название: {$product->name}\n";
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
        $prompt .= "- Используй Markdown (**жирный**, *курсив*, списки)\n";
        $prompt .= "- Стиль: продающий, естественный\n";
        $prompt .= "- Обязательно упомяни PARFUMSAD\n";
        $prompt .= "- **НИКОГДА** не добавляй в конце SEO Title, SEO Description, SEO Keywords, SEO-теги и любые другие блоки.\n";
        $prompt .= "- Просто текст описания.";

        $text = $this->callDeepSeek($prompt);
        return $this->markdownToHtml($text);
    }

    private function callDeepSeek(string $prompt)
    {
        $apiKey = env('DEEPSEEK_API_KEY');
        if (empty($apiKey)) return '';

        $systemPrompt = "Ты — профессиональный SEO-копирайтер. "
            . "Отвечай ТОЛЬКО запрошенным контентом. "
            . "Не добавляй никаких дополнительных блоков, заголовков, SEO-тегов в конце.";

        $data = [
            "model"       => "deepseek-chat",
            "messages"    => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user",   "content" => $prompt]
            ],
            "temperature" => 0.7,
            "max_tokens"  => 1100,
        ];

        $ch = curl_init("https://api.deepseek.com/v1/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey
            ],
            CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 45,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['choices'][0]['message']['content'] ?? '';
        }

        return '';
    }

    /**
     * Улучшенная очистка
     */
    private function markdownToHtml(string $markdown): string
    {
        $markdown = trim($markdown);
        if (empty($markdown)) return '';

        // Удаляем любые SEO-блоки в конце
        $markdown = preg_replace('/SEO-?теги:.*$/is', '', $markdown);
        $markdown = preg_replace('/SEO Title:.*$/im', '', $markdown);
        $markdown = preg_replace('/SEO Description:.*$/im', '', $markdown);
        $markdown = preg_replace('/SEO Keywords:.*$/im', '', $markdown);
        $markdown = preg_replace('/^\s*[\*\-]\s*seo_.*$/im', '', $markdown);

        // Удаляем вступления
        $markdown = preg_replace('/^(Вот|Я составил|SEO-описание|Описание товара).*?[:\n]/imu', '', $markdown);

        $html = Markdown::parse($markdown);

        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        return trim($html);
    }
}