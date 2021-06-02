<?php

namespace App\Entities;

use PDO;
use App\Services\MyPDO;

class Category
{
    public static function fetch(MyPDO $db, int $id)
    {
        $stmt = $db->run('
            SELECT t.id, t.url AS slug, t.tag AS name
            FROM tags AS t
                JOIN section AS l ON t.id = l.tag_id
            WHERE l.content_id = :id
            GROUP BY t.id
            ORDER BY t.tag ASC
        ', [
            'id' => $id
        ]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Entities\Category');
        return $stmt->fetch();
    }

    public static function exist(MyPDO $db, int $id, string $tagSlug)
    {
        $stmt = $db->run('
            SELECT COUNT(*) AS fiesta
            FROM tags AS t
                JOIN section AS l ON t.id = l.tag_id
            WHERE l.content_id = :id
            AND t.url = :tag
        ', [
            'id' => $id,
            'tag' => $tagSlug,
        ]);

        return $stmt->fetchColumn();
    }
}
