<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
        logAuth($pdo, null, $username, 'LOGIN_FAILED', 'EMPTY_FIELDS');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Успешный вход
            logAuth($pdo, $user['id'], $user['username'], 'LOGIN', 'SUCCESS');
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            // Неудачная попытка
            logAuth($pdo, null, $username, 'LOGIN_FAILED', 'INVALID_CREDENTIALS');
            $error = 'Неверное имя пользователя или пароль';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <style>
       body {
            background-image: url('financial-modelling-tool-for-farmers.webp');
            background-size: cover;
            background-blend-mode: overlay;
            color: rgb(255, 102, 0);
            font-size: 50px;
            font-weight: 400;
            font-style: italic;
            margin: 0;
        }   
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 Вход</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Имя пользователя или Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        
        <a href="register.php">Нет аккаунта? Зарегистрироваться</a>
    </div>
</body>
</html>