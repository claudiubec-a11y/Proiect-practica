<?php

/**
 * ============================================================================
 * core/Response.php
 * ----------------------------------------------------------------------------
 * Helper static pentru trimiterea de raspunsuri JSON standardizate din orice
 * controller, cu codul HTTP corect setat de fiecare data.
 *
 * Format standard:
 *   { "success": true,  "message": "...", "data": ... }
 *   { "success": false, "message": "...", "errors": {...} }
 * ============================================================================
 */

declare(strict_types=1);

final class Response
{
    private function __construct()
    {
    }

    public static function success($data = null, string $message = 'OK', int $statusCode = 200): void
    {
        self::send($statusCode, [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message = 'A aparut o eroare.', int $statusCode = 400, ?array $errors = null): void
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        self::send($statusCode, $payload);
    }

    public static function notFound(string $message = 'Resursa nu a fost gasita.'): void
    {
        self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Autentificare necesara.'): void
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Nu ai permisiunea necesara.'): void
    {
        self::error($message, 403);
    }

    public static function validationError(array $errors, string $message = 'Datele introduse sunt invalide.'): void
    {
        self::error($message, 422, $errors);
    }

    public static function serverError(string $message = 'Eroare interna de server.'): void
    {
        self::error($message, 500);
    }

    private static function send(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
