<?php

/**
 * ============================================================================
 * models/Category.php
 * ----------------------------------------------------------------------------
 * Acces la date pentru `categories` si `machinery_types` (cele 20 de tipuri
 * de utilaje, grupate in cele 2 categorii: Agricole / Constructii).
 * ============================================================================
 */

declare(strict_types=1);

final class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY id')->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function getMachineryTypes(?string $categorySlug = null): array
    {
        if ($categorySlug !== null) {
            $sql = 'SELECT mt.*, c.slug AS category_slug, c.name AS category_name
                    FROM machinery_types mt
                    JOIN categories c ON c.id = mt.category_id
                    WHERE c.slug = :slug
                    ORDER BY mt.name';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['slug' => $categorySlug]);
            return $stmt->fetchAll();
        }

        $sql = 'SELECT mt.*, c.slug AS category_slug, c.name AS category_name
                FROM machinery_types mt
                JOIN categories c ON c.id = mt.category_id
                ORDER BY c.id, mt.name';
        return $this->db->query($sql)->fetchAll();
    }

    public function findMachineryTypeById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM machinery_types WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $type = $stmt->fetch();
        return $type ?: null;
    }

    public function findMachineryTypeBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM machinery_types WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $type = $stmt->fetch();
        return $type ?: null;
    }
}
