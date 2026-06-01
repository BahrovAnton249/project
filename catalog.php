<?php
session_start();
require_once 'config.php';

// Получаем товары из БД
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем ID товаров в избранном текущего пользователя
$favorites_ids = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📰 Uncle Sema's catalog</title>
    <style>
        body {
            background-image: url('Catal fon.jpg');
            background-size: cover;
            color: rgb(47, 0, 255);
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        ul {
            background-color: rgba(216, 231, 3, 0.6);
            color: rgb(9, 230, 156);
            padding: 30px 50px;
            border-radius: 20px;
            font-size: 50px;
            list-style: none;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin: 0;
            gap: 20px; 
        }
        
        .logo {
            font-size: 40px;
            font-weight: bold;
            color: rgb(0, 26, 255);
        }
        
        .button a {
            color: rgb(5, 102, 247);
            font-size: 30px;
            font-weight: 400;
            text-decoration: none;
            background: rgba(0,0,0,0.5);
            padding: 10px 20px;
            border-radius: 10px;
        }
        
        .cart-link, .favorite-link {
            position: relative;
            display: inline-block;
        }
        
        .cart-link img, .favorite-link img {
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
        
        .filter-panel {
            background: rgba(0,0,0,0.7);
            padding: 20px;
            border-radius: 8px;
            margin: 20px;
            text-align: center;
        }
        
        .filter-panel h3 {
            color: rgb(0, 89, 255);
            font-size: 30px;
            font-weight: 400;
            font-style: italic;
            margin: 0 0 15px 0;
        }
        
        .filter-input {
            width: 50%;
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 18px;
            border-radius: 5px;
        }
        
        hr {
            border: none;
            border-top: 3px solid #ec085f;
            margin: 20px 0;
        }
        
        footer {
            text-align: left;
            padding: 15px;
            background-color: rgba(0,0,0,0.7);
            color: rgb(57, 241, 11);
            font-size: 18px;
            margin-top: 30px;
        }
        
        hr.another {
            border: none;
            border-top: 3px solid #ec0895;
            margin: 20px 0;
        }
        
        .catalog {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            padding: 20px;
        }
        
        .product-card {
            text-align: center;
            width: 280px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            background: rgba(0,0,0,0.6);
        }
        
        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .product-card h3 {
            font-size: 24px;
            margin: 10px 0;
        }
        
        .product-card h3 a {
            color: rgb(255, 0, 242);
            text-decoration: none;
        }
        
        .product-card h3 a:hover {
            text-decoration: underline;
        }
        
        .product-card p {
            font-size: 14px;
            color: white;
            margin: 10px 0;
        }
        
        .price {
            font-size: 22px;
            color: rgb(9, 230, 156);
            font-weight: bold;
            margin: 10px 0;
        }
        
        .add-to-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .add-to-cart:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5a67d8 0%, #6b46a0 100%);
        }
        
        .favorite-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .favorite-btn.active {
            background: linear-gradient(135deg, #eb3349 0%, #c0392b 100%);
        }
        
        .favorite-btn:hover {
            transform: translateY(-2px);
        }
        
        h2 {
            text-align: center;
            color: rgb(255, 123, 0);
            font-size: 50px;
            font-weight: 500;
            font-style: italic;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <header>
        <ul>
            <span class="logo">📰 Uncle Sema's catalog</span>
            <li class="button"><a href="index.php">👨‍🌾 Главная</a></li>
            <li class="button">
                <a href="Basket.php" class="cart-link">
                    <img src="7784863.png" alt="Корзина">
                    <span id="cartCount" class="cart-count">0</span>
                </a>
            </li>
            <a href="The_chosen_ones.php" class="favorite-link"> ❤️
                <span id="favoriteCount" class="favorite-count">0</span>
            </a>
        </ul>
    </header>
    <hr>

    <div class="filter-panel">
        <h3>Фильтр товаров</h3>
        <input type="text" id="filterInput" class="filter-input" placeholder="Поиск по названию...">
    </div>

    <h2>Каталог</h2>
    <div id="productsGrid" class="catalog"></div>

    <footer>
        <p>&copy; Все права защищены! Воровство — последнее ремесло.</p>
    </footer>
    <hr class="another">

    <script>
        // Товары из БД
        const products = <?= json_encode($products) ?>;
        const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
        let favoritesState = <?= json_encode($favorites_ids) ?>;

        function isFavorite(productId) {
            return favoritesState.includes(productId);
        }

        // ========== КОРЗИНА ==========
        function updateCartCount() {
            if (!isLoggedIn) return;
            fetch('get_cart_count.php')
                .then(res => res.json())
                .then(data => {
                    let el = document.getElementById('cartCount');
                    if (el) el.textContent = data.count;
                })
                .catch(err => console.log('Ошибка:', err));
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
            })
            .catch(err => console.log('Ошибка:', err));
        }

        // ========== ИЗБРАННОЕ ==========
        function updateFavoriteCount() {
            if (!isLoggedIn) return;
            fetch('get_fav_count.php')
                .then(res => res.json())
                .then(data => {
                    let el = document.getElementById('favoriteCount');
                    if (el) el.textContent = data.count;
                })
                .catch(err => console.log('Ошибка:', err));
        }

        function toggleFavorite(productId, buttonElement) {
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
                const index = favoritesState.indexOf(productId);
                if (index === -1) {
                    favoritesState.push(productId);
                    if (buttonElement) {
                        buttonElement.innerHTML = '💔 Удалить';
                        buttonElement.classList.add('active');
                    }
                } else {
                    favoritesState.splice(index, 1);
                    if (buttonElement) {
                        buttonElement.innerHTML = '❤️ В избранное';
                        buttonElement.classList.remove('active');
                    }
                }
                updateFavoriteCount();
            })
            .catch(err => console.log('Ошибка:', err));
        }

        // ========== ОТОБРАЖЕНИЕ ТОВАРОВ ==========
        function showProducts() {
            let filter = document.getElementById('filterInput').value.toLowerCase();
            let html = '';
            
            for (let p of products) {
                if (p.name.toLowerCase().includes(filter)) {
                    html += `
                        <div class="product-card">
                            <img src="${p.image_path}" alt="${p.name}">
                            <h3><a href="product.php?id=${p.id}">${escapeHtml(p.name)}</a></h3>
                            <p>${escapeHtml((p.description || '').substring(0, 100))}${(p.description && p.description.length > 100) ? '...' : ''}</p>
                            <div class="price">${p.price} ₽</div>
                            <button class="add-to-cart" onclick="addToCart(${p.id})">🛒 В корзину</button>
                            <button class="favorite-btn" onclick="toggleFavorite(${p.id}, this)">❤️ В избранное</button>
                        </div>
                    `;
                }
            }
            
            document.getElementById('productsGrid').innerHTML = html || '<p style="color:white; text-align:center;">Товаров не найдено</p>';
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // ========== ИНИЦИАЛИЗАЦИЯ ==========
        document.getElementById('filterInput').addEventListener('input', showProducts);
        showProducts();
        if (isLoggedIn) {
            updateCartCount();
            updateFavoriteCount();
        }
    </script>
</body>
</html>