<?php
require_once 'config.php';


if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $bio = trim($_POST['bio'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE users SET bio = ?, birth_date = ?, gender = ? WHERE id = ?");
        $stmt->execute([$bio, $birth_date, $gender, $user_id]);
        $success_message = '✅ Профиль обновлён!';
    }
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $file_type = $_FILES['avatar']['type'];
        
        if (!in_array($file_type, $allowed)) {
            $error_message = '❌ Можно загружать только JPG, PNG или WEBP';
        } else {
            $upload_dir = 'uploads/avatars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filepath)) {
                $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old = $stmt->fetch();
                if ($old && $old['avatar'] && file_exists($old['avatar'])) {
                    unlink($old['avatar']);
                }
                
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$filepath, $user_id]);
                $success_message = '✅ Аватар обновлён!';
            } else {
                $error_message = '❌ Ошибка загрузки файла';
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT id, username, email, created_at, bio, birth_date, gender, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = $stmt->fetch()['cart_count'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as fav_count FROM favorites WHERE user_id = ?");
$stmt->execute([$user_id]);
$fav_count = $stmt->fetch()['fav_count'] ?? 0;
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
            background-image: url('Resusrses.jpg');
            background-size: cover;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }
        .profile-container {
            max-width: 650px;
            width: 100%;
            margin: 0 auto;
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
        .avatar-section {
            text-align: center;
            margin-top: -50px;
            margin-bottom: 20px;
        }
        .avatar {
            background: #f39c12;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            overflow: hidden;
            background-size: cover;
            background-position: center;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar span {
            font-size: 52px;
        }
        .change-avatar-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 8px;
            transition: 0.2s;
        }
        .change-avatar-btn:hover {
            background: rgba(255,255,255,0.4);
        }
        .info-section {
            padding: 20px 25px;
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
        .info-value select, .info-value textarea, .info-value input {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.9);
            font-size: 14px;
        }
        .info-value textarea {
            resize: vertical;
            min-height: 60px;
        }
        .edit-btn {
            background: #3498db;
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
        }
        .save-btn {
            background: #2ecc71;
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
        }
        .cancel-btn {
            background: #95a5a6;
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
        }
        .stats-row {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            flex: 1;
            background: rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            transition: 0.2s;
        }
        .stat-card:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #ffd966;
        }
        .stat-label {
            font-size: 12px;
            color: #ccc;
            margin-top: 5px;
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
        .message {
            padding: 10px 20px;
            margin: 10px 25px;
            border-radius: 10px;
            text-align: center;
        }
        .success {
            background: rgba(46, 204, 113, 0.3);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        .error {
            background: rgba(231, 76, 60, 0.3);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #888;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .avatar-form {
            display: inline-block;
            position: relative;
        }
        .avatar-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        @media (max-width: 500px) {
            .action-buttons { flex-direction: column; }
            .info-item { flex-direction: column; text-align: center; }
            .stats-row { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <h1>👤 Мой профиль</h1>
        <p>Управление аккаунтом</p>
    </div>

    <!-- Аватар -->
    <div class="avatar-section">
        <div class="avatar">
            <?php if ($user['avatar'] && file_exists($user['avatar'])): ?>
                <img src="<?= $user['avatar'] ?>" alt="Аватар">
            <?php else: ?>
                <span>👨‍🌾</span>
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data" class="avatar-form">
            <button type="button" class="change-avatar-btn" onclick="document.getElementById('avatarInput').click()">📷 Сменить аватар</button>
            <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp" style="display: none" onchange="this.form.submit()">
        </form>
    </div>

    <?php if ($success_message): ?>
        <div class="message success"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error"><?= $error_message ?></div>
    <?php endif; ?>

    <div class="stats-row">
        <a href="Basket.php" class="stat-card">
            <div class="stat-number">🛒 <?= $cart_count ?></div>
            <div class="stat-label">Товаров в корзине</div>
        </a>
        <a href="The_chosen_ones.php" class="stat-card">
            <div class="stat-number">❤️ <?= $fav_count ?></div>
            <div class="stat-label">В избранном</div>
        </a>
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

        <form method="POST">
            <div class="info-item">
                <div class="info-icon">📝</div>
                <div class="info-content">
                    <div class="info-label">О себе</div>
                    <div class="info-value">
                        <textarea name="bio" placeholder="Расскажите о себе..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">🎂</div>
                <div class="info-content">
                    <div class="info-label">День рождения</div>
                    <div class="info-value">
                        <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">⚧</div>
                <div class="info-content">
                    <div class="info-label">Пол</div>
                    <div class="info-value">
                        <select name="gender">
                            <option value="">Не указан</option>
                            <option value="male" <?= ($user['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Мужской</option>
                            <option value="female" <?= ($user['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Женский</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="action-buttons" style="margin-top: 10px;">
                <button type="submit" name="save_profile" class="btn btn-primary">💾 Сохранить изменения</button>
            </div>
        </form>
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