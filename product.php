<?php
require_once 'config.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: catalog.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: catalog.php');
    exit;
}

$characteristics = null;
if (!empty($product['characteristics'])) {
    if (is_array($product['characteristics'])) {
        $characteristics = $product['characteristics'];
    } elseif (is_string($product['characteristics'])) {
        $decoded = json_decode($product['characteristics'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $characteristics = $decoded;
        }
    }
}

$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $is_favorite = $stmt->fetch() ? true : false;
}

$cart_count = 0;
$fav_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $fav_count = $stmt->fetch()['total'] ?? 0;
}

$user_avatar = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $user_avatar = $user['avatar'] ?? null;
}

$stmt = $pdo->prepare("
    SELECT r.*, u.username, u.avatar 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$review_success = '';
$review_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review']) && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $review_error = 'Оценка должна быть от 1 до 5';
    } elseif (empty($comment)) {
        $review_error = 'Напишите комментарий';
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment]);
        $review_success = '✅ Спасибо за отзыв!';
        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $avg_rating = $stmt->fetch()['avg_rating'] ?? 4.5;
        $stmt = $pdo->prepare("UPDATE products SET rating = ? WHERE id = ?");
        $stmt->execute([round($avg_rating, 1), $product_id]);
        
        header("Location: product.php?id=$product_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | Uncle Sema's catalog</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url('productsfon.jpg');
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }

        header ul {
            background-color: rgba(233, 236, 15, 0.7);
            padding: 20px 40px;
            border-radius: 20px;
            list-style: none;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin: 0;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .product-rating {
        font-size: 18px;
        color: #ffd966;
        margin-bottom: 15px;
        background: rgba(0,0,0,0.5);
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: rgb(0, 26, 255);
        }

        .button a {
            color: white;
            font-size: 24px;
            text-decoration: none;
            background: rgba(0,0,0,0.5);
            padding: 8px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .button a:hover {
            background: rgba(0,0,0,0.8);
            transform: translateY(-2px);
        }

        .cart-link, .favorite-link {
            position: relative;
            display: inline-block;
            text-decoration: none;
        }

        .cart-link img {
            width: 35px;
            height: 35px;
            vertical-align: middle;
        }

        .cart-count, .favorite-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .favorite-count {
            background: #e84393;
        }

        .favorite-link {
            color: white;
            font-size: 30px;
            background: rgba(0,0,0,0.5);
            padding: 0px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .favorite-link:hover {
            background: rgba(0,0,0,0.8);
        }

        .account-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            background: #f39c12;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .account-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .account-avatar span {
            font-size: 28px;
        }

        .product-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .breadcrumbs {
            margin-bottom: 20px;
        }

        .breadcrumbs a {
            color: #ffd966;
            text-decoration: none;
            font-size: 16px;
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
        }

        .breadcrumbs span {
            color: white;
        }

        .product-card {
            background: rgba(0, 0, 0, 0.85);
            border-radius: 24px;
            overflow: hidden;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .product-content {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            padding: 30px;
        }

        .product-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
        }

        .product-image img {
            width: 100%;
            max-width: 500px;
            border-radius: 16px;
            cursor: pointer;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .product-image img:hover {
            transform: scale(1.02);
        }

        .image-caption {
            margin-top: 10px;
            color: #aaa;
            font-size: 12px;
        }

        .image-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }

        .image-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .image-btn-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .image-btn-favorite {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .image-btn-favorite.active {
            background: linear-gradient(135deg, #eb3349 0%, #c0392b 100%);
        }

        .image-btn:hover {
            transform: translateY(-2px);
        }

        .product-info {
            flex: 1;
            min-width: 300px;
        }

        .product-name {
            font-size: 32px;
            color: #ffd966;
            margin-bottom: 15px;
            border-left: 4px solid #ff7b00;
            padding-left: 20px;
        }

        .product-price {
            font-size: 36px;
            color: #2ecc71;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .product-price span {
            font-size: 20px;
            color: white;
        }

        .product-description {
            color: #ddd;
            line-height: 1.6;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
        }

        .product-description h3 {
            color: #ffd966;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .characteristics {
            margin-bottom: 30px;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 20px;
        }

        .characteristics h3 {
            color: #ffd966;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .char-table {
            width: 100%;
            border-collapse: collapse;
        }

        .char-table tr {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .char-table td {
            padding: 10px 0;
        }

        .char-label {
            font-weight: bold;
            color: #ffd966;
            width: 40%;
        }

        .char-value {
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-cart, .btn-favorite {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-favorite {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-favorite:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(240, 147, 251, 0.4);
        }

        .btn-favorite.active {
            background: linear-gradient(135deg, #eb3349 0%, #c0392b 100%);
        }

        .btn-back {
            display: inline-block;
            padding: 12px 25px;
            background: rgba(0,0,0,0.7);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(0,0,0,0.9);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            cursor: pointer;
            justify-content: center;
            align-items: center;
        }

        .modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .reviews-section {
            margin-top: 40px;
            padding: 30px;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .reviews-section h3 {
            color: #ffd966;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .review-form {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .review-form h4 {
            color: white;
            margin-bottom: 15px;
        }

        .rating-input {
            margin-bottom: 15px;
        }

        .rating-input label {
            color: white;
            margin-right: 10px;
        }

        .rating-input select {
            padding: 8px;
            border-radius: 8px;
            background: rgba(255,255,255,0.9);
        }

        .review-form textarea {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            background: rgba(255,255,255,0.9);
            resize: vertical;
            min-height: 100px;
            margin-bottom: 15px;
        }

        .review-form button {
            padding: 10px 25px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
        }

        .review-form button:hover {
            background: #27ae60;
        }

        .review-item {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 15px;
        }

        .review-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .review-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f39c12;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .review-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .review-avatar span {
            font-size: 20px;
        }

        .review-author {
            font-weight: bold;
            color: #ffd966;
        }

        .review-rating {
            color: #ff7b00;
        }

        .review-date {
            font-size: 12px;
            color: #aaa;
            margin-left: auto;
        }

        .review-comment {
            color: white;
            line-height: 1.5;
            margin-top: 10px;
        }

        .message {
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
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
            text-align: left;
            padding: 15px;
            background-color: rgba(0,0,0,0.7);
            color: rgb(57, 241, 11);
            font-size: 18px;
            margin-top: 30px;
        }

        hr {
            border: none;
            border-top: 3px solid #ec085f;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .product-content {
                flex-direction: column;
            }
            .product-name {
                font-size: 24px;
            }
            .product-price {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<header>
    <ul>
        <span class="logo">📰 Uncle Sema's catalog</span>
        <li class="button"><a href="index.php">👨‍🌾 Главная</a></li>
        <li class="button"><a href="catalog.php">📰 Каталог</a></li>
        <li class="button">
            <a href="Basket.php" class="cart-link">
                <img src="7784863.png" alt="Корзина">
                <span id="cartCount" class="cart-count"><?= $cart_count ?></span>
            </a>
        </li>
        <li class="button">
            <a href="The_chosen_ones.php" class="favorite-link">
                ❤️ <span id="favoriteCount" class="favorite-count"><?= $fav_count ?></span>
            </a>
        </li>
        <li class="button">
            <a href="profile.php" class="account-link">
                <div class="account-avatar">
                    <?php if ($user_avatar && file_exists($user_avatar)): ?>
                        <img src="<?= $user_avatar ?>" alt="Аватар">
                    <?php else: ?>
                        <span>👤</span>
                    <?php endif; ?>
                </div>
            </a>
        </li>
    </ul>
</header>
<hr>

<div class="product-container">
    <a href="catalog.php" class="btn-back">← Назад в каталог</a>

    <div class="product-card">
        <div class="product-content">
            <div class="product-image">
                <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onclick="openModal(this.src)"
                     onerror="this.src='/Uncle/Productimages/no-image.jpg'">
                <div class="image-caption">📷 Нажмите на картинку для увеличения</div>
                <div class="image-buttons">
                    <button class="image-btn image-btn-cart" onclick="addToCart(<?= $product['id'] ?>)">
                        🛒 В корзину
                    </button>
                    <button class="image-btn image-btn-favorite <?= $is_favorite ? 'active' : '' ?>" 
                            onclick="toggleFavorite(<?= $product['id'] ?>, this)">
                        <?= $is_favorite ? '💔 Удалить' : '❤️ В избранное' ?>
                    </button>
                </div>
            </div>
            <div class="product-info">
                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                <div class="product-rating">⭐ Рейтинг: <?= $product['rating'] ?? 'Нет оценок' ?></div>

                <div class="product-description">
                    <h3>Описание</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>

                <?php if ($characteristics && is_array($characteristics) && count($characteristics) > 0): ?>
                <div class="characteristics">
                    <h3> Технические характеристики</h3>
                    <table class="char-table">
                        <?php foreach ($characteristics as $key => $value): ?>
                            <?php if (is_string($key) && (is_string($value) || is_numeric($value))): ?>
                            <tr>
                                <td class="char-label"><?= htmlspecialchars((string)$key) ?>:</td>
                                <td class="char-value"><?= htmlspecialchars((string)$value) ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <button class="btn-cart" onclick="addToCart(<?= $product['id'] ?>)">
                        🛒 Добавить в корзину
                    </button>
                    <button class="btn-favorite <?= $is_favorite ? 'active' : '' ?>" 
                            onclick="toggleFavorite(<?= $product['id'] ?>, this)">
                        <?= $is_favorite ? '💔 Удалить из избранного' : '❤️ Добавить в избранное' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="reviews-section">
        <h3>💬 Отзывы о товаре</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-form">
                <h4>Оставить отзыв</h4>
                <?php if ($review_success): ?>
                    <div class="message success"><?= $review_success ?></div>
                <?php endif; ?>
                <?php if ($review_error): ?>
                    <div class="message error"><?= $review_error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="rating-input">
                        <label>⭐ Оценка:</label>
                        <select name="rating" required>
                            <option value="5">5 — Отлично</option>
                            <option value="4">4 — Хорошо</option>
                            <option value="3">3 — Средне</option>
                            <option value="2">2 — Плохо</option>
                            <option value="1">1 — Ужасно</option>
                        </select>
                    </div>
                    <textarea name="comment" placeholder="Напишите ваш отзыв..." required></textarea>
                    <button type="submit" name="add_review">📝 Отправить отзыв</button>
                </form>
            </div>
        <?php else: ?>
            <div class="message error" style="text-align: center;">
                🔐 <a href="login.php" style="color: #2ecc71;">Войдите в аккаунт</a>, чтобы оставить отзыв
            </div>
        <?php endif; ?>
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="review-avatar">
                            <?php if (!empty($review['avatar']) && file_exists($review['avatar'])): ?>
                                <img src="<?= $review['avatar'] ?>" alt="">
                            <?php else: ?>
                                <span>👤</span>
                            <?php endif; ?>
                        </div>
                        <div class="review-author"><?= htmlspecialchars($review['username']) ?></div>
                        <div class="review-rating">⭐ <?= $review['rating'] ?>/5</div>
                        <div class="review-date"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></div>
                    </div>
                    <div class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="message" style="text-align: center; color: #aaa;">
                Пока нет отзывов. Будьте первым!
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; Все права защищены! Воровство — последнее ремесло.</p>
</footer>
<hr>
<div id="imageModal" class="modal" onclick="closeModal()">
    <span class="modal-close">&times;</span>
    <img id="modalImage" src="">
</div>

<script>
    const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

    function openModal(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('imageModal').style.display = 'none';
    }
    function updateCartCount() {
        if (!isLoggedIn) return;
        fetch('get_cart_count.php')
            .then(res => res.json())
            .then(data => {
                let el = document.getElementById('cartCount');
                if (el) el.textContent = data.count;
            });
    }

    function updateFavoriteCount() {
        if (!isLoggedIn) return;
        fetch('get_fav_count.php')
            .then(res => res.json())
            .then(data => {
                let el = document.getElementById('favoriteCount');
                if (el) el.textContent = data.count;
            });
    }
    function addToCart(id) {
        if (!isLoggedIn) {
            alert('Войдите в аккаунт, чтобы добавить товар в корзину');
            return;
        }
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + id
        })
        .then(() => {
            updateCartCount();
            alert('✅ Товар добавлен в корзину');
        });
    }

    function toggleFavorite(productId, button) {
        if (!isLoggedIn) {
            alert('Войдите в аккаунт, чтобы добавить в избранное');
            return;
        }
        
        fetch('toggle_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId
        })
        .then(() => {
            if (button.classList.contains('active')) {
                button.classList.remove('active');
                button.innerHTML = '❤️ Добавить в избранное';
            } else {
                button.classList.add('active');
                button.innerHTML = '💔 Удалить из избранного';
            }
            updateFavoriteCount();
        });
    }
    if (isLoggedIn) {
        updateCartCount();
        updateFavoriteCount();
    }
</script>

</body>
</html>