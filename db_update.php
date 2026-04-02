<?php
require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SHOW COLUMNS FROM nachrichten LIKE 'ticket_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE nachrichten 
                    ADD COLUMN ticket_id VARCHAR(20) NULL, 
                    ADD COLUMN geheimwort_hash VARCHAR(255) NULL, 
                    ADD COLUMN antwort_gewuenscht TINYINT(1) DEFAULT 0, 
                    ADD COLUMN antwort TEXT NULL");
        echo "Table altered successfully\n";
    } else {
        echo "Columns already exist\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
