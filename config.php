<?php
/**
 * Courier & Parcel Management System
 * Main configuration file
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Dhaka');

// ---------- Database settings (XAMPP default) ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'courier_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---------- Auto-detect base URL (works in /htdocs/courier) ----------
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appRoot = str_replace('\\', '/', __DIR__);
define('BASE_URL', rtrim(str_replace($docRoot, '', $appRoot), '/'));

// ---------- PDO connection ----------
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $ex) {
    die('<h3 style="font-family:sans-serif;color:#b91c1c">Database connection failed.</h3>'
      . '<p style="font-family:sans-serif">Please import <b>database.sql</b> in phpMyAdmin and check <b>config.php</b>.</p>');
}

require_once __DIR__ . '/includes/functions.php';
