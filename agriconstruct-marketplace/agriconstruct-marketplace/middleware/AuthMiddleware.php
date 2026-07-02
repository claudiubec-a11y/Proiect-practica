<?php

/**
 * ============================================================================
 * middleware/AuthMiddleware.php
 * ----------------------------------------------------------------------------
 * Verifica faptul ca exista un utilizator autentificat in sesiunea curenta.
 * ============================================================================
 */

declare(strict_types=1);

final class AuthMiddleware
{
    public static function handle(): array
    {
        if (empty($_SESSION['user_id'])) {
            Response::unauthorized('Trebuie sa fii autentificat pentru a accesa aceasta resursa.');
        }

        return [
            'id' => (int) $_SESSION['user_id'],
            'role' => $_SESSION['user_role'] ?? 'user',
        ];
    }
}
