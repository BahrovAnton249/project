<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👨‍🌾Uncle Sema's agricultural machines.</title>
    <style>
        body {
            background-image: url('Main 3.jpg');
            background-size: cover;
            background-blend-mode: overlay;
            color: rgb(255, 102, 0);
            font-size: 50px;
            font-weight: 400;
            font-style: italic;
            margin: 0;
        }
        ul {
            background-color: rgba(216, 231, 3, 0.6);
            color: rgb(9, 230, 156);
            padding: 30px 50px;
            border-radius: 20px;
            font-size: 20px;
            max-width: 3000px;
            border: 2px solid rgb(255, 255, 255);
            margin: 0;
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        li {
            display: inline-block;
        }
        .nav-links a {
            color: rgb(100, 4, 4);
            font-size: 30px;
            font-weight: 400;
            font-style: italic;
            text-decoration: none;
            background: rgba(255,255,255,0.5);
            padding: 10px 20px;
            border-radius: 15px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 3px solid #ddec08;
            margin: 20px 0;
            width: 100%;
        }
        footer {
            text-align: left;
            padding: 15px;
            background-color: rgba(0,0,0,0.7);
            color: rgb(57, 241, 11);
            font-size: 18px;
            width: 25%;
            font-weight: 400;
            margin-top: 30px;
        }
        hr.special {
            border: none;
            border-top: 5px solid #ddec08;
            margin: 15px 0;
            width: 30%;
        }
        .account-link img {
            width: 50px;
            height: 50px;
            vertical-align: middle;
        }
        .content {
            text-align: right;
            margin-top: 80px;
            margin-right: 100px;
        }
        .welcome-text {
            color: rgb(73, 241, 7);
            font-size: 50px;
            font-weight: 400;
        }
        .subtext {
            color: rgb(73, 241, 7);
            font-size: 30px;
            font-weight: 400;
        }
        .greeting {
            position: absolute;
            top: 20px;
            right: 180px;
            background: rgba(0,0,0,0.6);
            padding: 10px 20px;
            border-radius: 20px;
            color: yellow;
            font-size: 18px;
        }
        .custom-font {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 25px;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<header>
    <ul>
        <span class="logo" style="font-size: 40px; font-weight: bold; color: rgb(0, 26, 255);">Uncle Sema's agricultural machines.</span>
        <div class="nav-links">
            <?php if (isLoggedIn()): ?>
                <li><a href="catalog.php">📰 Каталог</a></li>
                <li><a href="The_chosen_ones.php">❤️ Избранное</a></li>
                <li><a href="Basket.php">🛒 Корзина</a></li>
                <li><a href="logout.php">🚪 Выйти</a></li>
            <?php endif; ?>
            <li>
                <a href="<?= isLoggedIn() ? 'profile.php' : 'login.php' ?>" class="account-link">
                    <img src="i.webp" alt="Аккаунт">
                </a>
            </li>
        </div>
    </ul>
</header>

<hr>

<?php if (isLoggedIn()): ?>
    <div class="greeting">
        ✅ Вы вошли как: <?= htmlspecialchars($_SESSION['username']) ?>
    </div>
<?php endif; ?>

<div class="content">
    <div class="welcome-text" style="margin-right: 450px;">Добро пожаловать!</div>
    <div class="subtext" style="margin-right: 360px;">У нас ты найдёшь сельскохозяйственную </div>
    <div class="subtext" style="margin-right: 600px;">машину своей мечты!</div>
</div>

<footer>
    <p>&copy; Все права защищены! Воровство — последнее ремесло.</p>
</footer>
<hr class="special">

</body>
</html>