<?php

/**
 * ============================================================================
 * models/User.php
 * ----------------------------------------------------------------------------
 * Acces la date pentru tabelul `users`. Toate interogarile folosesc prepared
 * statements PDO (protectie impotriva SQL Injection).
 * ============================================================================
 */

declare(strict_types=1);

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (first_name, last_name, email, password, role, phone, city, county)
                VALUES (:first_name, :last_name, :email, :password, :role, :phone, :city, :county)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'user',
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'county' => $data['county'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): bool
    {
        $sql = 'UPDATE users
                SET first_name = :first_name, last_name = :last_name, phone = :phone,
                    city = :city, county = :county, profile_image = :profile_image
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'county' => $data['county'] ?? null,
            'profile_image' => $data['profile_image'] ?? null,
            'id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute(['password' => $passwordHash, 'id' => $id]);
    }

    public function setStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where = '';

        if ($search !== null && $search !== '') {
            $where = 'WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search';
            $params['search'] = "%{$search}%";
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM users {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT id, first_name, last_name, email, role, phone, city, county, status, created_at
                FROM users {$where}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    public static function sanitize(array $user): array
    {
        unset($user['password'], $user['remember_token']);
        return $user;
    }
}
