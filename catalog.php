<?php
require_once 'config.php';

// Получаем товары из БД
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем ID товаров в избранном текущего пользователя
$favorites_ids = [];
$cart_count = 0;
$fav_count = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $fav_count = $stmt->fetch()['total'] ?? 0;
}

// Получаем уникальные категории
$categories = [];
foreach ($products as $product) {
    $cat = $product['category'];
    if (!empty($cat) && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}
sort($categories);

// Получаем уникальные регионы (created_at)
$regions = [];
foreach ($products as $product) {
    $region = $product['created_at'];
    if (!empty($region) && !in_array($region, $regions)) {
        $regions[] = $region;
    }
}
sort($regions);
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
            background: rgba(0,0,0,0.85);
            padding: 20px;
            border-radius: 12px;
            margin: 20px;
        }
        
        .filter-panel h3 {
            color: rgb(255, 123, 0);
            font-size: 28px;
            font-weight: 500;
            font-style: italic;
            margin: 0 0 15px 0;
            text-align: center;
        }
        
        .search-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            color: #ffd966;
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .filter-select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: rgba(255,255,255,0.9);
            font-size: 16px;
            cursor: pointer;
        }
        
        .filter-reset {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 25px;
            transition: all 0.3s ease;
        }
        
        .filter-reset:hover {
            background: #c0392b;
            transform: translateY(-2px);
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
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
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
        
        .filter-stats {
            text-align: center;
            color: white;
            margin-top: 10px;
            font-size: 14px;
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
                    <span id="cartCount" class="cart-count"><?= $cart_count ?></span>
                </a>
            </li>
            <a href="The_chosen_ones.php" class="favorite-link"> ❤️
                <span id="favoriteCount" class="favorite-count"><?= $fav_count ?></span>
            </a>
        </ul>
    </header>
    <hr>

    <div class="filter-panel">
        <h3>🔍 Фильтр товаров</h3>
        <input type="text" id="searchInput" class="search-input" placeholder="Поиск по названию...">
        
        <div class="filter-row">
            <div class="filter-group">
                <label>📂 Категория</label>
                <select id="categoryFilter" class="filter-select">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>🌍 Регион производства</label>
                <select id="regionFilter" class="filter-select">
                    <option value="">Все регионы</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>💰 Цена (до)</label>
                <select id="priceFilter" class="filter-select">
                    <option value="">Любая цена</option>
                    <option value="100000">до 100 000 ₽</option>
                    <option value="500000">до 500 000 ₽</option>
                    <option value="1000000">до 1 000 000 ₽</option>
                    <option value="5000000">до 5 000 000 ₽</option>
                    <option value="10000000">до 10 000 000 ₽</option>
                    <option value="20000000">до 20 000 000 ₽</option>
                    <option value="30000000">до 30 000 000 ₽</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>⭐ Рейтинг</label>
                <select id="ratingFilter" class="filter-select">
                    <option value="">Любой рейтинг</option>
                    <option value="4">4+ звезды</option>
                    <option value="4.5">4.5+ звезды</option>
                    <option value="4.7">4.7+ звезды</option>
                    <option value="4.9">4.9+ звезды</option>
                </select>
            </div>
            
            <button id="resetFilters" class="filter-reset">🔄 Сбросить фильтры</button>
        </div>
        <div class="filter-stats" id="filterStats"></div>
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

        // ========== ФИЛЬТРАЦИЯ ==========
        function filterProducts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const region = document.getElementById('regionFilter').value;
            const maxPrice = parseFloat(document.getElementById('priceFilter').value);
            const minRating = parseFloat(document.getElementById('ratingFilter').value);
            
            let filtered = products.filter(product => {
                // Поиск по названию
                if (searchTerm && !product.name.toLowerCase().includes(searchTerm)) {
                    return false;
                }
                // По категории
                if (category && product.category !== category) {
                    return false;
                }
                // По региону
                if (region && product.created_at !== region) {
                    return false;
                }
                // По максимальной цене
                if (maxPrice && product.price > maxPrice) {
                    return false;
                }
                // По минимальному рейтингу
                if (minRating && (product.rating < minRating)) {
                    return false;
                }
                return true;
            });
            
            return filtered;
        }
        
        function updateFilterStats(count) {
            const statsDiv = document.getElementById('filterStats');
            if (statsDiv) {
                statsDiv.innerHTML = `Найдено товаров: ${count}`;
            }
        }
        
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('regionFilter').value = '';
            document.getElementById('priceFilter').value = '';
            document.getElementById('ratingFilter').value = '';
            showProducts();
        }

        // ========== ОТОБРАЖЕНИЕ ТОВАРОВ ==========
        function showProducts() {
            const filteredProducts = filterProducts();
            let html = '';
            
            for (let p of filteredProducts) {
                let isFav = isFavorite(p.id);
                let favButtonText = isFav ? '💔 Удалить' : '❤️ В избранное';
                let favButtonClass = isFav ? 'favorite-btn active' : 'favorite-btn';
                
                let description = (p.description || '').substring(0, 100);
                if (p.description && p.description.length > 100) description += '...';
                
                html += `
                    <div class="product-card">
                        <img src="${p.image_path}" alt="${p.name}" onerror="this.src='/Uncle/Productimages/no-image.jpg'">
                        <h3><a href="product.php?id=${p.id}">${escapeHtml(p.name)}</a></h3>
                        <p>${escapeHtml(description)}</p>
                        <div class="price">${Number(p.price).toLocaleString()} ₽</div>
                        <div style="font-size: 12px; color: #ffd966; margin: 5px 0;">⭐ ${p.rating || 'Нет оценок'} | 📍 ${escapeHtml(p.created_at || 'Не указан')}</div>
                        <button class="add-to-cart" onclick="addToCart(${p.id})">🛒 В корзину</button>
                        <button class="${favButtonClass}" onclick="toggleFavorite(${p.id}, this)">${favButtonText}</button>
                    </div>
                `;
            }
            
            document.getElementById('productsGrid').innerHTML = html || '<p style="color:white; text-align:center; padding: 50px;">😔 Товаров не найдено. Попробуйте изменить фильтры.</p>';
            updateFilterStats(filteredProducts.length);
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
        function initFilters() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const regionFilter = document.getElementById('regionFilter');
            const priceFilter = document.getElementById('priceFilter');
            const ratingFilter = document.getElementById('ratingFilter');
            const resetBtn = document.getElementById('resetFilters');
            
            const update = () => showProducts();
            
            if (searchInput) searchInput.addEventListener('input', update);
            if (categoryFilter) categoryFilter.addEventListener('change', update);
            if (regionFilter) regionFilter.addEventListener('change', update);
            if (priceFilter) priceFilter.addEventListener('change', update);
            if (ratingFilter) ratingFilter.addEventListener('change', update);
            if (resetBtn) resetBtn.addEventListener('click', resetFilters);
            
            showProducts();
        }
        
        initFilters();
        if (isLoggedIn) {
            updateCartCount();
            updateFavoriteCount();
        }
    </script>
</body>
</html>