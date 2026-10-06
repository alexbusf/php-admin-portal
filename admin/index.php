<?php 
session_start(); // Запускаем сессию в самом начале

// Если пользователь НЕ залогинен, выбрасываем его на страницу входа
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Главная';
$currentPage = 'index.php';

// Теперь подключаем шапку. Если мы здесь, значит сессия есть.
include 'inc/header.php'; 
?>

<div class="text-center mt-5">
    <h1>Главная</h1>
    <p class="lead">Добро пожаловать в панель управления.</p>
</div>

<?php include 'inc/footer.php'; ?>