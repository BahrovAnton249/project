<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? 0;

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
}

echo 'OK';
?>