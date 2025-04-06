<?php

// Load environment variables
// Note: This is a basic approach. For production, consider a more robust library.
// Important: Make sure your web server (Apache) can read the .env file.
// You might need to configure this or ensure file permissions allow it.

$dotenvPath = __DIR__ . '/../.env';
if (is_readable($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Define Database Credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'sweet_creations');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Other configurations can be added here
// e.g., define('SITE_URL', 'http://localhost/sweet_creations');

?> 