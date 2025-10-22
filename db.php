<?php
// db.php - centralized DB connection (used by all other PHP files)
// Update only if your DB host, user, pass or database change.

$DB_HOST = 'localhost';
$DB_USER = 'ueyhm8rqreljw';
$DB_PASS = 'gutn2hie5vxa';
$DB_NAME = 'dbxgedehaoiwgg';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");
?>
