<?php

namespace App\Controllers;

use App\Entities\Post;

class CategoryController extends BaseController
{
    const POSTS_LIMIT = 8;

    protected $videos;

    public function indexAction(string $category)
    {
        $total = Post::countByCategory($this->db, $category);

        $this->videos = Post::fetchByCategory($this->db, $category, self::POSTS_LIMIT);

        // If no content, Error 404
        if (empty($this->videos)) {
            return $this->app->notFound();
        }

        $this->httpCache($this->videos);

        if ($category === 'vivencias-empresariales') {
            $template = 'vivencias-empresariales.phtml';
        } else {
            $template = 'category.phtml';
        }

        return $this->app->view()->render(
            $template,
            [
                'section' => 'section',
                'videos' => $this->videos,
                'total' => $total,
                'offset' => self::POSTS_LIMIT,
                'category' => $this->videos[0]->category,
            ]
        );
    }

    public function jsonAction(string $category, int $page)
    {
        // $category = $this->app->request()->query['category'];

        // $page = $this->app->request()->query['page'];

        $total = Post::countByCategory($this->db, $category);

        $offset = self::POSTS_LIMIT * $page;

        $this->videos = Post::fetchByCategory($this->db, $category, self::POSTS_LIMIT, $offset);

        $view = $this->app->view()->render(
            'partials/loop.phtml',
            [
                'videos' => $this->videos,
            ]
        );

        return $this->app->json(
            [
                'view' => $view,
                'page' => $page + 1,
                'remaining' => $total - ($offset + self::POSTS_LIMIT),
            ]
        );
    }
}
