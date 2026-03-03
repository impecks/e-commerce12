<?php
echo "--- Environment Check ---\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "APP_KEY set: " . (getenv('APP_KEY') ? 'YES' : 'NO') . "\n";
echo "DB_CONNECTION: " . getenv('DB_CONNECTION') . "\n";
echo "DB_SOCKET: " . getenv('DB_SOCKET') . "\n";

$extensions = ['pdo_mysql', 'gd', 'zip', 'intl', 'calendar', 'bcmath'];
foreach ($extensions as $ext) {
    echo "Extension $ext: " . (extension_loaded($ext) ? 'OK' : 'MISSING') . "\n";
}

$paths = ['storage', 'bootstrap/cache'];
foreach ($paths as $path) {
    echo "Path $path writable: " . (is_writable($path) ? 'YES' : 'NO') . "\n";
}

try {
    if (getenv('DB_SOCKET')) {
        $dsn = "mysql:unix_socket=" . getenv('DB_SOCKET') . ";dbname=" . getenv('DB_DATABASE');
    } else {
        $dsn = "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_DATABASE');
    }
    $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_TIMEOUT => 5]);
    echo "DB Connection: SUCCESS\n";
} catch (\Exception $e) {
    echo "DB Connection: FAILED (" . $e->getMessage() . ")\n";
}
echo "-----------------------\n";
