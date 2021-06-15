<?php

namespace App\Controllers;

use App\Entities\Post;

class HomeController extends BaseController
{
    public function indexAction()
    {
        $posts = Post::fetchFeatured($this->db);

        return $this->app->view()->render(
            'home.phtml',
            [
                'section' => 'home',
                'posts' => $posts
            ]
        );
    }
}
