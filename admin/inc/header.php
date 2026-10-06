<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    
    <!-- Тот же Bootstrap, чтобы сохранить внешний вид -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <!-- Ссылка ведет на главную страницу АДМИНКИ -->
            <a class="navbar-brand" href="index.php">Admin</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <div class="navbar-nav">
                    <!-- Только админские пункты -->
                    <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" aria-current="page" href="index.php">Главная</a>
                    <a class="nav-link <?= ($currentPage === 'posts.php') ? 'active' : '' ?>" href="posts.php">Посты</a>
                </div>
                <!-- Блок справа для кнопки выхода -->
                <?php if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="password.php">Сменить пароль</a>
                    <form action="logout.php" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Выйти</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container">