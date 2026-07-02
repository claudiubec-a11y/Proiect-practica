<?php

/**
 * ============================================================================
 * routes/api.php
 * ----------------------------------------------------------------------------
 * Tabelul central de rute ale API-ului REST. Fiecare ruta este un array:
 *   [ metoda HTTP, tipar de cale (cu parametri {id}), [Controller, metoda] ]
 *
 * Tiparele sunt verificate in ORDINEA din lista - rutele mai specifice
 * (ex: /listings/mine) trebuie declarate INAINTEA celor cu parametru
 * (ex: /listings/{id}), altfel "mine" ar fi interpretat ca un id.
 *
 * Exemple de URL complete (XAMPP, fara vhost):
 *   http://localhost/agriconstruct-marketplace/login
 *   http://localhost/agriconstruct-marketplace/listings
 *   http://localhost/agriconstruct-marketplace/listings/2011
 *
 * index.php citeste acest fisier, potriveste ruta ceruta si apeleaza
 * controller-ul corespunzator.
 * ============================================================================
 */

declare(strict_types=1);

return [
    // ---------------------------------------------------------------- Auth --
    ['POST', '/register', ['AuthController', 'register']],
    ['POST', '/login', ['AuthController', 'login']],
    ['POST', '/logout', ['AuthController', 'logout']],
    ['POST', '/password/forgot', ['AuthController', 'forgotPassword']],
    ['POST', '/password/reset', ['AuthController', 'resetPassword']],
    ['PUT', '/password/change', ['AuthController', 'changePassword']],

    // --------------------------------------------------------------- Users --
    ['GET', '/users/profile', ['UserController', 'profile']],
    ['PUT', '/users/profile', ['UserController', 'updateProfile']],

    // ------------------------------------------------------------ Listings --
    ['GET', '/listings/mine', ['ListingController', 'mine']],
    ['GET', '/listings', ['ListingController', 'index']],
    ['GET', '/listings/{id}', ['ListingController', 'show']],
    ['POST', '/listings', ['ListingController', 'store']],
    ['PUT', '/listings/{id}', ['ListingController', 'update']],
    ['DELETE', '/listings/{id}', ['ListingController', 'destroy']],

    // ----------------------------------------------------------- Favorites --
    ['GET', '/favorites', ['FavoriteController', 'index']],
    ['POST', '/favorites', ['FavoriteController', 'store']],
    ['DELETE', '/favorites/{id}', ['FavoriteController', 'destroy']],

    // ------------------------------------------------------------ Messages --
    ['GET', '/messages', ['MessageController', 'index']],
    ['GET', '/messages/{id}', ['MessageController', 'show']],
    ['POST', '/messages', ['MessageController', 'store']],

    // ------------------------------------------------------------- Rentals --
    ['GET', '/rentals', ['RentalController', 'index']],
    ['POST', '/rentals', ['RentalController', 'store']],

    // --------------------------------------------------------------- Admin --
    ['GET', '/admin/dashboard', ['AdminController', 'dashboard']],
    ['GET', '/admin/listings', ['AdminController', 'listings']],
    ['PUT', '/admin/listings/{id}/approve', ['AdminController', 'approveListing']],
    ['PUT', '/admin/listings/{id}/reject', ['AdminController', 'rejectListing']],
    ['DELETE', '/admin/listings/{id}', ['AdminController', 'deleteListing']],
    ['GET', '/admin/users', ['AdminController', 'users']],
    ['PUT', '/admin/users/{id}/block', ['AdminController', 'blockUser']],
    ['PUT', '/admin/users/{id}/unblock', ['AdminController', 'unblockUser']],
];
