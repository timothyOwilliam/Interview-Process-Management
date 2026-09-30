<?php
require_once __DIR__ . '/env.php';

// Load .env unconditionally so DB + SMTP + app-URL settings are in $_ENV
// for every script that includes app.php — local and production alike.
// (loadEnv silently does nothing when the file is absent, so local setups
// without a .env still fall back to the defaults in database.php / email.php.)
loadEnv(__DIR__ . '/.env');

// Auto-detects the project base URL based on environment.
// On localhost (XAMPP) → /Interview-Process-Management
// On production (InfinityFree, etc.) → '' (empty, files at root)

$host = $_SERVER['HTTP_HOST'] ?? '';//check if its localhost or online domain

$isLocal = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);// attach the localhost or the localhost IP adress to the $isLocal variable
define('APP_BASE', $isLocal ? '/Interview-Process-Management' : '');//if the $isLocal has a value let 'APP_BASE' have the /interview.... directory if not blank, which helps in online domains
