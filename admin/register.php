<?php
// 1. Запускаем сессию САМЫМ ПЕРВЫМ делом, до любого вывода
session_start();

// 2. Подключаем базу данных (путь из папки admin в корень)
require_once '../db.php'; 

// 3. ГЕНЕРАЦИЯ CSRF-ТОКЕНА
// Если токена еще нет в сессии (первый заход на страницу), создаем его
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Переменные для админской шапки
$pageTitle = 'Регистрация';
$currentPage = 'register.php';

// Подключаем админскую шапку
include 'inc/header.php'; 

$error = '';
$success = '';

// 4. ЛОГИКА ОБРАБОТКИ ФОРМЫ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ЗАЩИТА ОТ CSRF ---
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности страницы. Обновите страницу и попробуйте снова.';
    } else {
        // Получаем и очищаем данные
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        // --- ВАЛИДАЦИЯ ДАННЫХ ---
        $isValid = true;

        if ($login === '' || $password === '') {
            $error = 'Логин и пароль не могут быть пустыми.';
            $isValid = false;
        } 
        // Проверка длины логина
        elseif (mb_strlen($login) < 3 || mb_strlen($login) > 50) {
            $error = 'Логин должен быть от 3 до 50 символов.';
            $isValid = false;
        }
        // Проверка символов в логине (только латиница, цифры, подчеркивание)
        elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
            $error = 'В логине разрешены только латинские буквы, цифры и знак подчеркивания.';
            $isValid = false;
        }
        // Проверка паролей
        elseif ($password !== $confirm) {
            $error = 'Пароли не совпадают.';
            $isValid = false;
        }

        // Если все проверки пройдены
        if ($isValid) {
            // Проверяем, нет ли такого логина в базе
            $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким логином уже существует.';
            } else {
                // Хешируем пароль
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Сохраняем в базу
                $stmt = $pdo->prepare('INSERT INTO users (login, password_hash) VALUES (?, ?)');
                
                if ($stmt->execute([$login, $passwordHash])) {
                    $success = 'Пользователь создан успешно! Теперь вы можете <a href="login.php">войти</a>.';
                    
                    // Для безопасности: можно уничтожить токен после успешного использования
                    unset($_SESSION['csrf_token']);
                } else {
                    $error = 'Ошибка базы данных при создании пользователя.';
                }
            }
        }
    }
}
?>

<!-- 5. HTML-ФОРМА -->
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Создать администратора</h4>
            </div>
            <div class="card-body">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php else: ?>
                    <!-- ВАЖНО: добавили enctype и скрытое поле с токеном -->
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" value="<?= htmlspecialchars($login ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Повторите пароль</label>
                            <input type="password" name="confirm" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<?php 
// Подключаем админский футер
include 'inc/footer.php'; 
?>