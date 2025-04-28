<?php

Route::get('/xml/products.xml', function () {  
    $filename = storage_path('app/xml/products.xml');
    $content = file_get_contents($filename);    
    return Response::make($content)->header('Content-Type', 'text/xml');  
});