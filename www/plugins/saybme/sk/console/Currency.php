<?php namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Tailor\Models\GlobalRecord;
use Log;

/**
 * Currency Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class Currency extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'sk:currency';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Получение курсов валют с ЦБ РФ';

    /**
     * handle executes the console command.
     */
    public function handle()
    {        
        $this->info('Получение курсов валют с ЦБ РФ...');

        try {
            $rates = $this->getCurrencyRates();
            
            if (!$rates) {
                $this->error('Не удалось получить курсы валют');
                return 1;
            }

            $this->saveCurrencyRates($rates);
            
            $this->info('Курсы валют успешно обновлены');
            return 0;

        } catch (\Exception $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            Log::error('Ошибка получения курсов валют: ' . $e->getMessage());
            return 1;
        }
    }

    // Получаем курсы валют с ЦБ РФ
    private function getCurrencyRates()
    {
        $url = 'https://www.cbr-xml-daily.ru/daily_json.js';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        
        if (!$data || !isset($data['Valute'])) {
            return null;
        }

        return $data['Valute'];
    }

    // Сохраняем курсы валют
    private function saveCurrencyRates($rates)
    {
        foreach ($rates as $code => $rate) {
            $currency = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');

            if($code == 'USD'){
                $currency->dollar = $rate['Value'];
            } elseif($code == 'EUR'){
                $currency->euro = $rate['Value'];
            }

            $currency->save();        
        }
    }

}
