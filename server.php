<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * This file allows us to emulate Apache's "mod_rewrite" functionality from the
 * built-in PHP web server. This provides a convenient way to run the application
 * directly without Caddy or complex web server configurations.
 */

$publicPath = __DIR__ . '/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath . $uri) && !is_dir($publicPath . $uri)) {
    return false;
}

require_once $publicPath . '/index.php';
