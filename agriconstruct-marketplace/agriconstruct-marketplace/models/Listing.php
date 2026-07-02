<?php

/**
 * ============================================================================
 * models/Listing.php
 * ----------------------------------------------------------------------------
 * Acces la date pentru `listings` (anunturi) si `listing_images`.
 * Contine si logica de cautare/filtrare/sortare/paginare folosita de
 * GET /listings, aliniata la filtrele din frontend (js/listings.js).
 * ============================================================================
 */

declare(strict_types=1);

final class Listing
{
    private PDO $db;

    private const SORTABLE_COLUMNS = [
        'recente' => 'l.created_at DESC',
        'pret-crescator' => 'COALESCE(l.sale_price, l.rental_price_per_day) ASC',
        'pret-descrescator' => 'COALESCE(l.sale_price, l.rental_price_per_day) DESC',
        'an-desc' => 'l.manufacturing_year DESC',
    ];

    private const SELECT_BASE = '
        SELECT l.*, mt.slug AS machinery_type_slug, mt.name AS machinery_type_name,
               c.slug AS category_slug, c.name AS category_name,
               u.first_name AS seller_first_name, u.last_name AS seller_last_name, u.phone AS seller_phone
        FROM listings l
        JOIN machinery_types mt ON mt.id = l.machinery_type_id
        JOIN categories c ON c.id = mt.category_id
        JOIN users u ON u.id = l.user_id
    ';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function search(array $filters): array
    {
        $where = ['l.approval_status = :approval_status'];
        $params = ['approval_status' => 'approved'];

        if (!empty($filters['q'])) {
            $where[] = 'MATCH(l.title, l.description) AGAINST (:q IN NATURAL LANGUAGE MODE)';
            $params['q'] = $filters['q'];
        }
        if (!empty($filters['categorie'])) {
            $where[] = 'c.slug = :categorie';
            $params['categorie'] = $filters['categorie'];
        }
        if (!empty($filters['tip_utilaj'])) {
            $where[] = 'mt.slug = :tip_utilaj';
            $params['tip_utilaj'] = $filters['tip_utilaj'];
        }
        if (!empty($filters['pret_min'])) {
            $where[] = 'COALESCE(l.sale_price, l.rental_price_per_day) >= :pret_min';
            $params['pret_min'] = (float) $filters['pret_min'];
        }
        if (!empty($filters['pret_max'])) {
            $where[] = 'COALESCE(l.sale_price, l.rental_price_per_day) <= :pret_max';
            $params['pret_max'] = (float) $filters['pret_max'];
        }
        if (!empty($filters['judet'])) {
            $where[] = 'l.county = :judet';
            $params['judet'] = $filters['judet'];
        }
        if (!empty($filters['oras'])) {
            $where[] = 'l.city LIKE :oras';
            $params['oras'] = '%' . $filters['oras'] . '%';
        }
        if (!empty($filters['an_min'])) {
            $where[] = 'l.manufacturing_year >= :an_min';
            $params['an_min'] = (int) $filters['an_min'];
        }
        if (!empty($filters['an_max'])) {
            $where[] = 'l.manufacturing_year <= :an_max';
            $params['an_max'] = (int) $filters['an_max'];
        }
        if (!empty($filters['stare']) && is_array($filters['stare'])) {
            $placeholders = $this->buildInPlaceholders('stare', $filters['stare'], $params, ['new', 'used']);
            if ($placeholders !== '') {
                $where[] = "l.condition IN ({$placeholders})";
            }
        }
        if (!empty($filters['tip_oferta']) && is_array($filters['tip_oferta'])) {
            $placeholders = $this->buildInPlaceholders('oferta', $filters['tip_oferta'], $params, ['sale', 'rental']);
            if ($placeholders !== '') {
                $where[] = "l.offer_type IN ({$placeholders})";
            }
        }
        if (!empty($filters['status'])) {
            $where[] = 'l.status = :status';
            $params['status'] = $filters['status'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM listings l
                      JOIN machinery_types mt ON mt.id = l.machinery_type_id
                      JOIN categories c ON c.id = mt.category_id
                      {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sortKey = $filters['sort'] ?? 'recente';
        $orderBy = self::SORTABLE_COLUMNS[$sortKey] ?? self::SORTABLE_COLUMNS['recente'];

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 9)));
        $offset = ($page - 1) * $perPage;

        $sql = self::SELECT_BASE . " {$whereSql} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['images'] = $this->getImages((int) $item['id']);
        }
        unset($item);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    private function buildInPlaceholders(string $prefix, array $values, array &$params, array $allowed): string
    {
        $placeholders = [];
        foreach (array_values($values) as $index => $value) {
            if (!in_array($value, $allowed, true)) {
                continue;
            }
            $key = "{$prefix}_{$index}";
            $placeholders[] = ":{$key}";
            $params[$key] = $value;
        }
        return implode(', ', $placeholders);
    }

