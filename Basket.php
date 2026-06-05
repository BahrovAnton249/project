<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.quantity, p.* 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🛒 Корзина</title>
    <style>
        body { background-image: url('Bask.png'); background-size: cover; font-family: Arial, sans-serif; }
        .cart-container { max-width: 900px; margin: 40px auto; background: rgba(0,0,0,0.85); border-radius: 20px; padding: 30px; }
        h1 { text-align: center; color: #ff7b00; }
        table { width: 100%; border-collapse: collapse; color: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        .price { color: #2ecc71; }
        .quantity-btn { background: #3498db; border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; }
        .remove-btn { background: #e74c3c; border: none; color: white; padding: 5px 15px; border-radius: 20px; cursor: pointer; }
        .total { font-size: 24px; text-align: right; margin-top: 20px; color: white; }
        .total span { color: #2ecc71; font-size: 32px; }
        .cart-buttons { display: flex; gap: 15px; margin-top: 20px; justify-content: flex-end; }
        button { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }
        .continue-btn { background: #3498db; color: white; text-decoration: none; }
        .clear-btn { background: #95a5a6; color: white; }
        .pay-btn { background: #2ecc71; color: white; }
        .empty-cart { text-align: center; padding: 60px; color: white; }
        .empty-cart a { color: #2ecc71; }
    </style>
</head>
<body>
    <div class="cart-container">
        <h1>🛒 Моя корзина</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <a href="catalog.php">← Вернуться к покупкам</a>
            </div>
        <?php else: ?>
            <table>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th></th>
                </tr>
                <?php $total = 0; ?>
                <?php foreach ($cart_items as $item): ?>
                    <?php $item_total = $item['price'] * $item['quantity']; $total += $item_total; ?>
                    <tr data-cart-id="<?= $item['cart_id'] ?>">
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="price"><?= $item['price'] ?> ₽</td>
                        <td>
                            <button class="quantity-btn" onclick="updateQuantity(<?= $item['cart_id'] ?>, -1)">-</button>
                            <span id="qty-<?= $item['cart_id'] ?>"><?= $item['quantity'] ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(<?= $item['cart_id'] ?>, 1)">+</button>
                        </td>
                        <td class="price" id="total-<?= $item['cart_id'] ?>"><?= $item_total ?> ₽</td>
                        <td><button class="remove-btn" onclick="removeFromCart(<?= $item['cart_id'] ?>)">Удалить</button></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="total">ИТОГО: <span id="grandTotal"><?= number_format($total, 0, ',', ' ') ?> ₽</span></div>
            <div class="cart-buttons">
                <a href="catalog.php" class="continue-btn">← Продолжить покупки</a>
                <button class="clear-btn" onclick="clearCart()">Очистить корзину</button>
                <button class="pay-btn" onclick="checkout()">Оплатить →</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function api(action, data = {}) {
            data.action = action;
            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            });
            return response.json();
        }

        async function updateQuantity(cartId, change) {
            const result = await api('update_cart_quantity', { cart_id: cartId, change: change });
            if (result.success) {
                location.reload();
            }
        }

        async function removeFromCart(cartId) {
            if (confirm('Удалить товар из корзины?')) {
                const result = await api('remove_from_cart', { cart_id: cartId });
                if (result.success) {
                    location.reload();
                }
            }
        }

        async function clearCart() {
            if (confirm('Очистить всю корзину?')) {
                const result = await api('clear_cart');
                if (result.success) {
                    location.reload();
                }
            }
        }

        async function checkout() {
            if (confirm('Оформить покупку?')) {
                alert('Спасибо за покупку!');
                await api('clear_cart');
                location.reload();
            }
        }
    </script>
</body>
</html>