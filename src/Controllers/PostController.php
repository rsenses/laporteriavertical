<?php

namespace App\Controllers;

use App\Entities\Post;

class PostController extends BaseController
{
    public function showAction(string $slug)
    {
        $post = $this->getPost($slug);
        
        // If no post, Error 404
        if (!$post) {
            return $this->app->notFound();
        }

        $next = Post::fetchNext($this->db, $post->id);
        if (!$next) {
            $next = Post::fetchLast($this->db);
        }

        return $this->app->view()->render(
            'post.phtml',
            [
                'section' => 'noticia',
                'post' => $post,
                'next' => $next,
            ]
        );
    }

    public function aloneAction(string $slug)
    {
        $post = $this->getPost($slug);

        // If no post, Error 404
        if (!$post) {
            return $this->app->notFound();
        }

        $next = Post::fetchNext($this->db, $post->id);
        if (!$next) {
            $next = Post::fetchLast($this->db);
        }

        if ($post->featured) {
            $template = 'partials/article-featured.phtml';
        } else {
            $template = 'partials/article.phtml';
        }

        return $this->app->view()->render(
            $template,
            [
                'section' => 'noticia',
                'post' => $post,
                'next' => $next,
            ]
        );
    }

    private function getPost(string $slug)
    {
        $post = Post::fetch($this->db, $slug);

        $post->updateVisitsCounter();

        $this->httpCache($post);

        return $post;
    }
}
