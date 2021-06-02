<?php

namespace App\Controllers;

class NotFoundController extends BaseController
{
    public function indexAction()
    {
        return $this->app->view()->render(
            '404.phtml',
            [
                'section' => 'error'
            ]
        );
    }
}
