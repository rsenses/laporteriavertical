<?php

namespace App\Entities;

use Datetime;
use PDO;
use App\Services\MyPDO;

class Post
{
    const SAMPLE_RATE = 100;

    public $db;
    public $fiesta = false;
    public $options;

    public function __construct(MyPDO $db)
    {
        $this->db = $db;

        $this->title_txt = strip_tags(preg_replace("/<br\s?\/?>/", ' ', $this->title));

        $this->date_formated = $this->getDateTimeFromFormat('Y-m-d H:i:s');

        $this->options = $this->options();
    }

    public function tags()
    {
        return Tag::fetchAll($this->db, $this->id);
    }

    public function category()
    {
        return Category::fetch($this->db, $this->id);
    }

    public function options()
    {
        if (!empty($this->options)) {
            return json_decode($this->options, true);
        }

        return null;
    }

    private function getDateTimeFromFormat($format)
    {
        $dateTime = DateTime::createFromFormat($format, $this->date);
        return strftime('%e %B', $dateTime->getTimestamp());
    }

    public static function fetch(MyPDO $db, string $slug)
    {
        $stmt = $db->run('
            SELECT v.id, v.title, v.subtitle, v.important AS featured, v.content, v.url, v.image, v.vertical, v.vimeo, v.twitter, v.facebook, v.description, v.date, v.updated_at, v.options, v.visits, a.name AS author_name
            FROM videos AS v
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.url = :slug
        ', [
            'section' => $GLOBALS['config']['web_slug'],
            'slug' => $slug
        ]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
        return $stmt->fetch();
    }

    public static function fetchNext(MyPDO $db, int $id)
    {
        $stmt = $db->run('
            SELECT v.id, v.title, v.subtitle, v.important AS featured, v.content, v.url, v.image, v.vertical, v.vimeo, v.twitter, v.facebook, v.description, v.date, v.updated_at, v.options, v.visits, a.name AS author_name
            FROM videos AS v
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.id < :id
            ORDER BY v.date DESC
        ', [
            'section' => $GLOBALS['config']['web_slug'],
            'id' => $id
        ]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
        return $stmt->fetch();
    }

    public static function fetchLast(MyPDO $db)
    {
        $stmt = $db->run('
            SELECT v.id, v.title, v.subtitle, v.important AS featured, v.content, v.url, v.image, v.vertical, v.vimeo, v.twitter, v.facebook, v.description, v.date, v.updated_at, v.options, v.visits, a.name AS author_name
            FROM videos AS v
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            ORDER BY v.date DESC
        ', [
            'section' => $GLOBALS['config']['web_slug']
        ]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
        return $stmt->fetch();
    }

    public static function fetchFeatured(MyPDO $db)
    {
        $stmt = $db->run('
            SELECT v.id, v.title, v.subtitle, v.content, v.url, v.image, v.vertical, v.vimeo, v.twitter, v.facebook, v.description, v.date, v.updated_at, v.options, v.visits, a.name AS author_name, a.image AS author_image, a.link AS author_link, a.twitter AS author_twitter, a.position AS author_position
            FROM videos AS v
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.important = 1
            ORDER BY v.date DESC
        ', [
            'section' => $GLOBALS['config']['web_slug']
        ]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
        return $stmt->fetch();
    }

