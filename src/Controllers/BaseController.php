<?php

namespace App\Controllers;

use flight\Engine;
use Symfony\Component\Templating\Helper\SlotsHelper;
use App\Entities\Post;

class BaseController
{
    protected $app;
    protected $db;

    public function __construct(Engine $app)
    {
        $this->app = $app;

        $this->db = $this->app->db();

        // Template Improvements
        $this->app->view()->set(new SlotsHelper());

        $this->app->view()->setEscaper('path', function (string $value) {
            return htmlspecialchars($value, ENT_QUOTES);
        });

        $this->app->view()->setEscaper('url', function (string $value) {
            return urlencode($value);
        });

        $uri = explode('?', $this->app->request()->url, 2)[0];
        $this->app->view()->addGlobal('uri', $uri);
    }

    /**
     * Normalize de content, call the proper functions based on it
     *
     * @param mixed $content
     * @return void
     */
    protected function httpCache($content)
    {
        if ($content instanceof Post) {
            $this->httpCacheSingleContent($content);
        } else {
            $this->httpCacheMultipleContent($content);
        }
    }

    /**
     * Set etga and lastmodified headers for a single content
     *
     * @param Post $content
     * @return void
     */
    private function httpCacheSingleContent(Post $content)
    {
        // Set HTTP Caché
        $this->app->lastModified(strtotime($content->updated_at));
        $this->app->etag('"' . md5($content->id . strtotime($content->updated_at)) . '"', 'weak');
    }

    /**
     * Set etag and lastmodified headers for an array of contents
     *
     * @param array $posts
     * @return void
     */
    private function httpCacheMultipleContent(array $posts)
    {
        $etag = '';
        $lastModified = 0;

        foreach ($posts as $content) {
            $etag .= $content->id;

            $lastModified = $lastModified > $content->updated_at ? $lastModified : $content->updated_at;
        }

        // Set HTTP Caché
        $this->app->lastModified(strtotime($lastModified));
        $this->app->etag('"' . md5($etag) . '"', 'weak');
    }
}
