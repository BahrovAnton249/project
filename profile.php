<?php
require_once 'config.php';

// Если пользователь не авторизован — отправляем на страницу входа
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе из базы данных
$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Если пользователь не найден (маловероятно, но на всякий случай)
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-image: url('Main 3.jpg');
            background-size: cover;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .profile-container {
            max-width: 550px;
            width: 100%;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 24px;
            backdrop-filter: blur(8px);
            overflow: hidden;
            box-shadow: 0 20px 35px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .profile-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            text-align: center;
        }
        .profile-header h1 {
            color: #ffd966;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .profile-header p {
            color: #ccc;
            font-size: 14px;
        }
        .avatar {
            background: #f39c12;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -50px auto 0 auto;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .avatar span {
            font-size: 52px;
        }
        .info-section {
            padding: 30px 25px;
        }
        .info-item {
            background: rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 15px 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.2s;
        }
        .info-item:hover {
            background: rgba(255,255,255,0.15);
        }
        .info-icon {
            font-size: 28px;
            min-width: 45px;
            text-align: center;
        }
        .info-content {
            flex: 1;
        }
        .info-label {
            font-size: 12px;
            color: #aaa;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 18px;
            font-weight: 500;
            color: #f0f0f0;
            word-break: break-word;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            padding: 0 25px 30px 25px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-primary {
            background: #2ecc71;
            color: white;
        }
        .btn-primary:hover {
            background: #27ae60;
            transform: scale(1.02);
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
            transform: scale(1.02);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #3498db;
            color: #3498db;
        }
        .btn-outline:hover {
            background: #3498db;
            color: white;
        }
        footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #888;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        @media (max-width: 500px) {
            .action-buttons { flex-direction: column; }
            .info-item { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <h1>👤 Мой профиль</h1>
        <p>Управление аккаунтом</p>
    </div>

    <div class="avatar">
        <span>👨‍🌾</span>
    </div>

    <div class="info-section">
        <div class="info-item">
            <div class="info-icon">📛</div>
            <div class="info-content">
                <div class="info-label">Имя пользователя</div>
                <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📧</div>
            <div class="info-content">
                <div class="info-label">Email</div>
                <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📅</div>
            <div class="info-content">
                <div class="info-label">Дата регистрации</div>
                <div class="info-value"><?= date('d.m.Y', strtotime($user['created_at'])) ?></div>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">🆔</div>
            <div class="info-content">
                <div class="info-label">ID пользователя</div>
                <div class="info-value"><?= $user['id'] ?></div>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="index.php" class="btn btn-outline">🏠 На главную</a>
        <a href="catalog.php" class="btn btn-primary">📦 Каталог</a>
        <a href="logout.php" class="btn btn-danger">🚪 Выйти</a>
    </div>

    <footer>
        <p>© Uncle Sema's Agricultural Machines</p>
    </footer>
</div>
</body>
</html>