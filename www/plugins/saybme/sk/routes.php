<?php

use Saybme\Sk\Classes\Users\UserClass;
use Illuminate\Support\Facades\Cookie;
use Tailor\Models\GlobalRecord;

Route::get('/xml/products.xml', function () {  
    $filename = storage_path('app/xml/products.xml');
    $content = file_get_contents($filename);    
    return Response::make($content)->header('Content-Type', 'text/xml');  
});

Route::get('/useractive/{hash}', function ($hash) {     
    $q = new UserClass;
    $user = $q->activeProfile($hash);
    return $user;  
});

Route::get('/logout', function () {  
    return redirect('/')->withCookie(Cookie::forget('userid'));
});

// robots.txt
Route::get('/robots.txt', function () {
    $gl = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');    
    $content = $gl->robots;
    return Response::make($content)->header('Content-Type', 'text/plain');
});

// sitemap.xml
Route::get('/sitemap.xml', function () {
    $data['products'] = Saybme\Sk\Models\Product::where('is_active', true)->get();    
    return Response::view('saybme.sk::sitemap', $data)->header('Content-Type', 'text/xml');
});
