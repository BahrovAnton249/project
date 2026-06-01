<?php
// Запускаем сессию для работы с авторизацией
session_start();

// Параметры подключения к базе данных
$host = 'localhost';        // сервер БД (XAMPP)
$dbname = 'ferm_instruments';   // имя вашей базы данных
$username = 'root';         // пользователь MySQL (по умолчанию root)
$password = '';             // пароль (у XAMPP по умолчанию пустой)

try {
    // Подключение к MySQL через PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Настройка режима ошибок: исключения
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Устанавливаем кодировку
    $pdo->exec("SET NAMES utf8");
    
} catch(PDOException $e) {
    // Если ошибка подключения — показываем сообщение и останавливаем скрипт
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функция проверки авторизации пользователя
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Функция получения количества товаров в корзине
function getCartCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

// Функция получения количества товаров в избранном
function getFavCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

// Функция для записи логов авторизации
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