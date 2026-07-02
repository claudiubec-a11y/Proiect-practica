<?php

/**
 * ============================================================================
 * middleware/RoleMiddleware.php
 * ----------------------------------------------------------------------------
 * Middleware generic de verificare a rolului, reutilizabil pentru orice ruta
 * care trebuie restrictionata la un set de roluri.
 * ============================================================================
 */

declare(strict_types=1);

final class RoleMiddleware
{
    public static function handle(array $allowedRoles): array
    {
        $user = AuthMiddleware::handle();

        if (!in_array($user['role'], $allowedRoles, true)) {
            Response::forbidden('Nu ai rolul necesar pentru a accesa aceasta resursa.');
        }

        return $user;
    }
}