    public function findById(int $id): ?array
    {
        $sql = self::SELECT_BASE . ' WHERE l.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $listing = $stmt->fetch();
        if (!$listing) {
            return null;
        }
        $listing['images'] = $this->getImages($id);
        return $listing;
    }

    public function getImages(int $listingId): array
    {
        $stmt = $this->db->prepare('SELECT id, image_path, is_primary FROM listing_images WHERE listing_id = :id ORDER BY sort_order');
        $stmt->execute(['id' => $listingId]);
        return $stmt->fetchAll();
    }

    public function addImage(int $listingId, string $path, bool $isPrimary = false, int $sortOrder = 0): int
    {
        $stmt = $this->db->prepare('INSERT INTO listing_images (listing_id, image_path, is_primary, sort_order)
                                     VALUES (:listing_id, :path, :is_primary, :sort_order)');
        $stmt->execute([
            'listing_id' => $listingId,
            'path' => $path,
            'is_primary' => $isPrimary ? 1 : 0,
            'sort_order' => $sortOrder,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function create(int $userId, array $data): int
    {
        $sql = 'INSERT INTO listings
                (user_id, machinery_type_id, title, description, manufacturer, model,
                 sale_price, rental_price_per_day, manufacturing_year, operating_hours,
                 engine_power, `condition`, offer_type, status, approval_status, city, county)
                VALUES
                (:user_id, :machinery_type_id, :title, :description, :manufacturer, :model,
                 :sale_price, :rental_price_per_day, :manufacturing_year, :operating_hours,
                 :engine_power, :condition, :offer_type, :status, :approval_status, :city, :county)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'machinery_type_id' => $data['machinery_type_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'model' => $data['model'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'rental_price_per_day' => $data['rental_price_per_day'] ?? null,
            'manufacturing_year' => $data['manufacturing_year'],
            'operating_hours' => $data['operating_hours'] ?? null,
            'engine_power' => $data['engine_power'] ?? null,
            'condition' => $data['condition'] ?? 'used',
            'offer_type' => $data['offer_type'],
            'status' => 'available',
            'approval_status' => 'pending',
            'city' => $data['city'],
            'county' => $data['county'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE listings SET
                    machinery_type_id = :machinery_type_id, title = :title, description = :description,
                    manufacturer = :manufacturer, model = :model, sale_price = :sale_price,
                    rental_price_per_day = :rental_price_per_day, manufacturing_year = :manufacturing_year,
                    operating_hours = :operating_hours, engine_power = :engine_power, `condition` = :condition,
                    offer_type = :offer_type, city = :city, county = :county
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'machinery_type_id' => $data['machinery_type_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'model' => $data['model'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'rental_price_per_day' => $data['rental_price_per_day'] ?? null,
            'manufacturing_year' => $data['manufacturing_year'],
            'operating_hours' => $data['operating_hours'] ?? null,
            'engine_power' => $data['engine_power'] ?? null,
            'condition' => $data['condition'] ?? 'used',
            'offer_type' => $data['offer_type'],
            'city' => $data['city'],
            'county' => $data['county'],
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE listings SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function updateApprovalStatus(int $id, string $approvalStatus): bool
    {
        $stmt = $this->db->prepare('UPDATE listings SET approval_status = :status WHERE id = :id');
        return $stmt->execute(['status' => $approvalStatus, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM listings WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function ownerId(int $listingId): ?int
    {
        $stmt = $this->db->prepare('SELECT user_id FROM listings WHERE id = :id');
        $stmt->execute(['id' => $listingId]);
        $ownerId = $stmt->fetchColumn();
        return $ownerId === false ? null : (int) $ownerId;
    }

    public function findByUser(int $userId): array
    {
        $sql = self::SELECT_BASE . ' WHERE l.user_id = :user_id ORDER BY l.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM listings')->fetchColumn();
    }

    public function countByCategorySlug(string $slug): int
    {
        $sql = 'SELECT COUNT(*) FROM listings l
                JOIN machinery_types mt ON mt.id = l.machinery_type_id
                JOIN categories c ON c.id = mt.category_id
                WHERE c.slug = :slug';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        return (int) $stmt->fetchColumn();
    }

    public function countByApprovalStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM listings WHERE approval_status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function allForAdmin(?string $approvalStatus = null): array
    {
        $where = '';
        $params = [];
        if ($approvalStatus !== null) {
            $where = 'WHERE l.approval_status = :status';
            $params['status'] = $approvalStatus;
        }
        $sql = self::SELECT_BASE . " {$where} ORDER BY l.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
