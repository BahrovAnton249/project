<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 4) {
        $error = 'Пароль должен быть не менее 4 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким именем или email уже существует';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $password_hash])) {
                $success = 'Регистрация прошла успешно! Теперь вы можете войти.';
                logAuth($pdo, null, $username, 'REGISTER', 'SUCCESS');
            } else {
                $error = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style>
        body {
            background: #1a1a2e;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .register-container {
            background: #0f0f1a;
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            color: white;
        }
        h2 {
            text-align: center;
            color: #ff7b00;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2f8b5a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .error {
            color: #ff6b6b;
            text-align: center;
        }
        .success {
            color: #2ecc71;
            text-align: center;
        }
        a {
            color: #ff7b00;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>📝 Регистрация</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Имя пользователя" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
            <button type="submit">Зарегистрироваться</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </p>
    </div>
</body>
</html>