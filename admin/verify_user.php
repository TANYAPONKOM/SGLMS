<?php
session_start();
require_once __DIR__ . '/../functions.php';
$pdo = getPDO();

header('Content-Type: application/json; charset=utf-8');

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "error" => "user_not_found"]);
    exit;
}

$stored = $user['password'];

// ✅ ตรวจสอบรหัสผ่าน
$passOK = false;
if (preg_match('/^\$2[aby]\$|^\$argon2/i', $stored)) {
    // ถ้าเก็บแบบ hash (bcrypt, argon2)
    $passOK = password_verify($password, $stored);
} else {
    // ถ้าเก็บแบบ plain text
    $passOK = ($stored === $password);
}

if ($passOK) {
    $_SESSION['verified'] = true;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['fullname'] = $user['fullname'];
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "invalid_password"]);
}