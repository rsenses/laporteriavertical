<?php

namespace App\Entities;

use PDO;
use App\Services\MyPDO;

class Tag
{
    public function __construct(MyPDO $db)
    {
        $this->db = $db;
    }

    public static function fetchAll(MyPDO $db, int $id)
    {
        $stmt = $db->run('
            SELECT t.id, t.url AS slug, t.tag AS name
            FROM tags AS t
                JOIN tagLinks AS l ON t.id = l.tag_id
            WHERE l.content_id = :id
            GROUP BY t.id
            ORDER BY t.tag ASC
        ', [
            'id' => $id
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Tag', [$db]);
    }

    public static function fetch(MyPDO $db, string $url)
    {
        $stmt = $db->run('
            SELECT t.id, t.url AS slug, t.tag AS name
            FROM tags AS t
            WHERE t.url = :url
        ', [
            'url' => $url
        ]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Tag', [$db]);
        return $stmt->fetch();
    }

    public static function fetchAllProject(MyPDO $db)
    {
        $stmt = $db->run('
            SELECT t.id, t.url AS slug, t.tag AS name
            FROM tags AS t
                JOIN tagLinks AS l ON t.id = l.tag_id
            WHERE l.table = :section
            GROUP BY t.url
            ORDER BY t.tag ASC
        ', [
            'section' => $GLOBALS['config']['web_slug']
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Tag', [$db]);
    }
}
