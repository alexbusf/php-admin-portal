<?php
session_start();

// Защита: если не залогинен - выкидываем
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../db.php';


$pageTitle = 'Смена пароля';
$currentPage = 'password.php';

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности страницы.';
    } else {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Валидация
        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Все поля обязательны для заполнения.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Новые пароли не совпадают.';
        } else {
            // Ищем текущего пользователя в базе по логину из сессии
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE login = ?');
            $stmt->execute([$_SESSION['admin_login']]);
            $user = $stmt->fetch();



            // Проверяем старый пароль (этот код пока не работает из-за exit выше)
            if ($user && password_verify($oldPassword, $user['password_hash'])) {
                
                // Старый верный - хешируем новый и обновляем
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE login = ?');
                
                if ($updateStmt->execute([$newHash, $_SESSION['admin_login']])) {
                    $success = 'Пароль успешно изменен!';
                } else {
                    $error = 'Ошибка базы данных при сохранении.';
                }

            } else {
                $error = 'Текущий (старый) пароль неверен.';
            }
        }
    }
}

include 'inc/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4>Смена пароля</h4></div>
            <div class="card-body">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Текущий пароль</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Повторите новый пароль</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сменить пароль</button>
                    </form>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>