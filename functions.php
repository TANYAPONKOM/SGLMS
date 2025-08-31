<?php
function check_login(string $username, string $password): array {
    $dbHost = 'localhost';
    $dbName = 'government_letter';
    $dbUser = 'root';
    $dbPass = '';

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
                       $dbUser, $dbPass,
                       [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        // ไม่ echo ที่นี่ ส่งสถานะให้ผู้เรียกไปจัดการ
        return ['ok' => false, 'error' => 'db'];
    }

    $sql = "SELECT password, is_active FROM users WHERE username = :u LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || (int)$user['is_active'] !== 1) {
        return ['ok' => false, 'error' => 'user']; // ไม่พบ/ถูกปิดใช้งาน
    }

    $stored = (string)$user['password'];

    // รองรับรหัสผ่านแบบ hash ด้วย
    $passOK = preg_match('/^\$2[aby]\$|^\$argon2/i', $stored)
              ? password_verify($password, $stored)
              : hash_equals($stored, $password);

    return $passOK ? ['ok' => true] : ['ok' => false, 'error' => 'pass'];
}