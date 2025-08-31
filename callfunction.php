<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

// ว่างช่องไหน ส่งเตือนช่องนั้น
if ($username === '' && $password === '') { 
    header('Location: login.html?user=required&pass=required'); 
    exit;
}
if ($username === '') { 
    header('Location: login.html?user=required'); 
    exit;
}
if ($password === '') { 
    header('Location: login.html?pass=required'); 
    exit;
}

// ตรวจล็อกอิน
$res = __connect($username, $password);

if (!$res['ok']) {
    if ($res['error'] === 'db') {
        header('Location: login.html?user=invalid');
        exit;
    }
    if ($res['error'] === 'user') {
        header('Location: login.html?user=invalid');
        exit;
    }
    if ($res['error'] === 'pass') {
        header('Location: login.html?pass=invalid');
        exit;
    }
}

// ✅ ล็อกอินสำเร็จ → ไปหน้า user/home.html
header('Location: user/home.html');
exit;