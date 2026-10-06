<?php
// 1. Стартуем сессию САМЫМ ПЕРВЫМ действием
session_start();

// 2. Если пользователь УЖЕ залогинен, не показываем форму, а кидаем в админку
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: index.php');
    exit;
}

// 3. Подключаем базу данных
require_once '../db.php';

// Переменные для шаблона
$pageTitle = 'Вход в админку';
$currentPage = 'login.php';

// 4. Генерируем токен безопасности (CSRF)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// 5. Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка токена
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности страницы. Обновите её.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        // Базовая проверка полей
        if ($login === '' || $password === '') {
            $error = 'Введите логин и пароль.';
        } else {
            // Ищем пользователя в базе
            $stmt = $pdo->prepare('SELECT id, password_hash, login FROM users WHERE login = ?');
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если пользователь найден и пароль верный
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // ПЕРЕПИСЫВАЕМ сессию заново (защита от фиксации)
                session_regenerate_id(true);

                // Записываем данные
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_login'] = $user['login'];

                // Редирект
                header('Location: index.php');
                exit;

            } else {
                $error = 'Неверный логин или пароль.';
            }
        }
    }
}

// 6. Подключаем шапку (в ней тоже должен быть session_start() в самом начале!)
include 'inc/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4>Вход в панель управления</h4></div>
            <div class="card-body">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" name="login" class="form-control" value="<?= htmlspecialchars($login ?? '') ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>