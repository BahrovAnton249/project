<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');


$action = $_POST['action'] ?? $_GET['action'] ?? '';


if ($action === 'get_users') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $stmt = $pdo->query("SELECT id, username, email, age, created_at FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

if ($action === 'get_user' && isset($_GET['id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT id, username, email, age, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
    exit;
}

if ($action === 'create_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $age = isset($_POST['age']) ? (int)$_POST['age'] : null;
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Имя пользователя обязательно';
    }
    if (empty($email)) {
        $errors[] = 'Email обязателен';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    if (!empty($age) && ($age < 0 || $age > 150)) {
        $errors[] = 'Некорректный возраст';
    }
    if (empty($password)) {
        $errors[] = 'Пароль обязателен';
    } elseif (strlen($password) < 4) {
        $errors[] = 'Пароль должен быть не менее 4 символов';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'errors' => ['Пользователь с таким именем или email уже существует']]);
        exit;
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, age, password_hash) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$username, $email, $age, $password_hash]);
    
    if ($result) {
        $new_id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'user_id' => $new_id, 'message' => 'Пользователь создан']);
    } else {
        echo json_encode(['success' => false, 'errors' => ['Ошибка при создании пользователя']]);
    }
    exit;
}

if ($action === 'add_to_cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
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
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['count' => 0]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    echo json_encode(['count' => $result['total'] ?? 0]);
    exit;
}

if ($action === 'update_cart_quantity') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $cart_id = $_POST['cart_id'] ?? 0;
    $change = $_POST['change'] ?? 0;
    
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
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $cart_id = $_POST['cart_id'] ?? 0;
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'clear_cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true]);
    exit;
}


if ($action === 'toggle_favorite') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
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
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['count' => 0]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    echo json_encode(['count' => $result['total'] ?? 0]);
    exit;
}

if ($action === 'remove_from_fav') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? 0;
    
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>