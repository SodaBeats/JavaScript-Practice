<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Settings
const DBHOST = 'localhost';
const DBUSER = 'root';
const DBPWD = '';
const DBNAME = 'lmsdb';

try {
    $pdo = new PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPWD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>