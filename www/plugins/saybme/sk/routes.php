<?php

use Saybme\Sk\Classes\Users\UserClass;
use Illuminate\Support\Facades\Cookie;

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

// 