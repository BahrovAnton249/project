<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch();

echo json_encode(['count' => $result['total'] ?? 0]);
?>