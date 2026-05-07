<?php
// config/config.php

define('DB_HOST', 'db');
define('DB_PORT', '3306');
define('DB_USER', 'kueche');
define('DB_PASS', 'kueche');
define('DB_NAME', 'kueche');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Mensa Administration');

// Display errors for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
