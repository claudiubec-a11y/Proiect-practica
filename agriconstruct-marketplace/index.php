<?php

/**
 * ============================================================================
 * index.php  (rădăcina proiectului)
 * ----------------------------------------------------------------------------
 * Punctul UNIC de intrare al aplicației. Toate cererile — indiferent de URL —
 * ajung aici (vezi .htaccess), sunt potrivite cu o rută din routes/api.php
 * și trimise către controller-ul corespunzător.
 *
 * Proiectul este gândit să funcționeze direct dintr-un subfolder XAMPP, de
 * exemplu:
 *     http://localhost/agriconstruct-marketplace/login
 *     http://localhost/agriconstruct-marketplace/listings
 *     http://localhost/agriconstruct-marketplace/listings/2011
 *
 * Calea de bază (ex: "/agriconstruct-marketplace") este detectată AUTOMAT
 * din SCRIPT_NAME, deci proiectul funcționează indiferent de numele
 * folderului în care este copiat în htdocs.
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

$routes = require ROOT_PATH . '/routes/api.php';

// --- 1. Detectează automat calea de bază (numele folderului din htdocs) ---
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$basePath = rtrim($scriptDir, '/');

// --- 2. Calea cerută, curățată de query string și de calea de bază ---
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . trim((string) $path, '/');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// --- 3. Rută de sănătate (health check), utilă la instalare în XAMPP ---
if ($path === '/' && $method === 'GET') {
    Response::success([
        'app' => 'UtilajePro API',
        'status' => 'ok',
        'php_version' => PHP_VERSION,
        'time' => date('c'),
    ], 'API-ul UtilajePro rulează corect.');
}

// --- 4. Caută prima rută din tabel care se potrivește metodei + căii ---
foreach ($routes as [$routeMethod, $routePattern, $handler]) {
    if ($routeMethod !== $method) {
        continue;
    }

    $paramNames = [];
    $regex = preg_replace_callback('/\{([a-zA-Z_]+)\}/', static function ($matches) use (&$paramNames) {
        $paramNames[] = $matches[1];
        return '([^/]+)';
    }, $routePattern);

    if (preg_match('#^' . $regex . '$#', $path, $matches)) {
        array_shift($matches); // elimină potrivirea completă, păstrează doar grupurile capturate
        $params = array_combine($paramNames, $matches);

        [$controllerName, $methodName] = $handler;

        try {
            if (!class_exists($controllerName)) {
                Response::serverError("Controller-ul {$controllerName} nu a fost găsit.");
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $methodName)) {
                Response::serverError("Metoda {$methodName} nu există în {$controllerName}.");
            }

            // Parametrii din cale (ex: {id}) sunt convertiți la int înainte de a fi pasați.
            $args = array_map(
                static fn ($value) => is_numeric($value) ? (int) $value : $value,
                array_values($params)
            );

            $controller->$methodName(...$args);
        } catch (Throwable $e) {
            error_log('[UtilajePro] Dispatch error: ' . $e->getMessage());
            Response::serverError();
        }
        exit;
    }
}

// --- 5. Nicio rută găsită ---
Response::notFound('Ruta cerută nu există: ' . $method . ' ' . $path);
