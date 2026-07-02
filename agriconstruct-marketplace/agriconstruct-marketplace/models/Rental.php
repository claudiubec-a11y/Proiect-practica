<?php

/**
 * ============================================================================
 * models/Rental.php
 * ----------------------------------------------------------------------------
 * Acces la date pentru `rentals`. Include verificarea disponibilitatii unui
 * utilaj pentru o perioada data si calculul automat al costului total
 * (numar_zile x pret_pe_zi).
 * ============================================================================
 */

declare(strict_types=1);

final class Rental
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function isAvailable(int $listingId, string $startDate, string $endDate, ?int $excludeRentalId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM rentals
                WHERE listing_id = :listing_id
                  AND rental_status != :cancelled
                  AND start_date <= :end_date
                  AND end_date >= :start_date';
        $params = [
            'listing_id' => $listingId,
            'cancelled' => 'cancelled',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($excludeRentalId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeRentalId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) === 0;
    }

    public function calculateDays(string $startDate, string $endDate): int
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end)->days;
        return max(1, $diff);
    }

    public function create(int $listingId, int $renterId, string $startDate, string $endDate, float $pricePerDay): int
    {
        $days = $this->calculateDays($startDate, $endDate);
        $totalPrice = $days * $pricePerDay;

        $stmt = $this->db->prepare('INSERT INTO rentals (listing_id, renter_id, start_date, end_date, total_price, rental_status)
                                     VALUES (:listing_id, :renter_id, :start_date, :end_date, :total_price, :status)');
        $stmt->execute([
            'listing_id' => $listingId,
            'renter_id' => $renterId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rentals WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $rental = $stmt->fetch();
        return $rental ?: null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE rentals SET rental_status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function findByRenter(int $renterId): array
    {
        $sql = 'SELECT r.*, l.title AS listing_title
                FROM rentals r
                JOIN listings l ON l.id = r.listing_id
                WHERE r.renter_id = :renter_id
                ORDER BY r.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['renter_id' => $renterId]);
        return $stmt->fetchAll();
    }

    public function findByOwner(int $ownerId): array
    {
        $sql = 'SELECT r.*, l.title AS listing_title, u.first_name, u.last_name
                FROM rentals r
                JOIN listings l ON l.id = r.listing_id
                JOIN users u ON u.id = r.renter_id
                WHERE l.user_id = :owner_id
                ORDER BY r.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['owner_id' => $ownerId]);
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM rentals')->fetchColumn();
    }

    public function countActive(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rentals WHERE rental_status IN ('pending', 'confirmed')");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
