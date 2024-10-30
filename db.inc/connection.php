<?php

$dsn = "mysql:host=localhost;dbname=walid_calc";
$user_name = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $user_name, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed" . $e->getMessage();
}
