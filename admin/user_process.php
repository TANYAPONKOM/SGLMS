<?php
session_start();
require_once __DIR__ . '/../functions.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $currentUser = $_SESSION['username']; // ✅ เอาชื่อผู้ใช้ที่กำลังล็อกอินมาใช้ใน Log

    // ✅ เพิ่มผู้ใช้
    if ($action === 'add') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = $_POST['fullname'];
        $email = strtolower(trim($_POST['email'])); // 🔹 แปลงเป็นตัวพิมพ์เล็ก
        $role_id = $_POST['role_id'];
        $position = $_POST['position'];
        $department_id = $_POST['department_id'];

        $stmt = $pdo->prepare("INSERT INTO users 
            (username, password, fullname, email, role_id, position, department_id, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$username, $password, $fullname, $email, $role_id, $position, $department_id]);

        // 🔹 บันทึก Log
        addLog($_SESSION['user_id'], "ผู้ใช้ {$currentUser} จัดการเพิ่มผู้ใช้: {$username}");

        header("Location: user_Managerment.php?success=1");
        exit;
    }

    // ✅ แก้ไขผู้ใช้
    if ($action === 'edit') {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $fullname = $_POST['fullname'];
        $email = strtolower(trim($_POST['email'])); // 🔹 แปลงเป็นตัวพิมพ์เล็ก
        $role_id = $_POST['role_id'];
        $position = $_POST['position'];
        $department_id = $_POST['department_id'];
        $is_active = $_POST['is_active'] ?? 1;

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users 
                SET username=?, password=?, fullname=?, email=?, role_id=?, position=?, department_id=?, is_active=? 
                WHERE user_id=?");
            $stmt->execute([$username, $password, $fullname, $email, $role_id, $position, $department_id, $is_active, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users 
                SET username=?, fullname=?, email=?, role_id=?, position=?, department_id=?, is_active=? 
                WHERE user_id=?");
            $stmt->execute([$username, $fullname, $email, $role_id, $position, $department_id, $is_active, $user_id]);
        }

        // 🔹 บันทึก Log
        addLog($_SESSION['user_id'], "ผู้ใช้ {$currentUser} จัดการแก้ไขผู้ใช้: {$username}");

        header("Location: user_Managerment.php?success=1");
        exit;
    }

    // ✅ ลบผู้ใช้
    if ($action === 'delete') {
        $user_id = $_POST['user_id'];

        // ดึง username ของ user ที่ถูกลบ
        $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id=?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
            $stmt->execute([$user_id]);

            // 🔹 บันทึก Log
            addLog($_SESSION['user_id'], "ผู้ใช้ {$currentUser} จัดการลบผู้ใช้: {$user['username']}");
        }

        header("Location: user_Managerment.php?success=1");
        exit;
    }
}