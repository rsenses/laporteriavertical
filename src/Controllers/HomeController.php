<?php

namespace App\Controllers;

use App\Entities\Post;

class HomeController extends BaseController
{
    public function indexAction()
    {
        $last = Post::fetchLast($this->db);

        return $this->app->redirect('/' . $last->url);
    }

    public function indexOKAction()
    {
        $featured = Post::fetchFeatured($this->db);

        return $this->app->view()->render(
            'home.phtml',
            [
                'section' => 'home',
                'featured' => $featured
            ]
        );
    }
}
