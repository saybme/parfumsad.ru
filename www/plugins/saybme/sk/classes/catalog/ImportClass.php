<?php namespace Saybme\Sk\Classes\Catalog;

use Saybme\Sk\Models\Category;
use Saybme\Sk\Models\Product;
use Saybme\Sk\Models\Vendor;
use System\Models\File;
use Str;
use Log;

class ImportClass {

    // Загружаем категории
    public function importCategories(){

        // Получаем файл с категориями
        $path = file_get_contents('https://sakuradv.online/categories.json');
        $data = json_decode($path, true);

        foreach($data as $row){        
            $row['is_active'] = true;
            $this->skCategory($row);
        }        

        return 'Categories ' . count($data);
    }

    // Создаем категорию
    private function skCategory($data = array()){
        if(!$data) return;

        $category = Category::where('uid', $data['uid'])->first();
        if(!$category) $category = new Category;

        // Данные категории
        $category->fill($data);
        $category->save();

        $parent = Category::where('uid', $data['category'])->first();

        if($parent){
            $category->parent = $parent->id;    
            $category->save();
        }

        
    }

    // Удаляем все товары
    public function deleteProducts(){
        $items = Product::get();
        $count = 0;
        foreach($items as $item){
            $count++;
            $item->delete();
        } 
        return 'Товаров удалено ' . $count;  
    }

    // Загружаем товары
    public function importProducts(){
        // Получаем файл с категориями
        $path = file_get_contents('https://sakuradv.online/json_products');
        $data = json_decode($path, true);       

        foreach($data as $row){  

            $product = Product::where('uid', $row['uid'])->first();
            if(!$product) $product = new Product;              

            // Данные товара
            $product->fill($row);                  
            
            // Фото
            if(trim($row['uid'])){
                //$product->preview = (new File)->fromUrl($row['photo']);
            }   
            
            $product->save();
        }        

        return 'Товаров создано ' . count($data);
    }

    // Загружаем производителей
    public function importVendors(){
        // Получаем файл с категориями
        $path = file_get_contents('https://sakuradv.online/vendors.json');
        $data = json_decode($path, true);   

        foreach($data as $row){  

            $vendor = Vendor::where('uid', $row['uid'])->first();
            if(!$vendor) $vendor = new Vendor; 

            $row['is_active'] = true;
            $row['slug'] = Str::slug($row['name']);

            // Данные товара
            $vendor->fill($row);
            $vendor->save();  
            
            // Прикрепляем фото
            if($row['logo']){              
                $vendor->logo = (new File)->fromUrl($row['logo']);
                $vendor->save();
            }   
            
        }        

        return 'Vendors ' . count($data);
    }

    // Загружаем фото товара
    public function importProductImage(){
        
        $path = file_get_contents('https://sakuradv.online/json/product-photo.json');
        $data = json_decode($path, true);  

        foreach($data as $row){

            $product = Product::where('uid', $row['uid'])->first();

            if($product){

                if(!$product->preview){
                    
                    if($row['photo']){
                        $product->preview = (new File)->fromUrl($row['photo']);
                        $product->save();
                    }
                    
                }
                
            }

            Log::error($row);

        }
        

        return 'Photos ' . count($data);
    }

    // Обновление товаров
    public function importProductUpdate(){

        $path = file_get_contents('https://sakuradv.online/products.json');
        $data = json_decode($path, true);  

        $i = 0;

        foreach($data as $row){      
            
            //Log::error($row);

            $product = Product::where('uid', $row['uid'])->first();

            if($product){
                $product->article = intval($row['article']);
                $product->save();       
                
                $i++;
            }
          
        }

        return 'Обновлено ' . $i . ' товаров.';
    }

}