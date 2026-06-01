<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT p.* 
    FROM favorites f 
    JOIN products p ON f.product_id = p.id 
    WHERE f.user_id = ?
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>❤️ Избранное</title>
    <style>
        body { background-image: url('Ch.jpg'); background-size: cover; font-family: Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: rgba(0,0,0,0.85); border-radius: 20px; padding: 30px; }
        h1 { text-align: center; color: #ff7b00; }
        .favorite-item { display: flex; gap: 20px; padding: 15px; border-bottom: 1px solid #333; color: white; align-items: center; }
        .favorite-item img { width: 100px; height: 80px; object-fit: cover; border-radius: 10px; }
        .item-info { flex: 1; }
        .item-name { font-size: 20px; }
        .item-price { color: #2ecc71; font-size: 18px; }
        .remove-btn { background: #e74c3c; border: none; color: white; padding: 8px 20px; border-radius: 25px; cursor: pointer; }
        .empty { text-align: center; padding: 60px; color: white; }
        .empty a { color: #2ecc71; text-decoration: none; }
        .back-link { color: #2ecc71; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="catalog.php" class="back-link">← Назад в каталог</a>
        <h1>❤️ Моё избранное</h1>
        
        <?php if (empty($favorites)): ?>
            <div class="empty">
                <p>В избранном пока ничего нет</p>
                <a href="catalog.php">Перейти к покупкам →</a>
            </div>
        <?php else: ?>
            <?php foreach ($favorites as $item): ?>
                <div class="favorite-item" data-product-id="<?= $item['id'] ?>">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" onerror="this.src='/Uncle/Product_images/047.webp'">
                    <div class="item-info">
                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="item-price"><?= $item['price'] ?> ₽</div>
                    </div>
                    <button class="remove-btn" onclick="removeFromFav(<?= $item['id'] ?>)">💔 Удалить</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        async function removeFromFav(productId) {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=remove_from_fav&product_id=' + productId
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            }
        }
    </script>
</body>
</html>