<?php
// includes/functions.php

/**
 * Starts the session safely, ensuring it's not already started.
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Escapes HTML characters to prevent XSS.
 * @param string|null $string The input string
 * @return string The escaped string
 */
function h($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Checks if the user is logged in as admin.
 * @return bool True if logged in, false otherwise.
 */
function isAdminLoggedIn() {
    initSession();
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

/**
 * Redirects to a specified path.
 * @param string $path
 */
function redirect($path) {
    header("Location: " . $path);
    exit;
}

/**
 * Requires admin login, otherwise redirects to login form.
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        die('Bitte zuerst <a href="./index.php#login">einloggen</a>');
    }
}
