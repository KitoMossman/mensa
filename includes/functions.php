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
/**
 * Highlights the meal name (before parentheses) and mutes additives.
 * @param string|null $name The full meal name.
 * @return string The formatted HTML.
 */
function formatMealName($name) {
    if ($name === null || $name === '') {
        return '';
    }
    
    $pos = strpos($name, '(');
    if ($pos !== false) {
        $title = substr($name, 0, $pos);
        $additives = substr($name, $pos);
        return '<span class="meal-title">' . h($title) . '</span><span class="meal-additives">' . h($additives) . '</span>';
    }
    
    return '<span class="meal-title">' . h($name) . '</span>';
}
