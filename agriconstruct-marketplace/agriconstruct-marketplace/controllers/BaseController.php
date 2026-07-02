<?php

/**
 * ============================================================================
 * controllers/BaseController.php
 * ----------------------------------------------------------------------------
 * Functionalitate comuna tuturor controller-elor: citirea body-ului JSON,
 * citirea query string-ului si accesul la utilizatorul autentificat curent.
 * ============================================================================
 */

declare(strict_types=1);

abstract class BaseController
{
    protected function input(): array
    {
        return request_body();
    }

    protected function query(): array
    {
        return $_GET;
    }

    protected function currentUser(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return ['id' => (int) $_SESSION['user_id'], 'role' => $_SESSION['user_role'] ?? 'user'];
    }
}
