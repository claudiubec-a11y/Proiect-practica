<?php

/**
 * ============================================================================
 * models/Favorite.php
 * ----------------------------------------------------------------------------
 * Acces la date pentru `favorites`.
 * ============================================================================
 */

declare(strict_types=1);

final class Favorite
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function exists(int $userId, int $listingId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM favorites WHERE user_id = :user_id AND listing_id = :listing_id');
        $stmt->execute(['user_id' => $userId, 'listing_id' => $listingId]);
        return (bool) $stmt->fetchColumn();
    }

    public function add(int $userId, int $listingId): bool
    {
        if ($this->exists($userId, $listingId)) {
            return true;
        }
        $stmt = $this->db->prepare('INSERT INTO favorites (user_id, listing_id) VALUES (:user_id, :listing_id)');
        return $stmt->execute(['user_id' => $userId, 'listing_id' => $listingId]);
    }

    public function remove(int $userId, int $listingId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM favorites WHERE user_id = :user_id AND listing_id = :listing_id');
        return $stmt->execute(['user_id' => $userId, 'listing_id' => $listingId]);
    }

    public function listForUser(int $userId): array
    {
        $sql = 'SELECT l.*, mt.name AS machinery_type_name, c.slug AS category_slug,
                       f.created_at AS favorited_at
                FROM favorites f
                JOIN listings l ON l.id = f.listing_id
                JOIN machinery_types mt ON mt.id = l.machinery_type_id
                JOIN categories c ON c.id = mt.category_id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $items = $stmt->fetchAll();

        $listingModel = new Listing();
        foreach ($items as &$item) {
            $item['images'] = $listingModel->getImages((int) $item['id']);
        }
        unset($item);

        return $items;
    }
}
