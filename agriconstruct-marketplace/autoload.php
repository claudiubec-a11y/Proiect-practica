<?php

/**
 * ============================================================================
 * config/autoload.php
 * ----------------------------------------------------------------------------
 * Autoloader dedicat pentru TOATE clasele aplicației (fără Composer).
 * Este suficient să apelezi `new NumeClasa()` oriunde în cod — PHP va căuta
 * automat fișierul corespunzător în directoarele de mai jos, în ordine,
 * și îl va încărca o singură dată.
 *
 * Convenție: numele fișierului = numele clasei (ex: clasa `Listing` se
 * află în models/Listing.php).
 *
 * Acest fișier este inclus din config/bootstrap.php, care rulează la
 * începutul fiecărui request (prin index.php).
 * ============================================================================
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

spl_autoload_register(static function (string $className): void {
    // Ordinea directoarelor în care se caută clasa cerută.
    $directories = [
        'core',
        'models',
        'controllers',
        'middleware',
    ];

    foreach ($directories as $dir) {
        $path = ROOT_PATH . "/{$dir}/{$className}.php";
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }

    // Clasa nu a fost găsită în niciun director cunoscut.
    // Nu aruncăm eroare aici — PHP va arunca automat "Class not found"
    // dacă un alt autoloader (dacă există) nu o rezolvă, ceea ce ajută la
    // depanare (mesajul indică exact numele clasei lipsă).
});
