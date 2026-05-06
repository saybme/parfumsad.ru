<?php namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Saybme\Sk\Controllers\ProductController;
use Log;

/**
 * Skdeepproducts Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class Skdeepproducts extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'sk:skdeepproducts {user}';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Генерация SEO-описаний для товаров через DeepSeek API';

    /**
     * handle executes the console command.
     */
    public function handle()
    {        
        $username = $this->argument('user');
        $this->info("Привет, $username! Начинаю генерацию описаний...");
        
        $this->content();
    }   
    
    public function content()
    {
        $q = new ProductController;
        $items = $q->content();

        if ($items->isEmpty()) {
            $this->warn('Нет товаров без описания (content)');
            return;
        }

        $this->info("Найдено товаров: " . $items->count());
        
        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $key => $item) {
            // Если категория не указана, то пропускаем 
            if (!$item->category) {
                Log::info('Товар ID ' . $item->id . ' (' . $item->name . ') пропущен - нет категории');
                $bar->advance();
                continue;
            }

            // Получаем название категории
            $categoryName = $item->category->name;
            
            // Формируем промпт
            $prompt = $this->buildPrompt($item, $categoryName);
            
            // Отправляем запрос к DeepSeek
            $description = $this->generateTextWithDeepSeek($prompt);
            
            // Проверяем, что описание получено и не содержит ошибку
            if ($description && $description !== false && strlen($description) > 50) {
                // Сохраняем описание в базу
                $item->content = $description;
                $item->save();

                Log::info('Товар ID ' . $item->id . ' (' . $item->name . ') - описание сгенерировано, длина: ' . strlen($description) . ' символов');
                
                $this->newLine();
                $this->info("\n✅ Товар #{$item->id} ({$item->name}) - описание сгенерировано");
            } else {
                $errorMsg = ($description === false) ? 'Ошибка API' : 'Пустое описание';
                Log::error('Товар ID ' . $item->id . ' (' . $item->name . ') - ' . $errorMsg);
                $this->error("\n❌ Ошибка при генерации для товара #{$item->id}: " . $errorMsg);
            }
            
            $bar->advance();
            
            // Небольшая задержка, чтобы не превысить лимиты API
            usleep(500000); // 0.5 секунды
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Генерация завершена!');
    }

    /**
     * Формирование промпта для DeepSeek
     */
    private function buildPrompt($product, $categoryName)
    {
        $prompt = "Составь SEO-описание для интернет-магазина парфюмерии.\n\n";
        $prompt .= "Название товара: {$product->name}\n";
        $prompt .= "Категория: {$categoryName}\n";
        
        // Добавляем свойства товара, если есть
        if (!empty($product->props)) {
            $props = is_string($product->props) ? json_decode($product->props, true) : $product->props;
            if ($props && is_array($props)) {
                $prompt .= "Характеристики:\n";
                foreach ($props as $key => $value) {
                    if (is_string($key) && is_string($value)) {
                        $prompt .= "- {$key}: {$value}\n";
                    }
                }
            }
        }
        
        $prompt .= "\nТребования к описанию:\n";
        $prompt .= "- Длина: 500-1000 символов\n";
        $prompt .= "- Включи ключевые слова: 'купить', 'парфюм', 'аромат'\n";
        $prompt .= "- Стиль: продающий, но естественный\n";
        $prompt .= "- Укажи основные ноты аромата (если есть)\n";
        $prompt .= "- Добавь призыв к действию\n";
        $prompt .= "- Для форматирования используй: **жирный текст**, *курсив*, - для списков\n";
        
        return $prompt;
    }

    /**
     * Конвертирует Markdown разметку в HTML
     */
    private function markdownToHtml($content)
    {
        // Удаляем лишние фразы в начале
        $content = preg_replace('/^(Вот\s+)?(SEO-)?описание.*?(для товара.*?)?\n/im', '', $content);
        $content = preg_replace('/^\*{3}.*?\*{3}\n/im', '', $content);
        
        // Жирный текст: **text** -> <strong>text</strong>
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        
        // Курсив: *text* -> <em>text</em> (но не путать со списками)
        $content = preg_replace('/(?<!\n)\*(?!\s)(.*?)(?<!\s)\*(?!\s)/', '<em>$1</em>', $content);
        
        // Заголовки: *** text *** -> <h3>text</h3>
        $content = preg_replace('/\*\*\*(.*?)\*\*\*/', '<h3>$1</h3>', $content);
        
        // Обработка списков
        $lines = explode("\n", $content);
        $inList = false;
        $newLines = [];
        
        foreach ($lines as $line) {
            $line = rtrim($line);
            
            // Проверяем, является ли строка элементом списка (начинается с - или *)
            if (preg_match('/^[\-\*]\s+(.*)/', $line, $matches)) {
                if (!$inList) {
                    $newLines[] = '<ul>';
                    $inList = true;
                }
                $newLines[] = '<li>' . trim($matches[1]) . '</li>';
            } else {
                // Если были в списке, закрываем его
                if ($inList) {
                    $newLines[] = '</ul>';
                    $inList = false;
                }
                
                // Обработка пустых строк
                if (trim($line) === '') {
                    // Пропускаем пустые строки
                    continue;
                }
                
                // Если строка не начинается с HTML тега, оборачиваем в <p>
                if (strpos($line, '<') !== 0) {
                    $line = '<p>' . $line . '</p>';
                }
                $newLines[] = $line;
            }
        }
        
        // Закрываем список, если остался открытым
        if ($inList) {
            $newLines[] = '</ul>';
        }
        
        $content = implode("\n", $newLines);
        
        // Удаляем пустые теги
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        
        // Удаляем лишние переводы строк
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Очищаем от Markdown символов, которые могли остаться
        $content = str_replace(['**', '*', '__'], '', $content);
        
        return trim($content);
    }

    /**
     * Запрос к DeepSeek API
     */
    private function generateTextWithDeepSeek($prompt)
    {
        $apiKey = env('DEEPSEEK_API_KEY');
        
        if (empty($apiKey)) {
            $this->error('API ключ DEEPSEEK_API_KEY не найден в .env файле');
            return false;
        }
        
        $url = "https://api.deepseek.com/v1/chat/completions";
        
        // Инструкция для системы
        $systemPrompt = "Ты профессиональный SEO-копирайтер для интернет-магазина парфюмерии.
        Пиши продающие, уникальные описания товаров.
        Используй форматирование: **жирный текст**, *курсив*, - для списков.
        Не используй HTML теги в ответе, только Markdown разметку.
        Не добавляй пояснений типа 'Вот описание' - просто пиши текст.";

        $data = [
            "model" => "deepseek-chat",
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.7,
            "max_tokens" => 1000,
            "top_p" => 0.9,
            "frequency_penalty" => 0,
            "presence_penalty" => 0
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $this->error("cURL ошибка: " . $curlError);
            Log::error('cURL ошибка DeepSeek: ' . $curlError);
            return false;
        }
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            if (isset($result['choices'][0]['message']['content'])) {
                $content = $result['choices'][0]['message']['content'];
                
                // Конвертируем Markdown в HTML
                $content = $this->markdownToHtml($content);
                
                // Дополнительная очистка от лишних фраз
                $content = preg_replace('/^(Вот|Вам|Предлагаю|Ниже|SEO-описание|Описание[:\s]*)/iu', '', $content);
                $content = trim($content);
                
                // Если контент пустой или слишком короткий - возвращаем false
                if (strlen($content) < 50) {
                    Log::warning('Слишком короткое описание от API', ['content' => $content]);
                    return false;
                }
                
                return $content;
            } else {
                Log::error('Неверный формат ответа DeepSeek', ['response' => $response]);
                return false;
            }
        } else {
            $errorMsg = "HTTP {$httpCode}: " . substr($response, 0, 200);
            $this->error("API ошибка: " . $errorMsg);
            Log::error('DeepSeek API ошибка', ['http_code' => $httpCode, 'response' => $response]);
            return false;
        }
    }
}