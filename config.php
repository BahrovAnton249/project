<?php
session_start();
$host = 'localhost';        
$dbname = 'ferm_instruments';  
$username = 'root';         
$password = '';             
try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("SET NAMES utf8");
    
} catch(PDOException $e) {

    die("Ошибка подключения к базе данных: " . $e->getMessage());
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


function getCartCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}


function getFavCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}


function logAuth($pdo, $userId, $username, $action, $status, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $pdo->prepare("
        INSERT INTO auth_logs (user_id, username, action, status, ip_address, user_agent, details) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $username, $action, $status, $ip, $userAgent, $details]);
}
?>