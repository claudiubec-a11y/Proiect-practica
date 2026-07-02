<?php

/**
 * ============================================================================
 * config/database.php
 * ----------------------------------------------------------------------------
 * Conexiunea PDO la baza de date MySQL `utilajepro`.
 * Valorile implicite de mai jos sunt cele standard dintr-o instalare XAMPP
 * proaspătă (host: localhost, user: root, parolă: goală) — funcționează
 * imediat, fără nicio configurare suplimentară.
 *
 * Dacă folosești alte credențiale, le poți suprascrie fie direct în
 * constantele de mai jos, fie prin variabile de mediu (DB_HOST, DB_NAME,
 * DB_USER, DB_PASS), care au prioritate dacă sunt definite.
 * ============================================================================
 */

declare(strict_types=1);

final class Database
{
    // --- Valori implicite XAMPP ---
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'utilajepro';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_PORT = '3306';
    private const DB_CHARSET = 'utf8mb4';

    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    /** Întoarce conexiunea PDO curentă, creând-o dacă nu există deja (singleton). */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = getenv('DB_HOST') ?: self::DB_HOST;
            $port = getenv('DB_PORT') ?: self::DB_PORT;
            $name = getenv('DB_NAME') ?: self::DB_NAME;
            $user = getenv('DB_USER') ?: self::DB_USER;
            $pass = getenv('DB_PASS') ?: self::DB_PASS;

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $port,
                $name,
                self::DB_CHARSET
            );

            try {
                self::$connection = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Prepared statements REALE (nu emulate) — protecție SQL Injection.
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log('[UtilajePro] Database connection error: ' . $e->getMessage());

                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Nu s-a putut realiza conexiunea la baza de date. '
                        . 'Verifică faptul că MySQL rulează în XAMPP și că baza de date "utilajepro" există '
                        . '(vezi database/schema.sql).',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$connection;
    }
}
