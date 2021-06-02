<?php

namespace App\Controllers;

use App\Entities\Post;
use App\Entities\Tag;

class TagController extends BaseController
{
    const POSTS_LIMIT = 5;

    protected $videos;

    public function indexAction(string $tag)
    {
        $total = Post::countByTag($this->db, $tag);

        $this->videos = Post::fetchByTag($this->db, $tag, self::POSTS_LIMIT);

        // If no content, Error 404
        if (empty($this->videos)) {
            return $this->app->notFound();
        }

        $this->httpCache($this->videos);

        return $this->app->view()->render(
            'tag.phtml',
            [
                'section' => 'etiquetas',
                'tag' => Tag::fetch($this->db, $tag),
                'total' => $total,
                'offset' => self::POSTS_LIMIT,
                'videos' => $this->videos,
            ]
        );
    }
}
