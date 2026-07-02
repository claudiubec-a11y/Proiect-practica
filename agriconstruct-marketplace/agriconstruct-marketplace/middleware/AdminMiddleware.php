<?php

/**
 * ============================================================================
 * middleware/AdminMiddleware.php
 * ----------------------------------------------------------------------------
 * Verifica faptul ca utilizatorul autentificat are rolul de administrator.
 * ============================================================================
 */

declare(strict_types=1);

final class AdminMiddleware
{
    public static function handle(): array
    {
        $user = AuthMiddleware::handle();

        if ($user['role'] !== 'admin') {
            Response::forbidden('Aceasta actiune este permisa doar administratorilor.');
        }

        return $user;
    }
}