    public static function fetchPopular(MyPDO $db, int $limit = 99)
    {
        $stmt = $db->run('
            SELECT v.id, v.title, v.subtitle, v.twitter, v.url, v.image, v.vertical, v.vimeo, v.date, v.updated_at, a.name AS author_name
            FROM videos AS v
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.active = 1
            ORDER BY v.visits DESC
            LIMIT :limit
            ', [
            'section' => $GLOBALS['config']['web_slug'],
            'limit' => $limit
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
    }

    public static function fetchRelated(MyPDO $db, int $id)
    {
        try {
            $stmt = $db->run('
                SELECT v.id, v.title, v.url, v.image, v.date, v.vertical
                FROM videos AS v
                    JOIN section AS s ON v.id = s.content_id
                    JOIN tags AS t ON t.id = s.tag_id
                WHERE v.section = :section
                    AND v.id != :id
                    AND v.active = 1
                GROUP BY v.id
                ORDER BY v.date DESC
                LIMIT 2;
            ', [
                'section' => $GLOBALS['config']['web_slug'],
                'id' => $id,
            ]);

            return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }

    public static function fetchAll(MyPDO $db, int $limit = 99)
    {
        $args = [
            'section' => $GLOBALS['config']['web_slug'],
            'limit' => $limit
        ];

        $query = '
            SELECT v.id, v.title, v.subtitle, v.twitter, v.url, v.image, v.vertical, v.vimeo, v.date, v.updated_at, a.name AS author_name
            FROM videos AS v
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.active = 1
            ORDER BY v.date DESC
            LIMIT :limit
        ';

        $stmt = $db->run($query, $args);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
    }

    public static function fetchByCategory(MyPDO $db, string $category = null, int $limit = 99, int $offset = 0, Post $featured = null)
    {
        $args = [
            'section' => $GLOBALS['config']['web_slug'],
            'category' => $category,
            'limit' => $limit,
            'offset' => $offset,
            'featured' => $featured ? $featured->id : 0,
        ];

        $query = '
            SELECT v.id, v.title, v.subtitle, v.url, v.image, v.vertical, v.date, v.options, v.updated_at, a.name AS author_name
            FROM videos AS v
                JOIN section AS s ON v.id = s.content_id
                JOIN tags AS t ON t.id = s.tag_id
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.active = 1
            AND v.id != :featured
            AND t.url = :category
            ORDER BY v.date DESC
            LIMIT :limit
            OFFSET :offset
        ';

        $stmt = $db->run($query, $args);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
    }

    public static function fetchOneByCategory(MyPDO $db, string $category)
    {
        $args = [
            'section' => $GLOBALS['config']['web_slug'],
            'category' => $category
        ];

        $query = '
            SELECT v.id, v.title, v.subtitle, v.url, v.image, v.date, v.vertical
            FROM videos AS v
                JOIN section AS s ON v.id = s.content_id
                JOIN tags AS t ON t.id = s.tag_id
            WHERE v.section = :section
            AND v.active = 1
            AND t.url = :category
            ORDER BY v.date DESC
        ';

        $stmt = $db->run($query, $args);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
        return $stmt->fetch();
    }

    public static function countByCategory(MyPDO $db, string $category)
    {
        $args = [
            'section' => $GLOBALS['config']['web_slug'],
            'category' => $category
        ];

        $query = '
            SELECT v.id
            FROM videos AS v
                JOIN section AS s ON v.id = s.content_id
                JOIN tags AS t ON t.id = s.tag_id
            WHERE v.section = :section
            AND v.active = 1
            AND t.url = :category
        ';

        return $db->run($query, $args)->rowCount();
    }

    public static function fetchByTag(MyPDO $db, string $tag, int $limit = 99, int $offset = 0)
    {
        $args = [
            'section' => $GLOBALS['config']['web_slug'],
            'tag' => $tag,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $query = '
            SELECT v.id, v.title, v.subtitle, v.twitter, v.url, v.image, v.vertical, v.vimeo, v.date, v.updated_at, a.name AS author_name
            FROM videos AS v
                JOIN tagLinks AS s ON v.id = s.content_id
                JOIN tags AS t ON t.id = s.tag_id
                LEFT JOIN author AS a ON v.author_id = a.author_id
            WHERE v.section = :section
            AND v.active = 1
            AND t.url = :tag
            ORDER BY v.date DESC
            LIMIT :limit
            OFFSET :offset
        ';

        $stmt = $db->run($query, $args);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Post', [$db]);
    }

    public static function countByTag(MyPDO $db, string $tag)
    {
        $args = [
            'section' => $GLOBALS['config']['web_slug'],
            'tag' => $tag
        ];

        $query = '
            SELECT v.id
            FROM videos AS v
                JOIN tagLinks AS s ON v.id = s.content_id
                JOIN tags AS t ON t.id = s.tag_id
            WHERE v.section = :section
            AND v.active = 1
            AND t.url = :tag
        ';

        return $db->run($query, $args)->rowCount();
    }

    public function updateVisitsCounter()
    {
        // Add SAMPLE_RATE to visits counter
        if (mt_rand(1, self::SAMPLE_RATE) === 1) {
            $this->visits = $this->visits + self::SAMPLE_RATE;

            $this->db->prepare('UPDATE videos SET visits = ? WHERE id = ?')
                ->execute([$this->visits, $this->id]);
        }
    }
}
