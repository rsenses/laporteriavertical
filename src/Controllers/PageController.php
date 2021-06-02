<?php

namespace App\Controllers;

use App\Entities\Post;

class PageController extends BaseController
{
    public function contactAction()
    {
        return $this->app->view()->render(
            'consultas.phtml',
            [
                'section' => 'consultas',
            ]
        );
    }
}
