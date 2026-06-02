<?php
require_once 'config.php';

if (!isLoggedIn() || $_SESSION['username'] !== 'admin') {
    die('Доступ запрещён');
}

$stmt = $pdo->query("
    SELECT * FROM auth_logs 
    ORDER BY created_at DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Логи авторизации</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #333; color: white; }
        .success { color: green; }
        .failed { color: red; }
    </style>
</head>
<body>
    <h1>📋 Логи авторизации</h1>
    <a href="index.php">На главную</a>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Действие</th>
            <th>Статус</th>
            <th>IP</th>
            <th>Время</th>
        </tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['id'] ?></td>
            <td><?= htmlspecialchars($log['username'] ?? '-') ?></td>
            <td><?= $log['action'] ?></td>
            <td class="<?= strtolower($log['status']) ?>"><?= $log['status'] ?></td>
            <td><?= $log['ip_address'] ?></td>
            <td><?= $log['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>