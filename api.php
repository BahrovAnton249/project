<?php
require_once 'config.php';

header('Content-Type: application/json');

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ========== КОРЗИНА ==========
if ($action === 'add_to_cart') {
    $product_id = $_POST['product_id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Товар добавлен в корзину']);
    exit;
}

if ($action === 'get_cart_count') {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    echo json_encode(['count' => $result['total'] ?? 0]);
    exit;
}

if ($action === 'update_cart_quantity') {
    $cart_id = $_POST['cart_id'] ?? 0;
    $change = $_POST['change'] ?? 0; // +1 или -1
    
    if ($change == 1) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    } elseif ($change == -1) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE id = ? AND user_id = ? AND quantity > 1");
        $stmt->execute([$cart_id, $user_id]);
    }
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'remove_from_cart') {
    $cart_id = $_POST['cart_id'] ?? 0;
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'clear_cart') {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

// ========== ИЗБРАННОЕ ==========
if ($action === 'toggle_favorite') {
    $product_id = $_POST['product_id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
    exit;
}

if ($action === 'get_fav_count') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    echo json_encode(['count' => $result['total'] ?? 0]);
    exit;
}

if ($action === 'remove_from_fav') {
    $product_id = $_POST['product_id'] ?? 0;
    
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>
