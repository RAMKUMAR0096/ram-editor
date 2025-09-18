<?php
// config.php - DB connection and helper
session_start();

$DB_HOST = 'localhost';
$DB_NAME = 'one_editor_db';
$DB_USER = 'root';
$DB_PASS = ''; // set your DB password

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("DB Connection failed: " . $e->getMessage());
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
?>
