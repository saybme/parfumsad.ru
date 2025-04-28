<?php namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Category;
use View;
use Log;

/**
 * Skproducts Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class Skproducts extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'sk:skproducts';

    /**
     * @var string description is the console command description
     */
    protected $description = 'No description provided yet...';

    /**
     * handle executes the console command.
     */
    public function handle()
    {  
        $this->createFile();
        $this->output->writeln('Файл создан!');
    }

    // Создаем файл
    private function createFile(){

        $filename = storage_path('app/xml/products.xml');

        $products = Product::with('category')->active();
        $categoryIdx = $products->pluck('category_id')->unique();        

        $options['update'] = date('Y-m-d\TH:i:sP');
        $options['products'] = $products->get();
        $options['categories'] = Category::whereIn('id', $categoryIdx)->get();

        $text = View::make('saybme.sk::products', $options);

        $fh = fopen($filename, 'w');
        fwrite($fh, $text);
        fclose($fh);

        //Log::error($filename);

        

        // Перебор товаров
        // foreach($products as $product){
        //     Log::error($product);
        // }

        
        return;
    }

}
