<?php namespace Saybme\Sk\Classes\Deepseek;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    private $client;
    private $apiKey;
    private $apiUrl;
    private $maxRetries = 3;
    private $retryDelay = 2; // секунд

    private $names = [
        'Александр', 'Алексей', 'Анна', 'Артем', 'Валентина', 'Валерий',
        'Виктор', 'Виктория', 'Владимир', 'Денис', 'Дмитрий', 'Евгений',
        'Екатерина', 'Елена', 'Иван', 'Игорь', 'Ирина', 'Кирилл', 'Константин',
        'Ксения', 'Людмила', 'Максим', 'Марина', 'Мария', 'Михаил', 'Надежда',
        'Наталья', 'Николай', 'Олег', 'Ольга', 'Павел', 'Роман', 'Светлана',
        'Сергей', 'Татьяна', 'Юлия', 'Яна'
    ];

    public function __construct()
    {
        try {
            // Увеличенные таймауты
            $this->client = new Client([
                'timeout' => 120,           // 2 минуты общий таймаут
                'connect_timeout' => 30,    // 30 секунд на соединение
                'http_errors' => false,
                'curl' => [
                    CURLOPT_DNS_CACHE_TIMEOUT => 3600,  // Кешировать DNS на час
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Использовать IPv4
                ]
            ]);

            $this->apiKey = env('DEEPSEEK_API_KEY');
            $this->apiUrl = 'https://api.deepseek.com/v1/chat/completions';

            if (empty($this->apiKey)) {
                throw new \Exception('DEEPSEEK_API_KEY не установлен в .env файле');
            }

        } catch (\Exception $e) {
            Log::error('DeepSeekService initialization error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generateName()
    {
        return $this->names[array_rand($this->names)];
    }

    /**
     * Генерация отзыва с retry механизмом
     */
    public function generateReview($productName, $productDescription, $rating = 5)
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::info("DeepSeek API попытка {$attempt} из {$this->maxRetries}");

                $reviewerName = $this->generateName();
                $prompt = $this->buildPrompt($productName, $productDescription, $rating, $reviewerName);

                $response = $this->sendRequest($prompt);

                if (!$response) {
                    throw new \Exception('DeepSeek API не отвечает после попытки ' . $attempt);
                }

                $review = $this->parseResponse($response, $rating);
                $review['reviewer_name'] = $reviewerName;

                Log::info('DeepSeek API успешно отработал');

                return $review;

            } catch (GuzzleException $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();

                Log::warning("DeepSeek API ошибка (попытка {$attempt}): " . $errorMessage);

                // Проверяем конкретные ошибки cURL
                if (strpos($errorMessage, 'Resolving timed out') !== false) {
                    // Проблема с DNS - ждем дольше
                    $sleepTime = $this->retryDelay * $attempt * 2;
                    Log::warning("DNS проблема, повтор через {$sleepTime} секунд");
                    sleep($sleepTime);

                } elseif (strpos($errorMessage, 'Connection timed out') !== false) {
                    // Проблема с соединением
                    sleep($this->retryDelay * $attempt);

                } else {
                    // Другие ошибки
                    if ($attempt < $this->maxRetries) {
                        sleep($this->retryDelay);
                    }
                }

            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("DeepSeek API ошибка (попытка {$attempt}): " . $e->getMessage());

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay);
                }
            }
        }

        // Если все попытки провалились
        $errorMsg = $lastException ? $lastException->getMessage() : 'Неизвестная ошибка';
        Log::error('DeepSeek API не отвечает после ' . $this->maxRetries . ' попыток: ' . $errorMsg);
        throw new \Exception('DeepSeek API не отвечает. Невозможно сгенерировать отзыв. Ошибка: ' . $errorMsg);
    }

    private function buildPrompt($productName, $description, $rating, $reviewerName)
    {
        $ratingText = $this->getRatingText($rating);

        return "Ты покупатель по имени {$reviewerName}. Напиши отзыв на товар:

Название: {$productName}
Описание: {$description}
Оценка: {$rating} из 5 звезд ({$ratingText})

Отзыв от первого лица.

Формат ответа ТОЛЬКО JSON:
{
    \"title\": \"Заголовок (до 60 символов)\",
    \"content\": \"Текст отзыва (100-200 слов)\",
    \"pros\": [\"Плюс 1\", \"Плюс 2\"],
    \"cons\": [\"Минус 1\"]
}

Верни только JSON, без лишнего текста.";
    }

    private function sendRequest($prompt)
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Ты реальный покупатель, который пишет отзыв от первого лица.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.8,
                    'max_tokens' => 800
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            Log::info('DeepSeek API response status: ' . $statusCode);

            if ($statusCode !== 200) {
                Log::error('DeepSeek API error. Status: ' . $statusCode . ', Body: ' . $body);
                return null;
            }

            $result = json_decode($body, true);

            if (!isset($result['choices'][0]['message']['content'])) {
                Log::error('DeepSeek API: unexpected response format', ['response' => $body]);
                return null;
            }

            return $result['choices'][0]['message']['content'];

        } catch (GuzzleException $e) {
            Log::error('Guzzle error in sendRequest: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error in sendRequest: ' . $e->getMessage());
            throw $e;
        }
    }

    private function parseResponse($response, $rating)
    {
        try {
            $response = preg_replace('/```json\n?|```\n?/', '', trim($response));

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('DeepSeek API вернул невалидный JSON: ' . json_last_error_msg() . '. Response: ' . substr($response, 0, 200));
            }

            if (empty($data['title'])) {
                throw new \Exception('DeepSeek API вернул ответ без поля title');
            }

            if (empty($data['content'])) {
                throw new \Exception('DeepSeek API вернул ответ без поля content');
            }

            return [
                'title' => $data['title'],
                'content' => $data['content'],
                'pros' => is_array($data['pros'] ?? null) ? $data['pros'] : [],
                'cons' => is_array($data['cons'] ?? null) ? $data['cons'] : []
            ];

        } catch (\Exception $e) {
            Log::error('Parse response error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getRatingText($rating)
    {
        $texts = [
            5 => 'отлично',
            4 => 'хорошо',
            3 => 'нормально',
            2 => 'плохо',
            1 => 'ужасно'
        ];

        return $texts[$rating] ?? 'нормально';
    }
}
