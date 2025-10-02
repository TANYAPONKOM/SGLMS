<?php
session_start();
require_once __DIR__ . '/../functions.php';
$pdo = getPDO();

header('Content-Type: application/json; charset=utf-8');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['verified'] = true;
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}