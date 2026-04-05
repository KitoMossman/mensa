<?php
require_once __DIR__ . '/includes/Database.php';
$pdo = Database::getInstance()->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS custom_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    beginn DATE,
    ende DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS survey_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    type ENUM('radio', 'checkbox') DEFAULT 'radio',
    FOREIGN KEY (survey_id) REFERENCES custom_surveys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS survey_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    votes INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($sql);
    echo "Tables created successfully.";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
