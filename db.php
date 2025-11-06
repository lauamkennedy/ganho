<?php
$host = 'localhost';
$db = 'meus_ganhos';
$user = 'root'; // ou seu usuário
$pass = '';     // ou sua senha

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
