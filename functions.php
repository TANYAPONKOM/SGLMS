<?php
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dbHost = 'localhost';
        $dbName = 'government_letter';
        $dbUser = 'root';
        $dbPass = '';
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
                           $dbUser, $dbPass,
                           [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
function login(string $username, string $password): array {
    $pdo = getPDO();

    $sql = "SELECT u.user_id, u.username, u.password, u.role_id, 
                   u.position, u.fullname, u.is_active,
                   r.role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            WHERE u.username = :u 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ['ok' => false, 'error' => 'user'];
    }

    if ((int)$user['is_active'] !== 1) {
        return ['ok' => false, 'error' => 'inactive'];
    }

    $stored = (string)$user['password'];
    $passOK = preg_match('/^$2[aby]$|^$argon2/i', $stored)
              ? password_verify($password, $stored)
              : hash_equals($stored, $password);

    if (!$passOK) {
        return ['ok' => false, 'error' => 'pass'];
    }

    return [
        'ok'        => true,
        'user_id'   => $user['user_id'],
        'username'  => $user['username'],
        'role_id'   => $user['role_id'],
        'position'  => $user['position'],
        'fullname'  => $user['fullname'],
        'role_name' => $user['role_name'] ?? ''   // 🔹 กัน error
    ];
}
// ใส่ต่อท้ายไฟล์ functions.php เดิมได้เลย
function db(): PDO {
    $dbHost = 'localhost';
    $dbName = 'government_letter';
    $dbUser = 'root';
    $dbPass = '';
    return new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}