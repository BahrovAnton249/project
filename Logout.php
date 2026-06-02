<?php
require_once 'config.php';

if (isLoggedIn()) {
    logAuth($pdo, $_SESSION['user_id'], $_SESSION['username'], 'LOGOUT', 'SUCCESS');
}

session_destroy();
header('Location: login.php');
exit;