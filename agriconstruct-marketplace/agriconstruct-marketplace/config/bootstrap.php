<?php

/**
 * ============================================================================
 * config/bootstrap.php
 * ----------------------------------------------------------------------------
 * Se ocupă de tot ce trebuie pregătit ÎNAINTE ca o cerere să ajungă la
 * router (routes/api.php). Este inclus o singură dată, din index.php.
 *
 * Pași:
 *   1. Definește ROOT_PATH și încarcă autoloader-ul (config/autoload.php);
 *   2. Încarcă config/database.php (clasa Database);
 *   3. Pornește sesiunea PHP (folosită pentru autentificare);
 *   4. Setează headerele CORS + Content-Type: application/json;
 *   5. Instalează un handler global de erori, ca răspunsul să fie MEREU
 *      JSON valid, niciodată o pagină albă / eroare HTML din PHP.
 * ============================================================================
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// --- Erorile PHP nu trebuie afișate niciodată direct în output (ar strica JSON-ul) ---
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once ROOT_PATH . '/config/autoload.php';
require_once ROOT_PATH . '/config/database.php';

// --- Sesiune PHP (folosită de AuthMiddleware / AdminMiddleware) ---
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// --- CORS: frontend-ul static (HTML/CSS/JS) poate rula pe alt port/origin ---
$allowedOrigin = getenv('FRONTEND_ORIGIN') ?: '*';
header("Access-Control-Allow-Origin: {$allowedOrigin}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Cererile de tip OPTIONS (preflight CORS) se opresc aici, fără procesare suplimentară.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- Handler global: orice excepție / eroare PHP neprinsă devine un JSON 500 ---
set_exception_handler(static function (Throwable $e): void {
    error_log('[UtilajePro] Unhandled exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Eroare internă de server.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    // Transformă warning-urile / notice-urile PHP în excepții, ca să treacă prin handler-ul de mai sus.
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/**
 * Citește și decodează corpul JSON al request-ului curent (POST/PUT).
 * Folosită de toate controller-ele.
 */
function request_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}
