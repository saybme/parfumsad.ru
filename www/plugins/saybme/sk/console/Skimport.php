<?php namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Saybme\Sk\Classes\Catalog\ImportClass;
use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Vendor;
use Str;
use Tailor\Models\GlobalRecord;
use System\Models\File;
use Log;

/**
 * Skimport Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class Skimport extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'sk:skimport';

    /**
     * @var string description is the console command description
     */
    protected $description = 'No description provided yet...';

    /**
     * handle executes the console command.
     */
    public function handle() {        

        //$q = new ImportClass;
        //$this->deleteProducts();
        // //$data = $q->importCategories();    
        //$data = $q->importProducts();
        // //$data = $q->importVendors();  
        // //$data = $q->importProductImage();  
        // $data = $q->importProductUpdate();      

        //$this->updateSlug();

        $this->jsonProducts();

        $this->output->writeln("Скрипт закончен!");
    }

    // Удаляем товары
    private function deleteProducts(){
        // Перебор товаров
        $items = Product::get();

        foreach($items as $item){
            $item->delete();
        }
         
    }

    // Товарные предложентия
    private function jsonProducts(){
        $data = $this->getContentFile();
        
        foreach($data as $item){
            if($item['type'] == 'table'){
                $this->msProducts($item);     
            }                        
        }
        
    }

    // Перебо таблицы товары
    private function msProducts($item){
        $data = $item['data']; 
        
        foreach($data as $item){   
            
            $id = $item['id'];
            $obj = Product::where('uid', $id)->first();

            if($obj){  
                $obj->price = $item['price']; 
                $obj->price_usd = $item['price_usd'];       
                $obj->price_eur = $item['price_eur'];  
                $obj->save();      
            }
            
            //Log::error($item);    
        } 
        
        return;
    }

    // Перебор таблицы
    private function catalog($item){    
        $data = $item['data'];    
        
        foreach($data as $item){

            if($item['class_key'] == 'msCategory'){
                $this->changeCategory($item);
            }

            if($item['class_key'] == 'msProduct'){
                $this->changeProduct($item);
            }            
            
        }        
        
        return;
    }    

    // Создаем и обновляем категории
    private function changeCategory($data = array()){

        $id = $data['id'];
        $name = $data['pagetitle'];

        $arr = array();
        $arr['uid'] = $id;
        $arr['name'] = $data['pagetitle'];
        $arr['slug'] = Str::slug($name);
        $arr['parent'] = null;
        $arr['is_active'] = $data['published'];
        $arr['content'] = $data['content'];

        $parent = Category::where('uid', $data['parent'])->first();
        if($parent){
            $arr['parent'] = $parent->id;
        }         

        // Запись удалена
        if($data['deletedby'] == 1){
            $arr['is_active'] = false;    
        }

        // Модель
        $obj = Category::where('uid', $id)->first();

        if(!$obj){
            $obj = new Category;
            $obj->fill($arr);
            $obj->save();
        } else {
            unset($arr['parent']); // Удаляем родителя
            unset($arr['is_active']); // Удаляем публикацию

            $obj->fill($arr);
            $obj->save();
        }

    }

    // Создяем или обновляем товар
    private function changeProduct($data = array()){

        $id = $data['id'];
        $name = $data['pagetitle'];

        $arr = array();
        $arr['uid'] = $id;
        $arr['name'] = $data['pagetitle'];    
        $arr['is_active'] = $data['publishedby'];
        $arr['available'] = 1;

        $parent = Category::where('uid', $data['parent'])->first();
        if($parent){
            $arr['category'] = $parent->id;
        } 
        
        // Модель
        $obj = Product::where('uid', $id)->first();

        if(!$obj){        
            $arr['price'] = 0;       
            $obj = new Product;
            $obj->fill($arr);
            $obj->save();
        } else {
            $obj->fill($arr);
            $obj->save();    
        }
        
    }

    // Добавляем производителей из файла
    private function siteVendors(){
        $gl = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');
        $file = $gl->shop_file;

        if(!$file) return;        

        $content = file_get_contents($file->path);
        $arr = json_decode($content, true);

        foreach($arr['modx_ms2_vendors'] as $row){
            $name = stripslashes($row['name']);
            $obj = Vendor::where('name', $name)->first();
            if(!$obj){
                $vendor = new Vendor;
                $vendor->uid = $row['id'];
                $vendor->name = $name;
                $vendor->slug = Str::slug($name);
                $vendor->is_active = true;
                $vendor->save();
            } else {
                $obj->uid = $row['id'];  
                $obj->save();
            }            
        }

        
    }

    // Контент файла
    private function getContentFile(){
        $gl = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');
        $file = $gl->shop_file;

        if(!$file) return;        

        $content = file_get_contents($file->path);
        $arr = json_decode($content, true);

        return $arr;
    }

    function get_http_status_code($url) {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $http_status_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        return $http_status_code;
    }

}
