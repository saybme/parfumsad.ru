<?php namespace Saybme\Sk\Components;

use Saybme\Sk\Models\Post;

class Skblog extends \Cms\Classes\ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Блог',
            'description' => 'Вывод постов'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'SLUG',
                'description' => 'SLUG поста',
                'type' => 'string',
                'default' => '{{ :slug }}'
            ],
            'tpl' => [
                'title' => 'Шаблон вывода',
                'description' => 'Шаблон вывода',
                'type' => 'string'
            ],
            'type' => [
                'title' => 'Тип вывода',
                'description' => 'Укажите тип вывода',
                'default' => 'getAll',
                'type' => 'dropdown',
                'options' => [
                    'getAll' => 'Все посты',
                    'getPage' => 'Пост'
                ]             
            ]
        ];
    }

    function onRun(){
        $this->skblog = $this->getContent();
    }

    private function getContent(){
        $type = $this->property('type');
        return $this->$type();
    }

    private function getAll(){
        $tpl = $this->property('tpl');

        $posts = Post::select('id','name','slug','created_at')->active()->get();  
        if(!$tpl) return $posts;

        $options['posts'] = $posts;        
        return $this->renderPartial($tpl, $options);
    }

    private function getPage(){
        $slug = $this->property('slug');
        $page = Post::select('id','name','slug','content','created_at')->active()->where('slug', $slug)->first();  

        if(!$page) return $this->controller->run('404');

        $this->page->title = $page->name;
        $this->page->url = $page->link;

        $options['page'] = $page;
        $options['breadcrumbs'] = $this->breadcrumbs($page);
        return $this->renderPartial('posts/page', $options);
    }

    private function breadcrumbs($page){
        $arr[] = ['name' => 'Блог', 'url' => '/posts'];
        $arr[] = ['name' => $page->name, 'url' => $page->link ,'active' => true];
        return $arr;
    }

    public $skblog;


}
