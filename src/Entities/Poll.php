<?php

namespace App\Entities;

use App\Services\MyPDO;
use PDO;

class Poll
{
    public function __construct(MyPDO $db)
    {
        $this->db = $db;
    }

    public static function insert(MyPDO $db, string $json, string $poll, string $request, string $createdAt)
    {
        $stmt = $db->prepare('
            INSERT INTO poll (json, poll, request, created_at) VALUES (:json, :poll, :request, :createdAt)
        ');

        return $stmt->execute([
            'json' => $json,
            'poll' => $poll,
            'request' => $request,
            'createdAt' => $createdAt,
        ]);
    }

    public static function update(MyPDO $db, string $json, string $poll, string $request, string $createdAt)
    {
        $stmt = $db->prepare('
            UPDATE poll SET json = :json, request = :request, created_at = :createdAt WHERE poll = :poll
        ');

        return $stmt->execute([
            'json' => $json,
            'poll' => $poll,
            'request' => $request,
            'createdAt' => $createdAt,
        ]);
    }

    public static function fetchByPoll(MyPDO $db, string $poll, int $limit = 99)
    {
        $args = [
            'poll' => $poll,
            'limit' => $limit
        ];

        $query = '
            SELECT p.json
            FROM poll AS p
            WHERE p.poll = :poll
            ORDER BY p.created_at DESC
            LIMIT :limit
        ';

        $stmt = $db->run($query, $args);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'App\Entities\Poll', [$db]);
    }

    public static function count(MyPDO $db, string $poll, int $limit = 99)
    {
        $stmt = $db->prepare('SELECT count(*) FROM poll WHERE poll = ?');

        $stmt->execute([$poll]);

        return $stmt->fetchColumn();
    }
}
