<?php namespace Saybme\Sk\Classes\Global;

use Tailor\Models\GlobalRecord;
use Media\Classes\MediaLibrary;

class GlobalClass {

    // Контакты
    public static function networks(){

        $gl = GlobalRecord::findForGlobalUuid('fbec6dba-044f-48b1-914f-7c29831e104d');
        $networks = $gl->networks;

        $rows = array();

        foreach($networks as $item){

            $data['title'] = $item->title;
            $data['value'] = $item->value;
            $data['code'] = $item->code;
            $data['link'] = $item->link;

            if($item->icon){
                $data['icon'] = MediaLibrary::url($item->icon);
            }            

            $rows[$item->code] = $data;
        }

        return collect($rows);
    }

}